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
    $sql = "SELECT observation_type, observation_text 
            FROM class_observations 
            WHERE student_id = ? AND course_id = ? AND class_date = ?";
    
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