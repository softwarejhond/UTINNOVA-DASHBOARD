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
        
        // Obtener el username de la sesión
        $created_by = isset($_SESSION['username']) ? mysqli_real_escape_string($conn, $_SESSION['username']) : 'sistema';

        $query = "INSERT INTO headquarters_attendance (name, mode, created_by) VALUES ('$nombre', '$modalidad', '$created_by')";

        if(mysqli_query($conn, $query)) {
            echo json_encode([
                'success' => true,
                'message' => 'Sede de asistencia creada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al crear la sede de asistencia: ' . mysqli_error($conn)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}