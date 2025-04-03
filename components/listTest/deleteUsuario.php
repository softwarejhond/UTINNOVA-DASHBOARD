<?php
require_once __DIR__ . '/../../controller/conexion.php'; 

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cedula'])) {
    $cedula = $_POST['cedula'];
    
    // Validar la entrada
    if (empty($cedula)) {
        echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
        exit;
    }
    
    // Realizar la eliminación
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE cedula = ?");
    $stmt->bind_param("s", $cedula);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'No se encontró el registro para eliminar']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $conn->error]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Solicitud inválida']);
}
?>