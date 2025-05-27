<?php
function obtenerInformacionUsuario()
{
    if (session_status() == PHP_SESSION_NONE) {
        session_start(); // Asegúrate de que la sesión esté iniciada
    }

    // Verificar si las variables de sesión están establecidas correctamente
    if (isset($_SESSION['loggedin'], $_SESSION['nombre'], $_SESSION['rol'], $_SESSION['username'], $_SESSION['foto'], $_SESSION['extra_rol']) && $_SESSION['loggedin'] === true) {
        $mensajeRol = rolUsuario($_SESSION['rol']); // Obtener el mensaje basado en el rol
        $mensajeExtraRol = extraRolUsuario($_SESSION['extra_rol']); // Obtener el mensaje basado en el extra_rol

        return [
            'nombre' => htmlspecialchars($_SESSION['nombre']),
            'rol' => $mensajeRol,
            'extra_rol' => $mensajeExtraRol,
            'usuario' => htmlspecialchars($_SESSION['username']),
            'foto' => htmlspecialchars($_SESSION['foto'])
        ];
    } else {
        return [
            'nombre' => 'Usuario no logueado',
            'rol' => 'Rol no definido',
            'extra_rol' => 'Extra rol no definido',
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
        case 13:
            return "Interventoría";
        default:
            return "Rol desconocido";
    }
}

function extraRolUsuario($extra_rol)
{
    switch ($extra_rol) {
        case 1:
            return "Extra Administrador";
        default:
            return "Extra rol no definido";
    }
}
?>
