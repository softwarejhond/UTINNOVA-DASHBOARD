<?php
// Incluir conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar si hay datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar los datos recibidos
    $code = isset($_POST['code']) ? mysqli_real_escape_string($conn, $_POST['code']) : '';
    $notesLimit = isset($_POST['notes_limit']) ? mysqli_real_escape_string($conn, $_POST['notes_limit']) : null;

    // Validar datos recibidos
    if (empty($code)) {
        echo json_encode(['success' => false, 'message' => 'Código de curso no proporcionado']);
        exit;
    }

    // Preparar consulta SQL
    if ($notesLimit === '') {
        $sql = "UPDATE courses SET notes_limit = NULL WHERE code = '$code'";
    } else {
        $sql = "UPDATE courses SET notes_limit = '$notesLimit' WHERE code = '$code'";
    }

    // Ejecutar la consulta
    if ($conn->query($sql) === TRUE) {
        // Registro de actividad
        $currentUser = $_SESSION['username'] ?? 'sistema';
        $activityDescription = "Usuario $currentUser actualizó la fecha límite de notas para el curso $code";
        
        // Aquí puedes agregar código para registrar la actividad si tienes una tabla de logs
        
        echo json_encode(['success' => true, 'message' => 'Fecha límite actualizada correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar la fecha límite: ' . $conn->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}
?>