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
if (isset($_GET['json']) && $_GET['json'] == 'lote1') {
    // Consultar la cantidad de registros para lote 1
    $query1 = "SELECT COUNT(*) as cantidad FROM user_register WHERE lote = 1";
    $result1 = $conn->query($query1);
    $cantidad1 = $result1->fetch_assoc()['cantidad'] ?? 0;

    header('Content-Type: application/json');
    echo json_encode([
        'labels' => ['Lote 1'],
        'data' => [$cantidad1]
    ]);
    exit;
}

if (isset($_GET['json']) && $_GET['json'] == 'lote2') {
    // Consultar la cantidad de registros para lote 2
    $query2 = "SELECT COUNT(*) as cantidad FROM user_register WHERE lote = 2";
    $result2 = $conn->query($query2);
    $cantidad2 = $result2->fetch_assoc()['cantidad'] ?? 0;

    header('Content-Type: application/json');
    echo json_encode([
        'labels' => ['Lote 2'],
        'data' => [$cantidad2]
    ]);
    exit;
}

// JSON para total general de usuarios
if (isset($_GET['json']) && $_GET['json'] == 'total') {
    $queryTotal = "SELECT COUNT(*) as total FROM user_register";
    $resultTotal = $conn->query($queryTotal);
    $total = $resultTotal->fetch_assoc()['total'] ?? 0;
    header('Content-Type: application/json');
    echo json_encode(['total' => $total]);
    exit;
}
?>