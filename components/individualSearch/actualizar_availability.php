<?php
include_once('../../controller/conexion.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = isset($_POST['id']) ? trim($_POST['id']) : '';
    $nuevaAvailability = isset($_POST['nuevaAvailability']) ? trim($_POST['nuevaAvailability']) : '';
    $nuevoInternet = isset($_POST['nuevoInternet']) ? trim($_POST['nuevoInternet']) : '';
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

    if ($id === '' || $nuevaAvailability === '' || $nuevoInternet === '' || $username === '') {
        echo "error";
        exit;
    }

    // Iniciar transacción
    $conn->begin_transaction();

    try {
        // Actualizar los campos availability e internet en la tabla user_register
        $stmt = $conn->prepare("UPDATE user_register SET availability = ?, internet = ? WHERE number_id = ?");
        $stmt->bind_param("sss", $nuevaAvailability, $nuevoInternet, $id);

        if ($stmt->execute()) {
            // Insertar en el historial de cambios
            $descripcion = "Se actualiza disponibilidad de compromiso a '$nuevaAvailability' y estado de internet a '$nuevoInternet'";
            $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
            $stmtHistorial = $conn->prepare($historialSql);

            if ($stmtHistorial) {
                $stmtHistorial->bind_param('iss', $id, $username, $descripcion);
                if ($stmtHistorial->execute()) {
                    $conn->commit();
                    echo "success";
                } else {
                    throw new Exception("Error al registrar el historial");
                }
                $stmtHistorial->close();
            } else {
                throw new Exception("Error al preparar historial");
            }
        } else {
            throw new Exception("Error en la actualización");
        }
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        error_log("Error en la transacción: " . $e->getMessage());
        echo "error";
    }

    $conn->close();
} else {
    echo "error";
}
?>