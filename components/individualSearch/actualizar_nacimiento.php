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
if (isset($_POST['id']) && isset($_POST['nuevaFecha'])) {
    // Depuración: Verifica los valores recibidos
    error_log("ID recibido: " . $_POST['id']);
    error_log("Nueva fecha recibida: " . $_POST['nuevaFecha']);

    $id = $_POST['id'];
    $nuevaFecha = $_POST['nuevaFecha'];

    // Verificar que el id sea un número entero
    if (!is_numeric($id)) {
        echo "invalid_data";
        exit;
    }

    // Verificar formato de fecha válido (YYYY-MM-DD)
    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $nuevaFecha)) {
        echo "invalid_date";
        exit;
    }

    // Consulta SQL para actualizar la fecha de nacimiento
    $updateSql = "UPDATE user_register SET birthdate = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        // Vincular los parámetros para la consulta preparada
        $stmt->bind_param('si', $nuevaFecha, $id);

        // Ejecutar la consulta
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
    error_log("Datos no enviados correctamente: " . json_encode($_POST));
    echo "invalid_data";
}