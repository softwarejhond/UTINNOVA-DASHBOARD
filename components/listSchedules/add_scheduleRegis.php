<?php
// Deshabilitar la salida de errores PHP
error_reporting(0);
ini_set('display_errors', 0);

// Verificar si es una petición AJAX
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
    die('Acceso no permitido');
}

require_once '../../conexion.php';  // Cambiado a conexion.php

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

try {
    // Validar que todos los campos necesarios estén presentes
    if (empty($_POST['schedule']) || empty($_POST['program']) || 
        empty($_POST['mode']) || empty($_POST['headquarters']) || 
        empty($_POST['department'])) {
        throw new Exception('Todos los campos son obligatorios');
    }

    $schedule = $_POST['schedule'];
    $program = $_POST['program'];
    $mode = $_POST['mode'];
    $headquarters = $_POST['headquarters'];
    $department = $_POST['department'];

    // Validar modalidad y ajustar sede/departamento
    if ($mode === 'Virtual') {
        $headquarters = 'No aplica';
        $department = 'No aplica';
    }

    $sql = "INSERT INTO schedules_registrations (schedule, program, mode, headquarters, department) 
            VALUES (?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta: ' . $conn->error);
    }

    $stmt->bind_param("sssss", $schedule, $program, $mode, $headquarters, $department);

    if ($stmt->execute()) {
        $response['success'] = true;
        $response['message'] = 'Horario agregado exitosamente';
    } else {
        throw new Exception('Error al ejecutar la consulta: ' . $stmt->error);
    }

} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Asegurar que no haya salida antes del JSON
ob_clean();
echo json_encode($response);
exit;