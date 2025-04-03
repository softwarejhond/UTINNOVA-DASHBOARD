<?php
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();

    try {
        // Insertar profesor
        $stmt = $conn->prepare("INSERT INTO teachers (number_id, name, course_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $_POST['teacher']['username'], $_POST['teacher']['name'], $_POST['code']);
        $stmt->execute();

        // Insertar mentor
        $stmt = $conn->prepare("INSERT INTO mentors (number_id, name, course_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $_POST['mentor']['username'], $_POST['mentor']['name'], $_POST['code']);
        $stmt->execute();

        // Insertar monitor
        $stmt = $conn->prepare("INSERT INTO monitors (number_id, name, course_id) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $_POST['monitor']['username'], $_POST['monitor']['name'], $_POST['code']);
        $stmt->execute();

        // Insertar curso (usando usernames en lugar de IDs)
        $stmt = $conn->prepare("INSERT INTO courses (
            code, 
            name, 
            teacher, 
            mentor, 
            monitor, 
            status, 
            start_date, 
            end_date, 
            real_hours,
            monday_hours,
            tuesday_hours,
            wednesday_hours,
            thursday_hours,
            friday_hours,
            saturday_hours,
            sunday_hours
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        // Extraer correctamente el nombre del curso
        $fullText = $_POST['name'];
        $parts = explode(' - ', $fullText, 2); // Dividir solo en la primera ocurrencia
        
        // Si hay más de una parte, toma todo después del primer guión
        // Esto preservará otros guiones en el nombre del curso
        $courseName = count($parts) > 1 ? $parts[1] : $fullText;
        
        $stmt->bind_param("ssssssssiiiiiiii", 
            $_POST['code'],
            $courseName, // Usar el nombre procesado
            $_POST['teacher']['username'],
            $_POST['mentor']['username'],
            $_POST['monitor']['username'],
            $_POST['status'],
            $_POST['date_start'],
            $_POST['date_end'],
            $_POST['real_hours'],
            $_POST['monday_hours'],
            $_POST['tuesday_hours'],
            $_POST['wednesday_hours'],
            $_POST['thursday_hours'],
            $_POST['friday_hours'],
            $_POST['saturday_hours'],
            $_POST['sunday_hours']
        );
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