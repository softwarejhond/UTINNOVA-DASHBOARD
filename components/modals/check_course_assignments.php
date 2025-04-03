<?php
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['courseCode'])) {
    $courseCode = $_GET['courseCode'];
    $assignments = [];
    $hasAssignments = false;
    
    // Verificar si el curso existe y tiene asignaciones
    $stmt = $conn->prepare("SELECT * FROM courses WHERE code = ?");
    $stmt->bind_param("s", $courseCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $course = $result->fetch_assoc();
        
        // Verificar profesor
        if (!empty($course['teacher'])) {
            $assignments[] = [
                'role' => 'profesor',
                'id' => $course['teacher']
            ];
            $hasAssignments = true;
        }
        
        // Verificar mentor
        if (!empty($course['mentor'])) {
            $assignments[] = [
                'role' => 'mentor',
                'id' => $course['mentor']
            ];
            $hasAssignments = true;
        }
        
        // Verificar monitor
        if (!empty($course['monitor'])) {
            $assignments[] = [
                'role' => 'monitor',
                'id' => $course['monitor']
            ];
            $hasAssignments = true;
        }
    }
    
    echo json_encode([
        'success' => true,
        'hasAssignments' => $hasAssignments,
        'assignments' => $assignments
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Parámetros incorrectos'
    ]);
}