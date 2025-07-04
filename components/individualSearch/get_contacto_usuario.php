<?php
include_once('../../controller/conexion.php');

if ($_POST['id']) {
    $id = $_POST['id'];
    
    $query = "SELECT first_phone, second_phone, email, emergency_contact_name, emergency_contact_number, contactMedium 
              FROM user_register WHERE number_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ID no proporcionado']);
}
?>