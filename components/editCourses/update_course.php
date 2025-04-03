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

    $conn->begin_transaction();

    try {
        // Actualizar la información del curso con las horas por día
        $stmt = $conn->prepare("UPDATE courses SET 
            teacher = ?, 
            mentor = ?, 
            monitor = ?, 
            status = ?, 
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
            WHERE code = ?");
            
        $stmt->bind_param("ssssssiiiiiiiiis", 
            $_POST['teacher'],
            $_POST['mentor'],
            $_POST['monitor'],
            $_POST['status'],
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['real_hours'],
            $_POST['monday_hours'],
            $_POST['tuesday_hours'],
            $_POST['wednesday_hours'],
            $_POST['thursday_hours'],
            $_POST['friday_hours'],
            $_POST['saturday_hours'],
            $_POST['sunday_hours'],
            $_SESSION['username'], // Agregamos el username del usuario logueado
            $_POST['code']
        );
        $stmt->execute();
        
        // Actualizar profesor
        $stmt = $conn->prepare("UPDATE teachers SET number_id = ? WHERE course_id = ?");
        $stmt->bind_param("ss", $_POST['teacher'], $_POST['code']);
        $stmt->execute();
        
        // Actualizar mentor
        $stmt = $conn->prepare("UPDATE mentors SET number_id = ? WHERE course_id = ?");
        $stmt->bind_param("ss", $_POST['mentor'], $_POST['code']);
        $stmt->execute();
        
        // Actualizar monitor
        $stmt = $conn->prepare("UPDATE monitors SET number_id = ? WHERE course_id = ?");
        $stmt->bind_param("ss", $_POST['monitor'], $_POST['code']);
        $stmt->execute();

        $conn->commit();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>