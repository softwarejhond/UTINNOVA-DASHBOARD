<?php
require_once '../../controller/conexion.php';

if(isset($_GET['id'])) {
    $number_id = $_GET['id'];
    
    // Verificar si existe en la tabla participantes
    $sql = "SELECT COUNT(*) as existe FROM participantes WHERE numero_documento = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $number_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode(['existe' => $row['existe'] > 0]);
    
    $stmt->close();
} else {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'ID no proporcionado']);
}
$conn->close();