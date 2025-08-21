<?php
include("../../controller/conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $titulo = $_POST['titulo'] ?? '';
    $modulo = $_POST['modulo'] ?? '';
    $link = $_POST['link'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';

    if ($id && $titulo && $modulo && $link && $descripcion) {
        $stmt = $conn->prepare("UPDATE tutoriales SET titulo=?, modulo=?, link=?, descripcion=? WHERE id=?");
        $stmt->bind_param("ssssi", $titulo, $modulo, $link, $descripcion, $id);
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Error al actualizar']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Campos incompletos']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Método no permitido']);
}
?>