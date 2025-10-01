<?php
// filepath: c:\xampp\htdocs\DASBOARD-ADMIN-MINTICS\components\scanDocs\truncateVerificationTable.php
header('Content-Type: application/json');
require __DIR__ . '/../../controller/conexion.php';

try {
    $conn->query("TRUNCATE TABLE document_verification");
    echo json_encode(['status' => 'success']);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>