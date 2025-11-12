<?php
session_start();
require_once '../../controller/conexion.php';

header('Content-Type: application/json');

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso no autorizado']);
    exit;
}

$number_id = $_GET['number_id'] ?? '';

if (empty($number_id)) {
    echo json_encode(['exists' => false, 'error' => 'ID no proporcionado']);
    exit;
}

try {
    // Verificar si existe un carnet activo para el estudiante
    $stmt = $conn->prepare("
        SELECT COUNT(*) as count, file_name, generated_at 
        FROM carnet_records 
        WHERE number_id = ? AND is_active = 1
    ");
    
    $stmt->bind_param("s", $number_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    $exists = $row['count'] > 0;
    
    if ($exists) {
        // Verificar que el archivo físico también existe
        $file_path = __DIR__ . '/../../carnets/' . $row['file_name'];
        $file_exists = file_exists($file_path);
        
        echo json_encode([
            'exists' => $file_exists,
            'file_name' => $row['file_name'],
            'generated_at' => $row['generated_at']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
    
} catch (Exception $e) {
    error_log("Error al verificar carnet: " . $e->getMessage());
    echo json_encode(['exists' => false, 'error' => 'Error interno']);
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>