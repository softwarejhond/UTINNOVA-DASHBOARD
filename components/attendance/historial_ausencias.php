<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

try {
    $studentId = $_POST['studentId'] ?? null;

    // Validar entrada
    if (!$studentId) {
        throw new Exception('ID de estudiante no proporcionado', 400);
    }

    // Validar conexión
    if (!$conn) {
        throw new Exception('Error de conexión a la base de datos: ' . mysqli_connect_error(), 500);
    }

    $sql = "SELECT 
                a.class_date,
                a.contact_established,
                a.compromiso,
                a.seguimiento_compromiso,
                a.retiro,
                a.motivo_retiro,
                a.observacion,
                a.creation_date,
                u.nombre as advisor_name
            FROM absence_log a
            LEFT JOIN users u ON a.id_advisor = u.username
            WHERE a.number_id = ?
            ORDER BY a.class_date DESC
            LIMIT 10";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación de la consulta: ' . mysqli_error($conn), 500);
    }

    mysqli_stmt_bind_param($stmt, "s", $studentId);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error al ejecutar la consulta: ' . mysqli_stmt_error($stmt), 500);
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn), 500);
    }

    $registros = [];
    while ($row = mysqli_fetch_assoc($result)) {
        // Formatear la fecha en PHP
        $row['class_date'] = date('d/m/Y', strtotime($row['class_date']));
        $registros[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $registros,
        'count' => count($registros)
    ]);

} catch (Exception $e) {
    error_log("Error en historial_ausencias.php: " . $e->getMessage());
    http_response_code($e->getCode() ?: 500);
    echo json_encode([
        'success' => false,
        'error' => [
            'type' => get_class($e),
            'code' => $e->getCode(),
            'message' => $e->getMessage(),
            'detail' => $e->getTrace()[0] ?? null
        ]
    ]);
}

mysqli_close($conn);
