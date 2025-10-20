<?php
// =========================
// CONFIGURACIÓN DE CONEXIÓN
// =========================

// Datos del servidor remoto
$server = "localhost";
$username = "u609911669_dashbordinnova";
$password = "g3X~i$#M[Tf1";
$bd = "u609911669_dashbordinnova";

// =========================
// CONEXIÓN ÚNICA Y REUTILIZABLE
// =========================

function getConnection() {
    static $conn;

    if ($conn === null) {
        $conn = mysqli_connect("localhost", "root", "", "utinnova");

        if (!$conn) {
            die("❌ Conexión fallida: " . mysqli_connect_error());
        }

        // Configuración del charset
        mysqli_set_charset($conn, "utf8");
        mysqli_query($conn, "SET NAMES 'utf8'");
        mysqli_query($conn, "SET CHARACTER SET 'utf8'");
        mysqli_query($conn, "SET COLLATION_CONNECTION = 'utf8_general_ci'");
    }

    return $conn;
}

// =========================
// USO DE LA CONEXIÓN
// =========================
// Ejemplo de uso:
// $conn = getConnection();
// $resultado = mysqli_query($conn, "SELECT * FROM usuarios");
// =========================
?>