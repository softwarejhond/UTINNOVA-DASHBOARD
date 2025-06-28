<?php
// Deshabilitar la salida de errores para evitar contaminar el JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar que se recibieron los parámetros necesarios
if (!isset($_POST['student_id']) || !isset($_POST['course_id'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan parámetros necesarios']);
    exit;
}

// Limpiar y convertir tipos de datos
$studentId = trim($_POST['student_id']);
$courseId = intval($_POST['course_id']);
$requiresIntervention = isset($_POST['requires_intervention']) ? trim($_POST['requires_intervention']) : null;
$responsibleUsername = isset($_POST['responsible_username']) ? trim($_POST['responsible_username']) : null;
$interventionObservation = isset($_POST['intervention_observation']) ? trim($_POST['intervention_observation']) : null;
$isResolved = isset($_POST['is_resolved']) ? trim($_POST['is_resolved']) : null;
$requiresAdditionalStrategy = isset($_POST['requires_additional_strategy']) ? trim($_POST['requires_additional_strategy']) : null;
$strategyObservation = isset($_POST['strategy_observation']) ? trim($_POST['strategy_observation']) : null;
$strategyFulfilled = isset($_POST['strategy_fulfilled']) ? trim($_POST['strategy_fulfilled']) : null;
$withdrawalReason = isset($_POST['withdrawal_reason']) ? trim($_POST['withdrawal_reason']) : null;
$withdrawalDate = !empty($_POST['withdrawal_date']) ? trim($_POST['withdrawal_date']) : null;

try {
    // Verificar la conexión a la base de datos
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos");
    }
    
    // Verificar si ya existe un registro para este estudiante y curso
    $sqlCheck = "SELECT id FROM student_attendance_management 
                 WHERE student_id = ? AND course_id = ?";
    
    $stmtCheck = $conn->prepare($sqlCheck);
    if (!$stmtCheck) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmtCheck->bind_param('si', $studentId, $courseId);
    
    if (!$stmtCheck->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmtCheck->error);
    }
    
    $resultCheck = $stmtCheck->get_result();
    
    // Verificar que la tabla existe
    if ($resultCheck === false) {
        throw new Exception("Error al consultar la tabla. Es posible que la tabla 'student_attendance_management' no exista.");
    }
    
    if ($resultCheck->num_rows > 0) {
        // Actualizar registro existente
        $row = $resultCheck->fetch_assoc();
        $id = $row['id'];
        
        $sqlUpdate = "UPDATE student_attendance_management SET 
                      requires_intervention = ?,
                      responsible_username = ?,
                      intervention_observation = ?,
                      is_resolved = ?,
                      requires_additional_strategy = ?,
                      strategy_observation = ?,
                      strategy_fulfilled = ?,
                      withdrawal_reason = ?,
                      withdrawal_date = ?
                      WHERE id = ?";
        
        $stmt = $conn->prepare($sqlUpdate);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la actualización: " . $conn->error);
        }
        
        $stmt->bind_param('sssssssssi', 
            $requiresIntervention,
            $responsibleUsername,
            $interventionObservation,
            $isResolved,
            $requiresAdditionalStrategy,
            $strategyObservation,
            $strategyFulfilled,
            $withdrawalReason,
            $withdrawalDate,
            $id
        );
        
    } else {
        // Crear nuevo registro
        $sqlInsert = "INSERT INTO student_attendance_management (
                      student_id, course_id, requires_intervention, 
                      responsible_username, intervention_observation, is_resolved,
                      requires_additional_strategy, strategy_observation, strategy_fulfilled,
                      withdrawal_reason, withdrawal_date) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sqlInsert);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la inserción: " . $conn->error);
        }
        
        $stmt->bind_param('sisssssssss', // Añadido un 's' más para withdrawal_date
            $studentId,
            $courseId,
            $requiresIntervention,
            $responsibleUsername,
            $interventionObservation,
            $isResolved,
            $requiresAdditionalStrategy,
            $strategyObservation,
            $strategyFulfilled,
            $withdrawalReason,
            $withdrawalDate
        );
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al guardar datos de gestión: ' . $e->getMessage()
    ]);
}
?>