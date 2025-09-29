<?php
require __DIR__ . '/../../conexion.php';

$response = ['success' => false, 'message' => ''];

try {
    $id = $_POST['id'];
    $schedule = $_POST['schedule'];
    $program = $_POST['program'];
    $mode = $_POST['mode'];
    $headquarters = $_POST['headquarters'];
    $department = $_POST['department'];
    $available = isset($_POST['available']) ? intval($_POST['available']) : 0;

    $sql = "UPDATE schedules 
            SET schedule = ?, program = ?, mode = ?, headquarters = ?, department = ?, available = ?
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssssi", $schedule, $program, $mode, $headquarters, $department, $available, $id);
    
    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Horario actualizado exitosamente';
    } else {
        $response['message'] = 'Error al actualizar el horario';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);