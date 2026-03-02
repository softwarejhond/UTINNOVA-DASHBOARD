<?php
require __DIR__ . '/../../conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    if (empty($_POST['id'])) {
        throw new Exception('ID inválido');
    }

    $id = intval($_POST['id']);

    $stmt = $conn->prepare("DELETE FROM schedules WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Horario eliminado exitosamente';
    } else {
        $response['message'] = 'No se pudo eliminar el horario';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);