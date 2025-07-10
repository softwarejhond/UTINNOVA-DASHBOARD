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
    $courseType = $_POST['courseType'] ?? 'bootcamp'; // Por defecto técnico
    
    if (empty($courseCode)) {
        echo json_encode(['success' => false, 'message' => 'Código de curso requerido']);
        exit;
    }

    // NUEVO: Buscar directamente usando el código del curso en lugar de ID de Moodle
    $courseIds = findCourseIdsByCode($conn, $courseCode, $courseType);
    
    if (empty($courseIds)) {
        echo json_encode([
            'success' => true, 
            'data' => [
                'classes' => [],
                'totalStudents' => 0,
                'courseType' => $courseType
            ]
        ]);
        exit;
    }

    // Obtener el total de estudiantes según el tipo de curso
    $totalStudents = getTotalStudentsByCourseType($conn, $courseCode, $courseType);

    // Obtener estadísticas de clases por fecha usando los IDs encontrados
    $classesStats = getClassesStatsByType($conn, $courseIds, $courseType, $totalStudents);

    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => [
            'classes' => $classesStats,
            'totalStudents' => $totalStudents,
            'courseType' => $courseType,
            'debug' => [
                'courseCode' => $courseCode,
                'courseIds' => $courseIds,
                'totalClasses' => count($classesStats)
            ]
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// NUEVA función para buscar IDs de curso por código
function findCourseIdsByCode($conn, $courseCode, $courseType) {
    $courseIds = [];
    
    // Primero buscar en Moodle
    $moodleCourseId = findMoodleCourseId($courseCode);
    if ($moodleCourseId) {
        $courseIds[] = $moodleCourseId;
    }
    
    // Luego buscar en la base de datos local según el tipo de curso
    $localIds = findLocalCourseIds($conn, $courseCode, $courseType);
    $courseIds = array_merge($courseIds, $localIds);
    
    // Eliminar duplicados
    return array_unique($courseIds);
}

// NUEVA función para buscar IDs locales
function findLocalCourseIds($conn, $courseCode, $courseType) {
    $searchPattern = '%' . $courseCode . '%';
    $localIds = [];
    
    // Buscar en la tabla groups según el tipo de curso
    switch ($courseType) {
        case 'bootcamp':
            $sql = "SELECT DISTINCT id_bootcamp as course_id 
                    FROM groups 
                    WHERE bootcamp_name LIKE ? AND id_bootcamp IS NOT NULL";
            break;
        case 'leveling_english':
            $sql = "SELECT DISTINCT id_leveling_english as course_id 
                    FROM groups 
                    WHERE leveling_english_name LIKE ? AND id_leveling_english IS NOT NULL";
            break;
        case 'english_code':
            $sql = "SELECT DISTINCT id_english_code as course_id 
                    FROM groups 
                    WHERE english_code_name LIKE ? AND id_english_code IS NOT NULL";
            break;
        case 'skills':
            $sql = "SELECT DISTINCT id_skills as course_id 
                    FROM groups 
                    WHERE skills_name LIKE ? AND id_skills IS NOT NULL";
            break;
        default:
            return [];
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return [];
    
    mysqli_stmt_bind_param($stmt, "s", $searchPattern);
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return [];
    }
    
    $result = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($result)) {
        if ($row['course_id']) {
            $localIds[] = (int)$row['course_id'];
        }
    }
    
    mysqli_stmt_close($stmt);
    return $localIds;
}

// Función MODIFICADA para obtener el total de estudiantes por tipo de curso
function getTotalStudentsByCourseType($conn, $courseCode, $courseType) {
    $searchPattern = '%' . $courseCode . '%';
    
    // Construir la consulta según el tipo de curso
    switch ($courseType) {
        case 'bootcamp':
            $sql = "SELECT COUNT(DISTINCT number_id) as total_students
                    FROM groups 
                    WHERE bootcamp_name LIKE ?";
            break;
        case 'leveling_english':
            $sql = "SELECT COUNT(DISTINCT number_id) as total_students
                    FROM groups 
                    WHERE leveling_english_name LIKE ?";
            break;
        case 'english_code':
            $sql = "SELECT COUNT(DISTINCT number_id) as total_students
                    FROM groups 
                    WHERE english_code_name LIKE ?";
            break;
        case 'skills':
            $sql = "SELECT COUNT(DISTINCT number_id) as total_students
                    FROM groups 
                    WHERE skills_name LIKE ?";
            break;
        default:
            return 0;
    }
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) return 0;

    mysqli_stmt_bind_param($stmt, "s", $searchPattern);
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    return $row ? (int)$row['total_students'] : 0;
}

// Función MODIFICADA para obtener estadísticas de clases por tipo
function getClassesStatsByType($conn, $courseIds, $courseType, $totalStudents) {
    if (empty($courseIds)) {
        return [];
    }
    
    // Crear placeholders para los IDs
    $placeholders = str_repeat('?,', count($courseIds) - 1) . '?';
    
    // Obtener todas las fechas de clases para estos cursos
    $sql = "SELECT DISTINCT class_date 
            FROM attendance_records 
            WHERE course_id IN ($placeholders) 
            ORDER BY class_date ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }

    // Bind parameters dinámicamente
    $types = str_repeat('i', count($courseIds));
    mysqli_stmt_bind_param($stmt, $types, ...$courseIds);
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);
    $classesStats = [];
    
    // Configurar idioma para fechas en español
    setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'spanish');
    
    while ($row = mysqli_fetch_assoc($result)) {
        $classDate = $row['class_date'];
        
        // Obtener estadísticas para esta fecha
        $stats = getAttendanceStatsForDate($conn, $courseIds, $classDate, $totalStudents);
        
        if ($stats) {
            // Obtener el nombre del día en español
            $dayNames = [
                'Monday' => 'Lunes',
                'Tuesday' => 'Martes',
                'Wednesday' => 'Miércoles',
                'Thursday' => 'Jueves',
                'Friday' => 'Viernes',
                'Saturday' => 'Sábado',
                'Sunday' => 'Domingo'
            ];
            
            $englishDayName = date('l', strtotime($classDate));
            $spanishDayName = $dayNames[$englishDayName] ?? $englishDayName;
            
            $classesStats[] = [
                'class_date' => $classDate,
                'formatted_date' => date('d/m/Y', strtotime($classDate)),
                'day_name' => $spanishDayName,
                'present' => $stats['present'],
                'present_percentage' => $stats['present_percentage'],
                'late' => $stats['late'],
                'late_percentage' => $stats['late_percentage'],
                'absent' => $stats['absent'],
                'absent_percentage' => $stats['absent_percentage'],
                'no_record' => $stats['no_record'],
                'no_record_percentage' => $stats['no_record_percentage'],
                'total_with_record' => $stats['total_with_record']
            ];
        }
    }
    
    mysqli_stmt_close($stmt);
    return $classesStats;
}

// Función MODIFICADA para obtener estadísticas de asistencia para una fecha específica
function getAttendanceStatsForDate($conn, $courseIds, $classDate, $totalStudents) {
    if (empty($courseIds)) {
        return null;
    }
    
    // Crear placeholders para los IDs
    $placeholders = str_repeat('?,', count($courseIds) - 1) . '?';
    
    // Obtener conteos por estado de asistencia
    $sql = "SELECT 
                attendance_status,
                COUNT(*) as count
            FROM attendance_records 
            WHERE course_id IN ($placeholders) AND class_date = ?
            GROUP BY attendance_status";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return null;
    }

    // Preparar parámetros: IDs + fecha
    $params = array_merge($courseIds, [$classDate]);
    $types = str_repeat('i', count($courseIds)) . 's';
    
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return null;
    }

    $result = mysqli_stmt_get_result($stmt);
    
    // Inicializar contadores
    $present = 0;
    $late = 0;
    $absent = 0;
    
    while ($row = mysqli_fetch_assoc($result)) {
        switch (strtolower(trim($row['attendance_status']))) {
            case 'presente':
                $present = (int)$row['count'];
                break;
            case 'tarde':
                $late = (int)$row['count'];
                break;
            case 'ausente':
                $absent = (int)$row['count'];
                break;
        }
    }
    
    mysqli_stmt_close($stmt);
    
    // Calcular estudiantes sin registro
    $totalWithRecord = $present + $late + $absent;
    $noRecord = max(0, $totalStudents - $totalWithRecord);
    
    // Calcular porcentajes
    $presentPercentage = $totalStudents > 0 ? round(($present / $totalStudents) * 100, 1) : 0;
    $latePercentage = $totalStudents > 0 ? round(($late / $totalStudents) * 100, 1) : 0;
    $absentPercentage = $totalStudents > 0 ? round(($absent / $totalStudents) * 100, 1) : 0;
    $noRecordPercentage = $totalStudents > 0 ? round(($noRecord / $totalStudents) * 100, 1) : 0;
    
    return [
        'present' => $present,
        'present_percentage' => $presentPercentage,
        'late' => $late,
        'late_percentage' => $latePercentage,
        'absent' => $absent,
        'absent_percentage' => $absentPercentage,
        'no_record' => $noRecord,
        'no_record_percentage' => $noRecordPercentage,
        'total_with_record' => $totalWithRecord
    ];
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