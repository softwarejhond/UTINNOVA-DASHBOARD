<?php
require_once __DIR__ . '/../../controller/conexion.php';

$id_reporte = isset($_GET['id_reporte']) ? intval($_GET['id_reporte']) : 0;
$response = ['success' => false];

if ($id_reporte > 0) {
    $sql = "SELECT gestion_a_realizar, resultado_gestion, status 
            FROM gestiones_reportes 
            WHERE id_reporte = ? 
            ORDER BY fecha_gestion DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_reporte);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $response['success'] = true;
        $response['gestion'] = $row;
    }
    $stmt->close();
}
echo json_encode($response);
?>