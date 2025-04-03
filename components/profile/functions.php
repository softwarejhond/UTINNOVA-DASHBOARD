<?php 
function obtenerInformacionUsuario()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Asegúrate de que la sesión esté iniciada
    }

    // Debug: Mostrar el estado de las variables de sesión
    // var_dump($_SESSION); // Esto te mostrará todas las variables de sesión

    // Verificar si las variables de sesión están establecidas correctamente
    if (isset($_SESSION['loggedin'], $_SESSION['nombre'], $_SESSION['rol'], $_SESSION['username'], $_SESSION['foto']) && $_SESSION['loggedin'] === true) {
        $mensajeRol = rolUsuario($_SESSION['rol']); // Obtener el mensaje basado en el rol

        return [
            'nombre' => htmlspecialchars($_SESSION['nombre']),
            'rol' => $mensajeRol,
            'usuario' => htmlspecialchars($_SESSION['username']),
            'foto' => htmlspecialchars($_SESSION['foto'])
        ];
    } else {
        return [
            'nombre' => 'Usuario no logueado',
            'rol' => 'Rol no definido',
            'usuario' => 'Usuario no definido',
            'foto' => 'foto no definida'
        ];
    }
}
//ROLES DE USUARIOS 
function rolUsuario($rol)
{
    switch ($rol) {
        case 1:
            return "Administrador";
        case 2:
            return "Operario";
        case 3:
            return "Aprobador";
        case 4:
            return "Editor";
        default:
            return "Rol desconocido";
    }
}

//ACTUALIZAR FOTO DE PERFIL DEL USUARIO
function actualizarFotoPerfil($conn, $usuario, $imagen)
{
    $target_dir = "img/fotosUsuarios/";
    $target_file = $target_dir . basename($imagen["name"]);
    $uploadOk = 1;

    // Verificar si el archivo es una imagen
    $check = getimagesize($imagen["tmp_name"]);
    if ($check === false) {
        return ['success' => false, 'message' => 'El archivo no es una imagen.'];
    }

    // Mover el archivo a la carpeta destino
    if (move_uploaded_file($imagen["tmp_name"], $target_file)) {
        $sql = "UPDATE users SET foto = ? WHERE username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ss", $target_file, $usuario);
        if ($stmt->execute()) {
            return ['success' => true, 'message' => 'Foto actualizada exitosamente.'];
        } else {
            return ['success' => false, 'message' => 'Error al actualizar la foto en la base de datos.'];
        }
    } else {
        return ['success' => false, 'message' => 'Error al subir el archivo.'];
    }
}
