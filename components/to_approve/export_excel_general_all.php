<?php
// Control de errores y configuración de tiempo de ejecución
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/export_errors.log');

// Configurar tiempo de ejecución a 5 minutos
set_time_limit(300); // 5 minutos
ini_set('max_execution_time', 300);
ini_set('memory_limit', '512M'); // Aumentar memoria disponible

// Configurar timeout para MySQL
ini_set('mysql.connect_timeout', 300);
ini_set('default_socket_timeout', 300);

try {
    require __DIR__ . '/../../vendor/autoload.php';
    require __DIR__ . '/../../controller/conexion.php';
    
    // Verificar conexión a la base de datos
    if (!$conn) {
        throw new Exception('No se pudo conectar a la base de datos');
    }
    
    // Configurar timeout de MySQL
    mysqli_query($conn, "SET SESSION wait_timeout = 300");
    mysqli_query($conn, "SET SESSION interactive_timeout = 300");
    
} catch (Exception $e) {
    ob_end_clean();
    error_log("Error al cargar dependencias: " . $e->getMessage());
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al inicializar la aplicación: ' . $e->getMessage()
    ]);
    exit;
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Función para calcular horas basadas en asistencia con manejo de errores mejorado
function calcularHorasAsistencia($conn, $studentId, $courseId) {
    if (empty($courseId)) return 0;
    
    try {
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
            error_log("Error preparando consulta de horas: " . $conn->error);
            return 0;
        }
        
        $stmt->bind_param("si", $studentId, $courseId);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de horas para estudiante $studentId, curso $courseId: " . $stmt->error);
            $stmt->close();
            return 0;
        }
        
        $result = $stmt->get_result();
        
        $fechasContadas = [];
        $totalHoras = 0;
        
        while($asistencia = $result->fetch_assoc()) {
            $fecha = $asistencia['class_date'];
            
            if (!in_array($fecha, $fechasContadas)) {
                $totalHoras += floatval($asistencia['horas']);
                $fechasContadas[] = $fecha;
            }
        }
        
        $stmt->close();
        return $totalHoras;
        
    } catch (Exception $e) {
        error_log("Excepción en calcularHorasAsistencia: " . $e->getMessage());
        return 0;
    }
}

// Función para calcular horas totales con manejo de errores mejorado
function calcularHorasTotalesEstudiante($conn, $studentId) {
    try {
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
            error_log("Error preparando consulta de horas totales: " . $conn->error);
            return 0;
        }
        
        $stmt->bind_param("s", $studentId);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de horas totales para estudiante $studentId: " . $stmt->error);
            $stmt->close();
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
        
        // Calcular horas de Técnico con límite individual
        if (!empty($row['id_bootcamp'])) {
            $horasActualesTecnico = calcularHorasAsistencia($conn, $studentId, $row['id_bootcamp']);
            if ($row['is_certified']) {
                $totalHoras += min($horasTecnico, $horasActualesTecnico + 40);
            } else {
                $totalHoras += min($horasActualesTecnico, $horasTecnico);
            }
        }
        
        // Calcular horas de English Code con límite individual
        if (!empty($row['id_english_code'])) {
            $horasActualesIngles = calcularHorasAsistencia($conn, $studentId, $row['id_english_code']);
            $totalHoras += min($horasActualesIngles, $horasIngles);
        }
        
        // Calcular horas de Habilidades con límite individual
        if (!empty($row['id_skills'])) {
            if ($row['is_certified']) {
                $totalHoras += 15;
            } else {
                $horasActualesHabilidades = calcularHorasAsistencia($conn, $studentId, $row['id_skills']);
                $totalHoras += min($horasActualesHabilidades, $horasHabilidades);
            }
        }
        
        return $totalHoras;
        
    } catch (Exception $e) {
        error_log("Excepción en calcularHorasTotalesEstudiante: " . $e->getMessage());
        return 0;
    }
}

// Función para obtener notas del curso técnico con manejo de errores mejorado
function obtenerNotasTecnico($conn, $studentId, $courseCode) {
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
                    
                    // Calcular nota final - CORREGIDO: Aplicar ponderación siempre
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
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }

        $stmt_notas->bind_param("si", $studentId, $courseCode);
        if (!$stmt_notas->execute()) {
            error_log("Error ejecutando consulta de notas_estudiantes: " . $stmt_notas->error);
            $stmt_notas->close();
            return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        }

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
            
            // Calcular nota final - CORREGIDO: Aplicar ponderación siempre
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

        // 3. Si no se encuentra en ninguna tabla, devolver cero
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
        
    } catch (Exception $e) {
        error_log("Excepción en obtenerNotasTecnico para estudiante $studentId: " . $e->getMessage());
        return ['final' => 0, 'grade1' => 0, 'grade2' => 0];
    }
}

function estaAprobado($conn, $studentId, $courseCode) {
    try {
        $sql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta de aprobación: " . $conn->error);
            return false;
        }
        
        $stmt->bind_param("ss", $studentId, $courseCode);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de aprobación: " . $stmt->error);
            $stmt->close();
            return false;
        }
        
        $result = $stmt->get_result();
        $approved = $result->num_rows > 0;
        $stmt->close();
        
        return $approved;
        
    } catch (Exception $e) {
        error_log("Excepción en estaAprobado: " . $e->getMessage());
        return false;
    }
}

function obtenerNombrePrograma($conn, $courseId) {
    if (empty($courseId)) return 'No asignado';
    
    try {
        $sql = "SELECT name FROM courses WHERE code = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta de nombre programa: " . $conn->error);
            return 'Error al consultar';
        }
        
        $stmt->bind_param("s", $courseId);
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta de nombre programa: " . $stmt->error);
            $stmt->close();
            return 'Error al ejecutar';
        }
        
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row ? $row['name'] : 'Programa no encontrado';
        
    } catch (Exception $e) {
        error_log("Excepción en obtenerNombrePrograma: " . $e->getMessage());
        return 'Error al obtener nombre';
    }
}

$horasRequeridas = 159; // 120 Técnico + 24 English Code + 15 Habilidades

try {
    // Crear nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Informe Notas Técnico');

    // Establecer encabezados
    $headers = [
        'A1' => 'ID',
        'B1' => 'Número de Identificación',
        'C1' => 'Nombre Completo',
        'D1' => 'Correo Personal',
        'E1' => 'Correo Institucional',
        'F1' => 'Modalidad',
        'G1' => 'Sede',
        'H1' => 'Institución',
        'I1' => 'Horas Totales',
        'J1' => '% Asistencia',
        'K1' => 'Programa Técnico',
        'L1' => 'Nota 1',
        'M1' => 'Nota 2',
        'N1' => 'Nota Final',
        'O1' => 'Estado',
        'P1' => 'Fecha Exportación'
    ];

    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

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

    $sheet->getStyle('A1:P1')->applyFromArray($headerStyle);

    // Consultar estudiantes con paginación para evitar memoria excesiva - CORREGIDO: Agregar JOIN con user_register
    $sql = "SELECT DISTINCT g.number_id, g.full_name, g.institutional_email, g.mode, g.headquarters,
                   g.id_bootcamp,
                   u.email AS personal_email, u.institution
            FROM groups g
            LEFT JOIN user_register u ON g.number_id = u.number_id
            WHERE g.number_id IS NOT NULL AND g.number_id != ''
            AND g.id_bootcamp IS NOT NULL AND g.id_bootcamp != ''
            ORDER BY g.full_name ASC";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('Error al obtener estudiantes: ' . mysqli_error($conn));
    }

    $row = 2;
    $contador = 1;
    $procesados = 0;
    $errores = 0;

    error_log("Iniciando procesamiento de estudiantes...");

    while ($data = mysqli_fetch_assoc($result)) {
        try {
            // Calcular horas totales
            $horasAsistidas = calcularHorasTotalesEstudiante($conn, $data['number_id']);
            $porcentajeAsistencia = min(($horasAsistidas / $horasRequeridas) * 100, 100);
            
            // Obtener notas solo del técnico
            $notasTecnico = obtenerNotasTecnico($conn, $data['number_id'], $data['id_bootcamp']);
            
            // Verificar aprobación
            $aprobadoTecnico = estaAprobado($conn, $data['number_id'], $data['id_bootcamp']);
            
            // Determinar estado - CORREGIDO: Validar asistencia mínima del 75%
            $estadoTecnico = $aprobadoTecnico ? 'Aprobado' : 
                            (($notasTecnico['final'] >= 3.0 && $porcentajeAsistencia >= 75) ? 'Apto' : 'No Apto');
            
            // Aplicar lógica de institución
            $institution = !empty($data['institution']) ? $data['institution'] : 'No especificado';
            
            // Llenar fila
            $sheet->setCellValue('A' . $row, $contador);
            $sheet->setCellValue('B' . $row, $data['number_id']);
            $sheet->setCellValue('C' . $row, strtoupper($data['full_name']));
            $sheet->setCellValue('D' . $row, $data['personal_email'] ?? '');
            $sheet->setCellValue('E' . $row, $data['institutional_email']);
            $sheet->setCellValue('F' . $row, $data['mode']);
            $sheet->setCellValue('G' . $row, $data['headquarters']);
            $sheet->setCellValue('H' . $row, $institution);
            $sheet->setCellValue('I' . $row, $horasAsistidas . '/' . $horasRequeridas);
            $sheet->setCellValue('J' . $row, number_format($porcentajeAsistencia, 1) . '%');
            
            // Datos del técnico
            $sheet->setCellValue('K' . $row, obtenerNombrePrograma($conn, $data['id_bootcamp']));
            $sheet->setCellValue('L' . $row, number_format($notasTecnico['grade1'], 1));
            $sheet->setCellValue('M' . $row, number_format($notasTecnico['grade2'], 1));
            $sheet->setCellValue('N' . $row, number_format($notasTecnico['final'], 1));
            $sheet->setCellValue('O' . $row, $estadoTecnico);
            $sheet->setCellValue('P' . $row, date('Y-m-d H:i:s'));
            
            // Aplicar colores
            $colorAprobado = 'FFFFD700'; // Dorado
            $colorApto = 'FF66CC00'; // Verde
            $colorNoApto = 'FFFF0000'; // Rojo
            
            $color = $estadoTecnico === 'Aprobado' ? $colorAprobado : 
                    ($estadoTecnico === 'Apto' ? $colorApto : $colorNoApto);
            $sheet->getStyle('O' . $row)->getFill()
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setARGB($color);
            
            $row++;
            $contador++;
            $procesados++;
            
            // Log de progreso cada 50 estudiantes
            if ($procesados % 50 == 0) {
                error_log("Procesados $procesados estudiantes...");
            }
            
        } catch (Exception $e) {
            $errores++;
            error_log("Error procesando estudiante {$data['number_id']}: " . $e->getMessage());
            
            // Continuar con el siguiente estudiante
            continue;
        }
    }

    error_log("Procesamiento completado. Total: $procesados, Errores: $errores");

    if ($contador === 1) {
        $sheet->setCellValue('A2', 'No hay estudiantes matriculados en cursos técnicos');
        $sheet->mergeCells('A2:P2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Auto-ajustar columnas
    foreach(range('A','P') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Aplicar bordes
    $totalRows = $row - 1;
    if ($totalRows >= 1) {
        $sheet->getStyle('A1:P' . $totalRows)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
    }

    // Generar archivo
    $fechaHora = date('Y-m-d_H-i-s');
    $filename = "informe_notas_tecnico_todos_matriculados_{$fechaHora}.xlsx";

    // Limpiar buffer
    ob_end_clean();

    // Headers para descarga
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
    
    error_log("Error crítico en export_excel_general_all.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    // Respuesta de error
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al generar el archivo: ' . $e->getMessage(),
        'details' => 'Revise los logs del servidor para más información'
    ]);
}

exit;
?>