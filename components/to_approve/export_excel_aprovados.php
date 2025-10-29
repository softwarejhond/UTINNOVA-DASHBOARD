<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Obtener parámetros - Solo bootcamp es requerido ahora
$bootcamp = isset($_POST['bootcamp']) ? $_POST['bootcamp'] : null;

if (!$bootcamp) {
    http_response_code(400);
    echo json_encode(['error' => 'Falta el curso bootcamp requerido']);
    exit;
}

// Funciones de cálculo de horas (sin cambios)
function calcularHorasAsistencia($conn, $studentId, $courseId) {
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
    return $totalHoras;
}

function calcularHorasTotalesEstudiante($conn, $studentId) {
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
    
    // Calcular horas de Técnico (Bootcamp) con límite
    if (!empty($row['id_bootcamp'])) {
        $horasCalculadasTecnico = calcularHorasAsistencia($conn, $studentId, $row['id_bootcamp']);
        if ($row['is_certified']) {
            // Sumar 40 horas adicionales pero no superar el límite de horasTecnico
            $totalHoras += min($horasTecnico, $horasCalculadasTecnico + 40);
        } else {
            $totalHoras += min($horasCalculadasTecnico, $horasTecnico);
        }
    }
    
    // Calcular horas de English Code con límite
    if (!empty($row['id_english_code'])) {
        $horasCalculadasIngles = calcularHorasAsistencia($conn, $studentId, $row['id_english_code']);
        $totalHoras += min($horasCalculadasIngles, $horasIngles);
    }
    
    // Calcular horas de Habilidades con límite
    if (!empty($row['id_skills'])) {
        if ($row['is_certified']) {
            // Setear habilidades a 15 horas completas
            $totalHoras += 15;
        } else {
            $horasCalculadasHabilidades = calcularHorasAsistencia($conn, $studentId, $row['id_skills']);
            $totalHoras += min($horasCalculadasHabilidades, $horasHabilidades);
        }
    }
    
    return $totalHoras;
}

// Nueva función que obtiene las tres notas
function obtenerNotas($conn, $studentId, $courseCode) {
    if (empty($courseCode) || empty($studentId)) {
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
    
    try {
        // 1. Intentar obtener las notas desde course_approvals (tabla de notas finales/oficiales)
        $sql_approvals = "SELECT final_grade, grade_1, grade_2 FROM course_approvals 
                          WHERE student_number_id = ? AND course_code = ?";
        
        $stmt_approvals = $conn->prepare($sql_approvals);
        if (!$stmt_approvals) {
            error_log("Error preparando consulta de notas aprobadas: " . $conn->error);
            // Continúa para intentar desde la otra tabla
        } else {
            $stmt_approvals->bind_param("ss", $studentId, $courseCode);
            if ($stmt_approvals->execute()) {
                $result_approvals = $stmt_approvals->get_result();
                $row_approvals = $result_approvals->fetch_assoc();
                $stmt_approvals->close();
                
                if ($row_approvals) {
                    // Las notas en course_approvals ya están en escala 5.0
                    $grade1 = floatval($row_approvals['grade_1']);
                    $grade2 = floatval($row_approvals['grade_2']);
                    
                    // Calcular nota final - CORREGIDO: Aplicar ponderación 30%-70%
                    $notaFinal = 0;
                    if ($grade1 >= 0 && $grade2 >= 0) {
                        // Aplicar ponderación: 30% primera nota + 70% segunda nota
                        $notaFinal = ($grade1 * 0.30) + ($grade2 * 0.70);
                    } else if ($grade1 >= 0 && $grade2 < 0) {
                        // Solo primera nota disponible - sin ponderación
                        $notaFinal = $grade1;
                    } else if ($grade2 >= 0 && $grade1 < 0) {
                        // Solo segunda nota disponible - sin ponderación
                        $notaFinal = $grade2;
                    } else {
                        // Ambas notas son negativas o no válidas
                        $notaFinal = 0;
                    }
                    
                    return [
                        'final' => round($notaFinal, 2),
                        'grade1' => $grade1,
                        'grade2' => $grade2
                    ];
                }
            } else {
                error_log("Error ejecutando consulta de notas aprobadas: " . $stmt_approvals->error);
                $stmt_approvals->close();
            }
        }

        // 2. Si no está en la tabla de aprobados, obtener desde notas_estudiantes
        $sql_notas = "SELECT nota1, nota2 FROM notas_estudiantes WHERE number_id = ? AND code = ?";
        $stmt_notas = $conn->prepare($sql_notas);

        if (!$stmt_notas) {
            error_log("Error preparando consulta de notas_estudiantes: " . $conn->error);
            // Continuar con API de Moodle como fallback
        } else {
            $stmt_notas->bind_param("si", $studentId, $courseCode);
            if ($stmt_notas->execute()) {
                $result_notas = $stmt_notas->get_result();
                $row_notas = $result_notas->fetch_assoc();
                $stmt_notas->close();

                if ($row_notas) {
                    $grade1_raw = floatval($row_notas['nota1']);
                    $grade2_raw = floatval($row_notas['nota2']);
                    
                    // Determinar si las notas están en escala 10
                    // Si cualquiera de las dos notas es > 5.0, tratamos ambas como escala 10
                    $enEscala10 = ($grade1_raw > 5.0 || $grade2_raw > 5.0);
                    
                    // Convertir ambas notas consistentemente
                    if ($enEscala10) {
                        // Ambas notas se convierten de escala 10 a escala 5.0
                        $grade1_normalized = ($grade1_raw / 10.0) * 5.0;
                        $grade2_normalized = ($grade2_raw / 10.0) * 5.0;
                    } else {
                        // Ambas notas ya están en escala 5.0
                        $grade1_normalized = $grade1_raw;
                        $grade2_normalized = $grade2_raw;
                    }
                    
                    // Calcular nota final - CORREGIDO: Aplicar ponderación 30%-70%
                    $notaFinal = 0;
                    if ($grade1_normalized >= 0 && $grade2_normalized >= 0) {
                        // Aplicar ponderación: 30% primera nota + 70% segunda nota
                        $notaFinal = ($grade1_normalized * 0.30) + ($grade2_normalized * 0.70);
                    } else if ($grade1_normalized >= 0 && $grade2_normalized < 0) {
                        // Solo primera nota disponible - sin ponderación
                        $notaFinal = $grade1_normalized;
                    } else if ($grade2_normalized >= 0 && $grade1_normalized < 0) {
                        // Solo segunda nota disponible - sin ponderación
                        $notaFinal = $grade2_normalized;
                    } else {
                        // Ambas notas son negativas o no válidas
                        $notaFinal = 0;
                    }
                    
                    return [
                        'final' => round($notaFinal, 2),
                        'grade1' => round($grade1_normalized, 2),
                        'grade2' => round($grade2_normalized, 2)
                    ];
                }
            } else {
                error_log("Error ejecutando consulta de notas_estudiantes: " . $stmt_notas->error);
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
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
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
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }
        
        $data = json_decode($response, true);
        
        if ($data === null) {
            curl_close($ch);
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
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
                        $grademax = isset($item['grademax']) ? $item['grademax'] : 5.0;
                        
                        if ($notaRaw !== null && $grademax > 0) {
                            // Convertir la nota a escala 5.0 estándar
                            $notaNormalizada = ($notaRaw / $grademax) * 5.0;
                            $notas[] = $notaNormalizada;
                        }
                    }
                    if (count($notas) == 2) break; // Solo las dos primeras notas
                }
            }
            
            // Asignar valores de notas
            $grade1 = isset($notas[0]) ? $notas[0] : 0;
            $grade2 = isset($notas[1]) ? $notas[1] : 0;
            
            // Calcular nota final - CORREGIDO: Aplicar ponderación 30%-70%
            $notaFinal = 0;
            if ($grade1 >= 0 && $grade2 >= 0) {
                // Aplicar ponderación: 30% primera nota + 70% segunda nota
                $notaFinal = ($grade1 * 0.30) + ($grade2 * 0.70);
            } else if ($grade1 >= 0 && $grade2 < 0) {
                // Solo primera nota disponible - sin ponderación
                $notaFinal = $grade1;
            } else if ($grade2 >= 0 && $grade1 < 0) {
                // Solo segunda nota disponible - sin ponderación
                $notaFinal = $grade2;
            } else {
                // Ambas notas son negativas o no válidas
                $notaFinal = 0;
            }
            
            return [
                'final' => round($notaFinal, 2),
                'grade1' => round($grade1, 2),
                'grade2' => round($grade2, 2)
            ];
        }
        
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        
    } catch (Exception $e) {
        error_log("Excepción en obtenerNotas para estudiante $studentId: " . $e->getMessage());
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
}

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

// Simplificar: solo bootcamp
$courseIdColumn = 'id_bootcamp'; // Fijo como bootcamp
$courseType = 'bootcamp'; // Fijo como bootcamp
$horasRequeridas = 159; // 120 Técnico + 24 English Code + 15 Habilidades

try {
    // Crear nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    
    // PRIMERA HOJA - ESTUDIANTES APROBADOS
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Estudiantes Aprobados');

    // Establecer encabezados
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Número de Identificación');
    $sheet->setCellValue('C1', 'Nombre Completo');
    $sheet->setCellValue('D1', 'Correo Institucional');
    $sheet->setCellValue('E1', 'Programa');
    $sheet->setCellValue('F1', 'Modalidad');
    $sheet->setCellValue('G1', 'Sede');
    $sheet->setCellValue('H1', 'Horas Asistidas');
    $sheet->setCellValue('I1', 'Porcentaje Asistencia');
    $sheet->setCellValue('J1', 'Nota 1');
    $sheet->setCellValue('K1', 'Nota 2');
    $sheet->setCellValue('L1', 'Nota Final');
    $sheet->setCellValue('M1', 'Estado');
    $sheet->setCellValue('N1', 'Fecha Exportación');

    // Estilo del encabezado
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '30336b']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];

    $sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

    // Consultar TODOS los estudiantes del curso bootcamp
    $sql = "SELECT g.*, c.real_hours, c.name as course_name, UPPER(g.full_name) as full_name_upper
            FROM groups g
            LEFT JOIN courses c ON g.id_bootcamp = c.code
            WHERE g.id_bootcamp = ?
            ORDER BY g.full_name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $bootcamp);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error en la ejecución: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn));
    }

    $row = 2;
    $contador = 1;
    $estudiantesExportados = 0;
    $courseInfo = null;
    $allStudents = []; // Para almacenar todos los estudiantes

    while ($data = mysqli_fetch_assoc($result)) {
        // Guardar todos los estudiantes para la segunda hoja
        $allStudents[] = $data;
        
        // Obtener información del curso del primer estudiante
        if ($courseInfo === null && !empty($data['mode']) && !empty($data['headquarters'])) {
            $courseInfo = [
                'course_name' => $data['course_name'] ?: 'Técnico',
                'mode' => $data['mode'],
                'headquarters' => $data['headquarters']
            ];
        }

        // Calcular horas totales
        $horasAsistidas = min(calcularHorasTotalesEstudiante($conn, $data['number_id']), $horasRequeridas);
        $porcentajeAsistencia = min(($horasAsistidas / $horasRequeridas) * 100, 100);
        
        // Obtener las tres notas
        $notasData = obtenerNotas($conn, $data['number_id'], $bootcamp);
        $notaFinal = $notasData['final'];
        $nota1 = $notasData['grade1'];
        $nota2 = $notasData['grade2'];
        
        // Verificar criterios
        $cumpleAsistencia = $porcentajeAsistencia >= 75;
        $cumpleNota = $notaFinal >= 3.0;
        $cumpleCriterios = $cumpleAsistencia && $cumpleNota;
        
        // Verificar si está aprobado
        $yaAprobado = estaAprobado($conn, $data['number_id'], $bootcamp);
        
        // Solo exportar estudiantes que cumplan criterios en la primera hoja
        if ($cumpleCriterios) {
            $nombrePrograma = obtenerNombrePrograma($conn, $courseType, $bootcamp);
            $estado = $yaAprobado ? 'Aprobado' : 'Apto';
            
            $sheet->setCellValue('A' . $row, $contador);
            $sheet->setCellValue('B' . $row, $data['number_id']);
            $sheet->setCellValue('C' . $row, $data['full_name_upper']);
            $sheet->setCellValue('D' . $row, $data['institutional_email']);
            $sheet->setCellValue('E' . $row, $nombrePrograma);
            $sheet->setCellValue('F' . $row, $data['mode']);
            $sheet->setCellValue('G' . $row, $data['headquarters']);
            $sheet->setCellValue('H' . $row, $horasAsistidas . '/' . $horasRequeridas);
            $sheet->setCellValue('I' . $row, number_format($porcentajeAsistencia, 1) . '%');
            $sheet->setCellValue('J' . $row, number_format($nota1, 1));
            $sheet->setCellValue('K' . $row, number_format($nota2, 1));
            $sheet->setCellValue('L' . $row, number_format($notaFinal, 1));
            $sheet->setCellValue('M' . $row, $estado);
            $sheet->setCellValue('N' . $row, date('Y-m-d H:i:s'));
            
            // Aplicar color según el estado
            if ($yaAprobado) {
                $sheet->getStyle('M' . $row)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFFFD700'); // Dorado
            } else {
                $sheet->getStyle('M' . $row)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF66CC00'); // Verde
            }
            
            $row++;
            $contador++;
            $estudiantesExportados++;
        }
    }

    if ($estudiantesExportados === 0) {
        $sheet->setCellValue('A2', 'No hay estudiantes que cumplan los criterios (75% asistencia de 159 horas totales y nota ≥ 3.0)'); // CORREGIDO: Cambié de 70% a 75%
        $sheet->mergeCells('A2:N2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Auto-ajustar columnas
    foreach(range('A','N') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Aplicar bordes a toda la tabla
    $totalRows = $row - 1;
    if ($totalRows >= 1) {
        $sheet->getStyle('A1:N' . $totalRows)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // SEGUNDA HOJA - GRUPO COMPLETO
    $spreadsheet->createSheet();
    $spreadsheet->setActiveSheetIndex(1);
    $completeSheet = $spreadsheet->getActiveSheet();
    $completeSheet->setTitle('Grupo Completo');

    // Establecer los mismos encabezados en la segunda hoja
    $completeSheet->setCellValue('A1', 'ID');
    $completeSheet->setCellValue('B1', 'Número de Identificación');
    $completeSheet->setCellValue('C1', 'Nombre Completo');
    $completeSheet->setCellValue('D1', 'Correo Institucional');
    $completeSheet->setCellValue('E1', 'Programa');
    $completeSheet->setCellValue('F1', 'Modalidad');
    $completeSheet->setCellValue('G1', 'Sede');
    $completeSheet->setCellValue('H1', 'Horas Asistidas');
    $completeSheet->setCellValue('I1', 'Porcentaje Asistencia');
    $completeSheet->setCellValue('J1', 'Nota 1');
    $completeSheet->setCellValue('K1', 'Nota 2');
    $completeSheet->setCellValue('L1', 'Nota Final');
    $completeSheet->setCellValue('M1', 'Estado');
    $completeSheet->setCellValue('N1', 'Fecha Exportación');

    // Aplicar estilo de encabezado
    $completeSheet->getStyle('A1:N1')->applyFromArray($headerStyle);

    // Llenar con TODOS los estudiantes
    $row = 2;
    $contador = 1;
    
    foreach ($allStudents as $data) {
        // Calcular horas totales
        $horasAsistidas = min(calcularHorasTotalesEstudiante($conn, $data['number_id']), $horasRequeridas);
        $porcentajeAsistencia = min(($horasAsistidas / $horasRequeridas) * 100, 100);
        
        // Obtener las tres notas
        $notasData = obtenerNotas($conn, $data['number_id'], $bootcamp);
        $notaFinal = $notasData['final'];
        $nota1 = $notasData['grade1'];
        $nota2 = $notasData['grade2'];
        
        // Verificar criterios para determinar estado
        $cumpleAsistencia = $porcentajeAsistencia >= 75; // CORREGIDO: Cambié de 70 a 75
        $cumpleNota = $notaFinal >= 3.0;
        $cumpleCriterios = $cumpleAsistencia && $cumpleNota;
        $yaAprobado = estaAprobado($conn, $data['number_id'], $bootcamp);
        
        // Determinar estado
        if ($yaAprobado) {
            $estado = 'Aprobado';
            $colorEstado = 'FFFFD700'; // Dorado
        } elseif ($cumpleCriterios) {
            $estado = 'Apto';
            $colorEstado = 'FF66CC00'; // Verde
        } else {
            $estado = 'No Apto';
            $colorEstado = 'FFFF0000'; // Rojo
        }
        
        $nombrePrograma = obtenerNombrePrograma($conn, $courseType, $bootcamp);
        
        $completeSheet->setCellValue('A' . $row, $contador);
        $completeSheet->setCellValue('B' . $row, $data['number_id']);
        $completeSheet->setCellValue('C' . $row, $data['full_name_upper']);
        $completeSheet->setCellValue('D' . $row, $data['institutional_email']);
        $completeSheet->setCellValue('E' . $row, $nombrePrograma);
        $completeSheet->setCellValue('F' . $row, $data['mode']);
        $completeSheet->setCellValue('G' . $row, $data['headquarters']);
        $completeSheet->setCellValue('H' . $row, $horasAsistidas . '/' . $horasRequeridas);
        $completeSheet->setCellValue('I' . $row, number_format($porcentajeAsistencia, 1) . '%');
        $completeSheet->setCellValue('J' . $row, number_format($nota1, 1));
        $completeSheet->setCellValue('K' . $row, number_format($nota2, 1));
        $completeSheet->setCellValue('L' . $row, number_format($notaFinal, 1));
        $completeSheet->setCellValue('M' . $row, $estado);
        $completeSheet->setCellValue('N' . $row, date('Y-m-d H:i:s'));
        
        // Aplicar color según el estado
        $completeSheet->getStyle('M' . $row)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB($colorEstado);
        
        $row++;
        $contador++;
    }

    // Auto-ajustar columnas en la segunda hoja
    foreach(range('A','N') as $columnID) {
        $completeSheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Aplicar bordes a toda la tabla en la segunda hoja
    $totalRows = count($allStudents) + 1;
    if ($totalRows > 1) {
        $completeSheet->getStyle('A1:N' . $totalRows)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // Activar la primera hoja antes de enviar
    $spreadsheet->setActiveSheetIndex(0);

    // Generar nombre del archivo usando la información del curso
    $fechaHora = date('Y-m-d_H-i-s');
    
    if ($courseInfo) {
        $modeClean = str_replace(' ', '_', $courseInfo['mode']);
        $sedeClean = str_replace(' ', '_', $courseInfo['headquarters']);
        $filename = "estudiantes_aprobados_Tecnico_{$modeClean}_{$sedeClean}_{$fechaHora}.xlsx";
    } else {
        $filename = "estudiantes_aprobados_Tecnico_{$fechaHora}.xlsx";
    }

    // Limpiar buffer de salida
    ob_end_clean();

    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');
    header('Expires: 0');

    // Crear y enviar archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    ob_end_clean();
    
    // Configurar headers para respuesta de error JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al generar el archivo: ' . $e->getMessage()
    ]);
}

exit;
?>