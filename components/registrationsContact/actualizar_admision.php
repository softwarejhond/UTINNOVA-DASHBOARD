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
if (isset($_POST['id']) && isset($_POST['nuevoEstado'])) {
    $id = $_POST['id'];
    $nuevoEstado = $_POST['nuevoEstado'];

    // Verificar que el id sea un número entero
    if (!is_numeric($id)) {
        echo "invalid_data"; // Si el id no es válido
        exit;
    }

    // 1. Buscar el email del usuario
    $email = null;
    $sqlEmail = "SELECT email FROM user_register WHERE number_id = ?";
    $stmtEmail = $conn->prepare($sqlEmail);
    if ($stmtEmail) {
        $stmtEmail->bind_param('i', $id);
        $stmtEmail->execute();
        $stmtEmail->bind_result($email);
        $stmtEmail->fetch();
        $stmtEmail->close();
    }

    // 2. Verificar si existe en la tabla groups por number_id o email
    $sqlCheck = "SELECT COUNT(*) FROM groups WHERE number_id = ? OR email = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    if ($stmtCheck) {
        $stmtCheck->bind_param('is', $id, $email);
        $stmtCheck->execute();
        $stmtCheck->bind_result($existeGrupo);
        $stmtCheck->fetch();
        $stmtCheck->close();

        if ($existeGrupo > 0) {
            echo "desmatricular_primero";
            exit;
        }
    } else {
        error_log("Error al preparar la consulta de verificación: " . $conn->error);
        echo "error";
        exit;
    }

    // 3. Si no existe en groups, actualizar el estado de admisión
    $updateSql = "UPDATE user_register SET statusAdmin = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        $stmt->bind_param('si', $nuevoEstado, $id);
        if ($stmt->execute()) {
            // Registrar en el historial de cambios
            session_start();
            $username = $_SESSION['username'] ?? 'Sistema';
            $descripcion = "Cambio de estado de admisión a '$nuevoEstado'";
            $historialStmt = $conn->prepare("INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)");
            $historialStmt->bind_param('iss', $id, $username, $descripcion);
            $historialStmt->execute();
            $historialStmt->close();

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
    error_log("Datos no enviados correctamente: " . json_encode($_POST));
    echo "invalid_data"; // Si no se enviaron los datos requeridos
}