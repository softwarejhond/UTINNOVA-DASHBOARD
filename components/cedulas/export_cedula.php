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
    $baseDir = $isProduction ? '/dashboard/' : '/DASBOARD-ADMIN-MINTICS/';
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

    // Función para aplicar transformaciones CSS
    function imgTransformStyle($transform)
    {
        $rotation = isset($transform['rotation']) ? (float)$transform['rotation'] : 0;
        $scale = isset($transform['scale']) ? (float)$transform['scale'] : 1;
        $offsetX = isset($transform['offsetX']) ? (float)$transform['offsetX'] : 0;
        $offsetY = isset($transform['offsetY']) ? (float)$transform['offsetY'] : 0;

        return "transform: rotate({$rotation}deg) scale({$scale}) translate({$offsetX}px, {$offsetY}px);";
    }

    // Solo aplicar rotación en el PDF
    function imgRotationStyle($transform)
    {
        $rotation = isset($transform['rotation']) ? (float)$transform['rotation'] : 0;
        return "transform: rotate({$rotation}deg);";
    }

    // Validar que las imágenes existan - versión simplificada
    function validateImagePath($imagePath)
    {
        error_log("Validating image: " . $imagePath);

        // Si es una URL completa (http/https), la dejamos pasar
        if (filter_var($imagePath, FILTER_VALIDATE_URL)) {
            error_log("Image is URL, accepting: " . $imagePath);
            return $imagePath;
        }

        // Para rutas relativas, intentar diferentes combinaciones
        $possiblePaths = [
            $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($imagePath, '/'),
            $_SERVER['DOCUMENT_ROOT'] . $imagePath,
            dirname(__FILE__) . '/../../' . ltrim($imagePath, '/')
        ];

        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                error_log("Image found at: " . $path);
                return $imagePath; // Retornamos la ruta original
            }
        }

        // Si no encontramos el archivo, log pero no fallar inmediatamente
        error_log("Warning: Image not found in filesystem: " . $imagePath);
        return $imagePath; // Dejamos que DOMPDF maneje la imagen
    }

    try {
        $front_image = validateImagePath($front_image);
        $back_image = validateImagePath($back_image);
        error_log("Image paths validated");
    } catch (Exception $imgError) {
        error_log("Image validation error: " . $imgError->getMessage());
        // Continuamos, quizás DOMPDF pueda manejar las rutas
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
    $options->set('isRemoteEnabled', true);
    $options->set('debugKeepTemp', false);
    $options->set('isPhpEnabled', false); // Seguridad

    error_log("DOMPDF options configured");

    // Generar HTML más simple para el PDF
    $frontRotationCSS = imgRotationStyle($front_transform);
    $backRotationCSS = imgRotationStyle($back_transform);

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
                max-height: 200px;
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
                top: 150px;
                left: 30px;
                right: 0;
                bottom: 75px;
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
            .label {
                font-weight: bold;
                font-size: 14px;
                text-align: center;
                color: #333;
                margin-bottom: 5px;
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
                width: auto;
                height: 100%;
                object-fit: cover;
                display: block;
                margin: auto;
                position: absolute;
                top: 0; left: 0; bottom: 0; right: 0;
            }
        </style>
    </head>
    <body>
        <img src="https://dashboard.uttalento.co/dashboard/img/header_documento.png" alt="Header" class="header-img">
        
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
        
        <img src="https://dashboard.uttalento.co/img/InscripcionCajica_footer.jpg" alt="Footer" class="footer-img">
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

    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor: ' . $e->getMessage(),
        'file' => basename(__FILE__),
        'line' => $e->getLine()
    ]);
}

// Asegurar limpieza final del buffer
ob_end_flush();
