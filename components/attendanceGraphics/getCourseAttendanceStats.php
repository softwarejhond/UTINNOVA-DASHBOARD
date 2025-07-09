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

    // Inicializar arrays para los resultados
    $attendanceStats = [
        'bootcamp' => ['average_attendance' => 0, 'real_hours_attendance' => 0, 'real_hours' => 0],
        'leveling_english' => ['average_attendance' => 0, 'real_hours_attendance' => 0, 'real_hours' => 0],
        'english_code' => ['average_attendance' => 0, 'real_hours_attendance' => 0, 'real_hours' => 0],
        'skills' => ['average_attendance' => 0, 'real_hours_attendance' => 0, 'real_hours' => 0]
    ];

    // Obtener TODOS los grupos relacionados con este curso
    $groupSql = "SELECT 
        id_bootcamp, id_leveling_english, id_english_code, id_skills,
        b_intensity, le_intensity, ec_intensity, s_intensity
        FROM groups 
        WHERE id_bootcamp = ? OR id_leveling_english = ? OR id_english_code = ? OR id_skills = ?";
    
    $groupStmt = mysqli_prepare($conn, $groupSql);
    if ($groupStmt) {
        mysqli_stmt_bind_param($groupStmt, "iiii", $moodleCourseId, $moodleCourseId, $moodleCourseId, $moodleCourseId);
        
        if (mysqli_stmt_execute($groupStmt)) {
            $groupResult = mysqli_stmt_get_result($groupStmt);
            
            // Arrays para recopilar todos los datos
            $allData = [
                'bootcamp' => ['course_ids' => [], 'intensities' => []],
                'leveling_english' => ['course_ids' => [], 'intensities' => []],
                'english_code' => ['course_ids' => [], 'intensities' => []],
                'skills' => ['course_ids' => [], 'intensities' => []]
            ];
            
            // Procesar cada grupo y recopilar TODOS los datos
            while ($groupData = mysqli_fetch_assoc($groupResult)) {
                // Bootcamp - Recopilar TODOS los datos de bootcamp
                if (!empty($groupData['id_bootcamp'])) {
                    $allData['bootcamp']['course_ids'][] = $groupData['id_bootcamp'];
                    $allData['bootcamp']['intensities'][] = $groupData['b_intensity'] ?? 0;
                }
                
                // Leveling English - Recopilar TODOS los datos de inglés nivelador
                if (!empty($groupData['id_leveling_english'])) {
                    $allData['leveling_english']['course_ids'][] = $groupData['id_leveling_english'];
                    $allData['leveling_english']['intensities'][] = $groupData['le_intensity'] ?? 0;
                }
                
                // English Code - Recopilar TODOS los datos de English Code
                if (!empty($groupData['id_english_code'])) {
                    $allData['english_code']['course_ids'][] = $groupData['id_english_code'];
                    $allData['english_code']['intensities'][] = $groupData['ec_intensity'] ?? 0;
                }
                
                // Skills - Recopilar TODOS los datos de habilidades
                if (!empty($groupData['id_skills'])) {
                    $allData['skills']['course_ids'][] = $groupData['id_skills'];
                    $allData['skills']['intensities'][] = $groupData['s_intensity'] ?? 0;
                }
            }
            
            // Procesar cada tipo de curso
            foreach ($allData as $type => $data) {
                if (!empty($data['intensities'])) {
                    // Calcular promedio de asistencias
                    $attendanceStats[$type]['average_attendance'] = round(array_sum($data['intensities']) / count($data['intensities']), 1);
                    
                    // Obtener horas reales - usar el curso que coincida con el código buscado
                    $uniqueCourseIds = array_unique($data['course_ids']);
                    
                    foreach ($uniqueCourseIds as $courseId) {
                        $realHoursSql = "SELECT real_hours FROM courses WHERE code = ?";
                        $realHoursStmt = mysqli_prepare($conn, $realHoursSql);
                        
                        if ($realHoursStmt) {
                            mysqli_stmt_bind_param($realHoursStmt, "i", $courseId);
                            if (mysqli_stmt_execute($realHoursStmt)) {
                                $realHoursResult = mysqli_stmt_get_result($realHoursStmt);
                                if (mysqli_num_rows($realHoursResult) > 0) {
                                    $realHoursData = mysqli_fetch_assoc($realHoursResult);
                                    $realHours = $realHoursData['real_hours'] ?? 0;
                                    
                                    if ($realHours > 0) {
                                        $attendanceStats[$type]['real_hours'] = $realHours;
                                        $attendanceStats[$type]['real_hours_attendance'] = $realHours;
                                        break; // Encontramos horas reales, salir del bucle
                                    }
                                }
                            }
                            mysqli_stmt_close($realHoursStmt);
                        }
                    }
                }
            }
        }
        mysqli_stmt_close($groupStmt);
    }

    // Agregar información de depuración
    $debug = [
        'moodle_course_id' => $moodleCourseId,
        'course_code' => $courseCode,
        'found_data' => []
    ];

    foreach ($attendanceStats as $type => $stats) {
        if ($stats['average_attendance'] > 0 || $stats['real_hours'] > 0) {
            $debug['found_data'][$type] = $stats;
        }
    }

    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => $attendanceStats,
        'debug' => $debug
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// Función para buscar el ID del curso en Moodle
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