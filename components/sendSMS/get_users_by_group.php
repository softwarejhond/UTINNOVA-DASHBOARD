<?php
require __DIR__ . '/../../conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['type']) || !isset($_GET['value'])) {
    echo json_encode(['success' => false, 'message' => 'Parámetros requeridos']);
    exit;
}

$type = $_GET['type'];
$value = $_GET['value'];

$allowedTypes = ['headquarters', 'bootcamp_name'];
if (!in_array($type, $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Tipo inválido']);
    exit;
}

$query = "
    SELECT 
        g.number_id, 
        CONCAT_WS(' ', u.first_name, u.second_name, u.first_last, u.second_last) AS fullName, 
        g.headquarters, 
        g.bootcamp_name, 
        u.first_phone
    FROM groups g
    JOIN user_register u ON g.number_id = u.number_id
    WHERE g.$type = ?
";

$stmt = $conn->prepare($query);
$stmt->bind_param("s", $value);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode(['success' => true, 'users' => $users]);

$stmt->close();
$conn->close();
?>