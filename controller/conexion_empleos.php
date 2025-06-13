<?php
//datos del servidor
$server = "localhost";
$username = "root";
$password = "";
$bd = "empleos_innova";

// $server = "localhost";
// $username = "u609911669_empAdmin";
// $password = "U8|w*EB9r+";
// $bd = "u609911669_sigeinnova";

//creamos una conexión
$connEmpleos = mysqli_connect($server, $username, $password, $bd);
//Chequeamos la conexión
if (!$connEmpleos) {
    die("Conexión fallida:" . mysqli_connect_error());
}
