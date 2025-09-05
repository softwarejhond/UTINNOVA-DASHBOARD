<?php
require_once __DIR__ . '/../../controller/conexion.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    $headquarters = $data['headquarters'];
    $classrooms = $data['classrooms']; // Array de {name, bootcamp_id}
    $created_by = isset($_SESSION['username']) ? $_SESSION['username'] : 'desconocido';

    $success = true;
    $duplicated = false;
    foreach ($classrooms as $c) {
        $name = $c['name'];
        $bootcamp_id = $c['bootcamp_id'];

        // Verificar si ya existe un aula con ese bootcamp (único en toda la tabla)
        $sql_check = "SELECT id FROM classrooms WHERE bootcamp_id = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("i", $bootcamp_id);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            $duplicated = true;
            break;
        }

        // Si no existe, guardar
        $sql = "INSERT INTO classrooms (headquarters, classroom_name, bootcamp_id, created_by) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssis", $headquarters, $name, $bootcamp_id, $created_by);
        if (!$stmt->execute()) {
            $success = false;
            break;
        }
    }

    if ($duplicated) {
        echo json_encode(['success' => false, 'error' => 'Ya existe un aula con ese bootcamp registrado.' ]);
    } else {
        echo json_encode(['success' => $success]);
    }
}
?>