<?php
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $modulo = $_POST['modulo'] ?? '';
    $link = $_POST['link'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if ($titulo && $modulo && $link && $descripcion) {
        $stmt = $conn->prepare("INSERT INTO tutoriales (titulo, modulo, link, descripcion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $titulo, $modulo, $link, $descripcion);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al guardar']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Campos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>