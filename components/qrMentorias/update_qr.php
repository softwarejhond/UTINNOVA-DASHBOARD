<?php
require_once(__DIR__ . '/../../controller/conexion.php');
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $clases_equivalentes = intval($_POST['clases_equivalentes']);
    $authorized_by = $_SESSION['username'] ?? '';

    if ($id > 0 && $authorized_by !== '') {
        $stmt = $conn->prepare("UPDATE qr_mentorias SET clases_equivalentes = ?, authorized_by = ?, authorized = 1 WHERE id = ?");
        $stmt->bind_param("isi", $clases_equivalentes, $authorized_by, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Datos inválidos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}