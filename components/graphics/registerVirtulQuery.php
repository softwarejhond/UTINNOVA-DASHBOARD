<?php
session_start();
include_once('../../controller/conexion.php');

// Habilitar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar sesión activa
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: index.php');
    exit;
}

// Consultar la cantidad de Inscritos y Matriculados Virtuales
$queryInscritos = "SELECT COUNT(*) as cantidad FROM user_register WHERE mode = 'virtual'";
$queryMatriculados = "SELECT COUNT(*) as cantidad FROM user_register WHERE estatusAdmin = 3";

$resultadoInscritos = $conn->query($queryInscritos);
$resultadoMatriculados = $conn->query($queryMatriculados);

$data = [
    'labels' => ['Inscritos Virtuales', 'Matriculados Virtuales'],
    'data' => [
        $resultadoInscritos->fetch_assoc()['cantidad'] ?? 0,
        $resultadoMatriculados->fetch_assoc()['cantidad'] ?? 0
    ]
];

// Devolver en formato JSON
header('Content-Type: application/json');
echo json_encode($data);
exit;
?>
