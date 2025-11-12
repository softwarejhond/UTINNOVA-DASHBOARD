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
$lote = $_POST['lote'] ?? null;  // Nuevo: obtener lote
$created_by = $_SESSION['username'] ?? null;
$assignmentId = $_POST['assignmentId'] ?? null;  // Nuevo: obtener ID

if (!$username || !$filing_number || !$filing_date || !$contract_role || !$lote || !$created_by) {  // Nuevo: validar lote
    echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
    exit;
}

try {
    if ($assignmentId) {
        // Actualizar existente
        $stmt = $conn->prepare("UPDATE filing_assignments SET filing_number = ?, filing_date = ?, contract_role = ?, lote = ? WHERE id = ?");
        $stmt->bind_param("sssii", $filing_number, $filing_date, $contract_role, $lote, $assignmentId);
        $message = 'Asignación actualizada exitosamente';
    } else {
        // Insertar nueva
        $stmt = $conn->prepare("INSERT INTO filing_assignments (username, filing_number, filing_date, contract_role, lote, created_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssis", $username, $filing_number, $filing_date, $contract_role, $lote, $created_by);
        $message = 'Asignación guardada exitosamente';
    }
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => $message]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al guardar la asignación']);
    }
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>