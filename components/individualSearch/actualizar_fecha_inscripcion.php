<?php
include_once('../../controller/conexion.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $fecha = $_POST['nuevaFechaInscripcion'] ?? '';
    $username = $_SESSION['username'] ?? '';

    // Validaciones básicas
    if (!is_numeric($id) || !$fecha || !$username) {
        echo "invalid_data";
        exit;
    }

    // Validar formato de fecha y hora (YYYY-MM-DD HH:MM:SS)
    if (!preg_match("/^\d{4}-\d{2}-\d{2} 00:00:00$/", $fecha)) {
        echo "invalid_date";
        exit;
    }

    $conn->begin_transaction();

    try {
        // Actualizar la fecha de inscripción
        $updateSql = "UPDATE user_register SET creationDate = ? WHERE number_id = ?";
        $stmt = $conn->prepare($updateSql);

        if ($stmt) {
            $stmt->bind_param('si', $fecha, $id);

            if ($stmt->execute()) {
                // Registrar en historial
                $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
                $stmtHistorial = $conn->prepare($historialSql);

                if ($stmtHistorial) {
                    $descripcion = "Se actualiza fecha de inscripción";
                    $stmtHistorial->bind_param('iss', $id, $username, $descripcion);

                    if ($stmtHistorial->execute()) {
                        $conn->commit();
                        echo "success";
                    } else {
                        throw new Exception("Error al registrar el historial");
                    }
                    $stmtHistorial->close();
                }
            } else {
                throw new Exception("Error en la actualización de la fecha");
            }
            $stmt->close();
        } else {
            throw new Exception("Error al preparar la consulta");
        }
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en la transacción: " . $e->getMessage());
        echo "error";
    }
} else {
    echo "error";
}
?>