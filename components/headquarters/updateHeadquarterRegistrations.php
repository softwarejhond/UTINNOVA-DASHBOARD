<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include("../../conexion.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id = mysqli_real_escape_string($conn, $_POST['sede_id']);
        $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
        $modalidad = mysqli_real_escape_string($conn, $_POST['modalidad']);
        $programs = isset($_POST['programs']) ? json_encode($_POST['programs'], JSON_UNESCAPED_UNICODE) : json_encode([]);

        // Variables para la imagen
        $photoUpdate = "";
        $photoName = null;

        // Manejo de imagen - Si hay una nueva foto
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpeg', 'image/jpg' => 'jpg'];
            $fileType = $_FILES['foto']['type'];
            if (array_key_exists($fileType, $allowed)) {
                $ext = $allowed[$fileType] == 'jpeg' ? 'jpg' : $allowed[$fileType];
                $nombreSede = substr($nombre, 0, 5);
                $fecha = date('YmdHis');
                $photoName = "sedepreregistro_" . preg_replace('/[^A-Za-z0-9]/', '', $nombreSede) . "_" . $fecha . "." . $ext;
                $destino = "../../img/sedes/" . $photoName;
                
                // Intentar mover el archivo
                move_uploaded_file($_FILES['foto']['tmp_name'], $destino);
                $photoUpdate = ", photo = '$photoName'";
            }
        }

        // Agregar la actualización de la foto si es necesario
        $query = "UPDATE headquarters_registrations 
                 SET name = '$nombre', mode = '$modalidad', programs = '$programs' $photoUpdate 
                 WHERE id = $id";

        if(mysqli_query($conn, $query)) {
            echo json_encode([
                'success' => true,
                'message' => 'Sede de asistencia actualizada exitosamente'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Error al actualizar la sede de asistencia: ' . mysqli_error($conn)
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}