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

    // Buscar el curso en Moodle para obtener el ID
    $moodleCourseId = findMoodleCourseId($courseCode);
    if (!$moodleCourseId) {
        echo json_encode(['success' => false, 'message' => 'Curso no encontrado en Moodle']);
        exit;
    }

    // Obtener el total de estudiantes según el tipo de curso
    $totalStudents = getTotalStudentsByCourseType($conn, $moodleCourseId, $courseType);

    // Obtener estadísticas de clases por fecha
    $classesStats = getClassesStatsByType($conn, $moodleCourseId, $courseType, $totalStudents);

    // Preparar respuesta
    $response = [
        'success' => true,
        'data' => [
            'classes' => $classesStats,
            'totalStudents' => $totalStudents,
            'courseType' => $courseType
        ]
    ];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error en el servidor: ' . $e->getMessage()]);
}

// Función para obtener el total de estudiantes por tipo de curso
function getTotalStudentsByCourseType($conn, $moodleCourseId, $courseType) {
    // Primero, obtener todos los estudiantes que han tenido asistencia registrada para este curso
    $sql = "SELECT COUNT(DISTINCT student_id) as total_students
            FROM attendance_records 
            WHERE course_id = ?";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return 0;
    }

    mysqli_stmt_bind_param($stmt, "i", $moodleCourseId);
    
    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        return 0;
    }

    $result = mysqli_stmt_get_result($stmt);
    $row = mysqli_fetch_assoc($result);
    
    mysqli_stmt_close($stmt);
    
    $totalFromRecords = $row ? (int)$row['total_students'] : 0;
    
    // Si no hay registros de asistencia, intentar obtener de la tabla groups
    if ($totalFromRecords == 0) {
        $courseIdColumn = '';
        switch ($courseType) {
            case 'bootcamp':
                $courseIdColumn = 'id_bootcamp';
                break;
            case 'leveling_english':
                $courseIdColumn = 'id_leveling_english';
                break;
            case 'english_code':
                $courseIdColumn = 'id_english_code';
                break;
            case 'skills':
                $courseIdColumn = 'id_skills';
                break;
            default:
                return 0;
        }

        $groupSql = "SELECT COUNT(DISTINCT number_id) as total_students
                     FROM groups 
                     WHERE $courseIdColumn = ?";
        
        $groupStmt = mysqli_prepare($conn, $groupSql);
        if (!$groupStmt) return 0;

        mysqli_stmt_bind_param($groupStmt, "i", $moodleCourseId);
        
        if (!mysqli_stmt_execute($groupStmt)) {
            mysqli_stmt_close($groupStmt);
            return 0;
        }

        $groupResult = mysqli_stmt_get_result($groupStmt);
        $groupRow = mysqli_fetch_assoc($groupResult);
        
        mysqli_stmt_close($groupStmt);
        return $groupRow ? (int)$groupRow['total_students'] : 0;
    }
    
    return $totalFromRecords;
}

// Función para obtener estadísticas de clases por tipo
function getClassesStatsByType($conn, $moodleCourseId, $courseType, $totalStudents) {
    // Obtener todas las fechas de clases para este curso
    $sql = "SELECT DISTINCT class_date 
            FROM attendance_records 
            WHERE course_id = ? 
            ORDER BY class_date ASC";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return [];
    }

    mysqli_stmt_bind_param($stmt, "i", $moodleCourseId);
    
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
        $stats = getAttendanceStatsForDate($conn, $moodleCourseId, $classDate, $totalStudents);
        
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

// Función para obtener estadísticas de asistencia para una fecha específica
function getAttendanceStatsForDate($conn, $courseId, $classDate, $totalStudents) {
    // Obtener conteos por estado de asistencia
    $sql = "SELECT 
                attendance_status,
                COUNT(*) as count
            FROM attendance_records 
            WHERE course_id = ? AND class_date = ?
            GROUP BY attendance_status";
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "is", $courseId, $classDate);
    
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
    $noRecord = $totalStudents - $totalWithRecord;
    
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