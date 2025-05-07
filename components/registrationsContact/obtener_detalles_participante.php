<?php
include_once '../../controller/conexion.php';

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Establecer cabeceras
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

if (!isset($_GET['numero_documento'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Número de documento no proporcionado'], JSON_UNESCAPED_UNICODE);
    exit;
}

$numero_documento = $_GET['numero_documento'];

try {
    $sql = "SELECT * FROM participantes WHERE numero_documento = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }
    
    $stmt->bind_param("s", $numero_documento);
    
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }
    
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        
        // Sanitizar y formatear los datos
        $data_sanitized = array_map(function($value) {
            return $value === null ? '' : htmlspecialchars(trim($value));
        }, $data);
        
        // Asegurarse de que las fechas tengan formato correcto
        if (isset($data_sanitized['fecha_nacimiento'])) {
            $fecha = date_create($data_sanitized['fecha_nacimiento']);
            $data_sanitized['fecha_nacimiento'] = $fecha ? date_format($fecha, 'Y-m-d') : '';
        }
        
        if (isset($data_sanitized['fecha_inicio'])) {
            $fecha = date_create($data_sanitized['fecha_inicio']);
            $data_sanitized['fecha_inicio'] = $fecha ? date_format($fecha, 'Y-m-d') : '';
        }
        
        echo json_encode($data_sanitized, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Participante no encontrado'], JSON_UNESCAPED_UNICODE);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Error en el servidor',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}