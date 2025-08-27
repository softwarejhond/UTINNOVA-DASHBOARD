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

// Matriculados Presencial Lote 1
$sql_presencial_1 = "SELECT COUNT(DISTINCT g.number_id) as total_presencial 
                      FROM groups g 
                      INNER JOIN user_register ur ON g.number_id = ur.number_id 
                      WHERE ur.lote = 1 AND g.mode = 'Presencial'";
$result_presencial_1 = $conn->query($sql_presencial_1);
$total_presencial_1 = $result_presencial_1->fetch_assoc()['total_presencial'];

// Matriculados Virtual Lote 1
$sql_virtual_1 = "SELECT COUNT(DISTINCT g.number_id) as total_virtual 
                   FROM groups g 
                   INNER JOIN user_register ur ON g.number_id = ur.number_id 
                   WHERE ur.lote = 1 AND g.mode = 'Virtual'";
$result_virtual_1 = $conn->query($sql_virtual_1);
$total_virtual_1 = $result_virtual_1->fetch_assoc()['total_virtual'];

$data1 = [
    'labels' => ['Registrados', 'Matriculados Presencial', 'Matriculados Virtual'],
    'data' => [$total_user_register_1, $total_presencial_1, $total_virtual_1]
];

// Consulta para lote = 2
$sql_total_user_register_2 = "SELECT COUNT(*) as total_user_register FROM user_register WHERE lote = 2";
$result_user_register_2 = $conn->query($sql_total_user_register_2);
$total_user_register_2 = $result_user_register_2->fetch_assoc()['total_user_register'];

// Matriculados Presencial Lote 2
$sql_presencial_2 = "SELECT COUNT(DISTINCT g.number_id) as total_presencial 
                      FROM groups g 
                      INNER JOIN user_register ur ON g.number_id = ur.number_id 
                      WHERE ur.lote = 2 AND g.mode = 'Presencial'";
$result_presencial_2 = $conn->query($sql_presencial_2);
$total_presencial_2 = $result_presencial_2->fetch_assoc()['total_presencial'];

// Matriculados Virtual Lote 2
$sql_virtual_2 = "SELECT COUNT(DISTINCT g.number_id) as total_virtual 
                   FROM groups g 
                   INNER JOIN user_register ur ON g.number_id = ur.number_id 
                   WHERE ur.lote = 2 AND g.mode = 'Virtual'";
$result_virtual_2 = $conn->query($sql_virtual_2);
$total_virtual_2 = $result_virtual_2->fetch_assoc()['total_virtual'];

$data2 = [
    'labels' => ['Registrados', 'Matriculados Presencial', 'Matriculados Virtual'],
    'data' => [$total_user_register_2, $total_presencial_2, $total_virtual_2]
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