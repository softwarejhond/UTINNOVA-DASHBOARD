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
        
        // Solo contar una asistencia por fecha (la primera después de ordenar)
        if (!in_array($fecha, $fechasContadas)) {
            $totalHoras += $asistencia['horas'];
            $fechasContadas[] = $fecha;
        }
    }
    
    $stmt->close();
    return $totalHoras;
}

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Reporte de Horas');

// Establecer encabezados
$sheet->setCellValue('A1', 'Número de Identificación');
$sheet->setCellValue('B1', 'Nombre del Estudiante');
$sheet->setCellValue('C1', 'Programa');

// Títulos principales
$sheet->setCellValue('D1', 'Técnico');
$sheet->setCellValue('G1', 'Inglés');
$sheet->setCellValue('J1', 'Habilidades');
$sheet->setCellValue('M1', 'TOTALES');
$sheet->setCellValue('P1', 'PORCENTAJES');

// Subtítulos - Corregir el bucle
$subHeaders = ['Horas actuales', 'Horas reales', 'Total de Horas'];
$columns = ['D', 'G', 'J', 'M'];
foreach ($columns as $col) {
    $currentCol = $col;
    for ($i = 0; $i < 3; $i++) {
        $sheet->setCellValue($currentCol . '2', $subHeaders[$i]);
        $currentCol = chr(ord($currentCol) + 1);
    }
}

// Subtítulos de porcentajes
$sheet->setCellValue('P2', 'Porcentaje Actuales');
$sheet->setCellValue('Q2', 'Porcentaje Reales');
$sheet->setCellValue('R2', 'Porcentaje Faltante');

// Mejorar la consulta SQL para evitar error cuando no haya cursos asociados
$sql = "SELECT g.*, 
       b.real_hours AS bootcamp_hours, b.code AS bootcamp_code,
       e.real_hours AS english_hours, e.code AS english_code, 
       s.real_hours AS skills_hours, s.code AS skills_code
FROM groups g
LEFT JOIN courses b ON g.id_bootcamp = b.code 
LEFT JOIN courses e ON g.id_english_code = e.code 
LEFT JOIN courses s ON g.id_skills = s.code";

$result = $conn->query($sql);

// Asegurarse que el resultado de la consulta sea válido
if (!$result) {
    die("Error en la consulta: " . $conn->error);
}

$row = 3; // Comenzar datos en fila 3
$lastRow = $row; // Para mantener un registro de la última fila

// En el bucle while, verificar que los valores existan antes de usarlos
while($data = $result->fetch_assoc()) {
    // Datos básicos
    $sheet->setCellValue('A' . $row, $data['number_id'] ?? '');
    $sheet->setCellValue('B' . $row, $data['full_name'] ?? '');
    $sheet->setCellValue('C' . $row, ($data['id_bootcamp'] ?? '') . ' - ' . ($data['bootcamp_name'] ?? ''));
    
    // Obtener horas reales de courses con comprobación de valores null
    $horasTecnico = isset($data['bootcamp_hours']) ? intval($data['bootcamp_hours']) : 0;
    $horasIngles = isset($data['english_hours']) ? intval($data['english_hours']) : 0;
    $horasHabilidades = isset($data['skills_hours']) ? intval($data['skills_hours']) : 0;
    
    // Calcular horas actuales con comprobación adicional
    $horasActualesTecnico = isset($data['bootcamp_code']) && !empty($data['bootcamp_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['bootcamp_code']) : 0;
    
    $horasActualesIngles = isset($data['english_code']) && !empty($data['english_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['english_code']) : 0;
    
    $horasActualesHabilidades = isset($data['skills_code']) && !empty($data['skills_code']) ? 
        calcularHorasAsistencia($conn, $data['number_id'], $data['skills_code']) : 0;
    
    // Técnico - Actualizar para usar horas calculadas de asistencia
    $sheet->setCellValue('D' . $row, $horasActualesTecnico);
    $sheet->setCellValue('E' . $row, $horasTecnico);
    $sheet->setCellValue('F' . $row, 120);
    
    // Inglés - Actualizar para usar horas calculadas de asistencia
    $sheet->setCellValue('G' . $row, $horasActualesIngles);
    $sheet->setCellValue('H' . $row, $horasIngles);
    $sheet->setCellValue('I' . $row, 24);
    
    // Habilidades - Actualizar para usar horas calculadas de asistencia
    $sheet->setCellValue('J' . $row, $horasActualesHabilidades);
    $sheet->setCellValue('K' . $row, $horasHabilidades);
    $sheet->setCellValue('L' . $row, 15);
    
    // Totales - Asegurar conversión a números
    $totalActual = intval($horasActualesTecnico) + intval($horasActualesIngles) + intval($horasActualesHabilidades);
    $totalReales = intval($horasTecnico) + intval($horasIngles) + intval($horasHabilidades);
    $sheet->setCellValue('M' . $row, $totalActual);
    $sheet->setCellValue('N' . $row, $totalReales);
    $sheet->setCellValue('O' . $row, 159);
    
    // Porcentajes - Corregido fórmulas
    $sheet->setCellValue('P' . $row, '=M' . $row . '/O' . $row); // Porcentaje de horas actuales
    $sheet->setCellValue('Q' . $row, '=N' . $row . '/O' . $row); // Porcentaje de horas reales
    $sheet->setCellValue('R' . $row, '=1-(N' . $row . '/O' . $row . ')'); // Porcentaje regresivo
    
    $lastRow = $row; // Actualizar la última fila
    $row++;
}

// Fusionar celdas de encabezados
$sheet->mergeCells('A1:A2'); // Número de Identificación
$sheet->mergeCells('B1:B2'); // Nombre del Estudiante
$sheet->mergeCells('C1:C2'); // Programa
$sheet->mergeCells('D1:F1'); // Técnico 
$sheet->mergeCells('G1:I1'); // Inglés
$sheet->mergeCells('J1:L1'); // Habilidades
$sheet->mergeCells('M1:O1'); // TOTALES
$sheet->mergeCells('P1:R1'); // PORCENTAJES

// Estilo para encabezados
$headerStyle = [
    'font' => [
        'bold' => true,
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'CCCCCC'],
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
        ],
    ],
];

// Aplicar estilos
$sheet->getStyle('A1:R2')->applyFromArray($headerStyle);

// Aplicar formato de porcentaje a las columnas P, Q y R
if ($lastRow >= 3) { // Solo si hay datos
    $sheet->getStyle('P3:R' . $lastRow)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00); // Formato con 2 decimales
}

// Autoajustar columnas
foreach(range('A','R') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Crear segunda hoja para estadísticas
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->getSheet(1);
$sheet2->setTitle('Conteo Bootcamps');

// Establecer títulos
$sheet2->setCellValue('A1', 'Bootcamp');
$sheet2->setCellValue('B1', 'Inscritos');

// Consulta SQL para obtener bootcamps únicos y su conteo
$sqlBootcamps = "SELECT bootcamp_name, COUNT(*) as total 
                 FROM groups 
                 GROUP BY bootcamp_name 
                 ORDER BY bootcamp_name";
                 
$resultBootcamps = $conn->query($sqlBootcamps);

$row = 2; // Comenzar datos en fila 2
$totalInscritos = 0; // Variable para el total

while($bootcampData = $resultBootcamps->fetch_assoc()) {
    $sheet2->setCellValue('A' . $row, $bootcampData['bootcamp_name']);
    $sheet2->setCellValue('B' . $row, $bootcampData['total']);
    $totalInscritos += $bootcampData['total']; // Sumar al total
    $row++;
}

// Añadir fila de total
$sheet2->setCellValue('A' . $row, 'TOTAL INSCRITOS');
$sheet2->setCellValue('B' . $row, $totalInscritos);

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

// Aplicar estilos
$sheet2->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet2->getStyle('A' . $row . ':B' . $row)->applyFromArray($totalStyle);
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);

// Crear tercera hoja
$spreadsheet->createSheet();
$sheet3 = $spreadsheet->getSheet(2);
$sheet3->setTitle('Hoja 3');

// Limpiar cualquier salida anterior
ob_end_clean();

// Asegurarse de que el archivo se guarde correctamente al final
try {
    $writer = new Xlsx($spreadsheet);
    $filename = 'Reporte_Horas_' . date('Y-m-d_H-i-s') . '.xlsx';
    
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