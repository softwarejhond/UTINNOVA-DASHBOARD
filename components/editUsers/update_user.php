<?php
include '../../controller/conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $id = $_POST['id'];
    $nombre = trim($_POST['nombre']);
    $rol = $_POST['rol'];
    $extra_rol = $_POST['extra_rol'];
    $email = trim($_POST['email']);
    $genero = $_POST['genero'];
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $edad = $_POST['edad'];

    // Iniciar la construcción de la consulta SQL
    $sql = "UPDATE users SET nombre = ?, rol = ?, extra_rol = ?, email = ?, genero = ?, telefono = ?, direccion = ?, edad = ?";
    $params = [$nombre, $rol, $extra_rol, $email, $genero, $telefono, $direccion, $edad];
    $types = "siissssi"; // string, integer, integer, string, string, string, string, integer


    // Si se está actualizando la contraseña
    if (isset($_POST['password']) && !empty($_POST['password'])) {
        $password = $_POST['password'];
        $passwordHashed = password_hash($password, PASSWORD_DEFAULT);
        $sql .= ", password = ?";
        $params[] = $passwordHashed;
        $types .= "s";
    }

    // Completar la consulta
    $sql .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);
    } else {
        throw new Exception("Error al actualizar el usuario: " . $stmt->error);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$stmt->close();
$conn->close();
?>