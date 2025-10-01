<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$uploadDir = __DIR__ . '/../../uploads/';

// Crear directorio si no existe
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$targetFile = $uploadDir . 'E29_a_verificar.xlsx';

if (isset($_FILES['excel_file'])) {
    // Verificar errores de subida
    if ($_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error en la subida del archivo: ' . $_FILES['excel_file']['error']
        ]);
        exit;
    }
    
    $fileType = pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION);
    
    if (in_array(strtolower($fileType), ['xlsx', 'xls'])) {
        // Eliminar archivo anterior si existe
        if (file_exists($targetFile)) {
            unlink($targetFile);
        }
        
        if (move_uploaded_file($_FILES['excel_file']['tmp_name'], $targetFile)) {
            echo json_encode([
                'status' => 'success',
                'message' => 'Archivo subido correctamente como E29_a_verificar.xlsx'
            ]);
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al mover el archivo al directorio de destino.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Solo se permiten archivos Excel (.xlsx, .xls).'
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se recibió ningún archivo.'
    ]);
}
?>
