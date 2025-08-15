<?php
// Script para ser ejecutado por una tarea programada (cron job)

// Aumentar el tiempo máximo de ejecución para procesos largos
set_time_limit(0); 
// Ignorar si el usuario aborta la conexión (no aplicable en CLI, pero buena práctica)
ignore_user_abort(true); 

// --- INCLUIR LA LÓGICA DEL SCRIPT ORIGINAL ---

// Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// Verificar si la conexión se estableció correctamente (asumiendo que conexion.php define $conn)
if (!isset($conn) || $conn->connect_error) {
    // Escribir error en un log y salir
    $error_message = isset($conn) ? $conn->connect_error : "La variable de conexión no está definida en conexion.php";
    file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " - Error de conexión: " . $error_message . "\n", FILE_APPEND);
    die("Error de conexión: " . $error_message);
}

// Configuración del API de Moodle
$apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
$token = '3f158134506350615397c83d861c2104';
$format = 'json';

// Función para obtener las notas de un estudiante (copiada de obtener_notas.php)
function obtenerNotas($username, $courseid) {
    global $apiUrl, $token, $format;
    
    $functionGetUser = 'core_user_get_users_by_field';
    $paramsUser = ['field' => 'username', 'values[0]' => $username];
    $postdataUser = http_build_query(['wstoken' => $token, 'wsfunction' => $functionGetUser, 'moodlewsrestformat' => $format] + $paramsUser);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdataUser);
    
    $responseUser = curl_exec($ch);
    $userData = json_decode($responseUser, true);
    
    if (empty($userData)) {
        curl_close($ch);
        return ['nota1' => 0, 'nota2' => 0];
    }
    
    $userid = $userData[0]['id'];
    
    $function = 'gradereport_user_get_grade_items';
    $params = ['courseid' => $courseid, 'userid' => $userid];
    $postdata = http_build_query(['wstoken' => $token, 'wsfunction' => $function, 'moodlewsrestformat' => $format] + $params);
    
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    $response = curl_exec($ch);
    
    if ($response === false) {
        curl_close($ch);
        return ['nota1' => 0, 'nota2' => 0];
    }
    
    $data = json_decode($response, true);
    curl_close($ch);
    
    if ($data === null || !isset($data['usergrades'][0])) {
        return ['nota1' => 0, 'nota2' => 0];
    }
    
    $usergrade = $data['usergrades'][0];
    $notas = [];
    
    if (isset($usergrade['gradeitems'])) {
        foreach ($usergrade['gradeitems'] as $item) {
            if (isset($item['graderaw']) && $item['graderaw'] !== null) {
                $notas[] = $item['graderaw'];
                if (count($notas) == 2) break;
            }
        }
    }
    
    return [
        'nota1' => isset($notas[0]) ? $notas[0] : 0, 
        'nota2' => isset($notas[1]) ? $notas[1] : 0
    ];
}

// Función para guardar las notas en la base de datos (copiada de obtener_notas.php)
function guardarNotas($number_id, $nota1, $nota2, $code) {
    global $conn;
    try {
        $sql = "INSERT INTO notas_estudiantes (number_id, nota1, nota2, code) 
                VALUES (?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE nota1 = ?, nota2 = ?, code = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sddsdds", $number_id, $nota1, $nota2, $code, $nota1, $nota2, $code);
        return $stmt->execute();
    } catch (Exception $e) {
        file_put_contents('cron_log.txt', date('Y-m-d H:i:s') . " - Error al guardar notas para $number_id: " . $e->getMessage() . "\n", FILE_APPEND);
        return false;
    }
}

// --- LÓGICA PRINCIPAL DEL SCRIPT DE CRON ---

$logMessage = date('Y-m-d H:i:s') . " - Inicia el proceso de obtención de notas.\n";
file_put_contents('cron_log.txt', $logMessage, FILE_APPEND);

// 1. Obtener todos los estudiantes
$sql = "SELECT number_id, id_bootcamp FROM groups WHERE number_id IS NOT NULL AND id_bootcamp IS NOT NULL";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    $logMessage = date('Y-m-d H:i:s') . " - No se encontraron estudiantes para procesar.\n";
    file_put_contents('cron_log.txt', $logMessage, FILE_APPEND);
    exit;
}

$estudiantes = $result->fetch_all(MYSQLI_ASSOC);
$total = count($estudiantes);
$procesados = 0;

$logMessage = date('Y-m-d H:i:s') . " - Se encontraron $total estudiantes para procesar.\n";
file_put_contents('cron_log.txt', $logMessage, FILE_APPEND);

// 2. Iterar y procesar cada estudiante
foreach ($estudiantes as $estudiante) {
    $number_id = $estudiante['number_id'];
    $id_bootcamp = $estudiante['id_bootcamp'];

    // Obtener notas
    $notas = obtenerNotas($number_id, $id_bootcamp);

    // Guardar notas
    if (guardarNotas($number_id, $notas['nota1'], $notas['nota2'], $id_bootcamp)) {
        $logMessage = "  - OK: Estudiante $number_id (Curso: $id_bootcamp) -> Nota1: {$notas['nota1']}, Nota2: {$notas['nota2']}\n";
    } else {
        $logMessage = "  - ERROR: Estudiante $number_id (Curso: $id_bootcamp) -> No se pudieron guardar las notas.\n";
    }
    file_put_contents('cron_log.txt', $logMessage, FILE_APPEND);
    $procesados++;
}

$logMessage = date('Y-m-d H:i:s') . " - Proceso finalizado. Se procesaron $procesados de $total estudiantes.\n\n";
file_put_contents('cron_log.txt', $logMessage, FILE_APPEND);

$conn->close();

echo "Proceso completado.";
?>