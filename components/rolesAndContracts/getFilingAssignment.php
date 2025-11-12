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
    $stmt = $conn->prepare("SELECT id, filing_number, filing_date, contract_role, lote FROM filing_assignments WHERE username = ? ORDER BY creation_date DESC");
    $stmt->bind_param("i", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = [];
    while ($row = $result->fetch_assoc()) {
        $assignments[] = $row;
    }
    echo json_encode(['success' => true, 'data' => $assignments]);
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>