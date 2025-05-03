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

// Consultar la cantidad de registros por lote
$query = "SELECT 
            CASE 
                WHEN lote IS NULL THEN 'Sin asignar'
                ELSE lote 
            END AS lote_valor, 
            COUNT(*) as cantidad 
          FROM user_register 
          GROUP BY lote_valor";
$resultado = $conn->query($query);

$data = [
    'labels' => [],
    'data' => []
];

$lotes = [
    1 => 'Lote 1',
    2 => 'Lote 2',
    'Sin asignar' => 'Sin asignar lote'
];

if ($resultado->num_rows > 0) {
    while ($fila = $resultado->fetch_assoc()) {
        $etiqueta = isset($lotes[$fila['lote_valor']]) ? 
                    $lotes[$fila['lote_valor']] : 
                    'Lote ' . $fila['lote_valor'];
        
        $data['labels'][] = $etiqueta;
        $data['data'][] = $fila['cantidad'];
    }
}

// Retornar los datos en formato JSON si es una solicitud específica
if (isset($_GET['json'])) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>