<?php
include_once('../../controller/conexion.php');
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa'
    ]);
    exit;
}

// Recibir datos
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['student_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'ID de estudiante no proporcionado'
    ]);
    exit;
}

$studentId = $data['student_id'];

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // 1. Cambiar el estado de admisión del estudiante a "Pendiente" (0)
    $updateStatusSql = "UPDATE user_register SET statusAdmin = '0' WHERE number_id = ?";
    $statusStmt = $conn->prepare($updateStatusSql);
    
    if (!$statusStmt) {
        throw new Exception("Error al preparar la actualización de estado: " . $conn->error);
    }
    
    $statusStmt->bind_param('s', $studentId);
    
    if (!$statusStmt->execute()) {
        throw new Exception("Error al actualizar el estado del estudiante: " . $statusStmt->error);
    }
    
    // 2. Registrar en el historial de cambios
    $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
    $historialStmt = $conn->prepare($historialSql);
    
    if (!$historialStmt) {
        throw new Exception("Error al preparar el registro de historial: " . $conn->error);
    }
    
    $descripcion = "Se eliminaron asignaciones de cursos y se cambió el estado a Pendiente";
    $historialStmt->bind_param('sss', $studentId, $_SESSION['username'], $descripcion);
    
    if (!$historialStmt->execute()) {
        throw new Exception("Error al registrar en el historial: " . $historialStmt->error);
    }
    
    // 3. Eliminar la asignación de cursos
    $deleteSql = "DELETE FROM course_assignments WHERE student_id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    
    if (!$deleteStmt) {
        throw new Exception("Error al preparar la eliminación de asignaciones: " . $conn->error);
    }
    
    $deleteStmt->bind_param('s', $studentId);
    
    if (!$deleteStmt->execute()) {
        throw new Exception("Error al eliminar las asignaciones: " . $deleteStmt->error);
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Respuesta exitosa
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Asignación eliminada correctamente'
    ]);
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    $conn->rollback();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}