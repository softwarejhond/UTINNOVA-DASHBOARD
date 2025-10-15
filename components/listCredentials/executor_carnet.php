<?php
session_start();
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

function generateExecutorCarnet($username, $generatedBy = null) {
    global $conn;
    
    try {
        if (empty($username)) {
            throw new Exception('Username de ejecutor requerido');
        }
        
        // Verificar si ya existe un carnet activo
        $checkSql = "SELECT * FROM carnet_records WHERE number_id = ? AND is_active = 1 ORDER BY generated_at DESC LIMIT 1";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("s", $username);
        $checkStmt->execute();
        $existingCarnet = $checkStmt->get_result()->fetch_assoc();
        
        if ($existingCarnet && file_exists($existingCarnet['file_path'])) {
            if (isset($_GET['download']) && $_GET['download'] == '1') {
                $filename = $existingCarnet['file_name'];
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '"');
                header('Content-Length: ' . filesize($existingCarnet['file_path']));
                readfile($existingCarnet['file_path']);
                exit;
            }
            
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
        
        // Obtener datos del ejecutor y su sede
        $sql = "SELECT u.*, eh.headquarter,
                       CASE 
                           WHEN u.rol = 5 THEN 'Docente'
                           WHEN u.rol = 7 THEN 'Monitor'
                           WHEN u.rol = 8 THEN 'Mentor'
                           ELSE 'Ejecutor'
                       END as rol_nombre
                FROM users u 
                LEFT JOIN executor_headquarters eh ON u.username = eh.username
                WHERE u.username = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Error en la consulta de base de datos: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception('Ejecutor no encontrado con username: ' . $username);
        }
        
        $data = $result->fetch_assoc();
        
        if (empty($data['nombre'])) {
            throw new Exception('Datos incompletos del ejecutor');
        }
        
        if (empty($data['headquarter'])) {
            throw new Exception('El ejecutor no tiene una sede asignada');
        }
        
        // Detectar entorno
        $isProduction = strpos($_SERVER['HTTP_HOST'], 'localhost') === false && strpos($_SERVER['HTTP_HOST'], '127.0.0.1') === false;
        
        if ($isProduction) {
            $carnetDir = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/carnets/';
            $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/dashboard/';
        } else {
            $carnetDir = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/carnets/';
            $rootPath = $_SERVER['DOCUMENT_ROOT'] . '/UTINNOVA-DASHBOARD/';
        }
        
        if (!is_dir($carnetDir)) {
            if (!mkdir($carnetDir, 0755, true)) {
                throw new Exception('No se pudo crear el directorio de carnets');
            }
        }
        
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
        
        $fondoCarnet = imgToBase64($rootPath . 'img/fondo_carnet.png');
        $headerCarnet = imgToBase64($rootPath . 'img/header_carnet.png');
        $footerCarnet = imgToBase64($rootPath . 'img/footer_carnet.png');
        
        // Generar URL del código QR para ejecutor
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=https://dashboard.utinnova.co/executorInfo.php?id=' . urlencode($data['username']) . '&size=400x400';
        
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);
        
        // Separar nombres y apellidos del nombre completo
        $fullNameParts = explode(' ', trim($data['nombre']));
        $nombres = isset($fullNameParts[0]) && isset($fullNameParts[1]) ? $fullNameParts[0] . ' ' . $fullNameParts[1] : $fullNameParts[0];
        $apellidos = count($fullNameParts) > 2 ? implode(' ', array_slice($fullNameParts, 2)) : (isset($fullNameParts[1]) ? $fullNameParts[1] : '');
        
        // Determinar color del badge según el rol
        $badgeColor = '#4FC3D7'; // Color por defecto
        $badgeTextColor = '#30336b';
        switch($data['rol']) {
            case 5: // Docente
                $badgeColor = '#007bff'; // Azul
                $badgeTextColor = '#ffffff';
                break;
            case 7: // Monitor
                $badgeColor = '#ffc107'; // Amarillo
                $badgeTextColor = '#212529';
                break;
            case 8: // Mentor
                $badgeColor = '#17a2b8'; // Cian
                $badgeTextColor = '#ffffff';
                break;
        }
        
        // Adaptar disclaimer según el rol
        $rolNombreForDisclaimer = strtolower($data['rol_nombre']);
        
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
                .executor-badge {
                    background-color: ' . $badgeColor . ';
                    color: ' . $badgeTextColor . ';
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
                    height: 3.6cm;
                    width: auto;
                    object-fit: contain;
                    position: absolute;
                    bottom: 0.2cm;
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
                            <img src="' . $qrUrl . '" alt="Código QR del ejecutor">
                        </div>
                    </div>
                    
                    <div class="info-section">
                        <div class="executor-badge">' . htmlspecialchars($data['rol_nombre']) . '</div>
                        
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
                            <div class="field-value">' . htmlspecialchars($data['username']) . '</div>
                        </div>
                        
                        <div class="field" style="margin-bottom: 1.5cm;">
                            <div class="field-label">Sede</div>
                            <div class="field-value">' . htmlspecialchars(strtoupper($data['headquarter'])) . '</div>
                        </div>
                    </div>
                    
                    <div class="disclaimer">
                        Este carné acredita al portador como ' . $rolNombreForDisclaimer . ' del programa TalentoTech; es personal e intrasferible y se requiere su presentación para solicitar cualquier servicio y debe ser portado en un lugar visible durante su estadía en la entidad. En caso de pérdida o daño, se deberá informar de inmediato.
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
        
        $filename = 'carnet_executor_' . $data['username'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        $filePath = $carnetDir . $filename;
        
        $pdfContent = $dompdf->output();
        if (file_put_contents($filePath, $pdfContent) === false) {
            throw new Exception('No se pudo guardar el archivo PDF');
        }
        
        // Desactivar carnets anteriores
        $deactivateSql = "UPDATE carnet_records SET is_active = 0 WHERE number_id = ?";
        $deactivateStmt = $conn->prepare($deactivateSql);
        $deactivateStmt->bind_param("s", $username);
        $deactivateStmt->execute();
        
        // Registrar el nuevo carnet
        $insertSql = "INSERT INTO carnet_records (number_id, file_path, file_name, generated_by) VALUES (?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertSql);
        $insertStmt->bind_param("ssss", $username, $filePath, $filename, $generatedBy);
        $insertStmt->execute();
        
        if (isset($_GET['download']) && $_GET['download'] == '1') {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'exists' => false,
            'file_path' => $filePath,
            'file_name' => $filename,
            'generated_at' => date('Y-m-d H:i:s'),
            'generated_by' => $generatedBy,
            'message' => 'Carnet generado exitosamente'
        ]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Error generando carnet para username {$username}: " . $e->getMessage());
        
        http_response_code(500);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => true,
            'message' => $e->getMessage()
        ]);
        return false;
    }
}

if (isset($_GET['generate_carnet']) && isset($_GET['number_id'])) {
    $username = $_GET['number_id'];
    $generatedBy = isset($_SESSION['username']) ? $_SESSION['username'] : 'Sistema';
    generateExecutorCarnet($username, $generatedBy);
}
?>