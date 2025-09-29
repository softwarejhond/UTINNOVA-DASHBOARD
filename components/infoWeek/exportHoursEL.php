<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Corregir ruta del autoload
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Función actualizada para calcular horas basadas en asistencia
function calcularHorasAsistencia($conn, $studentId, $courseId)
{
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
        error_log("Error preparando consulta: " . $conn->error);
        return 0;
    }

    $stmt->bind_param("si", $studentId, $courseId);
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        return 0;
    }

    $result = $stmt->get_result();

    $fechasContadas = [];
    $totalHoras = 0;

    while ($asistencia = $result->fetch_assoc()) {
        $fecha = $asistencia['class_date'];

        // Solo contar una asistencia por fecha (la primera después de ordenar)
        if (!in_array($fecha, $fechasContadas)) {
            $totalHoras += $asistencia['horas'];
            $fechasContadas[] = $fecha;
        }
    }

    $stmt->close();
    return $totalHoras;
}

// Función para configurar encabezados de una hoja
function configurarEncabezados($sheet)
{
    $sheet->setCellValue('A1', 'Número de Identificación');
    $sheet->setCellValue('B1', 'Nombre del Estudiante');
    $sheet->setCellValue('C1', 'Correo Personal');
    $sheet->setCellValue('D1', 'Correo Institucional');
    $sheet->setCellValue('E1', 'Teléfono Principal');
    $sheet->setCellValue('F1', 'Teléfono Secundario');
    $sheet->setCellValue('G1', 'Programa');

    // Títulos específicos por área
    $sheet->setCellValue('H1', 'Técnico - Horas Actuales');
    $sheet->setCellValue('I1', 'Técnico - Horas Reales');
    $sheet->setCellValue('J1', 'Técnico - Total Horas');

    $sheet->setCellValue('K1', 'Inglés Nivelador - Horas Actuales');
    $sheet->setCellValue('L1', 'Inglés Nivelador - Horas Reales');
    $sheet->setCellValue('M1', 'Inglés Nivelador - Total Horas');

    $sheet->setCellValue('N1', 'Inglés - Horas Actuales');
    $sheet->setCellValue('O1', 'Inglés - Horas Reales');
    $sheet->setCellValue('P1', 'Inglés - Total Horas');

    $sheet->setCellValue('Q1', 'Habilidades - Horas Actuales');
    $sheet->setCellValue('R1', 'Habilidades - Horas Reales');
    $sheet->setCellValue('S1', 'Habilidades - Total Horas');

    $sheet->setCellValue('T1', 'Total - Horas Actuales');
    $sheet->setCellValue('U1', 'Total - Horas Reales');
    $sheet->setCellValue('V1', 'Total - Horas Programa');

    $sheet->setCellValue('W1', 'Porcentaje Actuales');
    $sheet->setCellValue('X1', 'Porcentaje Reales');
    $sheet->setCellValue('Y1', 'Porcentaje Faltante');
}

// Función para aplicar estilos a una hoja
function aplicarEstilos($sheet, $lastRow)
{
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

    $niveladordHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F3FF']], // Azul claro
    ]);

    $niveladordDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F8FF']], // Azul más claro para datos
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

    // Aplicar estilos específicos por área - Headers
    $sheet->getStyle('A1:G1')->applyFromArray($basicHeaderStyle); // Información básica
    $sheet->getStyle('H1:J1')->applyFromArray($tecnicoHeaderStyle);     // Técnico
    $sheet->getStyle('K1:M1')->applyFromArray($niveladordHeaderStyle);  // Inglés Nivelador
    $sheet->getStyle('N1:P1')->applyFromArray($inglesHeaderStyle);     // Inglés
    $sheet->getStyle('Q1:S1')->applyFromArray($habilidadesHeaderStyle); // Habilidades
    $sheet->getStyle('T1:V1')->applyFromArray($totalesHeaderStyle);    // Totales
    $sheet->getStyle('W1:Y1')->applyFromArray($porcentajesHeaderStyle); // Porcentajes

    // Aplicar estilos específicos por área - Datos
    if ($lastRow >= 2) {
        $sheet->getStyle('A2:G' . $lastRow)->applyFromArray($basicDataStyle); // Información básica
        $sheet->getStyle('H2:J' . $lastRow)->applyFromArray($tecnicoDataStyle);     // Técnico
        $sheet->getStyle('K2:M' . $lastRow)->applyFromArray($niveladordDataStyle);  // Inglés Nivelador
        $sheet->getStyle('N2:P' . $lastRow)->applyFromArray($inglesDataStyle);     // Inglés
        $sheet->getStyle('Q2:S' . $lastRow)->applyFromArray($habilidadesDataStyle); // Habilidades
        $sheet->getStyle('T2:V' . $lastRow)->applyFromArray($totalesDataStyle);    // Totales
        $sheet->getStyle('W2:Y' . $lastRow)->applyFromArray($porcentajesDataStyle); // Porcentajes
    }

    // Aplicar formato de porcentaje a las columnas W, X y Y
    if ($lastRow >= 2) {
        $sheet->getStyle('W2:Y' . $lastRow)->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    }

    // Autoajustar columnas
    foreach (range('A', 'Y') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Función para llenar datos de una hoja con un lote específico
function llenarDatosLote($conn, $sheet, $lote)
{
    // Consulta SQL actualizada para incluir filtro por lote
    $sql = "SELECT g.*, 
           b.real_hours AS bootcamp_hours, b.code AS bootcamp_code,
           e.real_hours AS english_hours, e.code AS english_code,
           l.real_hours AS leveling_hours, l.code AS leveling_code,
           s.real_hours AS skills_hours, s.code AS skills_code,
           u.email AS personal_email, u.first_phone, u.second_phone
    FROM groups g
    LEFT JOIN courses b ON g.id_bootcamp = b.code 
    LEFT JOIN courses e ON g.id_english_code = e.code
    LEFT JOIN courses l ON g.id_leveling_english = l.code 
    LEFT JOIN courses s ON g.id_skills = s.code
    LEFT JOIN user_register u ON g.number_id = u.number_id
    WHERE u.lote = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lote);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = 2; // Comenzar datos en fila 2
    $lastRow = $row; // Para mantener un registro de la última fila

    while ($data = $result->fetch_assoc()) {
        // Datos básicos con comprobación
        $sheet->setCellValue('A' . $row, $data['number_id'] ?? '');
        $sheet->setCellValue('B' . $row, $data['full_name'] ?? '');
        $sheet->setCellValue('C' . $row, $data['personal_email'] ?? '');
        $sheet->setCellValue('D' . $row, $data['institutional_email'] ?? '');
        $sheet->setCellValue('E' . $row, str_replace('+57', '', $data['first_phone'] ?? ''));
        $sheet->setCellValue('F' . $row, str_replace('+57', '', $data['second_phone'] ?? ''));
        $sheet->setCellValue('G' . $row, ($data['id_bootcamp'] ?? '') . ' - ' . ($data['bootcamp_name'] ?? ''));

        // Obtener horas reales con comprobación explícita de valores nulos
        $horasTecnico = isset($data['bootcamp_hours']) ? intval($data['bootcamp_hours']) : 0;
        $horasNivelador = isset($data['leveling_hours']) ? intval($data['leveling_hours']) : 0;
        $horasIngles = isset($data['english_hours']) ? intval($data['english_hours']) : 0;
        $horasHabilidades = isset($data['skills_hours']) ? intval($data['skills_hours']) : 0;

        // Calcular horas actuales con verificación adicional
        $horasActualesTecnico = isset($data['bootcamp_code']) && !empty($data['bootcamp_code']) ?
            calcularHorasAsistencia($conn, $data['number_id'], $data['bootcamp_code']) : 0;

        $horasActualesNivelador = isset($data['leveling_code']) && !empty($data['leveling_code']) ?
            calcularHorasAsistencia($conn, $data['number_id'], $data['leveling_code']) : 0;

        $horasActualesIngles = isset($data['english_code']) && !empty($data['english_code']) ?
            calcularHorasAsistencia($conn, $data['number_id'], $data['english_code']) : 0;

        $horasActualesHabilidades = isset($data['skills_code']) && !empty($data['skills_code']) ?
            calcularHorasAsistencia($conn, $data['number_id'], $data['skills_code']) : 0;

        // Técnico
        $sheet->setCellValue('H' . $row, $horasActualesTecnico);
        $sheet->setCellValue('I' . $row, isset($data['bootcamp_hours']) && $data['bootcamp_hours'] > 0 ? $data['bootcamp_hours'] : 0);
        $sheet->setCellValue('J' . $row, 120);

        // Inglés Nivelador
        $sheet->setCellValue('K' . $row, $horasActualesNivelador);
        $sheet->setCellValue('L' . $row, isset($data['leveling_hours']) && $data['leveling_hours'] > 0 ? $data['leveling_hours'] : 0);
        $sheet->setCellValue('M' . $row, 20);

        // Inglés
        $sheet->setCellValue('N' . $row, $horasActualesIngles);
        $sheet->setCellValue('O' . $row, isset($data['english_hours']) && $data['english_hours'] > 0 ? $data['english_hours'] : 0);
        $sheet->setCellValue('P' . $row, 24);

        // Habilidades
        $sheet->setCellValue('Q' . $row, $horasActualesHabilidades);
        $sheet->setCellValue('R' . $row, isset($data['skills_hours']) && $data['skills_hours'] > 0 ? $data['skills_hours'] : 0);
        $sheet->setCellValue('S' . $row, 15);

        // Totales
        $totalActual = intval($horasActualesTecnico) + intval($horasActualesNivelador) +
            intval($horasActualesIngles) + intval($horasActualesHabilidades);

        $totalReales = intval($horasTecnico) + intval($horasNivelador) +
            intval($horasIngles) + intval($horasHabilidades);

        $sheet->setCellValue('T' . $row, $totalActual);
        $sheet->setCellValue('U' . $row, $totalReales);
        $sheet->setCellValue('V' . $row, 159);

        // Porcentajes con validación (0% - 100%)
        $sheet->setCellValue('W' . $row, '=MIN(1,MAX(0,T' . $row . '/V' . $row . '))');
        $sheet->setCellValue('X' . $row, '=MIN(1,MAX(0,U' . $row . '/V' . $row . '))');
        $sheet->setCellValue('Y' . $row, '=MIN(1,MAX(0,1-(U' . $row . '/V' . $row . ')))');

        $lastRow = $row; // Actualizar la última fila
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

while ($bootcampData = $resultBootcamps->fetch_assoc()) {
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

// Aplicar estilos
$sheet3->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet3->getStyle('A' . $row . ':B' . $row)->applyFromArray($totalStyle);
$sheet3->getColumnDimension('A')->setAutoSize(true);
$sheet3->getColumnDimension('B')->setAutoSize(true);

// HOJA 4: Cursos Inglés Nivelatorio Lote 1
$spreadsheet->createSheet();
$sheet4 = $spreadsheet->getSheet(3);
$sheet4->setTitle('Inglés Nivelatorio Lote 1');

// Configurar encabezados simples
$sheet4->setCellValue('A1', 'Código del Curso');
$sheet4->setCellValue('B1', 'Nombre del Curso');
$sheet4->setCellValue('C1', 'Horas Reales');
$sheet4->setCellValue('D1', 'Porcentaje de Avance');
$sheet4->setCellValue('F1', 'Estadísticas del Lote 1');
$sheet4->setCellValue('F2', 'Total de Cursos:');
$sheet4->setCellValue('F3', 'Cursos Completados (100%):');
$sheet4->setCellValue('F4', '% Cursos Completados:');

// Consulta para obtener cursos de Inglés Nivelatorio del Lote 1
$sqlInglesNivelatorioLote1 = "SELECT DISTINCT g.id_leveling_english, g.leveling_english_name, c.real_hours 
                              FROM groups g 
                              LEFT JOIN user_register ur ON g.number_id = ur.number_id 
                              LEFT JOIN courses c ON g.id_leveling_english = c.code 
                              WHERE ur.lote = 1 
                              AND g.id_leveling_english IS NOT NULL 
                              AND g.id_leveling_english != ''
                              ORDER BY g.id_leveling_english";

$resultInglesNivelatorioLote1 = $conn->query($sqlInglesNivelatorioLote1);

$row = 2; // Comenzar datos en fila 2
$lastRowNivelatorioLote1 = $row;
$totalCursosLote1 = 0;
$cursosCompletadosLote1 = 0;

while ($cursoData = $resultInglesNivelatorioLote1->fetch_assoc()) {
    // Información del curso
    $sheet4->setCellValue('A' . $row, $cursoData['id_leveling_english']);
    $sheet4->setCellValue('B' . $row, $cursoData['leveling_english_name']);
    $sheet4->setCellValue('C' . $row, $cursoData['real_hours'] ?? 0);
    
    // Porcentaje basado en 20 horas como 100% con validación (0% - 100%)
    $sheet4->setCellValue('D' . $row, '=MIN(1,MAX(0,C' . $row . '/20))');
    
    // Contar cursos y cursos completados
    $totalCursosLote1++;
    if (($cursoData['real_hours'] ?? 0) >= 20) {
        $cursosCompletadosLote1++;
    }
    
    $lastRowNivelatorioLote1 = $row;
    $row++;
}

// Agregar estadísticas en la columna G
$sheet4->setCellValue('G2', $totalCursosLote1);
$sheet4->setCellValue('G3', $cursosCompletadosLote1);
$sheet4->setCellValue('G4', $totalCursosLote1 > 0 ? '=G3/G2' : 0);

// Aplicar estilos a la hoja de Inglés Nivelatorio Lote 1
$niveladoresHeaderStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F3FF']] // Azul claro
];

$niveladoresDataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F8FF']] // Azul más claro
];

// Estilo para las estadísticas
$estadisticasHeaderStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6CC']] // Naranja claro
];

$estadisticasDataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2E6']] // Naranja más claro
];

// Aplicar estilos a encabezados de cursos
$sheet4->getStyle('A1:D1')->applyFromArray($niveladoresHeaderStyle);

// Aplicar estilos a datos de cursos
if ($lastRowNivelatorioLote1 >= 2) {
    $sheet4->getStyle('A2:D' . $lastRowNivelatorioLote1)->applyFromArray($niveladoresDataStyle);
    
    // Aplicar formato de porcentaje a la columna D
    $sheet4->getStyle('D2:D' . $lastRowNivelatorioLote1)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
}

// Aplicar estilos a estadísticas
$sheet4->getStyle('F1:F4')->applyFromArray($estadisticasHeaderStyle);
$sheet4->getStyle('G2:G4')->applyFromArray($estadisticasDataStyle);

// Aplicar formato de porcentaje a G4
$sheet4->getStyle('G4')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

// Autoajustar columnas
foreach (range('A', 'G') as $col) {
    $sheet4->getColumnDimension($col)->setAutoSize(true);
}

// HOJA 5: Cursos Inglés Nivelatorio Lote 2
$spreadsheet->createSheet();
$sheet5 = $spreadsheet->getSheet(4);
$sheet5->setTitle('Inglés Nivelatorio Lote 2');

// Configurar encabezados simples
$sheet5->setCellValue('A1', 'Código del Curso');
$sheet5->setCellValue('B1', 'Nombre del Curso');
$sheet5->setCellValue('C1', 'Horas Reales');
$sheet5->setCellValue('D1', 'Porcentaje de Avance');
$sheet5->setCellValue('F1', 'Estadísticas del Lote 2');
$sheet5->setCellValue('F2', 'Total de Cursos:');
$sheet5->setCellValue('F3', 'Cursos Completados (100%):');
$sheet5->setCellValue('F4', '% Cursos Completados:');

// Consulta para obtener cursos de Inglés Nivelatorio del Lote 2
$sqlInglesNivelatorioLote2 = "SELECT DISTINCT g.id_leveling_english, g.leveling_english_name, c.real_hours 
                              FROM groups g 
                              LEFT JOIN user_register ur ON g.number_id = ur.number_id 
                              LEFT JOIN courses c ON g.id_leveling_english = c.code 
                              WHERE ur.lote = 2 
                              AND g.id_leveling_english IS NOT NULL 
                              AND g.id_leveling_english != ''
                              ORDER BY g.id_leveling_english";

$resultInglesNivelatorioLote2 = $conn->query($sqlInglesNivelatorioLote2);

$row = 2; // Comenzar datos en fila 2
$lastRowNivelatorioLote2 = $row;
$totalCursosLote2 = 0;
$cursosCompletadosLote2 = 0;

while ($cursoData = $resultInglesNivelatorioLote2->fetch_assoc()) {
    // Información del curso
    $sheet5->setCellValue('A' . $row, $cursoData['id_leveling_english']);
    $sheet5->setCellValue('B' . $row, $cursoData['leveling_english_name']);
    $sheet5->setCellValue('C' . $row, $cursoData['real_hours'] ?? 0);
    
    // Porcentaje basado en 20 horas como 100% con validación (0% - 100%)
    $sheet5->setCellValue('D' . $row, '=MIN(1,MAX(0,C' . $row . '/20))');
    
    // Contar cursos y cursos completados
    $totalCursosLote2++;
    if (($cursoData['real_hours'] ?? 0) >= 20) {
        $cursosCompletadosLote2++;
    }
    
    $lastRowNivelatorioLote2 = $row;
    $row++;
}

// Agregar estadísticas en la columna G
$sheet5->setCellValue('G2', $totalCursosLote2);
$sheet5->setCellValue('G3', $cursosCompletadosLote2);
$sheet5->setCellValue('G4', $totalCursosLote2 > 0 ? '=G3/G2' : 0);

// Aplicar estilos a encabezados de cursos
$sheet5->getStyle('A1:D1')->applyFromArray($niveladoresHeaderStyle);

// Aplicar estilos a datos de cursos
if ($lastRowNivelatorioLote2 >= 2) {
    $sheet5->getStyle('A2:D' . $lastRowNivelatorioLote2)->applyFromArray($niveladoresDataStyle);
    
    // Aplicar formato de porcentaje a la columna D
    $sheet5->getStyle('D2:D' . $lastRowNivelatorioLote2)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
}

// Aplicar estilos a estadísticas
$sheet5->getStyle('F1:F4')->applyFromArray($estadisticasHeaderStyle);
$sheet5->getStyle('G2:G4')->applyFromArray($estadisticasDataStyle);

// Aplicar formato de porcentaje a G4
$sheet5->getStyle('G4')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);

// Autoajustar columnas
foreach (range('A', 'G') as $col) {
    $sheet5->getColumnDimension($col)->setAutoSize(true);
}

// Limpiar cualquier salida anterior
ob_end_clean();

// Asegurarse de que el archivo se guarde correctamente al final
try {
    $writer = new Xlsx($spreadsheet);
    $filename = 'Reporte_Horas_Por_Lotes_' . date('Y-m-d_H-i-s') . '.xlsx';

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
