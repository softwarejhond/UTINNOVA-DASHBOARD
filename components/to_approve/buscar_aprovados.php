<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

$bootcamp = isset($_POST['bootcamp']) ? $_POST['bootcamp'] : null;

if (!$bootcamp) {
    echo json_encode(['error' => 'Falta el curso bootcamp requerido']);
    exit;
}

// Función para calcular horas basadas en asistencia (igual que exportHours.php)
function calcularHorasAsistencia($conn, $studentId, $courseId) {
    if (empty($courseId)) return 0;
    
    $sql = "SELECT ar.class_date, 
                   CASE 
                      WHEN ar.attendance_status = 'presente' THEN 
                          CASE DAYOFWEEK(ar.class_date)
                              WHEN 2 THEN c.monday_hours    -- Lunes
                              WHEN 3 THEN c.tuesday_hours   -- Martes
                              WHEN 4 THEN c.wednesday_hours -- Miércoles
                              WHEN 5 THEN c.thursday_hours  -- Jueves
                              WHEN 6 THEN c.friday_hours    -- Viernes
                              WHEN 7 THEN c.saturday_hours  -- Sábado
                              WHEN 1 THEN c.sunday_hours    -- Domingo
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
    return $totalHoras;
}

// Función para calcular horas totales de todos los cursos de un estudiante
function calcularHorasTotalesEstudiante($conn, $studentId) {
    // Obtener todos los cursos del estudiante (excluyendo leveling_english)
    $sql = "SELECT id_bootcamp, id_english_code, id_skills 
            FROM groups WHERE number_id = ?";
    
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
    
    // Calcular horas de Técnico (Bootcamp) - 120 horas
    if (!empty($row['id_bootcamp'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_bootcamp']);
    }
    
    // Calcular horas de English Code - 24 horas
    if (!empty($row['id_english_code'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_english_code']);
    }
    
    // Calcular horas de Habilidades - 15 horas
    if (!empty($row['id_skills'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_skills']);
    }
    
    // NO incluir leveling_english en el cálculo
    
    return $totalHoras;
}

// Función para obtener nota final
function obtenerNotaFinal($conn, $studentId, $courseCode) {
    if (empty($courseCode)) return 0;
    
    $sql = "SELECT final_grade FROM student_grades 
            WHERE student_number_id = ? AND course_code = ? 
            ORDER BY updated_at DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("ss", $studentId, $courseCode);
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? floatval($row['final_grade']) : 0;
}

// Función para verificar si el estudiante ya está aprobado
function estaAprobado($conn, $studentId, $courseCode) {
    $sql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ss", $studentId, $courseCode);
    if (!$stmt->execute()) {
        return false;
    }
    
    $result = $stmt->get_result();
    $approved = $result->num_rows > 0;
    $stmt->close();
    
    return $approved;
}

// Función para obtener el nombre del programa basado en el tipo de curso
function obtenerNombrePrograma($conn, $courseType, $courseId) {
    if (empty($courseId)) return 'No asignado';
    
    $sql = "SELECT name FROM courses WHERE code = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 'Error al consultar';
    }
    
    $stmt->bind_param("s", $courseId);
    if (!$stmt->execute()) {
        return 'Error al ejecutar';
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['name'] : 'Programa no encontrado';
}

// Simplificar: solo buscar por bootcamp
$courseIdColumn = 'id_bootcamp'; // Solo bootcamp
$courseType = 'bootcamp'; // Fijo como bootcamp

// Total de horas: 159 (120 Técnico + 24 English Code + 15 Habilidades)
// NO incluye leveling_english
$horasRequeridas = 159; // Solo: Técnico + English Code + Habilidades

try {
    // Consultar TODOS los estudiantes del curso bootcamp (sin filtrar por modalidad ni sede)
    $sql = "SELECT g.*, c.real_hours, c.name as course_name
            FROM groups g
            LEFT JOIN courses c ON g.id_bootcamp = c.code
            WHERE g.id_bootcamp = ?
            ORDER BY g.full_name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $bootcamp);

    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    // Variables para obtener información del curso (tomar del primer estudiante)
    $courseInfo = null;
    $estudiantesData = [];
    
    // Obtener todos los resultados
    while ($row = mysqli_fetch_assoc($result)) {
        $estudiantesData[] = $row;
        
        // Obtener información del curso del primer estudiante
        if ($courseInfo === null && !empty($row['mode']) && !empty($row['headquarters'])) {
            $courseInfo = [
                'course_name' => $row['course_name'] ?: 'Técnico',
                'mode' => $row['mode'],
                'headquarters' => $row['headquarters']
            ];
        }
    }

    // Construir el contenido de la tabla
    $tableContent = '';
    $contador = 1;
    $estudiantesAptos = 0;
    $estudiantesAprobados = 0;
    
    foreach ($estudiantesData as $row) {
        // Calcular horas TOTALES de todos los cursos del estudiante
        $horasAsistidas = calcularHorasTotalesEstudiante($conn, $row['number_id']);
        $porcentajeAsistencia = ($horasAsistidas / $horasRequeridas) * 100;
        
        // Obtener nota final
        $notaFinal = obtenerNotaFinal($conn, $row['number_id'], $bootcamp);
        
        // Verificar si cumple los criterios (70% asistencia de las 159 horas y nota >= 3.0)
        $cumpleAsistencia = $porcentajeAsistencia >= 70;
        $cumpleNota = $notaFinal >= 3.0;
        $cumpleCriterios = $cumpleAsistencia && $cumpleNota;
        
        // Verificar si ya está aprobado
        $yaAprobado = estaAprobado($conn, $row['number_id'], $bootcamp);
        
        // Solo mostrar estudiantes que cumplan los criterios
        if ($cumpleCriterios) {
            // Obtener nombre del programa
            $nombrePrograma = obtenerNombrePrograma($conn, $courseType, $bootcamp);
            
            $tableContent .= '<tr data-student-id="' . htmlspecialchars($row['number_id']) . '" id="student-' . htmlspecialchars($row['number_id']) . '">';
            $tableContent .= '<td>' . $contador . '</td>';
            $tableContent .= '<td>' . htmlspecialchars($row['number_id']) . '</td>';
            $tableContent .= '<td>' . htmlspecialchars($row['full_name']) . '</td>';
            $tableContent .= '<td>' . htmlspecialchars($row['institutional_email']) . '</td>';
            
            // Nueva columna: Programa
            $tableContent .= '<td>' . htmlspecialchars($nombrePrograma) . '</td>';
            
            // Nueva columna: Modalidad
            $tableContent .= '<td>' . htmlspecialchars($row['mode']) . '</td>';
            
            // Nueva columna: Sede
            $tableContent .= '<td>' . htmlspecialchars($row['headquarters']) . '</td>';
            
            // Porcentaje de asistencia con barra de progreso (ahora de 159 horas totales)
            $tableContent .= '<td>';
            $tableContent .= '<div class="percentage-bar">';
            $tableContent .= '<div class="percentage-fill" style="width: ' . min(100, $porcentajeAsistencia) . '%; background-color: #ec008c;"></div>';
            $tableContent .= '</div>';
            $tableContent .= '<small class="text-muted">' . number_format($porcentajeAsistencia, 1) . '% (' . $horasAsistidas . '/' . $horasRequeridas . ' hrs totales)</small>';
            $tableContent .= '</td>';
            
            // Nota final
            $colorNota = $notaFinal >= 4.0 ? 'success' : ($notaFinal >= 3.0 ? 'warning' : 'danger');
            $tableContent .= '<td class="text-center"><span class="badge badge-' . $colorNota . ' text-black">' . number_format($notaFinal, 1) . '</span></td>';
            
            // Estado - diferente si ya está aprobado
            $tableContent .= '<td class="text-center">';
            if ($yaAprobado) {
                $tableContent .= '<span class="badge" style="background-color: #ffd700; color: #000;"><i class="fa fa-medal"></i> Aprobado</span>';
            } else {
                $tableContent .= '<span class="badge badge-success"><i class="fa fa-check"></i> Apto</span>';
            }
            $tableContent .= '</td>';
            
            // Icono de birrete o eliminación - según el estado
            $tableContent .= '<td class="text-center">';
            if ($yaAprobado) {
                $tableContent .= '<i class="fas fa-trash-alt delete-approval-icon" ';
                $tableContent .= 'style="color: #dc3545; cursor: pointer; font-size: 20px;" ';
                $tableContent .= 'data-student-id="' . htmlspecialchars($row['number_id']) . '" ';
                $tableContent .= 'data-student-name="' . htmlspecialchars($row['full_name']) . '" ';
                $tableContent .= 'data-course-code="' . htmlspecialchars($bootcamp) . '" ';
                $tableContent .= 'title="Eliminar Aprobación">';
                $tableContent .= '</i>';
            } else {
                $tableContent .= '<i class="fas fa-graduation-cap graduation-icon" ';
                $tableContent .= 'data-bs-toggle="popover" ';
                $tableContent .= 'data-bs-placement="top" ';
                $tableContent .= 'data-bs-trigger="hover" ';
                $tableContent .= 'data-bs-title="Aprobar Estudiante" ';
                $tableContent .= 'data-bs-content="Haz clic para aprobar a este estudiante" ';
                $tableContent .= 'onclick="console.log(\'Click detectado\'); aprobarEstudiante(\'' . htmlspecialchars($row['number_id']) . '\')" ';
                $tableContent .= 'title="Aprobar">';
                $tableContent .= '</i>';
            }
            $tableContent .= '</td>';
            
            $tableContent .= '</tr>';
            
            if ($yaAprobado) {
                $estudiantesAprobados++;
            } else {
                $estudiantesAptos++;
            }
            
            $contador++; // Incrementar contador para cada estudiante mostrado
        }
    }
    
    if (empty($tableContent)) {
        $tableContent = '<tr><td colspan="11" class="text-center">No hay estudiantes que cumplan los criterios (70% asistencia de 159 horas totales y nota ≥ 3.0)</td></tr>';
    }

    echo json_encode([
        'html' => $tableContent,
        'aptos' => $estudiantesAptos,
        'aprobados' => $estudiantesAprobados,
        'total' => $estudiantesAptos + $estudiantesAprobados,
        'courseInfo' => $courseInfo // Agregar información del curso
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
}
?>