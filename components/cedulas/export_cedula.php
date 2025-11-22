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

try {
    // Verificar que sea una petición POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método no permitido');
    }

    // Debug: log de datos recibidos
    error_log("POST data received: " . print_r($_POST, true));

    // Verificar datos requeridos
    $required_fields = ['number_id', 'front_image', 'back_image'];
    foreach ($required_fields as $field) {
        if (!isset($_POST[$field])) {
            throw new Exception("Campo requerido faltante: {$field}");
        }
        if (empty($_POST[$field]) && $_POST[$field] !== '0') {
            throw new Exception("Campo requerido vacío: {$field}");
        }
    }

    // Validar number_id
    $number_id = filter_var($_POST['number_id'], FILTER_VALIDATE_INT);
    if ($number_id === false) {
        throw new Exception('ID de número inválido: ' . $_POST['number_id']);
    }

    error_log("Processing PDF for number_id: " . $number_id);

    $front_image = $_POST['front_image'];
    $back_image = $_POST['back_image'];

    // Detectar entorno y configurar rutas
    $isProduction = (strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
    $baseDir = $isProduction ? '/dashboard/' : '/UTINNOVA-DASHBOARD/';
    $pdfFolder = $_SERVER['DOCUMENT_ROOT'] . $baseDir . 'cedulas/';
    $pdfFile = $pdfFolder . "cedula_{$number_id}.pdf";

    error_log("PDF will be saved to: " . $pdfFile);

    // Verificar si el PDF ya existe
    if (file_exists($pdfFile)) {
        ob_clean(); // Limpiar buffer antes de salida JSON
        echo json_encode([
            'success' => true,
            'message' => 'Ya existe el PDF',
            'pdf' => "cedulas/cedula_{$number_id}.pdf"
        ]);
        exit;
    }

    // Verificar si DOMPDF está disponible
    if (!class_exists('Dompdf\Dompdf')) {
        throw new Exception('DOMPDF no está disponible. Verifica la instalación de Composer.');
    }

    error_log("DOMPDF classes loaded");

    // Crear directorio si no existe
    if (!is_dir($pdfFolder)) {
        error_log("Creating directory: " . $pdfFolder);
        if (!mkdir($pdfFolder, 0755, true)) {
            throw new Exception('No se pudo crear el directorio para PDFs: ' . $pdfFolder);
        }
    }

    // Configurar DOMPDF con opciones más conservadoras
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true); // Permite cargar imágenes desde URLs (para front/back)
    $options->set('chroot', $_SERVER['DOCUMENT_ROOT'] . $baseDir); // Directorio base para seguridad
    $options->set('debugKeepTemp', false);
    $options->set('isPhpEnabled', false); // Seguridad

    error_log("DOMPDF options configured");

    // Rutas locales absolutas para las imágenes de header y footer
    $headerImagePath = $_SERVER['DOCUMENT_ROOT'] . $baseDir . 'img/header_documentos.png';
    $footerImagePath = $_SERVER['DOCUMENT_ROOT'] . $baseDir . 'img/footer_documentos.png';

    // Verificar que las imágenes de header/footer existan
    if (!file_exists($headerImagePath)) {
        throw new Exception('No se encuentra la imagen del header: ' . $headerImagePath);
    }
    if (!file_exists($footerImagePath)) {
        throw new Exception('No se encuentra la imagen del footer: ' . $footerImagePath);
    }

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page { 
                margin: 0; 
                size: letter portrait;
            }
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            }
            body {
                font-family: Arial, sans-serif;
                width: 8.5in;
                height: 11in;
                position: relative;
                overflow: hidden;
            }
            .header-img {
                width: 100%;
                height: 100%;
                display: block;
                max-height: 170px;
                margin-top: 50px;
            }
            .footer-img {
                width: 100%;
                height: auto;
                max-height: 70px;
                object-fit: contain;
                position: absolute;
                bottom: 0;
                left: 0;
            }
            .contenido {
                position: absolute;
                top: 180px;
                left: 30px;
                right: 30px;
                bottom: 65px;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                padding: 20px;
            }
            .imagenes-container {
                width: 100%;
                max-width: 7in;
                display: flex;
                flex-direction: column;
                gap: 15px;
                align-items: center;
            }
            .img-block {
                width: 100%;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 8px;
            }
            .img-container {
                width: 95%;
                height: 3.5in;
                display: flex;
                justify-content: center;
                align-items: center;
                border: 1px solid #ddd;
                border-radius: 8px;
                background: #fff;
                overflow: hidden;
                margin: 0 auto 10px auto;
                position: relative;
                box-sizing: border-box;
            }
            .doc-img {
                max-width: 100%;
                max-height: 100%;
                object-fit: contain;
                display: block;
                margin: auto;
            }
        </style>
    </head>
    <body>
        <img src="' . $headerImagePath . '" alt="Header" class="header-img">
        
        <div class="contenido">
            <div class="imagenes-container">
                <div class="img-block">
                    <div class="img-container">
                        <img src="' . htmlspecialchars($front_image) . '" class="doc-img">
                    </div>
                </div>
                
                <div class="img-block">
                    <div class="img-container">
                        <img src="' . htmlspecialchars($back_image) . '" class="doc-img">
                    </div>
                </div>
            </div>
        </div>
        
        <img src="' . $footerImagePath . '" alt="Footer" class="footer-img">
    </body>
    </html>
    ';

    error_log("HTML generated, length: " . strlen($html));

    // Generar PDF
    try {
        $dompdf = new Dompdf($options);
        error_log("DOMPDF instance created");

        $dompdf->setPaper('letter', 'portrait');
        error_log("Paper size set");

        $dompdf->loadHtml($html);
        error_log("HTML loaded");

        $dompdf->render();
        error_log("PDF rendered");

        $pdfOutput = $dompdf->output();
        error_log("PDF output generated, size: " . strlen($pdfOutput) . " bytes");
    } catch (Exception $pdfError) {
        throw new Exception('Error generando PDF: ' . $pdfError->getMessage());
    }

    // Guardar PDF
    if (file_put_contents($pdfFile, $pdfOutput) === false) {
        throw new Exception('Error al guardar el archivo PDF en: ' . $pdfFile);
    }

    error_log("PDF saved successfully to: " . $pdfFile);

    // Registrar en la base de datos si la conexión existe
    if (isset($conn) && $conn) {
        try {
            $pdfRelativePath = "cedulas/cedula_{$number_id}.pdf";
            $stmt = $conn->prepare("INSERT INTO cedulas_pdf (number_id, pdf_path, created_at) VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE pdf_path=VALUES(pdf_path), created_at=NOW()");

            if ($stmt) {
                $stmt->bind_param("is", $number_id, $pdfRelativePath);
                if (!$stmt->execute()) {
                    error_log("Error al insertar en BD: " . $stmt->error);
                }
                $stmt->close();
                error_log("Database record updated");
            }
        } catch (Exception $dbError) {
            error_log("Database error (non-critical): " . $dbError->getMessage());
        }
    }

    // Limpiar buffer antes de respuesta JSON
    ob_clean();

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'PDF generado correctamente',
        'pdf' => "cedulas/cedula_{$number_id}.pdf"
    ]);
} catch (Exception $e) {
    // Limpiar buffer en caso de error
    ob_clean();

    // Log del error para debugging
    error_log("Error en export_cedula.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    // Respuesta de error en JSON
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ]);
} catch (Error $e) {
    // Limpiar buffer en caso de error fatal
    ob_clean();

    // Para errores fatales de PHP
    error_log("Error fatal en export_cedula.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ]);
}

// Asegurar limpieza final del buffer
ob_end_flush();
?>
