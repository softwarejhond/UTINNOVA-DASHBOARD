<?php
// Encabezado para JSON
header('Content-Type: application/json');

// Permitir solo métodos GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Incluir la conexión
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar si la conexión es válida
if (!$conn) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Error en la conexión a la base de datos'
    ]);
    exit;
}

// Acción principal: contar estudiantes con certificación previa
function contarEstudiantesConCertificacion($conn)
{
    // Consulta optimizada usando subconsulta
    $sql = "SELECT COUNT(*) as total
            FROM (
                SELECT 1
                FROM user_register u
                INNER JOIN participantes p ON u.number_id = p.numero_documento
                WHERE u.status = '1'
            ) AS sub";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        return intval($row['total']);
    }

    return 0;
}

// Ejecutar y devolver resultado
try {
    $total = contarEstudiantesConCertificacion($conn);
    echo json_encode([
        'status' => 'success',
        'data' => ['total_certificados' => $total]
    ]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'status' => 'error',
        'message' => 'Ocurrió un error al consultar los datos',
        'error' => $e->getMessage()
    ]);
}
?>
