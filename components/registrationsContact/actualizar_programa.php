<?php
include_once('../../controller/conexion.php');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // Obtener los valores actuales
    $selectSql = "SELECT program, level, headquarters, lote FROM user_register WHERE number_id = ?";
    $stmt = $conn->prepare($selectSql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentData = $result->fetch_assoc();
    $stmt->close();

    // Usar los valores enviados o mantener los actuales
    $nuevoPrograma = isset($_POST['nuevoPrograma']) ? $_POST['nuevoPrograma'] : $currentData['program'];
    $nuevoNivel = isset($_POST['nuevoNivel']) ? $_POST['nuevoNivel'] : $currentData['level'];
    $nuevoSede = isset($_POST['nuevoSede']) ? $_POST['nuevoSede'] : $currentData['headquarters'];
    $nuevoLote = isset($_POST['nuevoLote']) ? $_POST['nuevoLote'] : $currentData['lote'];

    if (!is_numeric($id)) {
        echo "invalid_data";
        exit;
    }

    $updateSql = "UPDATE user_register SET 
                  program = ?, 
                  level = ?, 
                  headquarters = ?, 
                  lote = ?,
                  dayUpdate = NOW() 
                  WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        $stmt->bind_param('sssis', $nuevoPrograma, $nuevoNivel, $nuevoSede, $nuevoLote, $id);

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
