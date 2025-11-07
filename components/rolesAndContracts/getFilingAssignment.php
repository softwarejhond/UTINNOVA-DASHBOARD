<?php
session_start();
include_once '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$username = $_GET['username'] ?? null;

if (!$username) {
    echo json_encode(['success' => false, 'message' => 'Username requerido']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT filing_number, filing_date, contract_role FROM filing_assignments WHERE username = ?");
    $stmt->bind_param("i", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => true, 'data' => null]);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>