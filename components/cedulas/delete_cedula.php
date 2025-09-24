<?php
// Activar reporte de errores
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');
ob_start();

require __DIR__ . '/../../controller/conexion.php';

$number_id = isset($_POST['number_id']) ? intval($_POST['number_id']) : 0;
$pdf_path = isset($_POST['pdf_path']) ? $_POST['pdf_path'] : '';

if (!$number_id || !$pdf_path) {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'Datos incompletos.']);
    exit;
}

// Detectar entorno para ruta base
$isProduction = (strpos($_SERVER['HTTP_HOST'], 'localhost') === false);
$baseDir = $isProduction ? '/dashboard/' : '/UTINNOVA-DASHBOARD/';
$fullPath = $_SERVER['DOCUMENT_ROOT'] . $baseDir . $pdf_path;

// Eliminar archivo PDF
if (file_exists($fullPath)) {
    if (!unlink($fullPath)) {
        ob_end_clean();
        echo json_encode(['success' => false, 'error' => 'No se pudo eliminar el archivo PDF.']);
        exit;
    }
}

// Eliminar registro en la base de datos usando mysqli
$stmt = $conn->prepare("DELETE FROM cedulas_pdf WHERE number_id = ?");
$stmt->bind_param("i", $number_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'PDF y registro eliminados correctamente.']);
} else {
    ob_end_clean();
    echo json_encode(['success' => false, 'error' => 'No se encontró el registro en la base de datos.']);
}

$stmt->close();
$conn->close();