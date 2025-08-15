<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

// Sanitiza y valida el input
$number_id = isset($_POST['number_id']) ? intval($_POST['number_id']) : 0;
if ($number_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Número de identificación inválido']);
    exit;
}

// Consulta SQL segura
$sql = "SELECT full_name, headquarters, mode, bootcamp_name, institutional_email FROM groups WHERE number_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $number_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    echo json_encode([
        'success' => true,
        'campista' => [
            'full_name' => $row['full_name'],
            'headquarters' => $row['headquarters'],
            'mode' => $row['mode'],
            'bootcamp_name' => $row['bootcamp_name'],
            'institutional_email' => $row['institutional_email']
        ]
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se encontró ningún campista matriculado con ese número de identificación.']);
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
?>