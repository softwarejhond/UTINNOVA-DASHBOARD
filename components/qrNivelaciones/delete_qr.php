<?php
require_once(__DIR__ . '/../../controller/conexion.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $filename = $_POST['filename'];
    
    try {
        // Primero eliminamos el archivo físico
        $file_path = __DIR__ . '/../../img/qrcodes/' . $filename;
        if (file_exists($file_path)) {
            if (!unlink($file_path)) {
                throw new Exception("No se pudo eliminar el archivo físico");
            }
        }
        
        // Luego eliminamos el registro de la base de datos
        $stmt = $conn->prepare("DELETE FROM qr_masterclass WHERE id = ?");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta");
        }
        
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            throw new Exception("Error al eliminar el registro de la base de datos");
        }
        
        $stmt->close();
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false, 
            'error' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Método no permitido'
    ]);
}

error_log("ID recibido para eliminar: " . var_export($id, true));