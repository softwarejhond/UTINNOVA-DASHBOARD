<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

date_default_timezone_set('America/Bogota');

$id_reporte = isset($_POST['id_reporte']) ? intval($_POST['id_reporte']) : 0;
$responsable = isset($_POST['responsable']) ? trim($_POST['responsable']) : '';
$gestion_a_realizar = isset($_POST['gestion_a_realizar']) ? trim($_POST['gestion_a_realizar']) : '';
$resultado_gestion = isset($_POST['resultado_gestion']) ? trim($_POST['resultado_gestion']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
$fecha_gestion = date('Y-m-d H:i:s');

if ($id_reporte <= 0 || !$responsable || !$gestion_a_realizar || !$resultado_gestion || !$status) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

// Guardar la gestión
$sql = "INSERT INTO gestiones_reportes (id_reporte, responsable, gestion_a_realizar, resultado_gestion, status, fecha_gestion)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssss", $id_reporte, $responsable, $gestion_a_realizar, $resultado_gestion, $status, $fecha_gestion);

if ($stmt->execute()) {
    // Opcional: actualizar el status en student_reports
    $sql2 = "UPDATE student_reports SET status = ? WHERE id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("si", $status, $id_reporte);
    $stmt2->execute();
    $stmt2->close();

    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la gestión']);
}

$stmt->close();
$conn->close();
?>