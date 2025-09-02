<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

date_default_timezone_set('America/Bogota');

$number_id = isset($_POST['number_id']) ? intval($_POST['number_id']) : 0;
$code = isset($_POST['code']) ? trim($_POST['code']) : '';
$grupo = isset($_POST['grupo']) ? trim($_POST['grupo']) : '';
$gestion = isset($_POST['gestion']) ? trim($_POST['gestion']) : '';
$status = 'PENDIENTE'; // Valor fijo por defecto

// Validar sesión y responsable
if (!isset($_SESSION['username']) || empty($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Sesión inválida. Por favor inicia sesión nuevamente.']);
    exit;
}
$responsable = $_SESSION['username'];
$fecha_registro = date('Y-m-d H:i:s');

// Validar datos obligatorios
if ($number_id <= 0 || !$code || !$grupo || !$gestion) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos. Todos los campos son obligatorios.']);
    exit;
}

$sql = "INSERT INTO student_reports (number_id, code, grupo, gestion, status, responsable, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "issssss", $number_id, $code, $grupo, $gestion, $status, $responsable, $fecha_registro);

if (mysqli_stmt_execute($stmt)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar el reporte']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>