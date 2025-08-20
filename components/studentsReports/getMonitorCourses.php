<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

$username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

if (!$username) {
    echo json_encode([]);
    exit;
}

// Buscar cursos donde el usuario es monitor (username directamente)
$sql = "SELECT code, name FROM courses WHERE monitor = ? ORDER BY name ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username); // "s" para string, no "i" para integer
$stmt->execute();
$result = $stmt->get_result();

$courses = [];
while ($row = $result->fetch_assoc()) {
    $courses[] = [
        'code' => $row['code'],
        'name' => $row['name']
    ];
}
$stmt->close();
$conn->close();

echo json_encode($courses);
?>