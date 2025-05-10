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
        'message' => 'Error en la conexión a la base de datos',
        'error' => $conn->connect_error
    ]);
    exit;
}

// Acción principal: contar estudiantes con certificación previa y obtener detalles
function obtenerEstudiantesConCertificacion($conn)
{
    // Consultas SQL
    $sqlTotal = "SELECT COUNT(*) as total 
                 FROM user_register u
                 JOIN participantes p ON u.number_id = p.numero_documento";
    
    // Incluye los campos first_name, second_name, first_last, second_last
    $sqlDetalles = "SELECT p.numero_documento, p.region, u.first_name, u.second_name, u.first_last, u.second_last 
                    FROM user_register u
                    JOIN participantes p ON u.number_id = p.numero_documento
                    LIMIT 10"; // Limitar a 10 resultados para evitar sobrecarga

    // Ejecutar consultas
    $resultTotal = $conn->query($sqlTotal);
    $resultDetalles = $conn->query($sqlDetalles);

    $total = 0;
    $estudiantes = [];

    // Obtener total de registros
    if ($resultTotal && $row = $resultTotal->fetch_assoc()) {
        $total = intval($row['total']);
    }

    // Obtener detalles de los estudiantes
    if ($resultDetalles) {
        while ($row = $resultDetalles->fetch_assoc()) {
            $estudiantes[] = [
                'numero_documento' => $row['numero_documento'],
                'region' => $row['region'],
                'first_name' => $row['first_name'],
                'second_name' => $row['second_name'],
                'first_last' => $row['first_last'],
                'second_last' => $row['second_last']
            ];
        }
    }

    return ['total' => $total, 'estudiantes' => $estudiantes];
}

// Ejecutar y devolver resultado
try {
    $data = obtenerEstudiantesConCertificacion($conn);
    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_certificados' => $data['total'],
            'estudiantes' => $data['estudiantes']
        ]
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
