<?php 

// Incluir la conexión a la base de datos
include("conexion.php"); 

// Iniciar sesión
session_start();

// Verificar si la conexión a la base de datos fue exitosa
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // Redirigir a la página de inicio de sesión si no está autenticado
    echo "Redirigiendo a la página de inicio de sesión...";
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <?php include("controller/head.php"); // Incluir cabecera ?>
</head>

<body>
    <!-- Contenido de la página -->
    <?php include("controller/scripts.php"); // Incluir scripts ?>
</body>

</html>
