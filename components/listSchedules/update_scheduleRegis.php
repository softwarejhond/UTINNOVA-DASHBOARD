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

    $sql = "UPDATE schedules_registrations 
            SET schedule = ?, program = ?, mode = ?, headquarters = ?, department = ? 
            WHERE id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $schedule, $program, $mode, $headquarters, $department, $id);
    
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