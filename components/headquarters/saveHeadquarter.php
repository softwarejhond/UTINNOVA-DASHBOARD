<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include("../../conexion.php");

// Iniciar sesión para obtener el username
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
        $modalidad = mysqli_real_escape_string($conn, $_POST['modalidad']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Obtener el username de la sesión
        $created_by = isset($_SESSION['username']) ? mysqli_real_escape_string($conn, $_SESSION['username']) : 'sistema';

        $photoName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpeg', 'image/jpg' => 'jpg'];
            $fileType = $_FILES['foto']['type'];
            if (array_key_exists($fileType, $allowed)) {
                $ext = $allowed[$fileType] == 'jpeg' ? 'jpg' : $allowed[$fileType];
                $nombreSede = substr($nombre, 0, 5);
                $fecha = date('YmdHis');
                $photoName = "sede_" . preg_replace('/[^A-Za-z0-9]/', '', $nombreSede) . "_" . $fecha . "." . $ext;
                $destino = "../../img/sedes/" . $photoName;
                move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
            }
        }

        $query = "INSERT INTO headquarters (name, mode, address, password, date_creation, photo) VALUES (?, ?, ?, ?, NOW(), ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssss", $nombre, $modalidad, $address, $hashedPassword, $photoName);

        if($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Sede guardada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al guardar la sede: ' . $stmt->error
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}