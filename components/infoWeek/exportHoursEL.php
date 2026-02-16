<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../../logs/export_errors.log');

// Configurar tiempo de ejecución a 10 minutos (puedes reducir si la optimización es suficiente)
set_time_limit(600); // 10 minutos
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M'); // Aumentar memoria disponible

// Configurar timeout para MySQL
ini_set('mysql.connect_timeout', 600);
ini_set('default_socket_timeout', 600);

// Corregir ruta del autoload
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

// Verificar conexión a la base de datos
if (!$conn) {
    ob_end_clean();
    error_log("No se pudo conectar a la base de datos");
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'No se pudo conectar a la base de datos'
    ]);
    exit;
}

// Configurar timeout de MySQL
mysqli_query($conn, "SET SESSION wait_timeout = 600");
mysqli_query($conn, "SET SESSION interactive_timeout = 600");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Función para configurar encabezados de una hoja
function configurarEncabezados($sheet) {
    $sheet->setCellValue('A1', 'Número de Identificación');
    $sheet->setCellValue('B1', 'Nombre del Estudiante');
    $sheet->setCellValue('C1', 'Correo Personal');
    $sheet->setCellValue('D1', 'Correo Institucional');
    $sheet->setCellValue('E1', 'Teléfono Principal');
    $sheet->setCellValue('F1', 'Teléfono Secundario');
    $sheet->setCellValue('G1', 'Programa');
    $sheet->setCellValue('H1', 'Estado Programa');
    $sheet->setCellValue('I1', 'Fecha Inicio');
    $sheet->setCellValue('J1', 'Fecha Fin');
    $sheet->setCellValue('K1', 'Código Curso');
    $sheet->setCellValue('L1', 'ID Bootcamp');
    $sheet->setCellValue('M1', 'Cohorte');
    $sheet->setCellValue('N1', 'Modalidad');
    $sheet->setCellValue('O1', 'Departamento');
    $sheet->setCellValue('P1', 'Sede');
    $sheet->setCellValue('Q1', 'Institución');

    // Títulos específicos por área
    $sheet->setCellValue('R1', 'Técnico - Horas Actuales');
    $sheet->setCellValue('S1', 'Técnico - Horas Reales');
    $sheet->setCellValue('T1', 'Técnico - Total Horas');

    $sheet->setCellValue('U1', 'Inglés Nivelador - Horas Actuales');
    $sheet->setCellValue('V1', 'Inglés Nivelador - Horas Reales');
    $sheet->setCellValue('W1', 'Inglés Nivelador - Total Horas');

    $sheet->setCellValue('X1', 'Inglés - Horas Actuales');
    $sheet->setCellValue('Y1', 'Inglés - Horas Reales');
    $sheet->setCellValue('Z1', 'Inglés - Total Horas');

    $sheet->setCellValue('AA1', 'Habilidades - Horas Actuales');
    $sheet->setCellValue('AB1', 'Habilidades - Horas Reales');
    $sheet->setCellValue('AC1', 'Habilidades - Total Horas');

    $sheet->setCellValue('AD1', 'Total - Horas Actuales');
    $sheet->setCellValue('AE1', 'Total - Horas Reales');
    $sheet->setCellValue('AF1', 'Total - Horas Programa');

    $sheet->setCellValue('AG1', 'Porcentaje Actuales');
    $sheet->setCellValue('AH1', 'Porcentaje Reales');
    $sheet->setCellValue('AI1', 'Porcentaje Faltante');

    // Nuevas columnas de export_excel_general_all.php
    $sheet->setCellValue('AJ1', '% Asistencia');
    $sheet->setCellValue('AK1', 'Nota 1');
    $sheet->setCellValue('AL1', 'Nota 2');
    $sheet->setCellValue('AM1', 'Nota Final');
    $sheet->setCellValue('AN1', 'Estado');
    $sheet->setCellValue('AO1', 'ESTADO ADMISION');
    $sheet->setCellValue('AP1', 'Año de finalización');
}

// Función para aplicar estilos a una hoja
function aplicarEstilos($sheet, $lastRow) {
    // Estilos por área
    $basicHeaderStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    // Estilos para datos (sin negrita)
    $basicDataStyle = [
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    $tecnicoHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6E6']], // Rojo claro
    ]);

    $tecnicoDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2F2']], // Rojo más claro para datos
    ]);

    $inglesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6FFE6']], // Verde claro
    ]);

    $inglesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2FFF2']], // Verde más claro para datos
    ]);

    $habilidadesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0E6']], // Naranja claro
    ]);

    $habilidadesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8F2']], // Naranja más claro para datos
    ]);

    $totalesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0E6FF']], // Púrpura claro
    ]);

    $totalesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F2FF']], // Púrpura más claro para datos
    ]);

    $porcentajesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFCC']], // Amarillo claro
    ]);

    $porcentajesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFF2']], // Amarillo más claro para datos
    ]);

    // Estilo para nuevas columnas (notas y estados)
    $notasEstadosHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F3FF']], // Azul claro
    ]);

    $notasEstadosDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F9FF']], // Azul más claro para datos
    ]);

    // Aplicar estilos específicos por área - Headers
    $sheet->getStyle('A1:Q1')->applyFromArray($basicHeaderStyle); // Información básica
    $sheet->getStyle('R1:T1')->applyFromArray($tecnicoHeaderStyle);     // Técnico
    $sheet->getStyle('U1:W1')->applyFromArray($inglesHeaderStyle);      // Inglés Nivelador
    $sheet->getStyle('X1:Z1')->applyFromArray($inglesHeaderStyle);      // Inglés
    $sheet->getStyle('AA1:AC1')->applyFromArray($habilidadesHeaderStyle); // Habilidades
    $sheet->getStyle('AD1:AF1')->applyFromArray($totalesHeaderStyle);     // Totales
    $sheet->getStyle('AG1:AI1')->applyFromArray($porcentajesHeaderStyle); // Porcentajes
    $sheet->getStyle('AJ1:AP1')->applyFromArray($notasEstadosHeaderStyle); // Nuevas columnas

    // Aplicar estilos específicos por área - Datos
    if ($lastRow >= 2) {
        $sheet->getStyle('A2:Q' . $lastRow)->applyFromArray($basicDataStyle); // Información básica
        $sheet->getStyle('R2:T' . $lastRow)->applyFromArray($tecnicoDataStyle);     // Técnico
        $sheet->getStyle('U2:W' . $lastRow)->applyFromArray($inglesDataStyle);      // Inglés Nivelador
        $sheet->getStyle('X2:Z' . $lastRow)->applyFromArray($inglesDataStyle);      // Inglés
        $sheet->getStyle('AA2:AC' . $lastRow)->applyFromArray($habilidadesDataStyle); // Habilidades
        $sheet->getStyle('AD2:AF' . $lastRow)->applyFromArray($totalesDataStyle);     // Totales
        $sheet->getStyle('AG2:AI' . $lastRow)->applyFromArray($porcentajesDataStyle); // Porcentajes
        $sheet->getStyle('AJ2:AP' . $lastRow)->applyFromArray($notasEstadosDataStyle); // Nuevas columnas
    }

    // Asegurar que los porcentajes tengan el formato correcto
    if ($lastRow >= 2) {
        $sheet->getStyle('AG2:AI' . $lastRow)->getNumberFormat()
              ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
        $sheet->getStyle('AJ2:AJ' . $lastRow)->getNumberFormat()
              ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    }

    // Autoajustar columnas
    foreach(range('A','AP') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Función para llenar datos de una hoja con un lote específico (optimizada)
function llenarDatosLote($conn, $sheet, $lote) {
    // Consulta principal para estudiantes del lote
    $sql = "SELECT g.*, g.department, g.headquarters, cp.start_date, cp.end_date, cp.period_name, cp.cohort, cp.status,
           b.real_hours AS bootcamp_hours, b.code AS bootcamp_code,
           e.real_hours AS english_hours, e.code AS english_code,
           l.real_hours AS leveling_hours, l.code AS leveling_code,
           s.real_hours AS skills_hours, s.code AS skills_code,
           u.email AS personal_email, u.first_phone, u.second_phone, u.institution,
           CASE WHEN cs.number_id IS NOT NULL THEN 1 ELSE 0 END AS is_certified
    FROM groups g
    LEFT JOIN courses b ON g.id_bootcamp = b.code 
    LEFT JOIN courses e ON g.id_english_code = e.code
    LEFT JOIN courses l ON g.id_leveling_english = l.code 
    LEFT JOIN courses s ON g.id_skills = s.code
    LEFT JOIN user_register u ON g.number_id = u.number_id
    LEFT JOIN certificados_senatics cs ON g.number_id = cs.number_id
    LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code
    WHERE u.lote = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lote);
    $stmt->execute();
    $result = $stmt->get_result();

    // Pre-cargar datos para optimización
    $attendanceData = [];
    $gradesData = [];
    $admissionStatusData = [];
    $finalizationYearData = [];

    // 1. Pre-cargar horas de asistencia para todos los estudiantes del lote
    $attendanceSql = "SELECT ar.student_id, ar.course_id, 
                             SUM(CASE 
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
                             END) as total_horas
                      FROM attendance_records ar
                      JOIN courses c ON ar.course_id = c.code
                      JOIN user_register u ON ar.student_id = u.number_id
                      WHERE u.lote = ?
                      GROUP BY ar.student_id, ar.course_id";
    $attendanceStmt = $conn->prepare($attendanceSql);
    $attendanceStmt->bind_param("i", $lote);
    $attendanceStmt->execute();
    $attendanceResult = $attendanceStmt->get_result();
    while ($row = $attendanceResult->fetch_assoc()) {
        $attendanceData[$row['student_id']][$row['course_id']] = $row['total_horas'];
    }
    $attendanceStmt->close();

    // 2. Pre-cargar notas para todos los estudiantes del lote
    $gradesSql = "SELECT student_number_id, course_code, final_grade, grade_1, grade_2 FROM course_approvals 
                  WHERE student_number_id IN (SELECT number_id FROM user_register WHERE lote = ?)
                  UNION
                  SELECT number_id, code, NULL, nota1, nota2 FROM notas_estudiantes 
                  WHERE number_id IN (SELECT number_id FROM user_register WHERE lote = ?)";
    $gradesStmt = $conn->prepare($gradesSql);
    $gradesStmt->bind_param("ii", $lote, $lote);
    $gradesStmt->execute();
    $gradesResult = $gradesStmt->get_result();
    while ($row = $gradesResult->fetch_assoc()) {
        $gradesData[$row['student_number_id']][$row['course_code']] = $row;
    }
    $gradesStmt->close();

    // 3. Pre-cargar estados de admisión
    $admissionSql = "SELECT number_id, statusAdmin FROM user_register WHERE lote = ?";
    $admissionStmt = $conn->prepare($admissionSql);
    $admissionStmt->bind_param("i", $lote);
    $admissionStmt->execute();
    $admissionResult = $admissionStmt->get_result();
    while ($row = $admissionResult->fetch_assoc()) {
        $admissionStatusData[$row['number_id']] = $row['statusAdmin'];
    }
    $admissionStmt->close();

    // 4. Pre-cargar años de finalización
    $finalizationSql = "SELECT bootcamp_code, YEAR(end_date) as year FROM course_periods";
    $finalizationResult = $conn->query($finalizationSql);
    while ($row = $finalizationResult->fetch_assoc()) {
        $finalizationYearData[$row['bootcamp_code']] = $row['year'];
    }

    $row = 2; // Comenzar datos en fila 2
    $lastRow = $row; // Para mantener un registro de la última fila

    while ($data = $result->fetch_assoc()) {
        $studentId = $data['number_id'];
        $bootcampCode = $data['bootcamp_code'];
        $englishCode = $data['english_code'];
        $levelingCode = $data['leveling_code'];
        $skillsCode = $data['skills_code'];

        // Datos básicos
        $sheet->setCellValue('A' . $row, $studentId);
        $sheet->setCellValue('B' . $row, $data['full_name'] ?? '');
        $sheet->setCellValue('C' . $row, $data['personal_email'] ?? '');
        $sheet->setCellValue('D' . $row, $data['institutional_email'] ?? '');
        $sheet->setCellValue('E' . $row, str_replace('+57', '', $data['first_phone'] ?? ''));
        $sheet->setCellValue('F' . $row, str_replace('+57', '', $data['second_phone'] ?? ''));
        $sheet->setCellValue('G' . $row, ($data['id_bootcamp'] ?? '') . ' - ' . ($data['bootcamp_name'] ?? ''));
        $sheet->setCellValue('H' . $row, ($data['status'] ?? 0) == 1 ? 'Activo' : 'Inactivo');
        $sheet->setCellValue('I' . $row, !empty($data['start_date']) ? date('d/m/Y', strtotime($data['start_date'])) : '');
        $sheet->setCellValue('J' . $row, !empty($data['end_date']) ? date('d/m/Y', strtotime($data['end_date'])) : '');
        $sheet->setCellValue('K' . $row, $data['period_name'] ?? '');
        $sheet->setCellValue('L' . $row, $data['id_bootcamp'] ?? '');
        $sheet->setCellValue('M' . $row, $data['cohort'] ?? '');
        $sheet->setCellValue('N' . $row, $data['mode'] ?? '');
        $sheet->setCellValue('O' . $row, $data['department'] ?? '');
        $sheet->setCellValue('P' . $row, $data['headquarters'] ?? '');
        $institution = !empty($data['institution']) ? $data['institution'] : 'No especificado';
        $sheet->setCellValue('Q' . $row, $institution);

        // Obtener horas reales
        $horasTecnico = isset($data['bootcamp_hours']) ? intval($data['bootcamp_hours']) : 0;
        $horasNivelador = isset($data['leveling_hours']) ? intval($data['leveling_hours']) : 0;
        $horasIngles = isset($data['english_hours']) ? intval($data['english_hours']) : 0;
        $horasHabilidades = isset($data['skills_hours']) ? intval($data['skills_hours']) : 0;

        // Calcular horas actuales desde array pre-cargado
        $horasActualesTecnico = $attendanceData[$studentId][$bootcampCode] ?? 0;
        $horasActualesNivelador = $attendanceData[$studentId][$levelingCode] ?? 0;
        $horasActualesIngles = $attendanceData[$studentId][$englishCode] ?? 0;
        $horasActualesHabilidades = $attendanceData[$studentId][$skillsCode] ?? 0;

        // Verificar si el estudiante está en certificados_senatics
        if ($data['is_certified']) {
            $horasActualesTecnico += 40;
            $horasActualesHabilidades = 15;
            $horasActualesNivelador = 20;
        }

        // Aplicar límites
        $horasActualesTecnico = min($horasActualesTecnico, 120);
        $horasActualesNivelador = min($horasActualesNivelador, 20);
        $horasActualesIngles = min($horasActualesIngles, 24);
        $horasActualesHabilidades = min($horasActualesHabilidades, 15);

        // Técnico
        $sheet->setCellValue('R' . $row, $horasActualesTecnico);
        $sheet->setCellValue('S' . $row, $horasTecnico);
        $sheet->setCellValue('T' . $row, 120);

        // Inglés Nivelador
        $sheet->setCellValue('U' . $row, $horasActualesNivelador);
        $sheet->setCellValue('V' . $row, $horasNivelador);
        $sheet->setCellValue('W' . $row, 20);

        // Inglés
        $sheet->setCellValue('X' . $row, $horasActualesIngles);
        $sheet->setCellValue('Y' . $row, $horasIngles);
        $sheet->setCellValue('Z' . $row, 24);

        // Habilidades
        $sheet->setCellValue('AA' . $row, $horasActualesHabilidades);
        $sheet->setCellValue('AB' . $row, $horasHabilidades);
        $sheet->setCellValue('AC' . $row, 15);

        // Totales
        $totalActual = intval($horasActualesTecnico) + intval($horasActualesNivelador) + intval($horasActualesIngles) + intval($horasActualesHabilidades);
        $totalReales = intval($horasTecnico) + intval($horasNivelador) + intval($horasIngles) + intval($horasHabilidades);
        $sheet->setCellValue('AD' . $row, $totalActual);
        $sheet->setCellValue('AE' . $row, $totalReales);
        $sheet->setCellValue('AF' . $row, 179);
        
        // Porcentajes
        $sheet->setCellValue('AG' . $row, '=AD' . $row . '/159');
        $sheet->setCellValue('AH' . $row, '=AE' . $row . '/AF' . $row);
        $sheet->setCellValue('AI' . $row, '=1-(AE' . $row . '/AF' . $row . ')');

        // Nuevas columnas - Calcular horas totales asistidas
        $horasAsistidasTecnico = min($horasActualesTecnico, $horasTecnico ?: 120);
        $horasAsistidasNivelador = min($horasActualesNivelador, $horasNivelador ?: 20);
        $horasAsistidasIngles = min($horasActualesIngles, $horasIngles ?: 24);
        $horasAsistidasHabilidades = min($horasActualesHabilidades, $horasHabilidades ?: 15);
        $horasAsistidas = $horasAsistidasTecnico + $horasAsistidasNivelador + $horasAsistidasIngles + $horasAsistidasHabilidades;
        $porcentajeAsistencia = min(($horasAsistidas / 159) * 100, 100);
        $sheet->setCellValue('AJ' . $row, $porcentajeAsistencia / 100);

        // Notas desde array pre-cargado
        $notasTecnico = $gradesData[$studentId][$bootcampCode] ?? ['final_grade' => null, 'grade_1' => 0, 'grade_2' => 0];
        if (is_null($notasTecnico['final_grade'])) { // De notas_estudiantes
            $grade1_raw = floatval($notasTecnico['grade_1']);
            $grade2_raw = floatval($notasTecnico['grade_2']);
            $enEscala10 = ($grade1_raw > 5.0 || $grade2_raw > 5.0);
            $grade1 = $enEscala10 ? ($grade1_raw / 10.0) * 5.0 : $grade1_raw;
            $grade2 = $enEscala10 ? ($grade2_raw / 10.0) * 5.0 : $grade2_raw;
            $final = ($grade1 >= 0 && $grade2 >= 0) ? ($grade1 * 0.30) + ($grade2 * 0.70) : max($grade1, $grade2);
            $notasTecnico = ['final' => round($final, 2), 'grade1' => round($grade1, 2), 'grade2' => round($grade2, 2)];
        } else { // De course_approvals
            $grade1 = floatval($notasTecnico['grade_1']);
            $grade2 = floatval($notasTecnico['grade_2']);
            $final = ($grade1 >= 0 && $grade2 >= 0) ? ($grade1 * 0.30) + ($grade2 * 0.70) : max($grade1, $grade2);
            $notasTecnico = ['final' => round($final, 2), 'grade1' => round($grade1, 2), 'grade2' => round($grade2, 2)];
        }

        $sheet->setCellValue('AK' . $row, number_format($notasTecnico['grade1'], 1));
        $sheet->setCellValue('AL' . $row, number_format($notasTecnico['grade2'], 1));
        $sheet->setCellValue('AM' . $row, number_format($notasTecnico['final'], 1));

        // Estado
        $aprobadoTecnico = !is_null($gradesData[$studentId][$bootcampCode]['final_grade'] ?? null);
        $estadoTecnico = $aprobadoTecnico ? 'Aprobado' : (($notasTecnico['final'] >= 3.0 && $porcentajeAsistencia >= 75) ? 'Apto' : 'No Apto');
        $sheet->setCellValue('AN' . $row, $estadoTecnico);

        // Colores para estado
        $colorAprobado = 'FFFFD700';
        $colorApto = 'FF66CC00';
        $colorNoApto = 'FFFF0000';
        $color = $estadoTecnico === 'Aprobado' ? $colorAprobado : ($estadoTecnico === 'Apto' ? $colorApto : $colorNoApto);
        $sheet->getStyle('AN' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);

        // Estado admisión desde array
        $statusAdmin = $admissionStatusData[$studentId] ?? '0';
        $map = ['1' => 'BENEFICIARIO', '0' => 'SIN ESTADO', '2' => 'RECHAZADO', '3' => 'MATRICULADO', '4' => 'SIN CONTACTO', '5' => 'EN PROCESO', '6' => 'CERTIFICADO', '7' => 'INACTIVO', '8' => 'BENEFICIARIO CONTRAPARTIDA', '9' => 'APLAZADO', '10' => 'FORMADO', '11' => 'NO VALIDO', '12' => 'NO APROBADO'];
        $estadoAdmision = $map[$statusAdmin] ?? 'SIN ESTADO';
        $sheet->setCellValue('AO' . $row, $estadoAdmision);

        // Año de finalización desde array
        $anoFinalizacion = $finalizationYearData[$bootcampCode] ?? 'N/A';
        $sheet->setCellValue('AP' . $row, $anoFinalizacion);

        $lastRow = $row;
        $row++;
    }

    $stmt->close();
    return $lastRow;
}

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();

// HOJA 1: Reporte Horas Lote 1
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Reporte Horas Lote 1');
configurarEncabezados($sheet1);
$lastRow1 = llenarDatosLote($conn, $sheet1, 1);
aplicarEstilos($sheet1, $lastRow1);

// HOJA 2: Reporte Horas Lote 2
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->getSheet(1);
$sheet2->setTitle('Reporte Horas Lote 2');
configurarEncabezados($sheet2);
$lastRow2 = llenarDatosLote($conn, $sheet2, 2);
aplicarEstilos($sheet2, $lastRow2);

// HOJA 3: Conteo Bootcamps
$spreadsheet->createSheet();
$sheet3 = $spreadsheet->getSheet(2);
$sheet3->setTitle('Conteo Bootcamps');

// Establecer títulos
$sheet3->setCellValue('A1', 'Bootcamp');
$sheet3->setCellValue('B1', 'Inscritos');

// Consulta SQL para obtener bootcamps únicos y su conteo
$sqlBootcamps = "SELECT bootcamp_name, COUNT(*) as total 
                 FROM groups g
                 LEFT JOIN user_register u ON g.number_id = u.number_id
                 GROUP BY bootcamp_name 
                 ORDER BY bootcamp_name";
                 
$resultBootcamps = $conn->query($sqlBootcamps);

$row = 2; // Comenzar datos en fila 2
$totalInscritos = 0; // Variable para el total

while($bootcampData = $resultBootcamps->fetch_assoc()) {
    $sheet3->setCellValue('A' . $row, $bootcampData['bootcamp_name']);
    $sheet3->setCellValue('B' . $row, $bootcampData['total']);
    $totalInscritos += $bootcampData['total']; // Sumar al total
    $row++;
}

// Añadir fila de total
$sheet3->setCellValue('A' . $row, 'TOTAL INSCRITOS');
$sheet3->setCellValue('B' . $row, $totalInscritos);

// Estilo para la fila de total
$totalStyle = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E6E6E6'],
    ],
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_MEDIUM,
        ],
    ],
];

// Definir headerStyle para la tercera hoja
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCCCCC']]
];

// Definir basicDataStyle para las hojas
$basicDataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];

// Aplicar estilos
$sheet3->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet3->getStyle('A' . $row . ':B' . $row)->applyFromArray($totalStyle);
$sheet3->getColumnDimension('A')->setAutoSize(true);
$sheet3->getColumnDimension('B')->setAutoSize(true);

// HOJA 4: Homologados
$spreadsheet->createSheet();
$sheet4 = $spreadsheet->getSheet(3);
$sheet4->setTitle('Homologados');

// Establecer títulos
$sheet4->mergeCells('A1:B1');
$sheet4->setCellValue('A1', 'Estas personas se les homologa 40 a las horas técnicas, y la totalidad de las horas de habilidades de poder');
$sheet4->setCellValue('A2', 'Número de Identificación');
$sheet4->setCellValue('B2', 'Nombre del Estudiante');

// Consulta SQL para obtener homologados
$sqlHomologados = "SELECT cs.number_id, g.full_name
                   FROM certificados_senatics cs
                   LEFT JOIN groups g ON cs.number_id = g.number_id
                   ORDER BY g.full_name";

$resultHomologados = $conn->query($sqlHomologados);

$row = 3; // Comenzar datos en fila 3
$lastRow4 = $row; // Para estilos

while($data = $resultHomologados->fetch_assoc()) {
    $sheet4->setCellValue('A' . $row, $data['number_id']);
    $sheet4->setCellValue('B' . $row, $data['full_name']);
    $lastRow4 = $row;
    $row++;
}

// Aplicar estilos
$sheet4->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet4->getStyle('A2:B2')->applyFromArray($headerStyle);
if ($lastRow4 >= 3) {
    $sheet4->getStyle('A3:B' . $lastRow4)->applyFromArray($basicDataStyle);
}
$sheet4->getColumnDimension('A')->setAutoSize(true);
$sheet4->getColumnDimension('B')->setAutoSize(true);

// Limpiar cualquier salida anterior
ob_end_clean();

// Asegurarse de que el archivo se guarde correctamente al final
try {
    $writer = new Xlsx($spreadsheet);
    $filename = 'Reporte_Horas_Por_Lotes_EL_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    // Encabezados HTTP para la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Guardar en la salida
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    ob_end_clean();
    echo "Error al generar el archivo: " . $e->getMessage();
}
?>
