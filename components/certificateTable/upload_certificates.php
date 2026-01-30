<?php
require_once '../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// Procesar subida de archivo Excel
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_certificates'])) {
    $targetDir = __DIR__ . '/../../uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = 'certificates_subido.xlsx';
    $targetFile = $targetDir . $fileName;
    
    if (move_uploaded_file($_FILES['archivo_certificates']['tmp_name'], $targetFile)) {
        // Procesar el Excel
        $spreadsheet = IOFactory::load($targetFile);
        $worksheet = $spreadsheet->getSheetByName('Hoja1'); // Buscar siempre en Hoja1
        
        if (!$worksheet) {
            echo json_encode(['success' => false, 'message' => 'No se encontró la hoja "Hoja1" en el archivo Excel.']);
            exit;
        }
        
        $highestRow = $worksheet->getHighestRow();
        
        // Conectar a la base de datos
        include_once __DIR__ . '/../../controller/conexion.php';
        
        $inserted = 0;
        $skipped = 0;
        for ($row = 2; $row <= $highestRow; ++$row) { // Asumiendo fila 1 es encabezado
            $number_id = $worksheet->getCell('A' . $row)->getValue();
            $link = $worksheet->getCell('B' . $row)->getValue();
            
            if ($number_id && $link) {
                // Verificar si number_id ya existe
                $checkSql = "SELECT id FROM certificates WHERE number_id = ?";
                $checkStmt = mysqli_prepare($conn, $checkSql);
                mysqli_stmt_bind_param($checkStmt, 'i', $number_id);
                mysqli_stmt_execute($checkStmt);
                mysqli_stmt_store_result($checkStmt);
                
                if (mysqli_stmt_num_rows($checkStmt) == 0) {
                    // No existe, insertar
                    $sql = "INSERT INTO certificates (number_id, link) VALUES (?, ?)";
                    $stmt = mysqli_prepare($conn, $sql);
                    mysqli_stmt_bind_param($stmt, 'is', $number_id, $link);
                    if (mysqli_stmt_execute($stmt)) {
                        $inserted++;
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $skipped++;
                }
                mysqli_stmt_close($checkStmt);
            }
        }
        
        echo json_encode(['success' => true, 'message' => "Certificados procesados. $inserted insertados, $skipped omitidos (duplicados)."]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido o archivo no encontrado.']);
}
?>