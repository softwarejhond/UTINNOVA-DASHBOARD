<?php
// filepath: c:\xampp\htdocs\DASBOARD-ADMIN-MINTICS\components\infoWeek\upload_informe.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['archivo_informe'])) {
    $targetDir = __DIR__ . '/../../uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
    $fileName = 'informe_subido.xlsx';
    $targetFile = $targetDir . $fileName;
    if (move_uploaded_file($_FILES['archivo_informe']['tmp_name'], $targetFile)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}
echo json_encode(['success' => false]);