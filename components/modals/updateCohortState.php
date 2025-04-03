<?php
include '../../controller/conexion.php';

header('Content-Type: application/json');

try {
    // Obtener datos de la solicitud
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['cohort_number'])) {
        throw new Exception('No se proporcionó el número de cohorte');
    }
    
    $cohortNumber = $data['cohort_number'];
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    // Primero, restablecer todos los estados a 0
    $resetSql = "UPDATE cohorts SET state = 0";
    if (!$conn->query($resetSql)) {
        throw new Exception("Error al restablecer estados: " . $conn->error);
    }
    
    // Si no se proporciona un cohort_number vacío, actualizar el estado de la cohorte seleccionada
    if ($cohortNumber !== '') {
        $updateSql = "UPDATE cohorts SET state = 1 WHERE cohort_number = ?";
        $stmt = $conn->prepare($updateSql);
        if (!$stmt) {
            throw new Exception("Error en la preparación de la actualización: " . $conn->error);
        }
        
        $stmt->bind_param('s', $cohortNumber);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar estado de cohorte: " . $stmt->error);
        }
    }
    
    // Confirmar transacción
    $conn->commit();
    
    // Devolver respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Estado de cohorte actualizado correctamente',
        'cohort' => $cohortNumber
    ]);
    
} catch (Exception $e) {
    // Revertir cambios si hay error
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    // Cerrar conexiones
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>