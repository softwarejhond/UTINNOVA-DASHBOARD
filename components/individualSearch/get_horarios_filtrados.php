<?php
include_once('../../controller/conexion.php');
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');

$id = isset($_POST['id']) ? $_POST['id'] : '';
$programa = isset($_POST['programa']) ? $_POST['programa'] : '';
$sede = isset($_POST['sede']) ? $_POST['sede'] : '';

// Opcional: puedes obtener la modalidad del usuario si lo necesitas
$mode = '';
if ($id) {
    $stmt = $conn->prepare("SELECT mode FROM user_register WHERE number_id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $mode = $row['mode'];
    }
    $stmt->close();
}

// Consulta de horarios filtrados por sede, programa y modalidad
$sql = "SELECT DISTINCT schedule 
        FROM schedules 
        WHERE 1=1 ";

$params = [];
$types = '';

if ($programa) {
    $sql .= " AND program = ? ";
    $params[] = $programa;
    $types .= 's';
}
if ($sede) {
    $sql .= " AND headquarters = ? ";
    $params[] = $sede;
    $types .= 's';
}
if ($mode) {
    $sql .= " AND mode = ? ";
    $params[] = $mode;
    $types .= 's';
}

$sql .= " ORDER BY schedule ASC";

$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$horarios = [];
while ($row = $result->fetch_assoc()) {
    $horarios[] = $row['schedule'];
}

echo json_encode([
    'success' => true,
    'horarios' => $horarios
]);