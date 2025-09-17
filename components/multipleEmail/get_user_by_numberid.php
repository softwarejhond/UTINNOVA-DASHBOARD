<?php
require __DIR__ . '/../../conexion.php';

header('Content-Type: application/json');

if (!isset($_GET['number_id']) || empty($_GET['number_id'])) {
    echo json_encode(['success' => false, 'message' => 'Número de identificación requerido']);
    exit;
}

$number_id = $_GET['number_id'];

// Consulta segura usando prepared statements
$stmt = $conn->prepare(
    "SELECT 
        id, 
        CONCAT_WS(' ', first_name, second_name, first_last, second_last) AS fullName, 
        email, 
        program, 
        headquarters 
     FROM user_register 
     WHERE number_id = ?"
);
$stmt->bind_param("s", $number_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $user = $result->fetch_assoc();
    echo json_encode(['success' => true, 'user' => $user]);
} else {
    echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
}

$stmt->close();
$conn->close();