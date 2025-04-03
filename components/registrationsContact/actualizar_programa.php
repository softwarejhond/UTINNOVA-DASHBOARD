<?php
include_once('../../controller/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Primero, obtener los valores actuales de la base de datos
    $selectSql = "SELECT program, level, headquarters FROM user_register WHERE number_id = ?";
    $stmt = $conn->prepare($selectSql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentData = $result->fetch_assoc();
    $stmt->close();

    // Usar los valores enviados o mantener los actuales si no se envían
    $nuevoPrograma = isset($_POST['nuevoPrograma']) ? $_POST['nuevoPrograma'] : $currentData['program'];
    $nuevoNivel = isset($_POST['nuevoNivel']) ? $_POST['nuevoNivel'] : $currentData['level'];
    $nuevoSede = isset($_POST['nuevoSede']) ? $_POST['nuevoSede'] : $currentData['headquarters'];

    // Verificar que el id sea un número entero
    if (!is_numeric($id)) {
        echo "invalid_data";
        exit;
    }

    $updateSql = "UPDATE user_register SET program = ?, level = ?, headquarters = ?, dayUpdate = NOW() WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        $stmt->bind_param('sssi', $nuevoPrograma, $nuevoNivel, $nuevoSede, $id);

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
