<?php
session_start();
include_once('../../controller/conexion.php');

// Habilitar la visualización de errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Consultar el total de registros en la tabla user_register
$sql_total_user_register = "SELECT COUNT(*) as total_user_register FROM user_register";
$result_user_register = $conn->query($sql_total_user_register);
$total_user_register = $result_user_register->fetch_assoc()['total_user_register'];

// Consultar el total de registros en la tabla user_register cuando statusAdmin = 3
$sql_total_groups = "SELECT COUNT(*) as total_groups FROM user_register WHERE statusAdmin = 3";
$result_groups = $conn->query($sql_total_groups);
$total_groups = $result_groups->fetch_assoc()['total_groups'];

// Preparar los datos para la gráfica
$data = [
    'labels' => ['Registrados', 'Matriculados'],
    'data' => [$total_user_register, $total_groups]
];

// Depuración: Verificar los datos obtenidos
error_log(print_r($data, true));

// Retornar los datos en formato JSON
header('Content-Type: application/json');
echo json_encode($data);
exit;
?>