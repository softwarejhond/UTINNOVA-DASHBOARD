<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar que se recibieron los parámetros necesarios
if (!isset($_POST['student_id']) || !isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros necesarios']);
    exit;
}

$studentId = $_POST['student_id'];
$courseId = $_POST['course_id'];

try {
    // Consulta para obtener la información de gestión de un estudiante
    $sql = "SELECT * FROM student_attendance_management 
            WHERE student_id = ? AND course_id = ? 
            ORDER BY updated_at DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $studentId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => true, 'data' => null]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener datos de gestión: ' . $e->getMessage()
    ]);
}
?>