<?php
// filepath: c:\xampp\htdocs\UTINNOVA-DASHBOARD\components\attendance\getObservationsStats.php
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

$courseCode = $_GET['course_code'] ?? '';

if (empty($courseCode)) {
    echo json_encode(['success' => false, 'message' => 'Código de curso requerido']);
    exit;
}

try {
    // Consulta para contar tipos de observación por curso
    $sql = "SELECT observation_type, COUNT(*) as count 
            FROM class_observations 
            WHERE course_id = ? 
            GROUP BY observation_type 
            ORDER BY count DESC";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $courseCode);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $observations = [];
    while ($row = $result->fetch_assoc()) {
        $observations[] = $row;
    }
    
    $stmt->close();
    
    echo json_encode(['success' => true, 'data' => $observations]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>