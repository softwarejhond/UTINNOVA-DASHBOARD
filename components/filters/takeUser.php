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

function rolUsuario($rol)
{
    switch ($rol) {
        case 1:
            return "Administrador";
        case 2:
            return "Editor";
        case 3:
            return "Asesor";
        case 4:
            return "Visualizador";
        case 5:
            return "Docente";
        case 6:
            return "Académico";
        case 7:
            return "Monitor";
        case 8:
            return "Mentor";
        case 9:
            return "Supervisor";
        case 10:
            return "Empleabilidad";
        case 11:
            return "Superacademico";
        case 12:
            return "Control maestro";
        default:
            return "Rol desconocido";
    }
}