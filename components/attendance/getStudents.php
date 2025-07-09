<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

require_once __DIR__ . '/../../controller/conexion.php';

// Verificar la conexión a la base de datos
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

$courseCode = $_POST['courseCode'] ?? '';

if (empty($courseCode)) {
    echo json_encode(['success' => false, 'message' => 'Código de curso faltante']);
    exit;
}

// Función para convertir el estado de admisión
function getStatusText($statusAdmin) {
    $statusMap = [
        '0' => 'Pendiente',
        '1' => 'Beneficiario',
        '2' => 'Rechazado',
        '3' => 'Matriculado',
        '4' => 'Sin contacto',
        '5' => 'En proceso',
        '6' => 'Culminó proceso',
        '7' => 'Inactivo',
        '8' => 'Beneficiario contrapartida',
        '10' => 'Formado'
    ];
    
    return $statusMap[(string)$statusAdmin] ?? 'Estado desconocido';
}

try {
    // Variable para almacenar información de depuración
    $debug = [
        'courseCode' => $courseCode,
        'queriesRun' => 0,
        'errors' => []
    ];

    // Obtener estudiantes para cada tipo de curso
    $students = [
        'tecnico' => [],
        'ingles_nivelado' => [],
        'english_code' => [],
        'habilidades' => []
    ];

    // Obtener fechas de clases para cada tipo de curso
    $classes = [
        'tecnico' => [],
        'ingles_nivelado' => [],
        'english_code' => [],
        'habilidades' => []
    ];

    // Función para obtener fechas de clases y registros de asistencia
    function getClassDatesWithAttendance($conn, $courseCode) {
        $sql = "SELECT DISTINCT class_date 
                FROM attendance_records 
                WHERE course_id = ? 
                ORDER BY class_date ASC";
        
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return [];
        }
        
        mysqli_stmt_bind_param($stmt, "i", $courseCode);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return [];
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $dates = [];
        
        while ($row = mysqli_fetch_assoc($result)) {
            $dates[] = ['class_date' => $row['class_date']];
        }
        
        mysqli_stmt_close($stmt);
        return $dates;
    }

    // Función para obtener estado de asistencia por estudiante y fecha
    function getAttendanceStatus($conn, $studentId, $courseCode, $classDate) {
        $sql = "SELECT attendance_status 
                FROM attendance_records 
                WHERE student_id = ? AND course_id = ? AND class_date = ?
                LIMIT 1";
        
        $stmt = mysqli_prepare($conn, $sql);
        if (!$stmt) {
            return null;
        }
        
        mysqli_stmt_bind_param($stmt, "sis", $studentId, $courseCode, $classDate);
        
        if (!mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            return null;
        }
        
        $result = mysqli_stmt_get_result($stmt);
        $row = mysqli_fetch_assoc($result);
        
        mysqli_stmt_close($stmt);
        return $row ? $row['attendance_status'] : null;
    }

    // Buscar estudiantes por código usando LIKE en los nombres de los cursos
    // 1. Buscar para bootcamp (tecnico)
    $sql = "(SELECT 
                g.number_id, g.full_name, g.email, g.institutional_email,
                g.bootcamp_name as group_name, g.id_bootcamp as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'active' as student_status
            FROM groups g
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.bootcamp_name LIKE ?)
            UNION ALL
            (SELECT 
                eh.number_id, eh.full_name, eh.email, eh.institutional_email,
                eh.bootcamp_name as group_name, eh.id_bootcamp as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'unenrolled' as student_status
            FROM enrollment_history eh
            LEFT JOIN user_register ur ON eh.number_id = ur.number_id
            WHERE eh.bootcamp_name LIKE ?)";

    $debug['queriesRun']++;
    $searchPattern = '%' . $courseCode . '%';
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $debug['errors'][] = 'Error preparación consulta bootcamp: ' . mysqli_error($conn);
        throw new Exception('Error en la preparación de consulta: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $searchPattern, $searchPattern);
    
    if (!mysqli_stmt_execute($stmt)) {
        $debug['errors'][] = 'Error ejecución consulta bootcamp: ' . mysqli_stmt_error($stmt);
        throw new Exception('Error al ejecutar consulta: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $debug['errors'][] = 'Error en resultados bootcamp: ' . mysqli_error($conn);
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn));
    }
    
    $students['tecnico'] = [];
    $courseCodeTecnico = null;
    while ($row = mysqli_fetch_assoc($result)) {
        $statusText = getStatusText($row['estado_admision']);
        $row['estado_admision_texto'] = ($row['student_status'] === 'unenrolled') ? $statusText . '/Desmatriculado' : $statusText;
        $students['tecnico'][] = $row;
        if (!$courseCodeTecnico && $row['course_code']) {
            $courseCodeTecnico = $row['course_code'];
        }
    }
    // Obtener fechas de clases para técnico
    if ($courseCodeTecnico) {
        $classDates = getClassDatesWithAttendance($conn, $courseCodeTecnico);
        // Agregar información de asistencia para cada estudiante y clase
        foreach ($classDates as $index => $classInfo) {
            foreach ($students['tecnico'] as $studentIndex => $student) {
                $attendanceStatus = getAttendanceStatus($conn, $student['number_id'], $courseCodeTecnico, $classInfo['class_date']);
                $classDates[$index]['attendance_by_student'][$student['number_id']] = $attendanceStatus;
            }
        }
        $classes['tecnico'] = $classDates;
    }
    
    $debug['studentCounts']['tecnico'] = count($students['tecnico']);
    mysqli_stmt_close($stmt);

    // 2. Buscar para inglés nivelado
    $sql = "(SELECT 
                g.number_id, g.full_name, g.email, g.institutional_email,
                g.leveling_english_name as group_name, g.id_leveling_english as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'active' as student_status
            FROM groups g
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.leveling_english_name LIKE ?)
            UNION ALL
            (SELECT 
                eh.number_id, eh.full_name, eh.email, eh.institutional_email,
                eh.leveling_english_name as group_name, eh.id_leveling_english as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'unenrolled' as student_status
            FROM enrollment_history eh
            LEFT JOIN user_register ur ON eh.number_id = ur.number_id
            WHERE eh.leveling_english_name LIKE ?)";

    $debug['queriesRun']++;
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $debug['errors'][] = 'Error preparación consulta inglés: ' . mysqli_error($conn);
        throw new Exception('Error en la preparación de consulta: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $searchPattern, $searchPattern);
    
    if (!mysqli_stmt_execute($stmt)) {
        $debug['errors'][] = 'Error ejecución consulta inglés: ' . mysqli_stmt_error($stmt);
        throw new Exception('Error al ejecutar consulta: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $debug['errors'][] = 'Error en resultados inglés: ' . mysqli_error($conn);
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn));
    }
    
    $students['ingles_nivelado'] = [];
    $courseCodeIngles = null;
    while ($row = mysqli_fetch_assoc($result)) {
        $statusText = getStatusText($row['estado_admision']);
        $row['estado_admision_texto'] = ($row['student_status'] === 'unenrolled') ? $statusText . '/Desmatriculado' : $statusText;
        $students['ingles_nivelado'][] = $row;
        if (!$courseCodeIngles && $row['course_code']) {
            $courseCodeIngles = $row['course_code'];
        }
    }
    // Obtener fechas de clases para inglés nivelado
    if ($courseCodeIngles) {
        $classDates = getClassDatesWithAttendance($conn, $courseCodeIngles);
        foreach ($classDates as $index => $classInfo) {
            foreach ($students['ingles_nivelado'] as $studentIndex => $student) {
                $attendanceStatus = getAttendanceStatus($conn, $student['number_id'], $courseCodeIngles, $classInfo['class_date']);
                $classDates[$index]['attendance_by_student'][$student['number_id']] = $attendanceStatus;
            }
        }
        $classes['ingles_nivelado'] = $classDates;
    }
    
    $debug['studentCounts']['ingles_nivelado'] = count($students['ingles_nivelado']);
    mysqli_stmt_close($stmt);

    // 3. Buscar para english_code
    $sql = "(SELECT 
                g.number_id, g.full_name, g.email, g.institutional_email,
                g.english_code_name as group_name, g.id_english_code as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'active' as student_status
            FROM groups g
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.english_code_name LIKE ?)
            UNION ALL
            (SELECT 
                eh.number_id, eh.full_name, eh.email, eh.institutional_email,
                eh.english_code_name as group_name, eh.id_english_code as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'unenrolled' as student_status
            FROM enrollment_history eh
            LEFT JOIN user_register ur ON eh.number_id = ur.number_id
            WHERE eh.english_code_name LIKE ?)";

    $debug['queriesRun']++;
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $debug['errors'][] = 'Error preparación consulta english_code: ' . mysqli_error($conn);
        throw new Exception('Error en la preparación de consulta: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $searchPattern, $searchPattern);
    
    if (!mysqli_stmt_execute($stmt)) {
        $debug['errors'][] = 'Error ejecución consulta english_code: ' . mysqli_stmt_error($stmt);
        throw new Exception('Error al ejecutar consulta: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $debug['errors'][] = 'Error en resultados english_code: ' . mysqli_error($conn);
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn));
    }
    
    $students['english_code'] = [];
    $courseCodeEnglishCode = null;
    while ($row = mysqli_fetch_assoc($result)) {
        $statusText = getStatusText($row['estado_admision']);
        $row['estado_admision_texto'] = ($row['student_status'] === 'unenrolled') ? $statusText . '/Desmatriculado' : $statusText;
        $students['english_code'][] = $row;
        if (!$courseCodeEnglishCode && $row['course_code']) {
            $courseCodeEnglishCode = $row['course_code'];
        }
    }
    // Obtener fechas de clases para english code
    if ($courseCodeEnglishCode) {
        $classDates = getClassDatesWithAttendance($conn, $courseCodeEnglishCode);
        foreach ($classDates as $index => $classInfo) {
            foreach ($students['english_code'] as $studentIndex => $student) {
                $attendanceStatus = getAttendanceStatus($conn, $student['number_id'], $courseCodeEnglishCode, $classInfo['class_date']);
                $classDates[$index]['attendance_by_student'][$student['number_id']] = $attendanceStatus;
            }
        }
        $classes['english_code'] = $classDates;
    }
    
    $debug['studentCounts']['english_code'] = count($students['english_code']);
    mysqli_stmt_close($stmt);

    // 4. Buscar para habilidades
    $sql = "(SELECT 
                g.number_id, g.full_name, g.email, g.institutional_email,
                g.skills_name as group_name, g.id_skills as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'active' as student_status
            FROM groups g
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.skills_name LIKE ?)
            UNION ALL
            (SELECT 
                eh.number_id, eh.full_name, eh.email, eh.institutional_email,
                eh.skills_name as group_name, eh.id_skills as course_code,
                ur.first_phone as celular, ur.schedules as horario, ur.statusAdmin as estado_admision,
                'unenrolled' as student_status
            FROM enrollment_history eh
            LEFT JOIN user_register ur ON eh.number_id = ur.number_id
            WHERE eh.skills_name LIKE ?)";

    $debug['queriesRun']++;
    
    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        $debug['errors'][] = 'Error preparación consulta habilidades: ' . mysqli_error($conn);
        throw new Exception('Error en la preparación de consulta: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($stmt, "ss", $searchPattern, $searchPattern);
    
    if (!mysqli_stmt_execute($stmt)) {
        $debug['errors'][] = 'Error ejecución consulta habilidades: ' . mysqli_stmt_error($stmt);
        throw new Exception('Error al ejecutar consulta: ' . mysqli_stmt_error($stmt));
    }
    
    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        $debug['errors'][] = 'Error en resultados habilidades: ' . mysqli_error($conn);
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn));
    }
    
    $students['habilidades'] = [];
    $courseCodeHabilidades = null;
    while ($row = mysqli_fetch_assoc($result)) {
        $statusText = getStatusText($row['estado_admision']);
        $row['estado_admision_texto'] = ($row['student_status'] === 'unenrolled') ? $statusText . '/Desmatriculado' : $statusText;
        $students['habilidades'][] = $row;
        if (!$courseCodeHabilidades && $row['course_code']) {
            $courseCodeHabilidades = $row['course_code'];
        }
    }
    // Obtener fechas de clases para habilidades
    if ($courseCodeHabilidades) {
        $classDates = getClassDatesWithAttendance($conn, $courseCodeHabilidades);
        foreach ($classDates as $index => $classInfo) {
            foreach ($students['habilidades'] as $studentIndex => $student) {
                $attendanceStatus = getAttendanceStatus($conn, $student['number_id'], $courseCodeHabilidades, $classInfo['class_date']);
                $classDates[$index]['attendance_by_student'][$student['number_id']] = $attendanceStatus;
            }
        }
        $classes['habilidades'] = $classDates;
    }
    
    $debug['studentCounts']['habilidades'] = count($students['habilidades']);
    mysqli_stmt_close($stmt);

    echo json_encode([
        'success' => true, 
        'data' => $students,
        'classes' => $classes,
        'debug' => $debug
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage(),
        'debug' => $debug ?? []
    ]);
}
?>