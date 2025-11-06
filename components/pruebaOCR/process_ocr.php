<?php
require __DIR__ . '/../../vendor/autoload.php';

use thiagoalessio\TesseractOCR\TesseractOCR;

// Configuración para producción
set_time_limit(60);
ini_set('memory_limit', '512M');

header('Content-Type: application/json');

$result = [
    'status' => '',
    'message' => '',
    'ocr_text' => '',
    'processing_time' => 0,
    'file_info' => [],
    'errors' => []
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $startTime = microtime(true);
    
    try {
        // Validar archivo subido
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error al subir el archivo: ' . $_FILES['image']['error']);
        }
        
        $uploadedFile = $_FILES['image'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!in_array($uploadedFile['type'], $allowedTypes)) {
            throw new Exception('Tipo de archivo no permitido. Solo se permiten: JPG, PNG, GIF, WEBP');
        }
        
        if ($uploadedFile['size'] > 10 * 1024 * 1024) { // 10MB máximo
            throw new Exception('El archivo es demasiado grande. Máximo 10MB permitido');
        }
        
        // Información del archivo
        $result['file_info'] = [
            'name' => $uploadedFile['name'],
            'size' => round($uploadedFile['size'] / 1024, 2) . ' KB',
            'type' => $uploadedFile['type'],
            'tmp_name' => $uploadedFile['tmp_name']
        ];
        
        // Crear archivo temporal con nombre único
        $tempDir = sys_get_temp_dir();
        $extension = pathinfo($uploadedFile['name'], PATHINFO_EXTENSION);
        $tempFile = $tempDir . '/ocr_test_' . uniqid() . '.' . $extension;
        
        if (!move_uploaded_file($uploadedFile['tmp_name'], $tempFile)) {
            throw new Exception('Error al mover el archivo temporal');
        }
        
        // Verificar que el archivo temporal existe y es legible
        if (!file_exists($tempFile) || !is_readable($tempFile)) {
            throw new Exception('El archivo temporal no es accesible');
        }
        
        // Ejecutar OCR con múltiples configuraciones
        $ocrResults = ejecutarOCRLinux($tempFile);
        
        if (empty($ocrResults['text'])) {
            $result['status'] = 'warning';
            $result['message'] = 'No se pudo extraer texto de la imagen';
            $result['ocr_text'] = 'Sin texto detectado';
            $result['errors'] = $ocrResults['errors'];
        } else {
            $result['status'] = 'success';
            $result['message'] = 'OCR procesado exitosamente con configuración: ' . $ocrResults['config_used'];
            $result['ocr_text'] = $ocrResults['text'];
        }
        
        // Limpiar archivo temporal
        if (file_exists($tempFile)) {
            unlink($tempFile);
        }
        
    } catch (Exception $e) {
        $result['status'] = 'error';
        $result['message'] = 'Error: ' . $e->getMessage();
        $result['errors'][] = $e->getMessage();
    }
    
    $result['processing_time'] = round((microtime(true) - $startTime) * 1000, 2);
} else {
    $result['status'] = 'error';
    $result['message'] = 'Método no permitido o archivo no proporcionado';
}

echo json_encode($result);

function ejecutarOCRLinux($rutaImagen) {
    $configuraciones = [
        ['psm' => 3, 'desc' => 'Segmentación automática de página'],
        ['psm' => 6, 'desc' => 'Bloque uniforme de texto'],
        ['psm' => 4, 'desc' => 'Columna única de texto'],
        ['psm' => 1, 'desc' => 'Segmentación automática con orientación'],
        ['psm' => 7, 'desc' => 'Línea de texto única'],
        ['psm' => 8, 'desc' => 'Palabra única'],
        ['psm' => 11, 'desc' => 'Texto disperso'],
        ['psm' => 12, 'desc' => 'Texto disperso con orientación'],
        ['psm' => 13, 'desc' => 'Raw line - texto sin segmentación']
    ];
    
    $errors = [];
    $bestText = '';
    $bestConfig = '';
    
    foreach ($configuraciones as $config) {
        try {
            $ocr = new TesseractOCR($rutaImagen);
            
            // Configuración para Linux (ruta típica en producción)
            $ocr->executable('/usr/bin/tesseract');
            
            // Configurar idiomas (español e inglés)
            $ocr->lang('spa+eng');
            
            // Configurar modo de segmentación de página
            $ocr->psm($config['psm']);
            
            // Configuraciones adicionales para mejor precisión
            $ocr->configFile('hocr');
            $ocr->whitelist(range('A', 'Z'), range('a', 'z'), range(0, 9), 
                           [' ', '.', ',', '-', '/', ':', ';', '(', ')', 'Ñ', 'ñ']);
            
            $texto = trim($ocr->run());
            
            if (!empty($texto) && strlen($texto) > strlen($bestText)) {
                $bestText = $texto;
                $bestConfig = $config['desc'] . " (PSM: {$config['psm']})";
            }
            
        } catch (Exception $e) {
            $errors[] = "Config PSM {$config['psm']}: " . $e->getMessage();
        }
    }
    
    return [
        'text' => $bestText,
        'config_used' => $bestConfig,
        'errors' => $errors
    ];
}

function formatearTiempo($milliseconds) {
    if ($milliseconds < 1000) {
        return $milliseconds . ' ms';
    } else {
        return round($milliseconds / 1000, 2) . ' segundos';
    }
}