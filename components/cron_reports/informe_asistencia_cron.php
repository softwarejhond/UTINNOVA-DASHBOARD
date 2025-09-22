<?php
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// ...Función calcularHorasAsistencia igual que en el archivo original...

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

// Crear hoja de cálculo y hojas adicionales (igual que el original)
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte de Horas');

// ...Encabezados y estilos igual que el original...

$sheet->setCellValue('A1', 'Número de Identificación');
$sheet->setCellValue('B1', 'Nombre del Estudiante');
$sheet->setCellValue('C1', 'Correo Personal');
$sheet->setCellValue('D1', 'Correo Institucional');
$sheet->setCellValue('E1', 'Teléfono Principal');
$sheet->setCellValue('F1', 'Teléfono Secundario');
$sheet->setCellValue('G1', 'Programa');
$sheet->setCellValue('H1', 'Técnico - Horas Actuales');
$sheet->setCellValue('I1', 'Técnico - Horas Reales');
$sheet->setCellValue('J1', 'Técnico - Total Horas');
$sheet->setCellValue('K1', 'Inglés - Horas Actuales');
$sheet->setCellValue('L1', 'Inglés - Horas Reales');
$sheet->setCellValue('M1', 'Inglés - Total Horas');
$sheet->setCellValue('N1', 'Habilidades - Horas Actuales');
$sheet->setCellValue('O1', 'Habilidades - Horas Reales');
$sheet->setCellValue('P1', 'Habilidades - Total Horas');
$sheet->setCellValue('Q1', 'Total - Horas Actuales');
$sheet->setCellValue('R1', 'Total - Horas Reales');
$sheet->setCellValue('S1', 'Total - Horas Programa');
$sheet->setCellValue('T1', 'Porcentaje Actuales');
$sheet->setCellValue('U1', 'Porcentaje Reales');
$sheet->setCellValue('V1', 'Porcentaje Faltante');

// ...Consulta SQL y llenado de datos igual que el original...

$sql = "SELECT g.*, 
       b.real_hours AS bootcamp_hours, b.code AS bootcamp_code,
       e.real_hours AS english_hours, e.code AS english_code, 
       s.real_hours AS skills_hours, s.code AS skills_code,
       u.email AS personal_email, u.first_phone, u.second_phone
FROM groups g
LEFT JOIN courses b ON g.id_bootcamp = b.code 
LEFT JOIN courses e ON g.id_english_code = e.code 
LEFT JOIN courses s ON g.id_skills = s.code
LEFT JOIN user_register u ON g.number_id = u.number_id";

$result = $conn->query($sql);
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$row = 2;
$lastRow = $row;

while($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['number_id'] ?? '');
    $sheet->setCellValue('B' . $row, $data['full_name'] ?? '');
    $sheet->setCellValue('C' . $row, $data['personal_email'] ?? '');
    $sheet->setCellValue('D' . $row, $data['institutional_email'] ?? '');
    $sheet->setCellValue('E' . $row, str_replace('+57', '', $data['first_phone'] ?? ''));
    $sheet->setCellValue('F' . $row, str_replace('+57', '', $data['second_phone'] ?? ''));
    $sheet->setCellValue('G' . $row, ($data['id_bootcamp'] ?? '') . ' - ' . ($data['bootcamp_name'] ?? ''));
    $horasTecnico = isset($data['bootcamp_hours']) ? intval($data['bootcamp_hours']) : 0;
    $horasIngles = isset($data['english_hours']) ? intval($data['english_hours']) : 0;
    $horasHabilidades = isset($data['skills_hours']) ? intval($data['skills_hours']) : 0;
    $horasActualesTecnico = isset($data['bootcamp_code']) && !empty($data['bootcamp_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['bootcamp_code']) : 0;
    $horasActualesIngles = isset($data['english_code']) && !empty($data['english_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['english_code']) : 0;
    $horasActualesHabilidades = isset($data['skills_code']) && !empty($data['skills_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['skills_code']) : 0;
    $sheet->setCellValue('H' . $row, $horasActualesTecnico);
    $sheet->setCellValue('I' . $row, $horasTecnico);
    $sheet->setCellValue('J' . $row, 120);
    $sheet->setCellValue('K' . $row, $horasActualesIngles);
    $sheet->setCellValue('L' . $row, $horasIngles);
    $sheet->setCellValue('M' . $row, 24);
    $sheet->setCellValue('N' . $row, $horasActualesHabilidades);
    $sheet->setCellValue('O' . $row, $horasHabilidades);
    $sheet->setCellValue('P' . $row, 15);
    $totalActual = intval($horasActualesTecnico) + intval($horasActualesIngles) + intval($horasActualesHabilidades);
    $totalReales = intval($horasTecnico) + intval($horasIngles) + intval($horasHabilidades);
    $sheet->setCellValue('Q' . $row, $totalActual);
    $sheet->setCellValue('R' . $row, $totalReales);
    $sheet->setCellValue('S' . $row, 159);
    $sheet->setCellValue('T' . $row, '=Q' . $row . '/S' . $row);
    $sheet->setCellValue('U' . $row, '=R' . $row . '/S' . $row);
    $sheet->setCellValue('V' . $row, '=1-(R' . $row . '/S' . $row . ')');
    $lastRow = $row;
    $row++;
}

// ...Estilos igual que el original...

$basicHeaderStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$basicDataStyle = [
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
];
$tecnicoHeaderStyle = array_merge($basicHeaderStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6E6']],
]);
$tecnicoDataStyle = array_merge($basicDataStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2F2']],
]);
$inglesHeaderStyle = array_merge($basicHeaderStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6FFE6']],
]);
$inglesDataStyle = array_merge($basicDataStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2FFF2']],
]);
$habilidadesHeaderStyle = array_merge($basicHeaderStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0E6']],
]);
$habilidadesDataStyle = array_merge($basicDataStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8F2']],
]);
$totalesHeaderStyle = array_merge($basicHeaderStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0E6FF']],
]);
$totalesDataStyle = array_merge($basicDataStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F2FF']],
]);
$porcentajesHeaderStyle = array_merge($basicHeaderStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFCC']],
]);
$porcentajesDataStyle = array_merge($basicDataStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFF2']],
]);

$sheet->getStyle('A1:G1')->applyFromArray($basicHeaderStyle);
$sheet->getStyle('H1:J1')->applyFromArray($tecnicoHeaderStyle);
$sheet->getStyle('K1:M1')->applyFromArray($inglesHeaderStyle);
$sheet->getStyle('N1:P1')->applyFromArray($habilidadesHeaderStyle);
$sheet->getStyle('Q1:S1')->applyFromArray($totalesHeaderStyle);
$sheet->getStyle('T1:V1')->applyFromArray($porcentajesHeaderStyle);

if ($lastRow >= 2) {
    $sheet->getStyle('A2:G' . $lastRow)->applyFromArray($basicDataStyle);
    $sheet->getStyle('H2:J' . $lastRow)->applyFromArray($tecnicoDataStyle);
    $sheet->getStyle('K2:M' . $lastRow)->applyFromArray($inglesDataStyle);
    $sheet->getStyle('N2:P' . $lastRow)->applyFromArray($habilidadesDataStyle);
    $sheet->getStyle('Q2:S' . $lastRow)->applyFromArray($totalesDataStyle);
    $sheet->getStyle('T2:V' . $lastRow)->applyFromArray($porcentajesDataStyle);
    $sheet->getStyle('T2:V' . $lastRow)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
}
foreach(range('A','V') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Segunda hoja
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->getSheet(1);
$sheet2->setTitle('Conteo Bootcamps');
$sheet2->setCellValue('A1', 'Bootcamp');
$sheet2->setCellValue('B1', 'Inscritos');
$sqlBootcamps = "SELECT bootcamp_name, COUNT(*) as total 
                 FROM groups 
                 GROUP BY bootcamp_name 
                 ORDER BY bootcamp_name";
$resultBootcamps = $conn->query($sqlBootcamps);
$row = 2;
$totalInscritos = 0;
while($bootcampData = $resultBootcamps->fetch_assoc()) {
    $sheet2->setCellValue('A' . $row, $bootcampData['bootcamp_name']);
    $sheet2->setCellValue('B' . $row, $bootcampData['total']);
    $totalInscritos += $bootcampData['total'];
    $row++;
}
$sheet2->setCellValue('A' . $row, 'TOTAL INSCRITOS');
$sheet2->setCellValue('B' . $row, $totalInscritos);
$totalStyle = [
    'font' => ['bold' => true],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6E6E6']],
    'borders' => ['top' => ['borderStyle' => Border::BORDER_MEDIUM]],
];
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCCCCC']]
];
$sheet2->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet2->getStyle('A' . $row . ':B' . $row)->applyFromArray($totalStyle);
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);

// Tercera hoja
$spreadsheet->createSheet();
$sheet3 = $spreadsheet->getSheet(2);
$sheet3->setTitle('Hoja 3');

// Limpiar cualquier salida anterior
ob_end_clean();

// Guardar archivo en reports con formato solicitado
try {
    $reportDir = __DIR__ . '/../../reports/asistencia/';
    
    // Eliminar archivos anteriores que coincidan con el patrón
    $pattern = $reportDir . 'informe_asistencia_*.xlsx';
    $existingFiles = glob($pattern);
    foreach ($existingFiles as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    // Establecer zona horaria Bogotá
    date_default_timezone_set('America/Bogota');
    
    // Crear nuevo archivo con timestamp
    $fecha = date('dmy_His');
    $filename = $reportDir . 'informe_asistencia_' . $fecha . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo "Archivo generado correctamente: $filename\n";
    echo "Archivos anteriores eliminados.\n";
} catch (Exception $e) {
    echo "Error al generar el archivo: " . $e->getMessage() . "\n";
}