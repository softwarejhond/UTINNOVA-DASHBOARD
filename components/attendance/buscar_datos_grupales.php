<?php
error_reporting(0); // Desactivar la salida de errores PHP
header('Content-Type: application/json'); // Establecer el header JSON

session_start();
require_once __DIR__ . '/../../controller/conexion.php'; // Asegúrate de que $conn esté definido

// Verificar la conexión a la base de datos
if (!$conn) {
    echo json_encode(['error' => 'Error de conexión a la base de datos']);
    exit;
}

// Verificar que se reciba una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Verificar que el usuario esté en sesión
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autorizado']);
    exit;
}

// Función para obtener las horas totales según el tipo de curso
function getHorasTotalesCurso($courseType) {
    switch ($courseType) {
        case 'bootcamp':
            return 120; // Técnico
        case 'english_code':
            return 24;  // English Code
        case 'skills':
            return 15;  // Habilidades de poder
        default:
            return 0;
    }
}

try {
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

    // Si la modalidad es virtual, se fuerza la sede a 'No aplica'
    if (strtolower($modalidad) === 'virtual') {
        $sede = 'No aplica';
    }

    $courseIdColumn = '';
    $realsColumn = '';
    switch ($courseType) {
        case 'bootcamp':
            $courseIdColumn = 'id_bootcamp';
            $realsColumn = 'b_reals';
            break;
        case 'leveling_english':
            $courseIdColumn = 'id_leveling_english';
            $realsColumn = 'le_reals';
            break;
        case 'english_code':
            $courseIdColumn = 'id_english_code';
            $realsColumn = 'ec_reals';
            break;
        case 'skills':
            $courseIdColumn = 'id_skills';
            $realsColumn = 's_reals';
            break;
    }

    // Consultar el progreso grupal
    $sqlProgress = "SELECT AVG($realsColumn) as avg_progress FROM groups 
                    WHERE $courseIdColumn = ? 
                    AND mode = ? 
                    AND headquarters = ?";
    
    $stmtProgress = mysqli_prepare($conn, $sqlProgress);
    if (!$stmtProgress) {
        echo json_encode(['error' => 'Error en la preparación de progreso: ' . mysqli_error($conn)]);
        exit;
    }
    
    mysqli_stmt_bind_param($stmtProgress, "iss", $bootcamp, $modalidad, $sede);
    
    if (!mysqli_stmt_execute($stmtProgress)) {
        echo json_encode(['error' => 'Error al ejecutar consulta de progreso: ' . mysqli_stmt_error($stmtProgress)]);
        exit;
    }
    
    $resultProgress = mysqli_stmt_get_result($stmtProgress);
    $progressRow = mysqli_fetch_assoc($resultProgress);
    $avgProgress = $progressRow['avg_progress'] ?? 0;
    
    // Obtener el total de horas requeridas para calcular el porcentaje
    $totalHorasRequeridas = getHorasTotalesCurso($courseType);
    $progressPercent = ($totalHorasRequeridas > 0) ? ($avgProgress / $totalHorasRequeridas) * 100 : 0;
    $progressPercent = round($progressPercent, 1);
    
    // MODIFICACIÓN: Mejorar la consulta SQL para obtener con seguridad los estados de asistencia
    $sql = "SELECT g.*, 
        (SELECT attendance_status FROM attendance_records 
         WHERE student_id = g.number_id AND class_date = ? AND course_id = ? LIMIT 1) as attendance_status,
        (SELECT COUNT(DISTINCT class_date) 
         FROM attendance_records 
         WHERE student_id = g.number_id 
         AND course_id = ?
         AND (attendance_status = 'presente' OR attendance_status = 'tarde')) as total_attendance
        FROM groups g 
        WHERE g.$courseIdColumn = ? 
        AND g.mode = ? 
        AND g.headquarters = ?
        ORDER BY g.full_name ASC";
        

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "siisss", $class_date, $bootcamp, $bootcamp, $bootcamp, $modalidad, $sede);

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
    $studentCount = 0;
    $attendanceCount = 0;
    $contador = 1;
    
    // Verificar primero si hay registros de asistencia para esta fecha y curso
    $checkAttendanceSQL = "SELECT COUNT(*) as count FROM attendance_records 
                          WHERE class_date = ? AND course_id = ?";
    $checkStmt = mysqli_prepare($conn, $checkAttendanceSQL);
    mysqli_stmt_bind_param($checkStmt, "si", $class_date, $bootcamp);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);
    $attendanceExists = mysqli_fetch_assoc($checkResult)['count'] > 0;
    
    // Si no hay registros para esta fecha, mostrar mensaje específico
    if (!$attendanceExists) {
        $tableContent = '<tr><td colspan="11" class="text-center">No hay registros de asistencia para la fecha seleccionada</td></tr>';
        echo json_encode([
            'html' => $tableContent,
            'progressInfo' => [
                'percent' => 0,
                'avgHours' => 0,
                'totalHours' => getHorasTotalesCurso($courseType)
            ]
        ]);
        exit;
    }
    
    // Continúa con el procesamiento normal si sí hay registros
    while ($row = mysqli_fetch_assoc($result)) {
        $studentCount++;
        // Normalizar el estado de asistencia (quitar espacios y convertir a minúsculas)
        $attendanceStatus = isset($row['attendance_status']) ? trim(strtolower($row['attendance_status'])) : '';
        
        if (!empty($attendanceStatus)) {
            $attendanceCount++;
        }

        // Obtener las horas según el tipo de curso
        $horasAsistidas = 0;
        switch ($courseType) {
            case 'bootcamp':
                $horasAsistidas = (float)$row['b_intensity'] ?? 0;
                break;
            case 'english_code':
                $horasAsistidas = (float)$row['ec_intensity'] ?? 0;
                break;
            case 'skills':
                $horasAsistidas = (float)$row['s_intensity'] ?? 0;
                break;
        }

        // Obtener el total de horas para el tipo de curso
        $totalHorasRequeridas = getHorasTotalesCurso($courseType);

        // Calcular el porcentaje
        $attendance_percent = ($totalHorasRequeridas > 0) ? ($horasAsistidas / $totalHorasRequeridas) * 100 : 0;

        // Calcular el porcentaje restante
        $remaining_percent = 100 - $attendance_percent;

        // Redondear para mejor visualización
        $horasAsistidasRound = round($horasAsistidas, 1);
        $totalHorasRequeridasRound = $totalHorasRequeridas;

        $circumference = 2 * pi() * 21;
        $offset = $circumference * ($attendance_percent / 100);

        $tableContent .= '<tr>
            <td class="text-center align-middle">' . $contador . '</td>
            <td class="text-center align-middle" style="width: 8%">' . htmlspecialchars($row['type_id']) . '</td>
            <td class="text-center align-middle" style="width: auto">' . htmlspecialchars($row['number_id']) . '</td>
            <td class="align-middle text-truncate" style="width: 30%; max-width: 300px">' . htmlspecialchars($row['full_name']) . '</td>
            <td class="email-cell">' . htmlspecialchars($row['institutional_email']) . '</td>

            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="presente" 
                    ' . ($attendanceStatus === 'presente' ? 'checked' : '') . ' disabled>
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="tarde" 
                    ' . ($attendanceStatus === 'tarde' ? 'checked' : '') . ' disabled>
            </td>
            <td class="text-center align-middle">
                <input type="radio" name="attendance_status_' . htmlspecialchars($row['number_id']) . '" 
                    class="form-check-input estado-asistencia" data-estado="ausente" 
                    ' . ($attendanceStatus === 'ausente' ? 'checked' : '') . ' disabled>
            </td>
            <td class="text-center align-middle">
                <div class="circular-progress">
                    <svg width="50" height="50">
                        <circle class="progress-background" cx="25" cy="25" r="21" />
                        <circle class="progress-bar" cx="25" cy="25" r="21" 
                            stroke-dasharray="' . $circumference . '" 
                            stroke-dashoffset="' . $offset . '" />
                    </svg>
                    <div class="progress-text">' . round($remaining_percent) . '%</div>
                </div>
            </td>
            
            <td class="text-center align-middle">
                <div class="attendance-hours">
                    <span class="font-weight-bold">' . $horasAsistidasRound . '</span> / 
                    <span>' . $totalHorasRequeridasRound . '</span>
                    <small class="d-block">hrs</small>
                </div>
            </td>

            <td class="text-center align-middle">
                <button type="button" 
                    class="btn btn-primary btn-sm registrar-ausencia" 
                    data-student-id="' . htmlspecialchars($row['number_id']) . '"
                    data-student-name="' . htmlspecialchars($row['full_name']) . '"
                    data-attendance-status="' . htmlspecialchars($attendanceStatus) . '">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
            </td>
        </tr>';

        $contador++;
    }

    if (empty($tableContent)) {
        $tableContent = '<tr><td colspan="10" class="text-center">No se encontraron registros</td></tr>';
    }

    echo json_encode([
        'html' => $tableContent, 
        'progressInfo' => [
            'percent' => $progressPercent,
            'avgHours' => round($avgProgress, 1),
            'totalHours' => $totalHorasRequeridas
        ],
        'debug' => [
            'totalStudents' => $studentCount,
            'withAttendance' => $attendanceCount
        ]
    ]);
    exit;
} catch (Exception $e) {
    echo json_encode(['error' => 'Error en el servidor: ' . $e->getMessage()]);
    exit;
}
?>