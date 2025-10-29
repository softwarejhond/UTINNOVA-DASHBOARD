<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

$bootcamp = isset($_POST['bootcamp']) ? $_POST['bootcamp'] : null;

if (!$bootcamp) {
    echo json_encode(['error' => 'Falta el curso bootcamp requerido']);
    exit;
}

// Función actualizada para calcular horas con límite (igual que exportHours.php)
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
    
    // Aplicar límite si se proporciona (NUEVA FUNCIONALIDAD)
    if ($horasMaximas !== null && $totalHoras > $horasMaximas) {
        return $horasMaximas;
    }
    
    return $totalHoras;
}

// Función corregida para calcular horas totales con límites individuales
function calcularHorasTotalesEstudiante($conn, $studentId) {
    // Obtener todos los cursos del estudiante
    $sql = "SELECT g.id_bootcamp, g.id_english_code, g.id_skills,
                   b.real_hours as bootcamp_hours,
                   e.real_hours as english_hours,
                   s.real_hours as skills_hours,
                   CASE WHEN cs.number_id IS NOT NULL THEN 1 ELSE 0 END AS is_certified
            FROM groups g
            LEFT JOIN courses b ON g.id_bootcamp = b.code
            LEFT JOIN courses e ON g.id_english_code = e.code
            LEFT JOIN courses s ON g.id_skills = s.code
            LEFT JOIN certificados_senatics cs ON g.number_id = cs.number_id
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
    
    // Obtener horas reales de cada curso
    $horasTecnico = isset($row['bootcamp_hours']) ? intval($row['bootcamp_hours']) : 120;
    $horasIngles = isset($row['english_hours']) ? intval($row['english_hours']) : 24;
    $horasHabilidades = isset($row['skills_hours']) ? intval($row['skills_hours']) : 15;
    
    // Calcular horas de Técnico (Bootcamp) - con límite
    if (!empty($row['id_bootcamp'])) {
        $horasCalculadasTecnico = calcularHorasAsistencia($conn, $studentId, $row['id_bootcamp'], $horasTecnico);
        if ($row['is_certified']) {
            $totalHoras += min($horasTecnico, $horasCalculadasTecnico + 40);
        } else {
            $totalHoras += $horasCalculadasTecnico;
        }
    }
    
    // Calcular horas de English Code - con límite
    if (!empty($row['id_english_code'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_english_code'], $horasIngles);
    }
    
    // Calcular horas de Habilidades - con límite
    if (!empty($row['id_skills'])) {
        if ($row['is_certified']) {
            $totalHoras += 15;
        } else {
            $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_skills'], $horasHabilidades);
        }
    }
    
    // NO incluir leveling_english en el cálculo
    
    return $totalHoras;
}

// Reemplazar la función obtenerNotaFinal() con esta función más completa:
function obtenerNotaFinal($conn, $studentId, $courseCode) {
    if (empty($courseCode) || empty($studentId)) {
        return ['final' => 0, 'items' => []];
    }
    
    try {
        // 1. Intentar obtener las notas desde course_approvals (tabla de notas finales/oficiales)
        $sql_approvals = "SELECT final_grade, grade_1, grade_2 FROM course_approvals 
                          WHERE student_number_id = ? AND course_code = ?";
        
        $stmt_approvals = $conn->prepare($sql_approvals);
        if ($stmt_approvals) {
            $stmt_approvals->bind_param("ss", $studentId, $courseCode);
            if ($stmt_approvals->execute()) {
                $result_approvals = $stmt_approvals->get_result();
                $row_approvals = $result_approvals->fetch_assoc();
                $stmt_approvals->close();
                
                if ($row_approvals) {
                    // Las notas en course_approvals ya están en escala 5.0
                    $grade1 = floatval($row_approvals['grade_1']);
                    $grade2 = floatval($row_approvals['grade_2']);
                    
                    // Calcular nota final - Aplicar ponderación 30%-70%
                    $notaFinal = 0;
                    if ($grade1 >= 0 && $grade2 >= 0) {
                        $notaFinal = ($grade1 * 0.30) + ($grade2 * 0.70);
                    } else if ($grade1 >= 0 && $grade2 < 0) {
                        $notaFinal = $grade1;
                    } else if ($grade2 >= 0 && $grade1 < 0) {
                        $notaFinal = $grade2;
                    } else {
                        $notaFinal = 0;
                    }
                    
                    return [
                        'final' => round($notaFinal, 2),
                        'items' => [
                            ['normalizada' => $grade1, 'nota' => number_format($grade1, 1), 'nombre' => 'Nota 1'],
                            ['normalizada' => $grade2, 'nota' => number_format($grade2, 1), 'nombre' => 'Nota 2']
                        ]
                    ];
                }
            } else {
                $stmt_approvals->close();
            }
        }

        // 2. Si no está en la tabla de aprobados, obtener desde notas_estudiantes
        $sql_notas = "SELECT nota1, nota2 FROM notas_estudiantes WHERE number_id = ? AND code = ?";
        $stmt_notas = $conn->prepare($sql_notas);

        if ($stmt_notas) {
            $stmt_notas->bind_param("si", $studentId, $courseCode);
            if ($stmt_notas->execute()) {
                $result_notas = $stmt_notas->get_result();
                $row_notas = $result_notas->fetch_assoc();
                $stmt_notas->close();

                if ($row_notas) {
                    $grade1_raw = floatval($row_notas['nota1']);
                    $grade2_raw = floatval($row_notas['nota2']);
                    
                    // Determinar si las notas están en escala 10
                    $enEscala10 = ($grade1_raw > 5.0 || $grade2_raw > 5.0);
                    
                    // Convertir ambas notas consistentemente
                    if ($enEscala10) {
                        $grade1_normalized = ($grade1_raw / 10.0) * 5.0;
                        $grade2_normalized = ($grade2_raw / 10.0) * 5.0;
                    } else {
                        $grade1_normalized = $grade1_raw;
                        $grade2_normalized = $grade2_raw;
                    }
                    
                    // Calcular nota final - Aplicar ponderación 30%-70%
                    $notaFinal = 0;
                    if ($grade1_normalized >= 0 && $grade2_normalized >= 0) {
                        $notaFinal = ($grade1_normalized * 0.30) + ($grade2_normalized * 0.70);
                    } else if ($grade1_normalized >= 0 && $grade2_normalized < 0) {
                        $notaFinal = $grade1_normalized;
                    } else if ($grade2_normalized >= 0 && $grade1_normalized < 0) {
                        $notaFinal = $grade2_normalized;
                    } else {
                        $notaFinal = 0;
                    }
                    
                    return [
                        'final' => round($notaFinal, 2),
                        'items' => [
                            ['normalizada' => round($grade1_normalized, 2), 'nota' => number_format($grade1_normalized, 1), 'nombre' => 'Nota 1'],
                            ['normalizada' => round($grade2_normalized, 2), 'nota' => number_format($grade2_normalized, 1), 'nombre' => 'Nota 2']
                        ]
                    ];
                }
            } else {
                $stmt_notas->close();
            }
        }

        // 3. Si no está en ninguna tabla, obtener desde Moodle API como fallback
        // Configuración básica para la API de Moodle
        $apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
        $token = '3f158134506350615397c83d861c2104';
        $format = 'json';
        
        // Paso 1: Obtener el userid a partir del número de identificación (username)
        $functionGetUser = 'core_user_get_users_by_field';
        $paramsUser = [
            'field' => 'username',
            'values[0]' => $studentId
        ];
        
        $postdataUser = http_build_query([
            'wstoken' => $token,
            'wsfunction' => $functionGetUser,
            'moodlewsrestformat' => $format
        ] + $paramsUser);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdataUser);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $responseUser = curl_exec($ch);
        $userData = json_decode($responseUser, true);
        
        if (empty($userData)) {
            curl_close($ch);
            return ['final' => 0, 'items' => []];
        }
        
        // Obtener el userid
        $userid = $userData[0]['id'];
        
        // Paso 2: Obtener las notas usando el userid
        $function = 'gradereport_user_get_grade_items';
        $params = [
            'courseid' => $courseCode,
            'userid' => $userid
        ];
        
        $postdata = http_build_query([
            'wstoken' => $token,
            'wsfunction' => $function,
            'moodlewsrestformat' => $format
        ] + $params);
        
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
        $response = curl_exec($ch);
        
        if ($response === false) {
            curl_close($ch);
            return ['final' => 0, 'items' => []];
        }
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            curl_close($ch);
            return ['final' => 0, 'items' => []];
        }
        
        curl_close($ch);
        
        // Paso 3: Procesar las notas de la API de Moodle
        if (isset($data['usergrades'][0])) {
            $usergrade = $data['usergrades'][0];
            $notas = [];
            
            if (isset($usergrade['gradeitems'])) {
                foreach ($usergrade['gradeitems'] as $item) {
                    // Solo tomar ítems de tipo 'course' o que tengan nota asignada
                    if (
                        (isset($item['itemtype']) && $item['itemtype'] === 'course') ||
                        (isset($item['graderaw']) && $item['graderaw'] !== null)
                    ) {
                        $notaRaw = isset($item['graderaw']) ? $item['graderaw'] : null;
                        $notaFormatted = isset($item['gradeformatted']) ? $item['gradeformatted'] : null;
                        $itemname = isset($item['itemname']) ? $item['itemname'] : 'Nota';
                        $grademax = isset($item['grademax']) ? $item['grademax'] : 5.0;
                        
                        if ($notaRaw !== null && $grademax > 0) {
                            // Convertir la nota a escala 5.0 estándar
                            $notaNormalizada = ($notaRaw / $grademax) * 5.0;
                            
                            $notas[] = [
                                'raw' => $notaRaw,
                                'normalizada' => $notaNormalizada,
                                'max' => $grademax,
                                'nota' => $notaFormatted,
                                'nombre' => $itemname
                            ];
                        }
                    }
                    if (count($notas) == 2) break; // Solo las dos primeras notas encontradas
                }
            }
            
            // Calcular nota final con ponderación: 30% primera nota + 70% segunda nota
            if (count($notas) >= 2) {
                $nota1 = $notas[0]['normalizada']; // Primera nota (30%)
                $nota2 = $notas[1]['normalizada']; // Segunda nota (70%)
                
                // Aplicar ponderación: 30% + 70%
                $notaFinal = round(($nota1 * 0.30) + ($nota2 * 0.70), 2);
                
                return [
                    'final' => $notaFinal,
                    'items' => $notas
                ];
            } else if (count($notas) == 1) {
                // Si solo hay una nota, usar esa nota como final
                $notaFinal = round($notas[0]['normalizada'], 2);
                
                return [
                    'final' => $notaFinal,
                    'items' => $notas
                ];
            }
        }
        
        return ['final' => 0, 'items' => []];
        
    } catch (Exception $e) {
        error_log("Excepción en obtenerNotaFinal para estudiante $studentId: " . $e->getMessage());
        return ['final' => 0, 'items' => []];
    }
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
        $horasAsistidas = min(calcularHorasTotalesEstudiante($conn, $row['number_id']), $horasRequeridas);
        $porcentajeAsistencia = min(($horasAsistidas / $horasRequeridas) * 100, 100);
        
        // Obtener nota final
        $resultadoNotas = obtenerNotaFinal($conn, $row['number_id'], $bootcamp);
        $notaFinal = $resultadoNotas['final'];
        $notasItems = $resultadoNotas['items'];
        
        // Verificar si cumple los criterios (75% asistencia de las 159 horas y nota >= 3.0)
        $cumpleAsistencia = $porcentajeAsistencia >= 75;
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
            $tableContent .= '<td class="text-center"><span class="badge badge-' . $colorNota . ' text-black">' . number_format($notaFinal, 1) . '</span>';
            
            // Añadir detalles de notas
            $tableContent .= '<br><small class="text-muted">';
            if (count($notasItems) > 0) {
                foreach ($notasItems as $i => $n) {
                    $tableContent .= "N" . ($i+1) . ": " . (isset($n['nota']) ? $n['nota'] : 'N/A');
                    if ($i < count($notasItems)-1) $tableContent .= " | ";
                }
            } else {
                $tableContent .= "Sin notas";
            }
            $tableContent .= '</small>';
            $tableContent .= '</td>';
            
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
        $tableContent = '<tr><td colspan="11" class="text-center">No hay estudiantes que cumplan los criterios (75% asistencia de 159 horas totales y nota ≥ 3.0)</td></tr>';
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