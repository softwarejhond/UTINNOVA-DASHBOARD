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

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Registro de Ausencias');

// Establecer encabezados
$sheet->setCellValue('A1', 'ID');
$sheet->setCellValue('B1', 'Número de Identificación');
$sheet->setCellValue('C1', 'Nombre del Estudiante');
$sheet->setCellValue('D1', 'Clase');
$sheet->setCellValue('E1', 'ID Asesor');
$sheet->setCellValue('F1', 'Nombre del Asesor');
$sheet->setCellValue('G1', 'Fecha de Clase');
$sheet->setCellValue('H1', 'Contacto Establecido');
$sheet->setCellValue('I1', 'Compromiso');
$sheet->setCellValue('J1', 'Seguimiento Compromiso');
$sheet->setCellValue('K1', 'Retiro');
$sheet->setCellValue('L1', 'Motivo de Retiro');
$sheet->setCellValue('M1', 'Observaciones');
$sheet->setCellValue('N1', 'Fecha de Registro');

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

// Aplicar estilos a los encabezados
$sheet->getStyle('A1:N1')->applyFromArray($headerStyle);

// Consulta SQL actualizada para obtener datos de ausencias con información de usuario y asesor
$sql = "SELECT a.*, 
        g.full_name AS student_name,
        u.nombre AS advisor_name
        FROM absence_log a
        LEFT JOIN groups g ON a.number_id = g.number_id
        LEFT JOIN users u ON a.id_advisor = u.username
        ORDER BY a.class_date DESC";

$result = $conn->query($sql);

$row = 2; // Comenzar datos en fila 2
while($data = $result->fetch_assoc()) {
    // Formateo de datos
    $contactoEstablecido = ($data['contact_established'] == 1) ? 'SÍ' : 'NO';
    
    // Cargar datos en el archivo
    $sheet->setCellValue('A' . $row, $data['id']);
    $sheet->setCellValue('B' . $row, $data['number_id']);
    $sheet->setCellValue('C' . $row, $data['student_name']);
    $sheet->setCellValue('D' . $row, $data['class_id']);
    $sheet->setCellValue('E' . $row, $data['id_advisor']);
    $sheet->setCellValue('F' . $row, $data['advisor_name']);
    $sheet->setCellValue('G' . $row, $data['class_date']);
    $sheet->setCellValue('H' . $row, $contactoEstablecido);
    $sheet->setCellValue('I' . $row, $data['compromiso']);
    $sheet->setCellValue('J' . $row, $data['seguimiento_compromiso']);
    $sheet->setCellValue('K' . $row, $data['retiro']);
    $sheet->setCellValue('L' . $row, $data['motivo_retiro']);
    $sheet->setCellValue('M' . $row, $data['observacion']);
    $sheet->setCellValue('N' . $row, $data['creation_date']);
    
    $row++;
}

// Aplicar formato de fecha a las columnas de fechas
$lastRow = $row - 1;
if ($lastRow >= 2) { // Solo si hay datos
    $sheet->getStyle('G2:G' . $lastRow)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_DATE_YYYYMMDD);
    $sheet->getStyle('N2:N' . $lastRow)->getNumberFormat()
          ->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
}

// Agregar bordes a todos los datos
$sheet->getStyle('A1:N' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Autoajustar columnas
foreach(range('A','N') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Crear segunda hoja para estadísticas
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->getSheet(1);
$sheet2->setTitle('Estadísticas de Ausencias');

// Establecer títulos en la segunda hoja
$sheet2->setCellValue('A1', 'Estadística');
$sheet2->setCellValue('B1', 'Cantidad');

// Consultas para estadísticas
$stats = [
    'Total de ausencias registradas' => "SELECT COUNT(*) AS total FROM absence_log",
    'Ausencias con contacto establecido' => "SELECT COUNT(*) AS total FROM absence_log WHERE contact_established = 1",
    'Ausencias sin contacto establecido' => "SELECT COUNT(*) AS total FROM absence_log WHERE contact_established = 0",
    'Estudiantes con retiro' => "SELECT COUNT(*) AS total FROM absence_log WHERE retiro = 'Retiro'",
    'Total de compromisos adquiridos' => "SELECT COUNT(*) AS total FROM absence_log WHERE compromiso IS NOT NULL AND compromiso != ''"
];

$row = 2;
foreach($stats as $name => $query) {
    $statResult = $conn->query($query);
    $statData = $statResult->fetch_assoc();
    
    $sheet2->setCellValue('A' . $row, $name);
    $sheet2->setCellValue('B' . $row, $statData['total']);
    $row++;
}

// Aplicar estilos a la segunda hoja
$sheet2->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet2->getColumnDimension('A')->setAutoSize(true);
$sheet2->getColumnDimension('B')->setAutoSize(true);

// Limpiar cualquier salida anterior
ob_end_clean();

// Asegurarse de que el archivo se guarde correctamente al final
try {
    $writer = new Xlsx($spreadsheet);
    $filename = 'Registro_Ausencias_' . date('Y-m-d_H-i-s') . '.xlsx';
    
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