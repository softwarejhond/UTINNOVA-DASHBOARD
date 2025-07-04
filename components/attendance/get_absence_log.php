<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../controller/conexion.php';

ob_start();

if (empty($_POST['studentId']) || empty($_POST['classId']) || empty($_POST['classDate'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Faltan parámetros requeridos.']);
    exit;
}

$studentId = intval($_POST['studentId']);
$classId = intval($_POST['classId']);
$classDate = $_POST['classDate'];

try {
    if (!$conn) {
        throw new Exception('Error de conexión a la base de datos.');
    }

    $sql = "SELECT * FROM absence_log 
            WHERE number_id = ? AND class_id = ? AND class_date = ? 
            ORDER BY creation_date DESC 
            LIMIT 1";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "iis", $studentId, $classId, $classDate);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    ob_clean();
    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => true, 'data' => null]);
    }

    mysqli_stmt_close($stmt);

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

ob_end_flush();
exit;
?>