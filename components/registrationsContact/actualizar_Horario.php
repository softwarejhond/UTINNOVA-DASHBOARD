<?php
include_once('../../controller/conexion.php');

// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asegurarse de que la conexión a la base de datos esté configurada
if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Verificar que los datos necesarios se hayan enviado mediante POST
if (isset($_POST['id'])) {
    $id = $_POST['id'];
    
    // Obtener los valores actuales
    $selectSql = "SELECT schedules, schedules_alternative FROM user_register WHERE number_id = ?";
    $stmt = $conn->prepare($selectSql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $currentData = $result->fetch_assoc();
    $stmt->close();

    // Usar los valores enviados o mantener los actuales
    $nuevoHorario = isset($_POST['nuevoHorario']) ? $_POST['nuevoHorario'] : $currentData['schedules'];
    $nuevoHorarioAlt = isset($_POST['nuevoHorarioAlternativo']) ? $_POST['nuevoHorarioAlternativo'] : $currentData['schedules_alternative'];

    // Verificar que el id sea un número entero
    if (!is_numeric($id)) {
        echo "invalid_data"; // Si el id no es válido
        exit;
    }

    // Consulta SQL para actualizar horario, horario alternativo y fecha de actualización
    $updateSql = "UPDATE user_register SET 
                  schedules = ?,
                  schedules_alternative = ?,
                  dayUpdate = NOW() 
                  WHERE number_id = ?";
    
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        // Vincular los parámetros para la consulta preparada
        $stmt->bind_param('ssi', $nuevoHorario, $nuevoHorarioAlt, $id);

        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "success"; // Devolver éxito si la actualización fue exitosa
        } else {
            // Log de error para depuración
            error_log("Error en la ejecución de la consulta: " . $stmt->error);
            echo "error"; // Devolver error si hubo un problema con la consulta
        }

        // Cerrar la consulta preparada
        $stmt->close();
    } else {
        // Log de error si la preparación de la consulta falló
        error_log("Error al preparar la consulta: " . $conn->error);
        echo "error"; // Si la preparación de la consulta falló
    }
} else {
    // Log de error si no se enviaron los datos requeridos
    error_log("ID no enviado: " . json_encode($_POST));
    echo "invalid_data"; // Si no se enviaron los datos requeridos
}