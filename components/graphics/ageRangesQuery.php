<?php
// session_start();
// include_once('../../controller/conexion.php');

// // Habilitar la visualización de errores
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// // Verificar si el usuario ha iniciado sesión
// if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
//     header('Location: index.php');
//     exit;
// }

// // Consulta para lote = 1
// $query = "SELECT 
//     CASE 
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 46 AND 55 THEN '46-55'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 56 AND 65 THEN '56-65'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) > 65 THEN '65+'
//         ELSE 'Sin especificar'
//     END AS rango_edad, 
//     COUNT(*) as cantidad 
// FROM user_register 
// WHERE birthdate IS NOT NULL 
// AND lote = 1
// GROUP BY rango_edad
// ORDER BY 
//     CASE 
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 25 THEN 1
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 26 AND 35 THEN 2
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 36 AND 45 THEN 3
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 46 AND 55 THEN 4
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 56 AND 65 THEN 5
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) > 65 THEN 6
//         ELSE 7
//     END";

// $resultado = $conn->query($query);

// $data = [
//     'labels' => [],
//     'data' => []
// ];

// if ($resultado->num_rows > 0) {
//     while ($fila = $resultado->fetch_assoc()) {
//         $data['labels'][] = $fila['rango_edad'];
//         $data['data'][] = $fila['cantidad'];
//     }
// }

// // Consulta para lote = 2
// $query2 = "SELECT 
//     CASE 
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 25 THEN '18-25'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 26 AND 35 THEN '26-35'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 36 AND 45 THEN '36-45'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 46 AND 55 THEN '46-55'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 56 AND 65 THEN '56-65'
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) > 65 THEN '65+'
//         ELSE 'Sin especificar'
//     END AS rango_edad, 
//     COUNT(*) as cantidad 
// FROM user_register 
// WHERE birthdate IS NOT NULL 
// AND lote = 2
// GROUP BY rango_edad
// ORDER BY 
//     CASE 
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 18 AND 25 THEN 1
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 26 AND 35 THEN 2
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 36 AND 45 THEN 3
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 46 AND 55 THEN 4
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) BETWEEN 56 AND 65 THEN 5
//         WHEN TIMESTAMPDIFF(YEAR, birthdate, CURDATE()) > 65 THEN 6
//         ELSE 7
//     END";

// $resultado2 = $conn->query($query2);

// $data2 = [
//     'labels' => [],
//     'data' => []
// ];

// if ($resultado2->num_rows > 0) {
//     while ($fila = $resultado2->fetch_assoc()) {
//         $data2['labels'][] = $fila['rango_edad'];
//         $data2['data'][] = $fila['cantidad'];
//     }
// }

// // Depuración: Verificar los datos obtenidos
// error_log(print_r($data, true));
// error_log(print_r($data2, true));

// // Retornar los datos en formato JSON si es una solicitud específica
// if (isset($_GET['json']) && $_GET['json'] == 1) {
//     header('Content-Type: application/json');
//     echo json_encode($data);
//     exit;
// }

// if (isset($_GET['json']) && $_GET['json'] == 2) {
//     header('Content-Type: application/json');
//     echo json_encode($data2);
//     exit;
// }
?>