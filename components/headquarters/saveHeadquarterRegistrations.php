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

        $programs = isset($_POST['programs']) ? json_encode($_POST['programs'], JSON_UNESCAPED_UNICODE) : json_encode([]);

        // Manejo de imagen - Ajustado para seguir exactamente la misma lógica que saveHeadquarter.php
        $photoName = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpeg', 'image/jpg' => 'jpg'];
            $fileType = $_FILES['foto']['type'];
            if (array_key_exists($fileType, $allowed)) {
                $ext = $allowed[$fileType] == 'jpeg' ? 'jpg' : $allowed[$fileType];
                $nombreSede = substr($nombre, 0, 5);
                $fecha = date('YmdHis');
                $photoName = "sedepreregistro_" . preg_replace('/[^A-Za-z0-9]/', '', $nombreSede) . "_" . $fecha . "." . $ext;
                $destino = "../../img/sedes/" . $photoName;
                
                // Intentar mover el archivo sin comprobación adicional
                move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
            }
        }

        // Usar consulta directa como en saveHeadquarter.php en lugar de prepared statement
        $query = "INSERT INTO headquarters_registrations (name, mode, programs, created_by, photo) 
                 VALUES ('$nombre', '$modalidad', '$programs', '$created_by', '$photoName')";

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