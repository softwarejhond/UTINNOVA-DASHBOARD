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

// Función para calcular horas basadas en asistencia
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

// Crear hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte de Horas');

// Encabezados
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

// Consulta SQL
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
    $horasNivelador = isset($data['leveling_hours']) ? intval($data['leveling_hours']) : 0;    
    $horasIngles = isset($data['english_hours']) ? intval($data['english_hours']) : 0;
    $horasHabilidades = isset($data['skills_hours']) ? intval($data['skills_hours']) : 0;
    $horasActualesTecnico = isset($data['bootcamp_code']) && !empty($data['bootcamp_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['bootcamp_code']) : 0;
    $horasActualesNivelador = isset($data['leveling_code']) && !empty($data['leveling_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['leveling_code']) : 0;
    $horasActualesIngles = isset($data['english_code']) && !empty($data['english_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['english_code']) : 0;
    $horasActualesHabilidades = isset($data['skills_code']) && !empty($data['skills_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['skills_code']) : 0;
    $sheet->setCellValue('H' . $row, $horasActualesTecnico);
    $sheet->setCellValue('I' . $row, $horasTecnico);
    $sheet->setCellValue('J' . $row, 120);
    $sheet->setCellValue('K' . $row, $horasActualesNivelador);
    $sheet->setCellValue('L' . $row, $horasNivelador);
    $sheet->setCellValue('M' . $row, 20);
    $sheet->setCellValue('N' . $row, $horasActualesIngles);
    $sheet->setCellValue('O' . $row, $horasIngles);
    $sheet->setCellValue('P' . $row, 24);
    $sheet->setCellValue('Q' . $row, $horasActualesHabilidades);
    $sheet->setCellValue('R' . $row, $horasHabilidades);
    $sheet->setCellValue('S' . $row, 15);
    $totalActual = intval($horasActualesTecnico) + intval($horasActualesNivelador) + 
                  intval($horasActualesIngles) + intval($horasActualesHabilidades);
    $totalReales = intval($horasTecnico) + intval($horasNivelador) + 
                  intval($horasIngles) + intval($horasHabilidades);
    $sheet->setCellValue('T' . $row, $totalActual);
    $sheet->setCellValue('U' . $row, $totalReales);
    $sheet->setCellValue('V' . $row, 159);
    $sheet->setCellValue('W' . $row, '=T' . $row . '/V' . $row);
    $sheet->setCellValue('X' . $row, '=U' . $row . '/V' . $row);
    $sheet->setCellValue('Y' . $row, '=1-(U' . $row . '/V' . $row . ')');
    $lastRow = $row;
    $row++;
}

// Estilos
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
$niveladordHeaderStyle = array_merge($basicHeaderStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6F3FF']],
]);
$niveladordDataStyle = array_merge($basicDataStyle, [
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F8FF']],
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
$sheet->getStyle('K1:M1')->applyFromArray($niveladordHeaderStyle);
$sheet->getStyle('N1:P1')->applyFromArray($inglesHeaderStyle);
$sheet->getStyle('Q1:S1')->applyFromArray($habilidadesHeaderStyle);
$sheet->getStyle('T1:V1')->applyFromArray($totalesHeaderStyle);
$sheet->getStyle('W1:Y1')->applyFromArray($porcentajesHeaderStyle);

if ($lastRow >= 2) {
    $sheet->getStyle('A2:G' . $lastRow)->applyFromArray($basicDataStyle);
    $sheet->getStyle('H2:J' . $lastRow)->applyFromArray($tecnicoDataStyle);
    $sheet->getStyle('K2:M' . $lastRow)->applyFromArray($niveladordDataStyle);
    $sheet->getStyle('N2:P' . $lastRow)->applyFromArray($inglesDataStyle);
    $sheet->getStyle('Q2:S' . $lastRow)->applyFromArray($habilidadesDataStyle);
    $sheet->getStyle('T2:V' . $lastRow)->applyFromArray($totalesDataStyle);
    $sheet->getStyle('W2:Y' . $lastRow)->applyFromArray($porcentajesDataStyle);
    $sheet->getStyle('W2:Y' . $lastRow)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
}
foreach(range('A','Y') as $col) {
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
    $reportDir = __DIR__ . '/../../reports/asistenciaLE/';
    
    // Eliminar archivos anteriores que coincidan con el patrón
    $pattern = $reportDir . 'informe_asistencia_LE_*.xlsx';
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
    $filename = $reportDir . 'informe_asistencia_LE_' . $fecha . '.xlsx';
    $writer = new Xlsx($spreadsheet);
    $writer->save($filename);
    echo "Archivo generado correctamente: $filename\n";
    echo "Archivos anteriores eliminados.\n";
} catch (Exception $e) {
    echo "Error al generar el archivo: " . $e->getMessage() . "\n";
}