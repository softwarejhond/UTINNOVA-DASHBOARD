<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php';

header('Content-Type: application/json');

// // Verificar sesión
// if (!isset($_SESSION['username'])) {
//     echo json_encode(['error' => 'Usuario no autorizado']);
//     exit;
// }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(['error' => 'Datos no válidos']);
    exit;
}

$course_id = $data['course_id'] ?? null;
$modalidad = $data['modalidad'] ?? null;
$sede = $data['sede'] ?? null;
$class_date = $data['class_date'] ?? null;
$attendance = $data['attendance'] ?? [];
$intensity_data = $data['intensity_data'] ?? [];
$course_type = $data['course_type'] ?? null;

if (empty($course_id) || empty($modalidad) || empty($sede) || empty($class_date) || empty($attendance) || empty($course_type)) {
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

// Verificar que no existan registros para esta fecha y curso
$sqlCheck = "SELECT COUNT(*) as count FROM attendance_records WHERE course_id = ? AND class_date = ?";
$stmtCheck = mysqli_prepare($conn, $sqlCheck);
if (!$stmtCheck) {
    echo json_encode(['error' => 'Error al preparar consulta de verificación: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmtCheck, "is", $course_id, $class_date);
if (!mysqli_stmt_execute($stmtCheck)) {
    echo json_encode(['error' => 'Error al ejecutar consulta de verificación: ' . mysqli_stmt_error($stmtCheck)]);
    exit;
}

$resultCheck = mysqli_stmt_get_result($stmtCheck);
$rowCheck = mysqli_fetch_assoc($resultCheck);

if ($rowCheck && $rowCheck['count'] > 0) {
    // Ya existe un registro para este curso y esta fecha
    echo json_encode([
        'error' => 'Ya se ha registrado asistencia para este curso en esta fecha. No es posible registrar asistencia dos veces para la misma fecha.'
    ]);
    exit;
}
mysqli_stmt_close($stmtCheck);

// Obtener el día de la semana de la fecha seleccionada
$dayOfWeek = date('N', strtotime($class_date));
$hours_column = '';
switch ($dayOfWeek) {
    case 1: $hours_column = 'monday_hours'; break;
    case 2: $hours_column = 'tuesday_hours'; break;
    case 3: $hours_column = 'wednesday_hours'; break;
    case 4: $hours_column = 'thursday_hours'; break;
    case 5: $hours_column = 'friday_hours'; break;
    case 6: $hours_column = 'saturday_hours'; break;
    case 7: $hours_column = 'sunday_hours'; break;
}

// Obtener el ID del profesor y session_hours desde la tabla courses
$sqlTeacher = "SELECT teacher, $hours_column as session_hours FROM courses WHERE code = ?";
$stmtTeacher = mysqli_prepare($conn, $sqlTeacher);
if (!$stmtTeacher) {
    echo json_encode(['error' => 'Error al preparar consulta de profesor: ' . mysqli_error($conn)]);
    exit;
}

mysqli_stmt_bind_param($stmtTeacher, "i", $course_id);
if (!mysqli_stmt_execute($stmtTeacher)) {
    echo json_encode(['error' => 'Error al ejecutar consulta de profesor: ' . mysqli_stmt_error($stmtTeacher)]);
    exit;
}

$resultTeacher = mysqli_stmt_get_result($stmtTeacher);
if ($rowTeacher = mysqli_fetch_assoc($resultTeacher)) {
    $teacher_id = $rowTeacher['teacher'];
    $session_hours = $rowTeacher['session_hours'] ?? 0; // Obtener horas por sesión
} else {
    echo json_encode(['error' => 'No se encontró el profesor para este curso']);
    exit;
}
mysqli_stmt_close($stmtTeacher);

// Comenzar transacción para asegurar la integridad de datos
mysqli_begin_transaction($conn);

try {
    // 1. Insertar registros de asistencia
    $sql = "INSERT INTO attendance_records 
            (teacher_id, student_id, course_id, modality, sede, class_date, attendance_status, recorded_hours)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE attendance_status = VALUES(attendance_status), recorded_hours = VALUES(recorded_hours)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    $errors = [];
    foreach ($attendance as $student_id => $status) {
        // Determinar las horas registradas según el estado de asistencia
        $recorded_hours = 0;
        if ($status === 'presente' || $status === 'tarde') {
            // Usar el valor de intensity_data si está disponible, sino usar session_hours
            $recorded_hours = isset($intensity_data[$student_id]) ? intval($intensity_data[$student_id]) : $session_hours;
        }

        mysqli_stmt_bind_param($stmt, "sisssssi", 
            $teacher_id,      // 1. teacher_id (string)
            $student_id,      // 2. student_id (integer)
            $course_id,       // 3. course_id (string)
            $modalidad,       // 4. modality (string)
            $sede,           // 5. sede (string)
            $class_date,      // 6. class_date (string)
            $status,          // 7. attendance_status (string)
            $recorded_hours   // 8. recorded_hours (integer)
        );
        
        if (!mysqli_stmt_execute($stmt)) {
            $errors[] = "Error al guardar para $student_id: " . mysqli_stmt_error($stmt);
        }
    }

    mysqli_stmt_close($stmt);

    // 2. Actualizar la intensidad horaria en la tabla groups según el tipo de curso
    $intensity_column = "";

    switch ($course_type) {
        case 'bootcamp':
            $intensity_column = "b_intensity";
            break;
        case 'english_code':
            $intensity_column = "ec_intensity";
            break;
        case 'skills':
            $intensity_column = "s_intensity";
            break;
        case 'leveling_english':
            $intensity_column = "le_intensity";
            break;
        default:
            break;
    }

    // Definir los topes máximos por tipo de curso
    $max_hours = [
        'bootcamp' => 120,
        'english_code' => 24,
        'leveling_english' => 20,
        'skills' => 15
    ];

    // Verificar tope de horas antes de actualizar
    if (!empty($intensity_column)) {
        foreach ($attendance as $student_id => $status) {
            // Determinar horas a añadir según el estado de asistencia
            $hours_to_add = 0;
            if ($status !== 'ausente') {
                $hours_to_add = isset($intensity_data[$student_id]) ? intval($intensity_data[$student_id]) : $session_hours;
            }
            
            // Obtener la intensidad actual del estudiante
            $check_sql = "SELECT $intensity_column FROM groups WHERE number_id = ?";
            $check_stmt = mysqli_prepare($conn, $check_sql);
            
            if (!$check_stmt) {
                throw new Exception('Error al preparar la consulta de verificación: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($check_stmt, "i", $student_id);
            
            if (!mysqli_stmt_execute($check_stmt)) {
                throw new Exception('Error al ejecutar la verificación: ' . mysqli_stmt_error($check_stmt));
            }
            
            $result = mysqli_stmt_get_result($check_stmt);
            $row = mysqli_fetch_assoc($result);
            
            // Obtener valor actual o inicializar en 0
            $current_intensity = ($row) ? intval($row[$intensity_column]) : 0;
            
            // Calcular nueva intensidad con límite máximo
            $remaining_hours = $max_hours[$course_type] - $current_intensity;

            if ($hours_to_add > $remaining_hours) {
                // Si excede, solo añadir las horas restantes
                $hours_to_add = $remaining_hours;
            }

            $new_intensity = $current_intensity + $hours_to_add;
            
            // Actualizar intensidad en groups con las horas ajustadas
            $update_sql = "UPDATE groups SET $intensity_column = ? WHERE number_id = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            
            if (!$update_stmt) {
                throw new Exception('Error al preparar la actualización: ' . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($update_stmt, "ii", $new_intensity, $student_id);
            
            if (!mysqli_stmt_execute($update_stmt)) {
                throw new Exception(
                    "Error al actualizar intensidad para estudiante $student_id. " .
                    "Actual: $current_intensity, " .
                    "Incremento: $hours_to_add, " .
                    "Nuevo: $new_intensity. " .
                    "Error: " . mysqli_stmt_error($update_stmt)
                );
            }
            
            mysqli_stmt_close($update_stmt);
            mysqli_stmt_close($check_stmt);
        }
    }
    
    // 3. Actualizar las horas reales en la tabla courses usando session_hours
    // Verificar el tope de horas reales en la tabla courses
    $check_real_hours_sql = "SELECT real_hours FROM courses WHERE code = ?";
    $check_real_hours_stmt = mysqli_prepare($conn, $check_real_hours_sql);
    
    if (!$check_real_hours_stmt) {
        throw new Exception('Error al preparar la consulta de horas reales: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($check_real_hours_stmt, "i", $course_id);
    
    if (!mysqli_stmt_execute($check_real_hours_stmt)) {
        throw new Exception('Error al ejecutar la consulta de horas reales: ' . mysqli_stmt_error($check_real_hours_stmt));
    }
    
    $result = mysqli_stmt_get_result($check_real_hours_stmt);
    $row = mysqli_fetch_assoc($result);
    
    // Obtener valor actual o inicializar en 0
    $current_real_hours = ($row && isset($row['real_hours'])) ? intval($row['real_hours']) : 0;
    
    // Usar session_hours para actualizar horas reales
    $hours_to_add = $session_hours;
    $remaining_real_hours = $max_hours[$course_type] - $current_real_hours;

    if ($hours_to_add > $remaining_real_hours) {
        // Si excede, solo añadir las horas restantes
        $hours_to_add = $remaining_real_hours;
    }

    $new_real_hours = $current_real_hours + $hours_to_add;
    
    // Actualizar horas reales en courses
    $update_real_hours_sql = "UPDATE courses SET real_hours = ? WHERE code = ?";
    $update_real_hours_stmt = mysqli_prepare($conn, $update_real_hours_sql);
    
    if (!$update_real_hours_stmt) {
        throw new Exception('Error al preparar la actualización de horas reales: ' . mysqli_error($conn));
    }
    
    mysqli_stmt_bind_param($update_real_hours_stmt, "ii", $new_real_hours, $course_id);
    
    if (!mysqli_stmt_execute($update_real_hours_stmt)) {
        throw new Exception('Error al actualizar horas reales: ' . mysqli_stmt_error($update_real_hours_stmt));
    }
    
    mysqli_stmt_close($update_real_hours_stmt);
    mysqli_stmt_close($check_real_hours_stmt);
    
    // Si todo salió bien, confirmar la transacción
    mysqli_commit($conn);
    
    echo json_encode(['success' => true, 'message' => 'Asistencias guardadas correctamente']);

} catch (Exception $e) {
    // Si ocurrió algún error, revertir la transacción
    mysqli_rollback($conn);
    echo json_encode(['error' => $e->getMessage()]);
}