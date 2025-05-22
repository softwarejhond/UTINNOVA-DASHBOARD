<?php
include_once('../../controller/conexion.php');

header('Content-Type: application/json');

if (!isset($_GET['course_id']) || !isset($_GET['course_type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'ID del curso o tipo de curso no proporcionado'
    ]);
    exit;
}

$courseId = $_GET['course_id'];
$courseType = $_GET['course_type'];

// Definir cupos máximos predeterminados según el tipo de curso
$cuposMaximos = [
    'bootcamp' => 40,
    'english' => 40, // Inglés Nivelatorio
    'english_code' => 40,
    'skills' => 40
];

// Verificar que el tipo de curso sea válido
if (!array_key_exists($courseType, $cuposMaximos)) {
    echo json_encode([
        'success' => false,
        'message' => 'Tipo de curso no válido'
    ]);
    exit;
}

try {
    // Mapear el tipo de curso al nombre de columna correcto
    $columnMapping = [
        'bootcamp' => 'bootcamp_id',
        'english' => 'leveling_english_id', 
        'english_code' => 'english_code_id',
        'skills' => 'skills_id'
    ];
    
    $columnName = $columnMapping[$courseType];
    
    // Obtener el nombre del curso desde la API de Moodle
    $api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
    $token   = "3f158134506350615397c83d861c2104";
    $format  = "json";
    
    // Consultar información del curso específico por su ID
    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_course_get_courses_by_field',
        'field' => 'id',
        'value' => $courseId,
        'moodlewsrestformat' => $format
    ];
    
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    
    $courseInfo = null;
    $isVirtualCourse = false;
    
    if (!curl_errno($ch)) {
        $data = json_decode($response, true);
        if (isset($data['courses']) && !empty($data['courses'])) {
            $courseInfo = $data['courses'][0];
            $courseName = $courseInfo['fullname'] ?? $courseInfo['shortname'] ?? '';
            
            // Verificar si es un curso virtual (termina en "V")
            if (!empty($courseName) && substr(trim($courseName), -1) === "V") {
                $isVirtualCourse = true;
                // Usar límite de 100 para cursos virtuales
                $cupoMaximo = 100;
            } else {
                // Usar límite normal
                $cupoMaximo = $cuposMaximos[$courseType];
            }
        } else {
            $cupoMaximo = $cuposMaximos[$courseType];
        }
    } else {
        $cupoMaximo = $cuposMaximos[$courseType];
    }
    
    curl_close($ch);
    
    // Si no pudimos obtener la información de la API, intentar obtenerla de la base de datos
    if ($courseInfo === null) {
        // Intentar obtener el nombre del curso desde las asignaciones existentes
        $sqlCourseName = "SELECT 
            CASE 
                WHEN '{$columnName}' = 'bootcamp_id' THEN bootcamp_name
                WHEN '{$columnName}' = 'leveling_english_id' THEN leveling_english_name
                WHEN '{$columnName}' = 'english_code_id' THEN english_code_name
                WHEN '{$columnName}' = 'skills_id' THEN skills_name
            END as course_name
            FROM course_assignments 
            WHERE {$columnName} = ? 
            LIMIT 1";
        
        $stmtCourseName = $conn->prepare($sqlCourseName);
        if ($stmtCourseName) {
            $stmtCourseName->bind_param('s', $courseId);
            $stmtCourseName->execute();
            $resultCourseName = $stmtCourseName->get_result();
            
            if ($row = $resultCourseName->fetch_assoc()) {
                $courseName = $row['course_name'];
                
                // Verificar si es un curso virtual
                if (!empty($courseName) && substr(trim($courseName), -1) === "V") {
                    $isVirtualCourse = true;
                    $cupoMaximo = 100; // Límite para cursos virtuales
                }
            }
        }
    }
    
    // Consultar asignaciones actuales para el curso específico
    $sql = "SELECT COUNT(*) as total 
            FROM course_assignments 
            WHERE {$columnName} = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $courseId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $totalAsignaciones = $row['total'];
    $cuposDisponibles = $cupoMaximo - $totalAsignaciones;

    echo json_encode([
        'success' => true,
        'total_asignaciones' => $totalAsignaciones,
        'cupos_disponibles' => $cuposDisponibles,
        'tiene_cupo' => ($cuposDisponibles > 0),
        'cupo_maximo' => $cupoMaximo,
        'tipo_curso' => $courseType,
        'curso_virtual' => $isVirtualCourse,
        'nombre_curso' => $courseInfo ? ($courseInfo['fullname'] ?? '') : ($courseName ?? 'No disponible')
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error al verificar cupos: ' . $e->getMessage()
    ]);
}