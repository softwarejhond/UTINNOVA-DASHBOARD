<?php
// Configurar manejo de errores para debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar content-type como JSON desde el inicio
header('Content-Type: application/json');

// Buffer de salida para capturar cualquier output inesperado
ob_start();

// Cargar dependencias al inicio
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use Dompdf\Dompdf;
use Dompdf\Options;

session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Acceso no autorizado']);
    exit;
}

require __DIR__ . '/../filters/takeUser.php';
$infoUsuario = obtenerInformacionUsuario();
$rol = $infoUsuario['rol'];

if ($rol !== 'Mentor') {
    ob_clean();
    echo json_encode(['success' => false, 'error' => 'Acceso denegado: Solo para Mentores']);
    exit;
}

$user_id = intval($_SESSION['username']);

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Verificar conexión a la base de datos
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Obtener nombre del usuario
    $sql_user = "SELECT nombre FROM users WHERE username = ?";
    $stmt_user = $conn->prepare($sql_user);
    $stmt_user->bind_param("i", $user_id);
    $stmt_user->execute();
    $result_user = $stmt_user->get_result();
    $user_data = $result_user->fetch_assoc();
    $stmt_user->close();
    if (!$user_data) {
        throw new Exception('Usuario no encontrado');
    }
    $user_name = $user_data['nombre'];
    $user_username = $_SESSION['username']; // El username es el id numérico

    // Obtener mentorías del mes actual, autorizadas, del usuario
    $current_month = date('Y-m');
    $sql = "SELECT * FROM qr_mentorias 
            WHERE created_by = ? 
            AND authorized = 1 
            AND DATE_FORMAT(created_at, '%Y-%m') = ? 
            ORDER BY created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $user_id, $current_month);
    $stmt->execute();
    $result = $stmt->get_result();
    $mentorias = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($mentorias)) {
        throw new Exception('No hay mentorías autorizadas este mes');
    }

    // Verificar si DOMPDF está disponible
    if (!class_exists('Dompdf\Dompdf')) {
        throw new Exception('DOMPDF no está disponible. Verifica la instalación de Composer.');
    }

    // Detectar entorno y configurar rutas
    $isProduction = (strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
    $baseDir = $isProduction ? '/dashboard/' : '/UTINNOVA-DASHBOARD/';
    $pdfFolder = $_SERVER['DOCUMENT_ROOT'] . $baseDir . 'facturas/';

    // Crear directorio si no existe
    if (!is_dir($pdfFolder)) {
        if (!mkdir($pdfFolder, 0755, true)) {
            throw new Exception('No se pudo crear el directorio para PDFs: ' . $pdfFolder);
        }
    }

    // Configurar DOMPDF
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('chroot', $_SERVER['DOCUMENT_ROOT'] . $baseDir);
    $options->set('debugKeepTemp', false);
    $options->set('isPhpEnabled', false);

    $dompdf = new Dompdf($options);
    $dompdf->setPaper('letter', 'portrait');

    // Función para obtener mes en español
    function mesEnEspanol($mes) {
        $meses = [
            'January' => 'Enero',
            'February' => 'Febrero',
            'March' => 'Marzo',
            'April' => 'Abril',
            'May' => 'Mayo',
            'June' => 'Junio',
            'July' => 'Julio',
            'August' => 'Agosto',
            'September' => 'Septiembre',
            'October' => 'Octubre',
            'November' => 'Noviembre',
            'December' => 'Diciembre'
        ];
        return $meses[$mes] ?? $mes;
    }

    $mes_actual = mesEnEspanol(date('F')) . ' ' . date('Y');

    // Generar HTML
    $html = '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Mentorías impartidas - ' . $mes_actual . '</title>
        <style>
            @page { 
                margin: 0.75in; 
                size: letter portrait;
            }
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                line-height: 1.4;
                color: #333;
                padding: 20px;
            }
            .header {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #30336b;
                padding-bottom: 10px;
            }
            .logo {
                width: 100px;
                height: auto;
                margin-right: 20px;
            }
            .contact-info {
                flex: 1;
                text-align: right;
                font-size: 10px;
            }
            .user-info {
                border: 1px solid #ddd;
                padding: 10px;
                margin-bottom: 20px;
                background-color: #f8f9fa;
                font-size: 11px;
            }
            .title {
                text-align: center;
                font-size: 18px;
                font-weight: bold;
                margin: 20px 0;
                color: #30336b;
            }
            .table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }
            .table th, .table td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            .table th {
                background-color: #f8f9fa;
                font-weight: bold;
            }
            .table tbody tr:nth-child(even) {
                background-color: #f8f9fa;
            }
            /* Estilos del footer */
            @font-face {
                font-family: "Sparose";
                src: url("fonts/fonnts.com-Sparose.ttf") format("truetype");
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
            .footer {
                margin-top: 40px;
                padding: 15px 0;
                text-align: center;
                border-top: 1px solid #ddd;
                background-color: #f8f9fa;
                font-size: 10px;
                font-family: "Sparose", Arial, sans-serif !important;
                color: #30336b !important;
            }
            .text-indigo-dark {
                color: #30336b !important;
            }
            .text-lime-dark {
                color: #32CD32 !important;
            }
        </style>
    </head>
    <body>
        <div class="header">
            <img src="' . $_SERVER['DOCUMENT_ROOT'] . $baseDir . 'img/uttInnova.png" alt="Logo" class="logo">
            <div class="contact-info">
                <strong>Servicio al Cliente:</strong> servicioalcliente.ut2@cendi.edu.co<br>
                <strong>Teléfono:</strong> 3125410929<br>
                <strong>NIT:</strong> 900.236.118-9 Corporación CENDI
            </div>
        </div>
        
        <div class="user-info">
            <strong>Información del Mentor:</strong><br>
            <strong>Nombre:</strong> ' . htmlspecialchars($user_name) . '<br>
            <strong>Cédula/Username:</strong> ' . htmlspecialchars($user_username) . '
        </div>
        
        <h1 class="title">Mentorías impartidas - ' . $mes_actual . '</h1>
        
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Título de la Mentoría</th>
                    <th>Curso</th>
                    <th>Fecha mentoria</th>
                </tr>
            </thead>
            <tbody>';

    $contador = 1;
    foreach ($mentorias as $mentoria) {
        // Parsear título para obtener curso
        $title_parts = explode(', ', $mentoria['title']);
        $curso = str_replace('Mentoría ', '', $title_parts[0]);
        $fecha = date('d/m/Y', strtotime($mentoria['created_at']));
        
        $html .= '
                <tr>
                    <td>' . $contador . '</td>
                    <td>' . htmlspecialchars($mentoria['title']) . '</td>
                    <td>' . htmlspecialchars($curso) . '</td>
                    <td>' . $fecha . '</td>
                </tr>';
        $contador++;
    }

    // Obtener información de la empresa
    $queryCompany = mysqli_query($conn, "SELECT nombre,nit FROM company");
    $empresa = '';
    while ($empresaLog = mysqli_fetch_array($queryCompany)) {
        $empresa = $empresaLog['nombre'];
    }

    $html .= '
            </tbody>
        </table>
        
        <!-- Footer -->
        <footer class="footer">
            <div class="text-center">
                <b>SYGNIA</b> &copy; Copyright ' . date("Y") . ' Todos los derechos de uso para <span class="text-lime-dark"><b>' . $empresa . '</b></span> |
                Eagle Software
            </div>
        </footer>
    </body>
    </html>';

    // Generar PDF
    $dompdf->loadHtml($html);
    $dompdf->render();
    $pdfOutput = $dompdf->output();

    // Guardar PDF
    $filename = "factura_mentorias_{$user_id}_" . date('Y-m') . ".pdf";
    $pdfFile = $pdfFolder . $filename;
    if (file_put_contents($pdfFile, $pdfOutput) === false) {
        throw new Exception('Error al guardar el archivo PDF en: ' . $pdfFile);
    }

    // Limpiar buffer antes de respuesta JSON
    ob_clean();

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'pdf' => "facturas/{$filename}"
    ]);

} catch (Exception $e) {
    // Limpiar buffer en caso de error
    ob_clean();

    // Log del error
    error_log("Error en factura_mentorias.php: " . $e->getMessage());

    // Respuesta de error en JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} catch (Error $e) {
    // Limpiar buffer en caso de error fatal
    ob_clean();

    error_log("Error fatal en factura_mentorias.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}

// Asegurar limpieza final del buffer
ob_end_flush();
?>