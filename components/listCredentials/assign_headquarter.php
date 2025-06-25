<?php
session_start();
require __DIR__ . '/../../conexion.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

try {
    $username = $_POST['username'] ?? '';
    $headquarter = $_POST['headquarter'] ?? '';
    
    if (empty($username) || empty($headquarter)) {
        throw new Exception('Username y sede son requeridos');
    }
    
    // Verificar si el usuario existe
    $checkUser = "SELECT username, nombre FROM users WHERE username = ?";
    $stmt = $conn->prepare($checkUser);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Usuario no encontrado');
    }
    
    $userData = $result->fetch_assoc();
    
    // Verificar si ya tiene una sede asignada
    $checkHeadquarter = "SELECT id FROM executor_headquarters WHERE username = ?";
    $stmt = $conn->prepare($checkHeadquarter);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Actualizar sede existente
        $updateSql = "UPDATE executor_headquarters SET headquarter = ?, creation_date = CURRENT_TIMESTAMP WHERE username = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("ss", $headquarter, $username);
        $action = 'updated';
    } else {
        // Insertar nueva sede
        $insertSql = "INSERT INTO executor_headquarters (username, headquarter) VALUES (?, ?)";
        $stmt = $conn->prepare($insertSql);
        $stmt->bind_param("ss", $username, $headquarter);
        $action = 'assigned';
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'action' => $action,
            'username' => $username,
            'headquarter' => $headquarter,
            'executor_name' => $userData['nombre'],
            'message' => 'Sede asignada correctamente'
        ]);
    } else {
        throw new Exception('Error al guardar en la base de datos: ' . $conn->error);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>