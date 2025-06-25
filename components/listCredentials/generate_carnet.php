<?php
session_start(); // Añadir esta línea al inicio
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateCarnet($numberId, $username = null) {
    global $conn;
    
    try {
        // Validar parámetros de entrada
        if (empty($numberId)) {
            throw new Exception('ID de estudiante requerido');
        }
        
        // Verificar si ya existe un carnet activo
        $checkSql = "SELECT * FROM carnet_records WHERE number_id = ? AND is_active = 1 ORDER BY generated_at DESC LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $numberId);
        $checkStmt->execute();
        $existingCarnet = $checkStmt->get_result()->fetch_assoc();
        
        // Si existe y el archivo aún existe, retornar la información del existente
        if ($existingCarnet && file_exists($existingCarnet['file_path'])) {
            // Si se solicita descarga
            if (isset($_GET['download']) && $_GET['download'] == '1') {
                $filename = $existingCarnet['file_name'];
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($existingCarnet['file_path']));
                readfile($existingCarnet['file_path']);
                exit;
            }
            
            // Retornar información del carnet existente
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'exists' => true,
                'file_path' => $existingCarnet['file_path'],
                'file_name' => $existingCarnet['file_name'],
                'generated_at' => $existingCarnet['generated_at'],
                'message' => 'El carnet ya existe'
            ]);
            return;
        }
        
        // Obtener datos del estudiante
        $sql = "SELECT g.*, ur.*, g.department as group_department FROM groups g 
                LEFT JOIN user_register ur ON g.number_id = ur.number_id 
                WHERE g.number_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error en la consulta de base de datos: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $numberId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception('Estudiante no encontrado con ID: ' . $numberId);
        }
        
        $data = $result->fetch_assoc();
        
        // Validar datos esenciales
        if (empty($data['full_name'])) {
            throw new Exception('Datos incompletos del estudiante');
        }
        
        // CAMBIO PARA PRODUCCIÓN: Detectar automáticamente el entorno
        $isProduction = strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
        
        if ($isProduction) {
            // Rutas para producción
            $carnetDir = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/carnets/';
            $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/';
        } else {
            // Rutas para desarrollo local
            $carnetDir = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/carnets/';
            $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/';
        }
        
        // Crear directorio de carnets si no existe
        if (!is_dir($carnetDir)) {
            if (!mkdir($carnetDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de carnets');
            }
        }
        
        // Función para convertir imagen a base64
        function imgToBase64($path) {
            if (!file_exists($path)) {
                throw new Exception('Archivo de imagen no encontrado: ' . $path);
            }
            
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            
            if ($data === false) {
                throw new Exception('Error al leer archivo de imagen: ' . $path);
            }
            
            return 'data:image/' . $type . ';base64,' . base64_encode($data);
        }
        
        // Obtener imágenes en base64
        $fondoCarnet = imgToBase64($rootPath . 'img/fondo_carnet.png');
        $headerCarnet = imgToBase64($rootPath . 'img/header_carnet.png');
        $footerCarnet = imgToBase64($rootPath . 'img/footer_carnet.png');
        
        // Generar URL del código QR
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=https://dashboard.utinnova.co/studentInfo.php?id=' . urlencode($data['number_id']) . '&size=400x400';
        
        // Configurar dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        
        // Separar nombres y apellidos
        $fullNameParts = explode(' ', trim($data['full_name']));
        $nombres = isset($fullNameParts[0]) && isset($fullNameParts[1]) ? $fullNameParts[0] . ' ' . $fullNameParts[1] : $fullNameParts[0];
        $apellidos = count($fullNameParts) > 2 ? implode(' ', array_slice($fullNameParts, 2)) : (isset($fullNameParts[1]) ? $fullNameParts[1] : '');
        
        // HTML del carnet (mismo código anterior)
        $html = '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <style>
                @page {
                    margin: 0;
                    size: letter;
                }
                body {
                    margin: 0;
                    padding: 0;
                    font-family: Arial, sans-serif;
                    width: 21.59cm;
                    height: 27.94cm;
                    position: relative;
                    background-image: url("' . $fondoCarnet . '");
                    background-size: cover;
                    background-repeat: no-repeat;
                    background-position: center;
                }
                .carnet-container {
                    width: 100%;
                    height: 100%;
                    position: relative;
                    display: flex;
                    flex-direction: column;
                    max-height: 27.94cm;
                    overflow: hidden;
                }
                .header {
                    height: 3cm;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0.3cm;
                    text-align: center;
                }
                .header img {
                    height: 2.5cm;
                    width: auto;
                    object-fit: contain;
                    margin: 0 auto;
                    display: block;
                    position: relative;
                    left: 10%;
                    transform: translateX(-10%);
                }
                .content {
                    flex: 1;
                    display: flex;
                    flex-direction: column;
                    padding: 0.8cm;
                    align-items: center;
                    justify-content: center;
                    max-height: 20cm;
                }
                .photo-section {
                    width: 100%;
                    height: 7cm;
                    margin-bottom: 0.5cm;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                .qr-code {
                    width: 6cm;
                    height: 6cm;
                    background-color: transparent;
                    border-radius: 0;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto;
                    padding: 0;
                }
                .qr-code img {
                    width: 100%;
                    height: 100%;
                    object-fit: contain;
                    border-radius: 0;
                }
                .info-section {
                    width: 100%;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    gap: 0.3cm;
                }
                .student-badge {
                    background-color: #4FC3D7;
                    color: #30336b;
                    padding: 0.3cm 1cm;
                    border-radius: 25px;
                    font-size: 18px;
                    font-weight: bold;
                    text-align: center;
                    margin: 0 auto 0.3cm auto;
                    max-width: 120px;
                }
                .field {
                    margin-bottom: 0.3cm;
                    width: 100%;
                    max-width: 15cm;
                    display: flex;
                    flex-direction: column;
                    align-items: center;
                    justify-content: center;
                    margin-left: auto;
                    margin-right: auto;
                    text-align: center;
                }
                .field-label {
                    font-size: 16px;
                    color: #d4d7ff;
                    font-weight: bold;
                    margin-bottom: 0.1cm;
                    text-align: center;
                    width: 100%;
                }
                .field-value {
                    font-size: 18px;
                    color: #333;
                    font-weight: bold;
                    background-color: #ffffff;
                    padding: 0.2cm;
                    border-radius: 15px;
                    word-wrap: break-word;
                    line-height: 1.2;
                    text-align: center;
                    width: fit-content;
                    min-width: 8cm;
                    margin: 0 auto;
                    display: block;
                }
                .footer {
                    height: 3cm;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    padding: 0.2cm;
                }
                .footer img {
                    height: 4.8cm;
                    width: auto;
                    object-fit: contain;
                    position: absolute;
                    bottom: -0.5cm;
                    left: 50%;
                    transform: translateX(-50%);
                    padding: 0.2cm;
                }
                .disclaimer {
                    position: absolute;
                    bottom: 3cm;
                    left: 2.1cm;
                    right: 2.1cm;
                    font-size:20px;
                    color: #d3d3d3;
                    text-align: center;
                    line-height: 1.2;
                    padding: 0.2cm;
                    border-radius: 8px;
                }
            </style>
        </head>
        <body>
            <div class="carnet-container">
                <div class="header">
                    <img src="' . $headerCarnet . '" alt="Header">
                </div>
                
                <div class="content">
                    <div class="photo-section">
                        <div class="qr-code">
                            <img src="' . $qrUrl . '" alt="Código QR del estudiante">
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="student-badge">Estudiante</div>
                        
                        <div class="field">
                            <div class="field-label">Nombres</div>
                            <div class="field-value">' . htmlspecialchars(mb_strtoupper($nombres, 'UTF-8')) . '</div>
                        </div>
                        
                        <div class="field">
                            <div class="field-label">Apellidos</div>
                            <div class="field-value">' . htmlspecialchars(mb_strtoupper($apellidos, 'UTF-8')) . '</div>
                        </div>
                        
                        <div class="field">
                            <div class="field-label">Documento</div>
                            <div class="field-value">' . htmlspecialchars($data['number_id']) . '</div>
                        </div>
                        
                        <div class="field" style="margin-bottom: 1.5cm;">
                            <div class="field-label">Curso</div>
                            <div class="field-value">' . htmlspecialchars(strtoupper($data['program'])) . '</div>
                        </div>
                    </div>
                    
                    <div class="disclaimer">
                        Este carné acredita al portador como estudiante del programa TalentoTech; es personal e intrasferible y se requiere su presentación para solicitar cualquier servicio y debe ser portado en un lugar visible durante su estadía en la entidad. En caso de pérdida o daño, se deberá informar de inmediato.
                    </div>
                </div>
                
                <div class="footer">
                    <img src="' . $footerCarnet . '" alt="Footer">
                </div>
            </div>
        </body>
        </html>';
        
        $dompdf->loadHtml($html);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        
        // Generar nombre del archivo y ruta
        $filename = 'carnet_' . $data['number_id'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $filePath = $carnetDir . $filename;
        
        // Guardar el PDF en el servidor
        $pdfContent = $dompdf->output();
        if (file_put_contents($filePath, $pdfContent) === false) {
            throw new Exception('No se pudo guardar el archivo PDF');
        }
        
        // Desactivar carnets anteriores para este estudiante
        $deactivateSql = "UPDATE carnet_records SET is_active = 0 WHERE number_id = ?";
        $deactivateStmt = $conn->prepare($deactivateSql);
        $deactivateStmt->bind_param("s", $numberId);
        $deactivateStmt->execute();
        
        // Registrar el nuevo carnet en la base de datos
        $insertSql = "INSERT INTO carnet_records (number_id, file_path, file_name, generated_by) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ssss", $numberId, $filePath, $filename, $username);
        $insertStmt->execute();
        
        // Si se solicita descarga directa
        if (isset($_GET['download']) && $_GET['download'] == '1') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
        
        // Retornar información del carnet generado
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'exists' => false,
            'file_path' => $filePath,
            'file_name' => $filename,
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => $username,
            'message' => 'Carnet generado exitosamente'
        ]);
        
        return true;
        
    } catch (Exception $e) {
        // Log del error
        error_log("Error generando carnet para ID {$numberId}: " . $e->getMessage());
        
        // Retornar error en formato JSON
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage()
        ]);
        return false;
    }
}

// Verificar si se solicita generar un carnet
if (isset($_GET['generate_carnet']) && isset($_GET['number_id'])) {
    $numberId = $_GET['number_id'];
    // Obtener el username de la sesión
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Sistema';
    generateCarnet($numberId, $username);
}
?>