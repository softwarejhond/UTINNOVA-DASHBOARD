<?php
// Activar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

$bootcamp = isset($_POST['bootcamp']) ? $_POST['bootcamp'] : null;

if (!$bootcamp) {
    echo json_encode(['error' => 'Falta el curso bootcamp requerido']);
    exit;
}

// Log del valor recibido para depuración
error_log("Bootcamp recibido: " . $bootcamp);

// Función para calcular horas con límite (reutilizada de buscar_aprovados.php)
function calcularHorasAsistencia($conn, $studentId, $courseId, $horasMaximas = null) {
    if (empty($courseId)) return 0;
    
    $sql = "SELECT ar.class_date, 
                   CASE 
                      WHEN ar.attendance_status = 'presente' THEN 
                          CASE DAYOFWEEK(ar.class_date)
                              WHEN 2 THEN c.monday_hours
                              WHEN 3 THEN c.tuesday_hours
                              WHEN 4 THEN c.wednesday_hours
                              WHEN 5 THEN c.thursday_hours
                              WHEN 6 THEN c.friday_hours
                              WHEN 7 THEN c.saturday_hours
                              WHEN 1 THEN c.sunday_hours
                              ELSE 0
                          END
                      WHEN ar.attendance_status = 'tarde' THEN ar.recorded_hours
                      ELSE 0 
                   END as horas,
                   ar.attendance_status
            FROM attendance_records ar
            JOIN courses c ON ar.course_id = c.code
            WHERE ar.student_id = ? 
            AND ar.course_id = ?
            ORDER BY ar.class_date, FIELD(ar.attendance_status, 'presente', 'tarde')";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("si", $studentId, $courseId);
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    
    $fechasContadas = [];
    $totalHoras = 0;
    
    while($asistencia = $result->fetch_assoc()) {
        $fecha = $asistencia['class_date'];
        if (!in_array($fecha, $fechasContadas)) {
            $totalHoras += $asistencia['horas'];
            $fechasContadas[] = $fecha;
        }
    }
    
    $stmt->close();
    
    if ($horasMaximas !== null && $totalHoras > $horasMaximas) {
        return $horasMaximas;
    }
    
    return $totalHoras;
}

// Función para calcular horas totales del estudiante
function calcularHorasTotalesEstudiante($conn, $studentId) {
    $sql = "SELECT g.id_bootcamp, g.id_english_code, g.id_skills,
                   b.real_hours as bootcamp_hours,
                   e.real_hours as english_hours,
                   s.real_hours as skills_hours
            FROM groups g
            LEFT JOIN courses b ON g.id_bootcamp = b.code
            LEFT JOIN courses e ON g.id_english_code = e.code
            LEFT JOIN courses s ON g.id_skills = s.code
            WHERE g.number_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("s", $studentId);
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {
        return 0;
    }
    
    $totalHoras = 0;
    
    $horasTecnico = isset($row['bootcamp_hours']) ? intval($row['bootcamp_hours']) : 120;
    $horasIngles = isset($row['english_hours']) ? intval($row['english_hours']) : 24;
    $horasHabilidades = isset($row['skills_hours']) ? intval($row['skills_hours']) : 15;
    
    if (!empty($row['id_bootcamp'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_bootcamp'], $horasTecnico);
    }
    
    if (!empty($row['id_english_code'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_english_code'], $horasIngles);
    }
    
    if (!empty($row['id_skills'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_skills'], $horasHabilidades);
    }
    
    return $totalHoras;
}

// Función para obtener el nombre del programa
function obtenerNombrePrograma($conn, $courseCode) {
    if (empty($courseCode)) return 'No asignado';
    
    $sql = "SELECT name FROM courses WHERE code = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 'Error al consultar';
    }
    
    $stmt->bind_param("s", $courseCode);
    if (!$stmt->execute()) {
        return 'Error al ejecutar';
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['name'] : 'Programa no encontrado';
}

$horasRequeridas = 159; // Total de horas requeridas

try {
    // Primero verificar si existe la tabla course_approvals
    $checkTable = "SHOW TABLES LIKE 'course_approvals'";
    $checkResult = mysqli_query($conn, $checkTable);
    if (!$checkResult || mysqli_num_rows($checkResult) == 0) {
        echo json_encode(['error' => 'La tabla course_approvals no existe en la base de datos']);
        exit;
    }

    // Verificar si hay registros en course_approvals
    $countQuery = "SELECT COUNT(*) as total FROM course_approvals";
    $countResult = mysqli_query($conn, $countQuery);
    $countRow = mysqli_fetch_assoc($countResult);
    error_log("Total de registros en course_approvals: " . $countRow['total']);

    // Verificar si hay registros para este curso específico
    $courseCheckQuery = "SELECT COUNT(*) as total FROM course_approvals WHERE course_code = ?";
    $courseCheckStmt = mysqli_prepare($conn, $courseCheckQuery);
    mysqli_stmt_bind_param($courseCheckStmt, "s", $bootcamp);
    mysqli_stmt_execute($courseCheckStmt);
    $courseCheckResult = mysqli_stmt_get_result($courseCheckStmt);
    $courseCheckRow = mysqli_fetch_assoc($courseCheckResult);
    error_log("Registros para el curso $bootcamp: " . $courseCheckRow['total']);

    // Consultar ÚNICAMENTE estudiantes APROBADOS para el curso específico con sus notas desde course_approvals
    $sql = "SELECT g.*, c.real_hours, c.name as course_name, 
                   ca.created_at, ca.final_grade, ca.grade_1, ca.grade_2, ca.approved_by
            FROM course_approvals ca
            JOIN groups g ON ca.student_number_id = g.number_id
            LEFT JOIN courses c ON ca.course_code = c.code
            WHERE ca.course_code = ?
            ORDER BY ca.created_at DESC, g.full_name ASC";

    error_log("Query a ejecutar: " . $sql);
    error_log("Parámetro course_code: " . $bootcamp);

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    // Cambié de "i" a "s" porque course_code puede ser string
    mysqli_stmt_bind_param($stmt, "s", $bootcamp);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    $courseInfo = null;
    $estudiantesData = [];
    
    while ($row = mysqli_fetch_assoc($result)) {
        $estudiantesData[] = $row;
        error_log("Estudiante encontrado: " . $row['full_name'] . " - ID: " . $row['number_id']);
        
        if ($courseInfo === null && !empty($row['mode']) && !empty($row['headquarters'])) {
            $courseInfo = [
                'course_name' => $row['course_name'] ?: 'Técnico',
                'mode' => $row['mode'],
                'headquarters' => $row['headquarters']
            ];
        }
    }

    error_log("Total de estudiantes encontrados: " . count($estudiantesData));

    // Si no se encontraron estudiantes, mostrar información de depuración adicional
    if (empty($estudiantesData)) {
        // Verificar qué valores de course_code existen en course_approvals
        $debugQuery = "SELECT DISTINCT course_code FROM course_approvals LIMIT 10";
        $debugResult = mysqli_query($conn, $debugQuery);
        $existingCodes = [];
        while ($debugRow = mysqli_fetch_assoc($debugResult)) {
            $existingCodes[] = $debugRow['course_code'];
        }
        error_log("Códigos de curso existentes en course_approvals: " . implode(', ', $existingCodes));
        
        echo json_encode([
            'html' => '<tr><td colspan="11" class="text-center py-5"><i class="fa fa-info-circle fa-2x text-info"></i><br><br>No hay estudiantes aprobados para este curso<br><small class="text-muted">Curso buscado: ' . htmlspecialchars($bootcamp) . '<br>Códigos disponibles: ' . implode(', ', $existingCodes) . '</small></td></tr>',
            'total' => 0,
            'courseInfo' => null,
            'debug' => [
                'bootcamp_searched' => $bootcamp,
                'existing_codes' => $existingCodes,
                'total_approvals' => $countRow['total']
            ]
        ]);
        exit;
    }

    $tableContent = '';
    $contador = 1;
    $totalAprobados = 0;
    
    foreach ($estudiantesData as $row) {
        // Calcular datos del estudiante
        $horasAsistidas = min(calcularHorasTotalesEstudiante($conn, $row['number_id']), $horasRequeridas);
        $porcentajeAsistencia = min(($horasAsistidas / $horasRequeridas) * 100, 100);
        
        // Usar las notas directamente de la tabla course_approvals
        $notaFinal = floatval($row['final_grade']) ?: 0;
        $nota1 = floatval($row['grade_1']) ?: 0;
        $nota2 = floatval($row['grade_2']) ?: 0;
        
        $nombrePrograma = obtenerNombrePrograma($conn, $bootcamp);
        
        $tableContent .= '<tr data-student-id="' . htmlspecialchars($row['number_id']) . '"
                          data-student-cedula="' . htmlspecialchars($row['number_id']) . '"
                          data-student-name="' . htmlspecialchars($row['full_name']) . '"
                          data-student-email="' . htmlspecialchars($row['institutional_email']) . '"
                          data-student-modalidad="presencial"
                          data-student-start-date="' . date('Y-m-d') . '"
                          data-student-end-date="' . date('Y-m-d') . '">';
        $tableContent .= '<td class="text-center">';
        $tableContent .= '<input type="checkbox" class="student-checkbox" value="' . htmlspecialchars($row['number_id']) . '">';
        $tableContent .= '</td>';
        $tableContent .= '<td>' . $contador . '</td>';
        $tableContent .= '<td>' . htmlspecialchars($row['number_id']) . '</td>';
        $tableContent .= '<td>' . htmlspecialchars($row['full_name']) . '</td>';
        $tableContent .= '<td>' . htmlspecialchars($row['institutional_email']) . '</td>';
        $tableContent .= '<td>' . htmlspecialchars($nombrePrograma) . '</td>';
        $tableContent .= '<td>' . htmlspecialchars($row['mode']) . '</td>';
        $tableContent .= '<td>' . htmlspecialchars($row['headquarters']) . '</td>';
        
        // Porcentaje de asistencia
        $tableContent .= '<td>';
        $tableContent .= '<div class="percentage-bar">';
        $tableContent .= '<div class="percentage-fill" style="width: ' . min(100, $porcentajeAsistencia) . '%; background-color: #ec008c;"></div>';
        $tableContent .= '</div>';
        $tableContent .= '<small class="text-muted">' . number_format($porcentajeAsistencia, 1) . '% (' . $horasAsistidas . '/' . $horasRequeridas . ' hrs totales)</small>';
        $tableContent .= '</td>';
        
        // Nota final con las notas individuales almacenadas en la BD
        $colorNota = $notaFinal >= 4.0 ? 'success' : ($notaFinal >= 3.0 ? 'warning' : 'danger');
        $tableContent .= '<td class="text-center"><span class="badge badge-' . $colorNota . ' text-black">' . number_format($notaFinal, 1) . '</span>';
        
        // Mostrar las notas individuales si están disponibles
        $tableContent .= '<br><small class="text-muted">';
        if ($nota1 > 0 || $nota2 > 0) {
            $notasArray = [];
            if ($nota1 > 0) $notasArray[] = "N1: " . number_format($nota1, 1);
            if ($nota2 > 0) $notasArray[] = "N2: " . number_format($nota2, 1);
            $tableContent .= implode(" | ", $notasArray);
        } else {
            $tableContent .= "Solo nota final disponible";
        }
        $tableContent .= '</small>';
        $tableContent .= '</td>';
        
        // Estado (siempre aprobado)
        $tableContent .= '<td class="text-center">';
        $tableContent .= '<span class="badge" style="background-color: #ffd700; color: #000;"><i class="fa fa-medal"></i> Aprobado</span>';
        $tableContent .= '</td>';
        
        // Botón de acción (sin funcionalidad por el momento)
        $tableContent .= '<td class="text-center">';
        $tableContent .= '<button class="btn btn-sm btn-outline-primary" disabled>';
        $tableContent .= '<i class="fa fa-certificate"></i> Generar';
        $tableContent .= '</button>';
        $tableContent .= '</td>';
        
        $tableContent .= '</tr>';
        
        $totalAprobados++;
        $contador++;
    }

    echo json_encode([
        'html' => $tableContent,
        'total' => $totalAprobados,
        'courseInfo' => $courseInfo
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>