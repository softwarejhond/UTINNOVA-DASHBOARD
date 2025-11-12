<?php
session_start();
require_once '../../controller/conexion.php';

// Verificar que el usuario esté autenticado
if (!isset($_SESSION['username'])) {
    http_response_code(403);
    die('Acceso no autorizado');
}

// Obtener el number_id desde la URL
$number_id = $_GET['number_id'] ?? '';

if (empty($number_id)) {
    http_response_code(400);
    die('ID de estudiante no proporcionado');
}

try {
    // Consultar el archivo más reciente del estudiante
    $stmt = $conn->prepare("
        SELECT file_path, file_name 
        FROM carnet_records 
        WHERE number_id = ? AND is_active = 1 
        ORDER BY generated_at DESC 
        LIMIT 1
    ");
    
    $stmt->bind_param("s", $number_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        http_response_code(404);
        die('No se encontró carnet para este estudiante');
    }
    
    $row = $result->fetch_assoc();
    $file_path = $row['file_path'];
    $file_name = $row['file_name'];
    
    // Construir la ruta completa del archivo
    $full_path = __DIR__ . '/../../carnets/' . $file_name;
    
    // Verificar que el archivo existe
    if (!file_exists($full_path)) {
        http_response_code(404);
        die('El archivo del carnet no existe en el servidor');
    }
    
    // Obtener información del archivo
    $file_size = filesize($full_path);
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
    
    // Determinar el tipo MIME
    $mime_type = 'application/octet-stream'; // Por defecto
    
    switch (strtolower($file_extension)) {
        case 'pdf':
            $mime_type = 'application/pdf';
            break;
        case 'jpg':
        case 'jpeg':
            $mime_type = 'image/jpeg';
            break;
        case 'png':
            $mime_type = 'image/png';
            break;
        case 'gif':
            $mime_type = 'image/gif';
            break;
    }
    
    // Enviar headers para la descarga
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: attachment; filename="carnet_' . $number_id . '.' . $file_extension . '"');
    header('Content-Length: ' . $file_size);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: public');
    
    // Limpiar cualquier output buffer
    if (ob_get_level()) {
        ob_end_clean();
    }
    
    // Enviar el archivo
    readfile($full_path);
    exit;
    
} catch (Exception $e) {
    error_log("Error al descargar carnet: " . $e->getMessage());
    http_response_code(500);
    die('Error interno del servidor');
} finally {
    if (isset($stmt)) {
        $stmt->close();
    }
    if (isset($conn)) {
        $conn->close();
    }
}
?>