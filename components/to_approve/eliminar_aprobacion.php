<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$studentId = isset($_POST['studentId']) ? $_POST['studentId'] : null;
$courseCode = isset($_POST['courseCode']) ? $_POST['courseCode'] : null;

if (!$studentId || !$courseCode) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos requeridos']);
    exit;
}

try {
    // Iniciar transacción
    $conn->autocommit(false);
    
    // Verificar que la aprobación existe
    $checkSql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
    $checkStmt = $conn->prepare($checkSql);
    
    if (!$checkStmt) {
        throw new Exception("Error en la preparación de verificación: " . $conn->error);
    }
    
    $checkStmt->bind_param("ss", $studentId, $courseCode);
    
    if (!$checkStmt->execute()) {
        throw new Exception("Error al verificar la aprobación: " . $checkStmt->error);
    }
    
    $result = $checkStmt->get_result();
    
    if ($result->num_rows === 0) {
        $checkStmt->close();
        $conn->rollback();
        $conn->autocommit(true);
        echo json_encode(['success' => false, 'message' => 'La aprobación no existe']);
        exit;
    }
    
    $checkStmt->close();
    
    // Eliminar la aprobación
    $deleteSql = "DELETE FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    
    if (!$deleteStmt) {
        throw new Exception("Error en la preparación de eliminación: " . $conn->error);
    }
    
    $deleteStmt->bind_param("ss", $studentId, $courseCode);
    
    if (!$deleteStmt->execute()) {
        throw new Exception("Error al eliminar la aprobación: " . $deleteStmt->error);
    }
    
    if ($deleteStmt->affected_rows === 0) {
        throw new Exception("No se pudo eliminar la aprobación");
    }
    
    $deleteStmt->close();
    
    // Actualizar status_admin en user_register (revertir a un estado anterior, por ejemplo 9)
    $updateSql = "UPDATE user_register SET statusAdmin = 3 WHERE number_id = ?";
    $updateStmt = $conn->prepare($updateSql);
    
    if (!$updateStmt) {
        throw new Exception('Error en la preparación de actualización de status: ' . $conn->error);
    }
    
    $updateStmt->bind_param("s", $studentId);
    
    if (!$updateStmt->execute()) {
        throw new Exception('Error al actualizar status_admin: ' . $updateStmt->error);
    }
    
    $statusAffectedRows = $updateStmt->affected_rows;
    $updateStmt->close();
    
    // Confirmar transacción
    $conn->commit();
    $conn->autocommit(true);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Aprobación eliminada correctamente',
        'studentId' => $studentId,
        'status_reverted' => $statusAffectedRows > 0 ? true : false,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

$conn->close();
?>