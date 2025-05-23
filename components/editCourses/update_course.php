<?php
include '../../controller/conexion.php';
session_start(); // Iniciar sesión si no está iniciada

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar si el usuario está logueado
    if (!isset($_SESSION['username'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
        exit;
    }
    
    // Validar datos obligatorios
    $requiredFields = ['code', 'teacher', 'mentor', 'monitor', 'status', 'start_date', 'end_date'];
    foreach ($requiredFields as $field) {
        if (!isset($_POST[$field]) || empty($_POST[$field])) {
            echo json_encode(['success' => false, 'message' => "El campo {$field} es obligatorio"]);
            exit;
        }
    }
    
    // Validar y formatear fechas
    $startDate = date('Y-m-d', strtotime($_POST['start_date']));
    if ($startDate === '1970-01-01') {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha de inicio no válido']);
        exit;
    }
    
    $endDate = date('Y-m-d', strtotime($_POST['end_date']));
    if ($endDate === '1970-01-01') {
        echo json_encode(['success' => false, 'message' => 'Formato de fecha de fin no válido']);
        exit;
    }
    
    // Validación adicional: inicio debe ser anterior o igual al fin
    if (strtotime($startDate) > strtotime($endDate)) {
        echo json_encode(['success' => false, 'message' => 'La fecha de inicio debe ser anterior o igual a la fecha de fin']);
        exit;
    }
    
    // Asegurar que los valores numéricos sean enteros
    $code = isset($_POST['code']) ? intval($_POST['code']) : 0;
    $cohort = isset($_POST['cohort']) ? intval($_POST['cohort']) : 1;
    $status = isset($_POST['status']) ? intval($_POST['status']) : 1;
    $realHours = isset($_POST['real_hours']) ? intval($_POST['real_hours']) : 0;
    $mondayHours = isset($_POST['monday_hours']) ? intval($_POST['monday_hours']) : 0;
    $tuesdayHours = isset($_POST['tuesday_hours']) ? intval($_POST['tuesday_hours']) : 0;
    $wednesdayHours = isset($_POST['wednesday_hours']) ? intval($_POST['wednesday_hours']) : 0;
    $thursdayHours = isset($_POST['thursday_hours']) ? intval($_POST['thursday_hours']) : 0;
    $fridayHours = isset($_POST['friday_hours']) ? intval($_POST['friday_hours']) : 0;
    $saturdayHours = isset($_POST['saturday_hours']) ? intval($_POST['saturday_hours']) : 0;
    $sundayHours = isset($_POST['sunday_hours']) ? intval($_POST['sunday_hours']) : 0;
    
    $conn->begin_transaction();

    try {
        // Actualizar la información del curso
        $sql = "UPDATE courses SET 
                teacher = ?, 
                mentor = ?, 
                monitor = ?, 
                status = ?, 
                cohort = ?, 
                start_date = ?, 
                end_date = ?, 
                real_hours = ?,
                monday_hours = ?,
                tuesday_hours = ?,
                wednesday_hours = ?,
                thursday_hours = ?,
                friday_hours = ?,
                saturday_hours = ?,
                sunday_hours = ?,
                user_update = ?
                WHERE code = ?";
            
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
            
        $stmt->bind_param("sssiissiiiiiiiiis", 
            $_POST['teacher'],
            $_POST['mentor'],
            $_POST['monitor'],
            $status,
            $cohort,
            $startDate,
            $endDate,
            $realHours,
            $mondayHours,
            $tuesdayHours,
            $wednesdayHours,
            $thursdayHours,
            $fridayHours,
            $saturdayHours,
            $sundayHours,
            $_SESSION['username'],
            $code
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Error al ejecutar la actualización del curso: " . $stmt->error);
        }
        
        // Actualizar profesor
        $stmt = $conn->prepare("UPDATE teachers SET number_id = ? WHERE course_id = ?");
        $stmt->bind_param("si", $_POST['teacher'], $code);
        $stmt->execute();
        
        // Actualizar mentor
        $stmt = $conn->prepare("UPDATE mentors SET number_id = ? WHERE course_id = ?");
        $stmt->bind_param("si", $_POST['mentor'], $code);
        $stmt->execute();
        
        // Actualizar monitor
        $stmt = $conn->prepare("UPDATE monitors SET number_id = ? WHERE course_id = ?");
        $stmt->bind_param("si", $_POST['monitor'], $code);
        $stmt->execute();

        $conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Curso actualizado correctamente',
            'debug' => [
                'startDate' => $startDate,
                'endDate' => $endDate
            ]
        ]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode([
            'success' => false, 
            'message' => $e->getMessage(),
            'debug' => [
                'startDate' => $startDate ?? 'no disponible',
                'endDate' => $endDate ?? 'no disponible'
            ]
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>