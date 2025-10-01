<?php
session_start();
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $period_id = intval($_POST['period_id'] ?? 0);
        $cohort = intval($_POST['cohort'] ?? 0);
        $payment_number = intval($_POST['payment_number'] ?? 0); // <-- NUEVO CAMPO
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $status = intval($_POST['status'] ?? 1);

        // Validar campos obligatorios
        if ($period_id <= 0 || $cohort <= 0 || empty($start_date) || empty($end_date)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
            exit;
        }

        // Validar fechas
        if (strtotime($end_date) <= strtotime($start_date)) {
            echo json_encode(['success' => false, 'message' => 'La fecha de fin debe ser posterior a la fecha de inicio']);
            exit;
        }

        // Verificar que el período existe
        $check_sql = "SELECT id FROM course_periods WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $period_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'El período no existe']);
            exit;
        }

        // Preparar la consulta de actualización (solo campos permitidos)
        $sql = "UPDATE course_periods SET 
            cohort = ?, 
            payment_number = ?, 
            start_date = ?, 
            end_date = ?, 
            status = ?,
            updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        $stmt->bind_param("iissii", $cohort, $payment_number, $start_date, $end_date, $status, $period_id);

        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Período actualizado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'success' => true, 
                    'message' => 'No se realizaron cambios en los datos'
                ]);
            }
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $stmt->close();
        $check_stmt->close();

    } catch (Exception $e) {
        error_log("Error en update_period.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>