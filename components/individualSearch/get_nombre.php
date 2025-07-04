<?php
require_once '../../controller/conexion.php';

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $sql = "SELECT first_name, second_name, first_last, second_last 
            FROM user_register 
            WHERE number_id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'first_name' => $row['first_name'],
            'second_name' => $row['second_name'],
            'first_last' => $row['first_last'],
            'second_last' => $row['second_last']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Usuario no encontrado'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID no proporcionado'
    ]);
}