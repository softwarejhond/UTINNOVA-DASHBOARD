<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Agregar log para debug
error_log("Aprobar estudiante - Datos recibidos: " . print_r($_POST, true));

// Verificar que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    error_log("Error: Usuario no autenticado");
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$studentId = isset($_POST['studentId']) ? $_POST['studentId'] : null;
$courseCode = isset($_POST['courseCode']) ? $_POST['courseCode'] : null;
$approvedBy = $_SESSION['username'];

error_log("StudentId: $studentId, CourseCode: $courseCode, ApprovedBy: $approvedBy");

if (!$studentId || !$courseCode) {
    error_log("Error: Faltan datos requeridos");
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Iniciar transacción para asegurar consistencia
    $conn->autocommit(false);
    
    // Verificar si ya está aprobado
    $checkSql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $studentId, $courseCode);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $checkStmt->close();
        $conn->rollback();
        $conn->autocommit(true);
        echo json_encode(['success' => false, 'message' => 'El estudiante ya está aprobado']);
        exit;
    }
    $checkStmt->close();
    
    // Insertar la nueva aprobación
    $insertSql = "INSERT INTO course_approvals (course_code, student_number_id, approved_by, created_at) VALUES (?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);
    
    if (!$insertStmt) {
        throw new Exception('Error en la preparación de inserción: ' . $conn->error);
    }
    
    $insertStmt->bind_param("sss", $courseCode, $studentId, $approvedBy);
    
    if (!$insertStmt->execute()) {
        throw new Exception('Error al aprobar estudiante: ' . $insertStmt->error);
    }
    
    $approvalId = $insertStmt->insert_id;
    $insertStmt->close();
    
    error_log("Aprobación insertada con ID: $approvalId");
    
    // Actualizar status_admin en user_register
    $updateSql = "UPDATE user_register SET statusAdmin = 10 WHERE number_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    if (!$updateStmt) {
        throw new Exception('Error en la preparación de actualización: ' . $conn->error);
    }
    
    $updateStmt->bind_param("s", $studentId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Error al actualizar status_admin: ' . $updateStmt->error);
    }
    
    $affectedRows = $updateStmt->affected_rows;
    $updateStmt->close();
    
    error_log("Status_admin actualizado. Filas afectadas: $affectedRows");
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Estudiante aprobado exitosamente',
        'approvalId' => $approvalId,
        'approved_by' => $approvedBy,
        'timestamp' => date('Y-m-d H:i:s'),
        'status_updated' => $affectedRows > 0 ? true : false,
        'affected_rows' => $affectedRows
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    error_log("Error en aprobar_estudiante.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>