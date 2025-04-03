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

// Consultar la cantidad de registros por departamento con filtro
$query = "SELECT 
            CASE 
                WHEN department IN (15, 25) THEN department 
                ELSE 'Otros' 
            END AS department, 
            COUNT(*) as cantidad 
          FROM user_register 
          GROUP BY department";
$resultado = $conn->query($query);

$data = [
    'labels' => [],
    'data' => []
];

$departamentos = [
    15 => 'Boyacá',
    25 => 'Cundinamarca',
    'Otros' => 'Otros'
];

if ($resultado->num_rows > 0) {
    $otros_count = 0;
    while ($fila = $resultado->fetch_assoc()) {
        if ($fila['department'] === 'Otros') {
            $otros_count += $fila['cantidad'];
        } else {
            $data['labels'][] = $departamentos[$fila['department']];
            $data['data'][] = $fila['cantidad'];
        }
    }
    if ($otros_count > 0) {
        $data['labels'][] = 'Otros';
        $data['data'][] = $otros_count;
    }
}

// Depuración: Verificar los datos obtenidos
error_log(print_r($data, true));

// Retornar los datos en formato JSON si es una solicitud específica
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>