<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json');
ob_start(); // Iniciar buffer de salida

require_once __DIR__ . '/../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$studentId = $_POST['student_id'] ?? '';
$courseId = $_POST['course_id'] ?? '';
$classDate = $_POST['class_date'] ?? '';
$observationType = $_POST['observation_type'] ?? '';
$observationText = $_POST['observation_text'] ?? '';

if (empty($studentId) || empty($courseId) || empty($classDate) || empty($observationType)) {
    echo json_encode(['success' => false, 'message' => 'Parámetros obligatorios faltantes']);
    exit;
}

try {
    // Verificar conexión
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Usar INSERT ... ON DUPLICATE KEY UPDATE (más eficiente)
    $sql = "INSERT INTO class_observations (student_id, course_id, class_date, observation_type, observation_text) 
            VALUES (?, ?, ?, ?, ?) 
            ON DUPLICATE KEY UPDATE 
                observation_type = VALUES(observation_type), 
                observation_text = VALUES(observation_text),
                updated_at = CURRENT_TIMESTAMP";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación de consulta: ' . $conn->error);
    }
    
    $stmt->bind_param("sisss", $studentId, $courseId, $classDate, $observationType, $observationText);
    
    if (!$stmt->execute()) {
        throw new Exception('Error al ejecutar consulta: ' . $stmt->error);
    }
    
    $stmt->close();
    
    ob_clean(); // Limpiar cualquier salida no deseada
    echo json_encode([
        'success' => true,
        'message' => 'Observación guardada correctamente'
    ]);
    
} catch (Exception $e) {
    ob_clean(); // Limpiar cualquier salida no deseada
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

ob_end_flush(); // Enviar el buffer de salida
?>