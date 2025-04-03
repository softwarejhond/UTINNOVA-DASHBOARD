<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

try {
    // Recoger datos del POST
    $number_id = $_POST['studentId'] ?? '';
    $class_id = $_POST['classId'] ?? '';
    $contact_established = $_POST['contactEstablished'] ?? '';
    $compromiso = $_POST['compromiso'] ?? '';
    $seguimiento_compromiso = $_POST['seguimientoCompromiso'] ?? '';
    $retiro = $_POST['retiro'] ?? '';
    $motivo_retiro = $_POST['motivoRetiro'] ?? '';
    $observacion = $_POST['observacion'] ?? '';
    $class_date = $_POST['classDate'] ?? date('Y-m-d');
    session_start(); // Ensure session is started
    $id_advisor = $_SESSION['username'] ?? 1; // Use session username, fallback to 1 if not set

    $sql = "INSERT INTO absence_log (
        number_id, class_id, id_advisor, class_date, contact_established,
        compromiso, seguimiento_compromiso, retiro, motivo_retiro, observacion
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iiisisssss", 
        $number_id, $class_id, $id_advisor, $class_date, $contact_established,
        $compromiso, $seguimiento_compromiso, $retiro, $motivo_retiro, $observacion
    );

    if (mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception(mysqli_error($conn));
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}