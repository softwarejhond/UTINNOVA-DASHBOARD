<?php
// Deshabilitar la salida de errores PHP
error_reporting(0);
ini_set('display_errors', 0);

// Asegurarnos de que no haya output antes de los headers
ob_start();

require __DIR__ . '../../../controller/conexion.php';

$response = ['success' => false, 'data' => [], 'message' => ''];

try {
    if (!$conn) {
        throw new Exception("Error de conexión a la base de datos");
    }

    $courseCode = $_GET['courseCode'] ?? '';
    
    // Consulta corregida
    $sql = "SELECT 
                ur.*,
                d.departamento,
                ca.bootcamp_name as bootcamp_pre_asignado,
                ca.leveling_english_name as ingles_pre_asignado,
                ca.english_code_name as english_code_pre_asignado,
                ca.skills_name as habilidades_pre_asignado,
                ca.bootcamp_id,
                ca.leveling_english_id,
                ca.english_code_id,
                ca.skills_id
            FROM user_register ur
            INNER JOIN departamentos d ON ur.department = d.id_departamento 
            LEFT JOIN course_assignments ca ON ur.number_id = ca.student_id
            WHERE d.id_departamento = 11
              AND ur.status = '1' 
              AND ur.statusAdmin IN ('1', '8')";
    
    $result = $conn->query($sql);
    
    if ($result === false) {
        throw new Exception($conn->error);
    }
    
    $data = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'typeID' => $row['typeID'] ?? '',
                'number_id' => $row['number_id'] ?? '',
                'first_name' => $row['first_name'] ?? '',
                'second_name' => $row['second_name'] ?? '',
                'first_last' => $row['first_last'] ?? '',
                'second_last' => $row['second_last'] ?? '',
                'email' => $row['email'] ?? '',
                'institutional_email' => $row['institutional_email'] ?? '',
                'departamento' => $row['departamento'] ?? '',
                'headquarters' => $row['headquarters'] ?? '',
                'program' => $row['program'] ?? '',
                'mode' => $row['mode'] ?? '',
                'level' => $row['level'] ?? '',
                'schedules' => $row['schedules'] ?? '',
                'bootcamp_pre_asignado' => $row['bootcamp_pre_asignado'] ?? 'Sin asignar',
                'ingles_pre_asignado' => $row['ingles_pre_asignado'] ?? 'Sin asignar',
                'english_code_pre_asignado' => $row['english_code_pre_asignado'] ?? 'Sin asignar',
                'habilidades_pre_asignado' => $row['habilidades_pre_asignado'] ?? 'Sin asignar',
                'bootcamp_id' => $row['bootcamp_id'] ?? '',
                'leveling_english_id' => $row['leveling_english_id'] ?? '',
                'english_code_id' => $row['english_code_id'] ?? '',
                'skills_id' => $row['skills_id'] ?? ''
            ];
        }
        $response['success'] = true;
        $response['data'] = $data;
    } else {
        $response['message'] = 'No se encontraron datos';
    }
    
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
    // Registrar el error en los logs
    error_log("Error en load_students.php: " . $e->getMessage());
} finally {
    // Limpiar cualquier output anterior
    ob_clean();
    
    // Headers
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET');
    header('Content-Type: application/json; charset=utf-8');
    
    // Enviar respuesta
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit;
}