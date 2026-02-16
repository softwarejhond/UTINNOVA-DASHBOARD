<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once '../controller/conexion.php';

// Iniciar sesión para obtener el usuario
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['documentos']) || !is_array($data['documentos'])
) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$documentos = array_map('trim', $data['documentos']);
$documentos = array_filter($documentos, function($d) { return $d !== ''; });

if (count($documentos) === 0) {
    echo json_encode(['success' => false, 'message' => 'No hay documentos']);
    exit;
}

// Iniciar transacción
$conn->autocommit(false);

try {
    // Actualizar el estado de los usuarios
    $placeholders = implode(',', array_fill(0, count($documentos), '?'));
    $sql = "UPDATE user_register SET statusAdmin = 12 WHERE number_id IN ($placeholders)";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception('Error en la preparación de la consulta');
    }

    $stmt->execute($documentos);
    $stmt->close();

    // Obtener el usuario que está haciendo el cambio
    $username = $_SESSION['username'] ?? 'Sistema';
    $descripcion = "Cambio a 'No aprobado' (Multiple)";

    // Crear un registro en change_history por cada cédula
    $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
    $historialStmt = $conn->prepare($historialSql);
    
    if ($historialStmt === false) {
        throw new Exception('Error en la preparación de la consulta de historial');
    }

    // Un registro por cada cédula procesada
    foreach ($documentos as $cedula) {
        $historialStmt->bind_param('iss', $cedula, $username, $descripcion);
        $historialStmt->execute();
    }
    
    $historialStmt->close();

    // Confirmar la transacción
    $conn->commit();
    $conn->autocommit(true);

    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Revertir en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    error_log("Error en pasar_no_aprobado.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
}
?>