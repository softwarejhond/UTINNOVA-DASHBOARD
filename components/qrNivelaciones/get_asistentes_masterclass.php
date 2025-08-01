<?php
require_once(__DIR__ . '/../../controller/conexion.php');
header('Content-Type: application/json');

$curso_id = $_POST['curso_id'] ?? null;
if (!$curso_id) {
    echo json_encode([]);
    exit;
}

// Consulta asistentes de la masterclass seleccionada
$sql = "SELECT am.number_id, 
               g.full_name, 
               g.program, 
               g.mode, 
               g.email, 
               g.institutional_email, 
               g.bootcamp_name,
               am.fecha
        FROM asistencias_masterclass am
        INNER JOIN groups g ON am.number_id = g.number_id
        WHERE am.code = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $curso_id);
$stmt->execute();
$res = $stmt->get_result();

$asistentes = [];
while ($row = $res->fetch_assoc()) {
    $asistentes[] = $row;
}
echo json_encode($asistentes);