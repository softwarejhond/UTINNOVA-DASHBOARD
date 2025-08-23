<?php
require_once __DIR__ . '/../../controller/conexion.php';

$id_reporte = isset($_GET['id_reporte']) ? intval($_GET['id_reporte']) : 0;
$response = ['success' => false, 'gestiones' => []];

if ($id_reporte > 0) {
    $sql = "SELECT fecha_gestion, responsable, gestion_a_realizar, resultado_gestion, status
            FROM gestiones_reportes
            WHERE id_reporte = ?
            ORDER BY fecha_gestion DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_reporte);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        // Formatear fecha
        $fecha = new DateTime($row['fecha_gestion']);
        $row['fecha_gestion'] = $fecha->format('d/m/Y H:i');
        $response['gestiones'][] = $row;
    }
    $stmt->close();
    $response['success'] = true;
}
echo json_encode($response);
?>