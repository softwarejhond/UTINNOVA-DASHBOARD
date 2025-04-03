<?php
include_once('../../controller/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Verificar que el id sea un número entero
    if (!is_numeric($id)) {
        echo "invalid_data";
        exit;
    }

    // Obtener los valores enviados
    $nuevoFirstName = $_POST['primerNombre'] ?? '';
    $nuevoSecondName = $_POST['segundoNombre'] ?? '';
    $nuevoFirstLast = $_POST['primerApellido'] ?? '';
    $nuevoSecondLast = $_POST['segundoApellido'] ?? '';

    $updateSql = "UPDATE user_register SET 
                  first_name = ?, 
                  second_name = ?, 
                  first_last = ?, 
                  second_last = ?,
                  dayUpdate = NOW() 
                  WHERE number_id = ?";
                  
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        $stmt->bind_param('ssssi', $nuevoFirstName, $nuevoSecondName, $nuevoFirstLast, $nuevoSecondLast, $id);

        if ($stmt->execute()) {
            echo "success";
        } else {
            error_log("Error en la ejecución de la consulta: " . $stmt->error);
            echo "error";
        }
        $stmt->close();
    } else {
        error_log("Error al preparar la consulta: " . $conn->error);
        echo "error";
    }
} else {
    error_log("ID no enviado: " . json_encode($_POST));
    echo "invalid_data";
}
