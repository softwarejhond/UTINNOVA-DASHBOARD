<?php
// Configuración para depuración
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Asegurar que solo se envíe JSON
header('Content-Type: application/json');

try {
    // Ruta al archivo de conexión
    require_once __DIR__ . '/../../controller/conexion.php';

    // Verificar si hay datos POST 
    $groupCode = isset($_POST['groupCode']) ? $_POST['groupCode'] : null;
    
    if (empty($groupCode)) {
        // Verificar si hay datos POST JSON
        $inputJSON = file_get_contents('php://input');
        $input = json_decode($inputJSON, TRUE);
        $groupCode = $input['groupCode'] ?? null;
    }
    
    if (empty($groupCode)) {
        throw new Exception('No se proporcionó el código de grupo');
    }
    
    $searchPattern = '%' . $groupCode . '%';
    
    // Conexión mysqli (la que parece estar utilizando)
    $sql = "SELECT DISTINCT ur.*, d.departamento 
            FROM user_register ur
            INNER JOIN departamentos d ON ur.department = d.id_departamento
            INNER JOIN course_assignments ca ON ur.number_id = ca.student_id
            WHERE 
                (ca.bootcamp_name LIKE '%$groupCode%' OR 
                 ca.leveling_english_name LIKE '%$groupCode%' OR 
                 ca.english_code_name LIKE '%$groupCode%' OR 
                 ca.skills_name LIKE '%$groupCode%')
                AND ur.status = '1'
                AND ur.statusAdmin = '1'
            ORDER BY ur.first_name ASC";

    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }
    
    $results = [];
    while ($row = $result->fetch_assoc()) {
        $results[] = $row;
    }
    
    $students = [];
    foreach ($results as $row) {
        // Procesar datos del usuario
        $firstName   = ucwords(strtolower(trim($row['first_name'])));
        $secondName  = ucwords(strtolower(trim($row['second_name'] ?? '')));
        $firstLast   = ucwords(strtolower(trim($row['first_last'])));
        $secondLast  = ucwords(strtolower(trim($row['second_last'] ?? '')));
        $fullName = trim($firstName . " " . $secondName . " " . $firstLast . " " . $secondLast);
        
        // Generar correo institucional
        $nuevoCorreo = strtolower(substr(trim($row['first_name']), 0, 1))
            . strtolower(substr(trim($row['second_name'] ?? ''), 0, 1))
            . substr(trim($row['number_id']), -4)
            . strtolower(substr(trim($row['first_last']), 0, 1))
            . strtolower(substr(trim($row['second_last'] ?? ''), 0, 1))
            . '.ut@cendi.edu.co';
            
        $students[] = [
            'typeID' => $row['typeID'],
            'number_id' => $row['number_id'],
            'full_name' => $fullName,
            'email' => $row['email'],
            'institutional_email' => $nuevoCorreo,
            'departamento' => $row['departamento'],
            'headquarters' => $row['headquarters'],
            'program' => $row['program'],
            'level' => $row['level'],
            'schedules' => $row['schedules'],
            'mode' => $row['mode']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'students' => $students,
        'groupCode' => $groupCode,
        'count' => count($students)
    ]);

} catch (Exception $e) {
    // Log del error para debugging
    error_log('Error en get_group_students.php: ' . $e->getMessage());
    
    // Devolver respuesta de error en formato JSON
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>