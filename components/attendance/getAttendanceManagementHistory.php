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
    // Consulta con JOIN para obtener el nombre del responsable para todos los registros
    $sql = "SELECT sam.*, u.nombre as responsible_name, 
            DATE_FORMAT(sam.created_at, '%d/%m/%Y %H:%i') as formatted_date
            FROM student_attendance_management sam
            LEFT JOIN users u ON sam.responsible_username = u.username
            WHERE sam.student_id = ? AND sam.course_id = ?
            ORDER BY sam.created_at DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('si', $studentId, $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    echo json_encode([
        'success' => true,
        'data' => $history
    ]);
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al obtener historial de gestión: ' . $e->getMessage()
    ]);
}
?>