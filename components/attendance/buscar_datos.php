<?php
session_start();
require_once __DIR__ . '/../../controller/conexion.php'; // Asegúrate de que $conn esté definido

// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    // Verificar que el usuario esté en sesión
    if (!isset($_SESSION['username'])) {
        echo json_encode(['error' => 'Usuario no autorizado']);
        exit;
    }

    // Recoger y validar datos
    $bootcamp   = isset($_POST['bootcamp']) ? (int)$_POST['bootcamp'] : 0;
    $modalidad  = $_POST['modalidad'] ?? '';
    $sede       = $_POST['sede'] ?? '';
    $class_date = $_POST['class_date'] ?? '';
    $courseType = $_POST['courseType'] ?? '';

    if (empty($bootcamp) || empty($modalidad) || empty($sede) || empty($class_date) || empty($courseType)) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        exit;
    }

    // NUEVA VALIDACIÓN: Verificar si ya existe registro de asistencia para esta fecha y curso
    $sqlCheck = "SELECT COUNT(*) as count FROM attendance_records WHERE course_id = ? AND class_date = ?";
    $stmtCheck = mysqli_prepare($conn, $sqlCheck);
    if (!$stmtCheck) {
        echo json_encode(['error' => 'Error al preparar consulta de verificación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmtCheck, "is", $bootcamp, $class_date);
    if (!mysqli_stmt_execute($stmtCheck)) {
        echo json_encode(['error' => 'Error al ejecutar consulta de verificación: ' . mysqli_stmt_error($stmtCheck)]);
        exit;
    }

    $resultCheck = mysqli_stmt_get_result($stmtCheck);
    $rowCheck = mysqli_fetch_assoc($resultCheck);
    
    if ($rowCheck && $rowCheck['count'] > 0) {
        // Ya existe un registro para este curso y esta fecha
        echo json_encode([
            'exists' => true, 
            'message' => 'Ya se ha registrado asistencia para este curso en esta fecha. No es posible registrar asistencia dos veces para la misma fecha.'
        ]);
        exit;
    }
    mysqli_stmt_close($stmtCheck);

    // Si la modalidad es virtual, se fuerza la sede a 'No aplica'
    if (strtolower($modalidad) === 'virtual') {
        $sede = 'No aplica';
    }

    // Obtener el ID del profesor y las horas según el día de la semana desde la tabla courses
    $class_date = $_POST['class_date'];
    $dayOfWeek = date('N', strtotime($class_date)); // 1 (lunes) hasta 7 (domingo)
    
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

    $sqlTeacher = "SELECT teacher, $hours_column as session_hours FROM courses WHERE code = ?";
    $stmtTeacher = mysqli_prepare($conn, $sqlTeacher);
    if (!$stmtTeacher) {
        echo json_encode(['error' => 'Error al preparar consulta de profesor: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmtTeacher, "i", $bootcamp);
    if (!mysqli_stmt_execute($stmtTeacher)) {
        echo json_encode(['error' => 'Error al ejecutar consulta de profesor: ' . mysqli_stmt_error($stmtTeacher)]);
        exit;
    }

    $resultTeacher = mysqli_stmt_get_result($stmtTeacher);
    if ($rowTeacher = mysqli_fetch_assoc($resultTeacher)) {
        $teacher_id = $rowTeacher['teacher'];
        $session_hours = $rowTeacher['session_hours'] ?? 0;

        // Si no hay horas asignadas para ese día, devolver error
        if ($session_hours == 0) {
            echo json_encode([
                'error' => true,
                'message' => 'No hay horas de clase programadas para este día'
            ]);
            exit;
        }
    } else {
        echo json_encode(['error' => 'No se encontró el profesor para este curso']);
        exit;
    }
    mysqli_stmt_close($stmtTeacher);
    
    // Obtener el nombre del profesor
    $sqlTeacherName = "SELECT nombre FROM users WHERE username = ?";
    $stmtTeacherName = mysqli_prepare($conn, $sqlTeacherName);
    if (!$stmtTeacherName) {
        echo json_encode(['error' => 'Error al preparar consulta de nombre de profesor: ' . mysqli_error($conn)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmtTeacherName, "s", $teacher_id);
    if (!mysqli_stmt_execute($stmtTeacherName)) {
        echo json_encode(['error' => 'Error al ejecutar consulta de nombre de profesor: ' . mysqli_stmt_error($stmtTeacherName)]);
        exit;
    }
    
    $resultTeacherName = mysqli_stmt_get_result($stmtTeacherName);
    $teacher_name = '';
    if ($rowTeacherName = mysqli_fetch_assoc($resultTeacherName)) {
        $teacher_name = $rowTeacherName['nombre'];
    }
    mysqli_stmt_close($stmtTeacherName);

    $teacherColumn = '';
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
    }

    // Definir los límites máximos
    $max_hours = [
        'bootcamp' => 120,
        'english_code' => 24,
        'leveling_english' => 20,
        'skills' => 15
    ];

    // Modificar la consulta SQL para obtener las horas actuales y homologación
    $sql = "SELECT g.*, 
            CASE 
                WHEN '$courseType' = 'bootcamp' THEN g.b_intensity
                WHEN '$courseType' = 'english_code' THEN g.ec_intensity
                WHEN '$courseType' = 'leveling_english' THEN g.le_intensity
                WHEN '$courseType' = 'skills' THEN g.s_intensity
            END as current_hours,
            CASE WHEN cs.number_id IS NOT NULL THEN 1 ELSE 0 END AS is_certified
            FROM groups g 
            LEFT JOIN certificados_senatics cs ON g.number_id = cs.number_id
            WHERE $courseIdColumn = ? 
            AND mode = ? 
            AND headquarters = ? 
            ORDER BY full_name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "iss", $bootcamp, $modalidad, $sede);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    // Construir el contenido de la tabla
    $tableContent = '';
    while ($row = mysqli_fetch_assoc($result)) {
        $cumplimiento = $session_hours; // Usar session_hours en lugar de la intensidad seleccionada
        $current_hours = intval($row['current_hours']);
        $max_allowed = $max_hours[$courseType];
        
        // Aplicar ajustes por homologación
        if ($row['is_certified']) {
            switch ($courseType) {
                case 'bootcamp':
                    $current_hours = min($max_allowed, $current_hours + 40);
                    break;
                case 'leveling_english':
                    $current_hours = 20;
                    break;
                case 'skills':
                    $current_hours = 15;
                    break;
                // Para english_code, no hay cambio
            }
        }
        
        // Verificar si el estudiante ya alcanzó el máximo de horas
        $is_disabled_cumplimiento = ($current_hours >= $max_allowed) ? 'disabled' : '';
        $remaining_hours = $max_allowed - $current_hours;
        
        // Si quedan menos horas que la intensidad por sesión, ajustar el cumplimiento
        if ($remaining_hours < $session_hours && $remaining_hours > 0) {
            $cumplimiento = $remaining_hours;
        }

        $tableContent .= '<tr>
            <td class="text-center align-middle" style="width: 8%">' . htmlspecialchars($row['type_id']) . '</td>
            <td class="text-center align-middle" style="width: auto">' . htmlspecialchars($row['number_id']) . '</td>
            <td class="align-middle text-truncate" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['full_name']) . '</td>
            <td class="align-middle">' . htmlspecialchars($row['institutional_email']) . '</td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                       class="form-check-input estado-asistencia" 
                       data-estado="presente" 
                       data-student-id="' . htmlspecialchars($row['number_id']) . '">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                       class="form-check-input estado-asistencia" 
                       data-estado="tarde" 
                       data-student-id="' . htmlspecialchars($row['number_id']) . '">
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                       class="form-check-input estado-asistencia" 
                       data-estado="ausente" 
                       data-student-id="' . htmlspecialchars($row['number_id']) . '">
            </td>
            <td class="text-center align-middle">
                <input type="number" 
                       value="' . $cumplimiento . '" 
                       name="cumplimiento_' . htmlspecialchars($row['number_id']) . '" 
                       class="form-control text-center cumplimiento-input" 
                       data-max="' . $cumplimiento . '" 
                       data-current-hours="' . $current_hours . '"
                       data-max-allowed="' . $max_allowed . '"
                       readonly ' . $is_disabled_cumplimiento . '>
                <small class="text-muted">(' . $current_hours . '/' . $max_allowed . ' hrs)</small>
            </td>
        </tr>';
    }

    if (empty($tableContent)) {
        $tableContent = '<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>';
    }

    // Devolver contenido de tabla, datos del profesor y horas de sesión
    echo json_encode([
        'html' => $tableContent,
        'teacher_id' => $teacher_id,
        'teacher_name' => $teacher_name,
        'session_hours' => $session_hours
    ]);
    exit;
}