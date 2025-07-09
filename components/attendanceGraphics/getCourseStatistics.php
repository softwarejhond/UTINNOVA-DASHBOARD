<?php
error_reporting(0);
header('Content-Type: application/json');

session_start();
require_once __DIR__ . '/../../controller/conexion.php';

// Verificar la conexión a la base de datos
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté en sesión
if (!isset($_SESSION['username'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autorizado']);
    exit;
}

try {
    $courseCode = $_POST['courseCode'] ?? '';
    
    if (empty($courseCode)) {
        echo json_encode(['success' => false, 'message' => 'Código de curso requerido']);
        exit;
    }

    // Buscar el curso en Moodle para obtener el ID
    $moodleCourseId = findMoodleCourseId($courseCode);
    if (!$moodleCourseId) {
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado en Moodle']);
        exit;
    }

    // Consultar el promedio total de todas las intensidades del curso
    $sql = "SELECT 
                AVG(COALESCE(b_intensity, 0) + COALESCE(le_intensity, 0) + COALESCE(ec_intensity, 0) + COALESCE(s_intensity, 0)) as total_hours,
                COUNT(*) as total_students
            FROM groups 
            WHERE id_bootcamp = ? OR id_leveling_english = ? OR id_english_code = ? OR id_skills = ?";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de consulta']);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "iiii", $moodleCourseId, $moodleCourseId, $moodleCourseId, $moodleCourseId);
    
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['success' => false, 'message' => 'Error en la ejecución de consulta']);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);

    // *** INICIO MODIFICACIÓN: Consulta mejorada para encontrar horas reales ***
    $realHours = 0;
    $debug = [];
    $coursesFound = [];

    // Obtener los IDs de cursos en la tabla groups para los cuatro tipos
    $groupSql = "SELECT 
        id_bootcamp, id_leveling_english, id_english_code, id_skills 
        FROM groups 
        WHERE id_bootcamp = ? OR id_leveling_english = ? OR id_english_code = ? OR id_skills = ?";
    $groupStmt = mysqli_prepare($conn, $groupSql);
            
    if ($groupStmt) {
        mysqli_stmt_bind_param($groupStmt, "iiii", $moodleCourseId, $moodleCourseId, $moodleCourseId, $moodleCourseId);
        if (mysqli_stmt_execute($groupStmt)) {
            $groupResult = mysqli_stmt_get_result($groupStmt);
            $courseCodesFound = [];
            
            // Recoger todos los IDs de curso de los grupos
            while ($groupData = mysqli_fetch_assoc($groupResult)) {
                $debug['group_data'][] = $groupData;
                
                // Recopilar los IDs únicos de todos los tipos de cursos
                $courseIds = [
                    $groupData['id_bootcamp'],
                    $groupData['id_leveling_english'],
                    $groupData['id_english_code'],
                    $groupData['id_skills']
                ];
                
                foreach ($courseIds as $id) {
                    if (!empty($id) && !in_array($id, $courseCodesFound)) {
                        $courseCodesFound[] = $id;
                    }
                }
            }
            
            $debug['course_codes_found'] = $courseCodesFound;
            
            // Ahora buscar cada código en la tabla courses
            if (!empty($courseCodesFound)) {
                $placeholders = implode(',', array_fill(0, count($courseCodesFound), '?'));
                $coursesSql = "SELECT code, name, real_hours FROM courses WHERE code IN ($placeholders)";
                $coursesStmt = mysqli_prepare($conn, $coursesSql);
                
                if ($coursesStmt) {
                    // Crear array de tipos para bind_param
                    $types = str_repeat('i', count($courseCodesFound));
                    
                    // Crear array de referencias para bind_param
                    $bindParams = array($coursesStmt, $types);
                    foreach ($courseCodesFound as $key => $value) {
                        $bindParams[] = &$courseCodesFound[$key];
                    }
                    
                    // Llamar a bind_param con parámetros dinámicos
                    call_user_func_array('mysqli_stmt_bind_param', $bindParams);
                    
                    if (mysqli_stmt_execute($coursesStmt)) {
                        $coursesResult = mysqli_stmt_get_result($coursesStmt);
                        
                        while ($course = mysqli_fetch_assoc($coursesResult)) {
                            $coursesFound[] = $course;
                            // Sumar las horas reales de todos los cursos encontrados
                            $realHours += (int)$course['real_hours'];
                        }
                        
                        $debug['courses_found'] = $coursesFound;
                        $debug['method'] = 'multiple_courses';
                    }
                    mysqli_stmt_close($coursesStmt);
                }
            }
        }
        mysqli_stmt_close($groupStmt);
    }

    // Si no encontramos nada por grupos, intentamos buscar directamente por código
    if ($realHours == 0) {
        $directSql = "SELECT real_hours FROM courses WHERE code = ?";
        $directStmt = mysqli_prepare($conn, $directSql);
        
        if ($directStmt) {
            mysqli_stmt_bind_param($directStmt, "i", $courseCode);
            
            if (mysqli_stmt_execute($directStmt)) {
                $directResult = mysqli_stmt_get_result($directStmt);
                
                if (mysqli_num_rows($directResult) > 0) {
                    $directData = mysqli_fetch_assoc($directResult);
                    $realHours = $directData['real_hours'];
                    $debug['method'] = 'direct_match';
                }
            }
            mysqli_stmt_close($directStmt);
        }
    }

    // Método 3: Si todo lo anterior falla, consultar todos los cursos para depuración
    if ($realHours == 0) {
        $allCoursesSql = "SELECT id, code, name, real_hours FROM courses LIMIT 5";
        $allCoursesResult = mysqli_query($conn, $allCoursesSql);
        $allCourses = [];
        while ($row = mysqli_fetch_assoc($allCoursesResult)) {
            $allCourses[] = $row;
        }
        $debug['available_courses'] = $allCourses;
        $debug['moodle_course_id'] = $moodleCourseId;
        $debug['course_code_param'] = $courseCode;
    }
    // *** FIN MODIFICACIÓN ***

    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'No se encontraron datos para este curso']);
        exit;
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => [
            'courseCode' => $courseCode,
            'totalHours' => round($data['total_hours'] ?? 0, 1),
            'totalStudents' => $data['total_students'] ?? 0,
            'realHours' => $realHours
        ],
        'debug' => $debug
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// Función para buscar el ID del curso en Moodle (sin cambios)
function findMoodleCourseId($courseCode) {
    // Configuración de la API de Moodle
    $api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
    $token = "3f158134506350615397c83d861c2104";
    $format = "json";

    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_course_get_courses',
        'moodlewsrestformat' => $format
    ];

    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $courses = json_decode($response, true);
    
    if (!$courses) {
        return null;
    }

    // Buscar el curso que contenga el código
    foreach ($courses as $course) {
        if (strpos($course['fullname'], $courseCode) !== false) {
            return $course['id'];
        }
    }

    return null;
}
?>