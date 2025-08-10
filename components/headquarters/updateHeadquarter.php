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
        $address = mysqli_real_escape_string($conn, $_POST['address']);

        // Iniciar transacción
        mysqli_autocommit($conn, false);

        // Obtener el nombre anterior de la sede antes de actualizar
        $queryAnterior = "SELECT name FROM headquarters WHERE id = $id";
        $resultAnterior = mysqli_query($conn, $queryAnterior);
        $nombreAnterior = mysqli_fetch_assoc($resultAnterior)['name'];

        $photoName = null;
        $archivoSubido = false;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
            $allowed = ['image/png' => 'png', 'image/jpeg' => 'jpeg', 'image/jpg' => 'jpg'];
            $fileType = $_FILES['foto']['type'];
            if (array_key_exists($fileType, $allowed)) {
                $ext = $allowed[$fileType] == 'jpeg' ? 'jpg' : $allowed[$fileType];
                $nombreSede = substr($nombre, 0, 5);
                $fecha = date('YmdHis');
                $photoName = "sede_" . preg_replace('/[^A-Za-z0-9]/', '', $nombreSede) . "_" . $fecha . "." . $ext;
                $destino = "../../img/sedes/" . $photoName;
                if (move_uploaded_file($_FILES['foto']['tmp_name'], $destino)) {
                    $archivoSubido = true;
                } else {
                    throw new Exception("Error al subir el archivo");
                }
            }
        }

        // Actualizar user_register primero
        $updateUsers = "UPDATE user_register SET headquarters = '$nombre' WHERE headquarters = '$nombreAnterior'";
        if (!mysqli_query($conn, $updateUsers)) {
            throw new Exception("Error al actualizar user_register: " . mysqli_error($conn));
        }

        // Actualizar headquarters
        if ($photoName) {
            $query = "UPDATE headquarters SET name = '$nombre', mode = '$modalidad', address = '$address', photo = '$photoName' WHERE id = $id";
        } else {
            $query = "UPDATE headquarters SET name = '$nombre', mode = '$modalidad', address = '$address' WHERE id = $id";
        }

        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error al actualizar headquarters: " . mysqli_error($conn));
        }

        // Confirmar transacción
        mysqli_commit($conn);
        mysqli_autocommit($conn, true);

        echo json_encode([
            'success' => true,
            'message' => 'Sede actualizada exitosamente'
        ]);

    } catch (Exception $e) {
        // Rollback de la transacción
        mysqli_rollback($conn);
        mysqli_autocommit($conn, true);

        // Eliminar archivo subido si hubo error
        if ($archivoSubido && isset($destino) && file_exists($destino)) {
            unlink($destino);
        }

        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
}