<?php
// Activar reporte de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../../controller/conexion.php';

// Obtener parámetros de filtros desde GET
$filterNumberId = isset($_GET['number_id']) ? trim($_GET['number_id']) : '';
$filterHeadquarters = isset($_GET['headquarters']) ? trim($_GET['headquarters']) : '';
$filterBootcamp = isset($_GET['bootcamp']) ? trim($_GET['bootcamp']) : '';
$filterMode = isset($_GET['mode']) ? trim($_GET['mode']) : '';

// Construir la consulta base - usar principalmente datos de groups
$sql = "SELECT g.type_id, g.number_id, g.full_name, g.email, g.institutional_email, 
               g.department as group_department, g.headquarters, g.mode,
               ur.first_phone,
               cr.id as carnet_id, cr.file_path, cr.file_name, cr.generated_at as carnet_date
        FROM groups g 
        LEFT JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN carnet_records cr ON g.number_id = cr.number_id AND cr.is_active = 1";

// Agregar condiciones WHERE basadas en filtros
$conditions = [];
if (!empty($filterNumberId)) {
    $conditions[] = "g.number_id = '" . $conn->real_escape_string($filterNumberId) . "'";
}
if (!empty($filterHeadquarters)) {
    $conditions[] = "g.headquarters = '" . $conn->real_escape_string($filterHeadquarters) . "'";
}
if (!empty($filterBootcamp)) {
    $conditions[] = "g.id_bootcamp = '" . $conn->real_escape_string($filterBootcamp) . "'";
}
if (!empty($filterMode)) {
    $conditions[] = "g.mode = '" . $conn->real_escape_string($filterMode) . "'";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY g.full_name";

$result = $conn->query($sql);
$data = [];

if ($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
} else {
    // Si hay error en la consulta, devolver error
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la consulta: ' . $conn->error]);
    exit;
}

// Debug: Imprimir en log del servidor
error_log("Datos JSON devueltos: " . json_encode($data));

// Devolver datos en JSON
header('Content-Type: application/json');
echo json_encode($data);
?>