<?php
session_start();
include_once '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$username = $_POST['username'] ?? null;
$filing_number = $_POST['filing_number'] ?? null;
$filing_date = $_POST['filing_date'] ?? null;
$contract_role = $_POST['contract_role'] ?? null;
$created_by = $_SESSION['username'] ?? null;

if (!$username || !$filing_number || !$filing_date || !$contract_role || !$created_by) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO filing_assignments (username, filing_number, filing_date, contract_role, created_by) VALUES (?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE filing_number = VALUES(filing_number), filing_date = VALUES(filing_date), contract_role = VALUES(contract_role)");
    $stmt->bind_param("isssi", $username, $filing_number, $filing_date, $contract_role, $created_by);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Asignación guardada exitosamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>