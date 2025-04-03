<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Consulta para obtener los datos
$sql = "SELECT 
    ar.student_id,
    g.full_name,
    ur.email,
    ur.first_phone,
    GROUP_CONCAT(DISTINCT CONCAT(c.code, ' - ', c.name) SEPARATOR ', ') as courses,
    COUNT(ar.id) as total_faltas
FROM attendance_records ar
JOIN groups g ON ar.student_id = g.number_id
LEFT JOIN user_register ur ON g.number_id = ur.number_id
LEFT JOIN courses c ON ar.course_id = c.code
WHERE ar.attendance_status = 'ausente'
GROUP BY ar.student_id
HAVING COUNT(ar.id) >= 3
ORDER BY total_faltas DESC";

$result = $conn->query($sql);

// Crear nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar encabezados
$sheet->setCellValue('A1', 'ID Estudiante');
$sheet->setCellValue('B1', 'Nombre Completo');
$sheet->setCellValue('C1', 'Correo Electrónico');
$sheet->setCellValue('D1', 'Teléfono');
$sheet->setCellValue('E1', 'Cursos en los que tiene faltas');
$sheet->setCellValue('F1', 'Total Faltas');

// Dar formato a los encabezados
$sheet->getStyle('A1:F1')->getFont()->setBold(true);
$sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
$sheet->getStyle('A1:F1')->getFill()->getStartColor()->setRGB('D9D9D9');

// Llenar datos
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['student_id']);
    $sheet->setCellValue('B' . $row, $data['full_name']);
    $sheet->setCellValue('C' . $row, $data['email']);
    $sheet->setCellValue('D' . $row, $data['first_phone']);
    $sheet->setCellValue('E' . $row, $data['courses']);
    $sheet->setCellValue('F' . $row, $data['total_faltas']);
    $row++;
}

// Ajustar ancho de columnas
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Configurar headers para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="listado_ausentes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;