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
    // Consulta con JOIN para obtener el nombre del responsable
    $sql = "SELECT sam.*, u.nombre as responsible_name 
            FROM student_attendance_management sam
            LEFT JOIN users u ON sam.responsible_username = u.username
            WHERE sam.student_id = ? AND sam.course_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $studentId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'data' => null
        ]);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener información de gestión: ' . $e->getMessage()
    ]);
}
?>