<?php
require_once(__DIR__ . '/../../controller/conexion.php');
header('Content-Type: application/json');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener datos del formulario
    $course_id = $_POST['bootcamp'];
    $course_name = $_POST['course_name']; // Recibido desde el modal
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $clases_equivalentes = 0; // Siempre 0
    $created_by = isset($_SESSION['username']) ? intval($_SESSION['username']) : 0; // Asegura que sea int

    // Combinar fecha y hora para crear datetime
    $datetime = $fecha . ' ' . $hora . ':00'; // Agregar segundos

    // Formatear fecha para URL y título
    $fecha_url = date('Y-m-d', strtotime($fecha));
    $fecha_titulo = date('d/m/Y', strtotime($fecha));
    $hora_formateada = date('H:i', strtotime($hora));

    // Generar título y URL automáticamente para mentoría
    $title = "Mentoría {$course_name}, {$fecha_titulo} {$hora_formateada}";
    $url = "https://dashboard.utinnova.co/asistenciaMentorias.php?fecha={$fecha_url}&curso={$course_id}&hora={$hora}";

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

        // Preparar la consulta con el campo date
        $stmt = $conn->prepare("INSERT INTO qr_mentorias (title, url, date, image_filename, clases_equivalentes, authorized_by, authorized, created_by) VALUES (?, ?, ?, ?, ?, 0, 0, ?)");
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }
        $stmt->bind_param("ssssii", $title, $url, $datetime, $image_filename, $clases_equivalentes, $created_by);

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
                'filename' => $image_filename,
                'datetime' => $datetime
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