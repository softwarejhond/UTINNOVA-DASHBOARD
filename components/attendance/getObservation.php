<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$studentId = $_POST['student_id'] ?? '';
$courseId = $_POST['course_id'] ?? '';
$classDate = $_POST['class_date'] ?? '';

if (empty($studentId) || empty($courseId) || empty($classDate)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros faltantes']);
    exit;
}

try {
    $sql = "SELECT co.observation_type, co.observation_text, co.created_by, 
            u.nombre as created_by_name 
            FROM class_observations co 
            LEFT JOIN users u ON co.created_by = u.username
            WHERE co.student_id = ? AND co.course_id = ? AND co.class_date = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación de consulta: ' . $conn->error);
    }
    
    $stmt->bind_param("sis", $studentId, $courseId, $classDate);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $observation = $result->fetch_assoc();
    
    $stmt->close();
    
    echo json_encode([
        'success' => true,
        'data' => $observation
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>