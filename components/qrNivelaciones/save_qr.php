<?php
require_once(__DIR__ . '/../../controller/conexion.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $course_id = $_POST['bootcamp'];
    $course_name = $_POST['course_name']; // Recibido desde el modal
    $fecha = $_POST['fecha'];
    $clases_equivalentes = isset($_POST['clases_equivalentes']) ? intval($_POST['clases_equivalentes']) : 1;

    // Formatear fecha para URL y título
    $fecha_url = date('Y-m-d', strtotime($fecha));
    $fecha_titulo = date('d/m/Y', strtotime($fecha));

    // Generar título y URL automáticamente
    $title = "Masterclass {$course_name}, {$fecha_titulo}";
    $url = "https://dashboard.uttalento.co/asistenciaMasterClass.php?fecha={$fecha_url}&curso={$course_id}";

    // Generar nombre de archivo con título y fecha/hora
    $fecha_archivo = date('Y-m-d_H-i-s');
    $titulo_limpio = preg_replace('/[^a-zA-Z0-9]/', '_', $title);
    $image_filename = $titulo_limpio . '_' . $fecha_archivo . '.png';

    try {
        // Crear directorio si no existe
        $qr_directory = __DIR__ . '/../../img/qrcodes/';
        if (!file_exists($qr_directory)) {
            mkdir($qr_directory, 0777, true);
        }

        // Preparar la consulta
        $stmt = $conn->prepare("INSERT INTO qr_masterclass (title, url, image_filename, clases_equivalentes) VALUES (?, ?, ?, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("sssi", $title, $url, $image_filename, $clases_equivalentes);

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