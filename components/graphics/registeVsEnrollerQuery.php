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

// Consulta para lote = 1
$sql_total_user_register_1 = "SELECT COUNT(*) as total_user_register FROM user_register WHERE lote = 1";
$result_user_register_1 = $conn->query($sql_total_user_register_1);
$total_user_register_1 = $result_user_register_1->fetch_assoc()['total_user_register'];

$sql_total_groups_1 = "SELECT COUNT(*) as total_groups FROM user_register WHERE statusAdmin = 3 AND lote = 1";
$result_groups_1 = $conn->query($sql_total_groups_1);
$total_groups_1 = $result_groups_1->fetch_assoc()['total_groups'];

$data1 = [
    'labels' => ['Registrados', 'Matriculados'],
    'data' => [$total_user_register_1, $total_groups_1]
];

// Consulta para lote = 2
$sql_total_user_register_2 = "SELECT COUNT(*) as total_user_register FROM user_register WHERE lote = 2";
$result_user_register_2 = $conn->query($sql_total_user_register_2);
$total_user_register_2 = $result_user_register_2->fetch_assoc()['total_user_register'];

$sql_total_groups_2 = "SELECT COUNT(*) as total_groups FROM user_register WHERE statusAdmin = 3 AND lote = 2";
$result_groups_2 = $conn->query($sql_total_groups_2);
$total_groups_2 = $result_groups_2->fetch_assoc()['total_groups'];

$data2 = [
    'labels' => ['Registrados', 'Matriculados'],
    'data' => [$total_user_register_2, $total_groups_2]
];

// Depuración: Verificar los datos obtenidos
error_log(print_r($data1, true));
error_log(print_r($data2, true));

// Retornar los datos en formato JSON según el lote solicitado
if (isset($_GET['json']) && $_GET['json'] == 1) {
    header('Content-Type: application/json');
    echo json_encode($data1);
    exit;
}

if (isset($_GET['json']) && $_GET['json'] == 2) {
    header('Content-Type: application/json');
    echo json_encode($data2);
    exit;
}
?>