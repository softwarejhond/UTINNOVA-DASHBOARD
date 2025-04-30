<?php
//datos del servidor
// $server = "localhost";
// $username = "u609911669_dashbordinnova";
// $password = "g3X~i$#M[Tf1";
// $bd = "u609911669_dashbordinnova";

$server = "localhost";
$username = "root";
$password = "";
$bd = "utinnova";
//creamos una conexión
$conn = mysqli_connect($server, $username, $password, $bd);
//Chequeamos la conexión
if (!$conn) {
    die("Conexión fallida:" . mysqli_connect_error());
}
// Set the character set to UTF-8
mysqli_set_charset($conn, "utf8");
// Set the collation to utf8_general_ci
mysqli_query($conn, "SET NAMES 'utf8'");
mysqli_query($conn, "SET CHARACTER SET 'utf8'");
mysqli_query($conn, "SET COLLATION_CONNECTION = 'utf8_general_ci'");        