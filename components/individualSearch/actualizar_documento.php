<?php
include_once('../../controller/conexion.php');

// Verificar que se recibieron todos los datos necesarios
if (!isset($_POST['id']) || !isset($_POST['nuevoTipoDoc']) || !isset($_POST['nuevoNumeroDoc']) || !isset($_POST['confirmarNumeroDoc']) || !isset($_POST['numeroActual'])) {
    echo "error_datos_faltantes";
    exit;
}

$id = $_POST['id'];
$nuevoTipoDoc = trim($_POST['nuevoTipoDoc']);
$nuevoNumeroDoc = trim($_POST['nuevoNumeroDoc']);
$confirmarNumeroDoc = trim($_POST['confirmarNumeroDoc']);
$numeroActual = $_POST['numeroActual'];

// Validaciones
if (empty($nuevoTipoDoc) || empty($nuevoNumeroDoc) || empty($confirmarNumeroDoc)) {
    echo "error_campos_vacios";
    exit;
}

// Validar que los números coincidan
if ($nuevoNumeroDoc !== $confirmarNumeroDoc) {
    echo "error_numeros_no_coinciden";
    exit;
}

// Validar tipo de documento
if (!in_array($nuevoTipoDoc, ['CC', 'Otra'])) {
    echo "error_tipo_documento_invalido";
    exit;
}

// Validar formato del número de documento
if (!is_numeric($nuevoNumeroDoc) || strlen($nuevoNumeroDoc) < 6 || strlen($nuevoNumeroDoc) > 10) {
    echo "error_formato_numero";
    exit;
}

try {
    // Verificar que el usuario actual existe
    $stmt = $conn->prepare("SELECT id FROM user_register WHERE number_id = ?");
    $stmt->bind_param("s", $numeroActual);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo "error_usuario_no_encontrado";
        exit;
    }
    $userData = $result->fetch_assoc();
    $userId = $userData['id'];
    $stmt->close();
    
    // Si el número no cambió, solo actualizar el tipo
    if ($nuevoNumeroDoc === $numeroActual) {
        $stmt = $conn->prepare("UPDATE user_register SET typeID = ?, dayUpdate = NOW() WHERE id = ?");
        $stmt->bind_param("si", $nuevoTipoDoc, $userId);
        
        if ($stmt->execute()) {
            echo "success";
        } else {
            echo "error_actualizacion";
        }
        $stmt->close();
        exit;
    }
    
    // Verificar que el nuevo número no esté siendo usado por otro usuario
    $stmt = $conn->prepare("SELECT id FROM user_register WHERE number_id = ? AND id != ?");
    $stmt->bind_param("si", $nuevoNumeroDoc, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "duplicate";
        $stmt->close();
        exit;
    }
    $stmt->close();
    
    // Iniciar transacción para actualizar todas las tablas relacionadas
    $conn->autocommit(false);
    
    try {
        // Actualizar tabla principal user_register
        $stmt = $conn->prepare("UPDATE user_register SET typeID = ?, number_id = ?, dayUpdate = NOW() WHERE id = ?");
        $stmt->bind_param("ssi", $nuevoTipoDoc, $nuevoNumeroDoc, $userId);
        $stmt->execute();
        $stmt->close();
        
        // Actualizar tabla course_assignments si existe
        $stmt = $conn->prepare("UPDATE course_assignments SET student_id = ? WHERE student_id = ?");
        $stmt->bind_param("ss", $nuevoNumeroDoc, $numeroActual);
        $stmt->execute();
        $stmt->close();
        
        // Confirmar transacción
        $conn->commit();
        echo "success";
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        echo "error_transaccion: " . $e->getMessage();
    }
    
    $conn->autocommit(true);
    
} catch (Exception $e) {
    echo "error_servidor: " . $e->getMessage();
}
?>