<?php
// Asegurarnos de que no haya salida previa
ob_start();

// Reportar todos los errores, pero no mostrarlos en la salida
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/conexion.php';

try {
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_POST['studentId']) || empty($_POST['classId']) || !isset($_POST['contactEstablished'])) {
        throw new Exception('Faltan datos requeridos');
    }

    $number_id = intval($_POST['studentId']);
    $class_id = intval($_POST['classId']);
    $contact_established = intval($_POST['contactEstablished']);
    $compromiso = $_POST['compromiso'] ?? '';
    $seguimiento_compromiso = $_POST['seguimientoCompromiso'] ?? '';
    $retiro = $_POST['retiro'] ?? '';
    $motivo_retiro = $_POST['motivoRetiro'] ?? '';
    $observacion = $_POST['observacion'] ?? '';
    $class_date = $_POST['classDate'] ?? date('Y-m-d');
    $id_advisor = $_SESSION['username'] ?? 0;

    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }

    // 1. Verificar si ya existe un registro para ese día, estudiante y clase
    $check_sql = "SELECT id FROM absence_log WHERE number_id = ? AND class_id = ? AND class_date = ?";
    $check_stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($check_stmt, "iis", $number_id, $class_id, $class_date);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    $existing_record = mysqli_fetch_assoc($result);
    mysqli_stmt_close($check_stmt);

    if ($existing_record) {
        // 2. Si existe, ACTUALIZAR (UPDATE)
        $sql = "UPDATE absence_log SET 
                    id_advisor = ?, contact_established = ?, compromiso = ?, 
                    seguimiento_compromiso = ?, retiro = ?, motivo_retiro = ?, observacion = ?
                WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        $record_id = $existing_record['id'];
        mysqli_stmt_bind_param($stmt, "issssssi", 
            $id_advisor, $contact_established, $compromiso, $seguimiento_compromiso, 
            $retiro, $motivo_retiro, $observacion, $record_id
        );
        $message = 'Registro actualizado correctamente';

    } else {
        // 3. Si no existe, INSERTAR (INSERT)
        $sql = "INSERT INTO absence_log (
                    number_id, class_id, id_advisor, class_date, contact_established,
                    compromiso, seguimiento_compromiso, retiro, motivo_retiro, observacion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "iiisisssss", 
            $number_id, $class_id, $id_advisor, $class_date, $contact_established,
            $compromiso, $seguimiento_compromiso, $retiro, $motivo_retiro, $observacion
        );
        $message = 'Registro guardado correctamente';
    }
    
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al ejecutar la consulta: ' . mysqli_stmt_error($stmt));
    }

    ob_clean();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} finally {
    if (isset($stmt) && $stmt) {
        mysqli_stmt_close($stmt);
    }
    ob_end_flush();
}
exit;
?>