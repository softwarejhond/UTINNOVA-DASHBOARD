<?php
include_once('../../controller/conexion.php');

if ($_POST['id']) {
    $id = $_POST['id'];
    $telefono1 = trim($_POST['telefono1']);
    $telefono2 = trim($_POST['telefono2']);
    $email = trim($_POST['email']);
    $contactoEmergencia = trim($_POST['contactoEmergencia']);
    $numeroEmergencia = trim($_POST['numeroEmergencia']);
    $medioContacto = $_POST['medioContacto'];
    
    // Validaciones del servidor
    if (empty($telefono1) || empty($email) || empty($contactoEmergencia) || empty($numeroEmergencia)) {
        echo "error_campos_vacios";
        exit;
    }
    
    // Validar formato de teléfonos
    if (!preg_match('/^[0-9]{10}$/', $telefono1)) {
        echo "error_formato_telefono1";
        exit;
    }
    
    if (!empty($telefono2) && !preg_match('/^[0-9]{10}$/', $telefono2)) {
        echo "error_formato_telefono2";
        exit;
    }
    
    if (!preg_match('/^[0-9]{10}$/', $numeroEmergencia)) {
        echo "error_formato_numero_emergencia";
        exit;
    }
    
    // Validar email
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "error_formato_email";
        exit;
    }
    
    try {
        $query = "UPDATE user_register SET 
                  first_phone = ?, 
                  second_phone = ?, 
                  email = ?, 
                  emergency_contact_name = ?, 
                  emergency_contact_number = ?, 
                  contactMedium = ?,
                  dayUpdate = NOW()
                  WHERE number_id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssss", $telefono1, $telefono2, $email, $contactoEmergencia, $numeroEmergencia, $medioContacto, $id);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error_query: " . $stmt->error;
        }
    } catch (Exception $e) {
        echo "error_exception: " . $e->getMessage();
    }
} else {
    echo "error_no_id";
}
?>