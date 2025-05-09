<?php
require_once(__DIR__ . '/../../controller/conexion.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'];
    $url = $_POST['url'];
    
    // Generar nombre de archivo con título y fecha
    $fecha = date('Y-m-d_H-i-s');
    // Limpiar el título para usarlo como nombre de archivo
    $titulo_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', $title);
    $image_filename = $titulo_limpio . '_' . $fecha . '.png';
    
    try {
        // Crear directorio si no existe
        $qr_directory = __DIR__ . '/../../img/qrcodes/';
        if (!file_exists($qr_directory)) {
            mkdir($qr_directory, 0777, true);
        }
        
        // Preparar la consulta
        $stmt = $conn->prepare("INSERT INTO qr_codes (title, url, image_filename) VALUES (?, ?, ?)");
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        
        $stmt->bind_param("sss", $title, $url, $image_filename);
        
        if ($stmt->execute()) {
            // Generar y guardar el código QR
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?data=" . urlencode($url) . "&size=200x200";
            $qr_image = file_get_contents($qr_url);
            
            if ($qr_image === false) {
                throw new Exception("Error al obtener la imagen QR");
            }
            
            if (file_put_contents($qr_directory . $image_filename, $qr_image) === false) {
                throw new Exception("Error al guardar la imagen QR");
            }
            
            echo json_encode([
                'success' => true,
                'filename' => $image_filename
            ]);
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
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