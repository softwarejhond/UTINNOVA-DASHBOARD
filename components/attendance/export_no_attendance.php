<?php
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Limpiar cualquier salida anterior
ob_clean();

// Consulta SQL modificada para incluir cursos
$sql = "SELECT 
            g.number_id,
            g.full_name,
            COUNT(ar.id) as total_ausencias,
            GROUP_CONCAT(DISTINCT CONCAT(c.code, ' - ', c.name) SEPARATOR ', ') as courses
        FROM groups g
        INNER JOIN attendance_records ar ON g.number_id = ar.student_id
        LEFT JOIN courses c ON ar.course_id = c.code
        GROUP BY g.number_id, g.full_name
        HAVING SUM(CASE WHEN ar.attendance_status != 'ausente' THEN 1 ELSE 0 END) = 0
        ORDER BY g.full_name";

$result = $conn->query($sql);

// Crear nuevo spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Encabezados
$sheet->setCellValue('A1', 'Documento');
$sheet->setCellValue('B1', 'Nombre Completo');
$sheet->setCellValue('C1', 'Cursos');
$sheet->setCellValue('D1', 'Total Ausencias');

// Dar formato a los encabezados
$sheet->getStyle('A1:D1')->getFont()->setBold(true);
$sheet->getStyle('A1:D1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
$sheet->getStyle('A1:D1')->getFill()->getStartColor()->setRGB('D9D9D9');

// Datos
$row = 2;
while($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['number_id']);
    $sheet->setCellValue('B' . $row, $data['full_name']);
    $sheet->setCellValue('C' . $row, $data['courses']);
    $sheet->setCellValue('D' . $row, $data['total_ausencias']);
    $row++;
}

// Ajustar anchos de columna
$sheet->getColumnDimension('A')->setWidth(15);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(50);
$sheet->getColumnDimension('D')->setWidth(15);

// Asegurarse de que no hay salida previa
if (ob_get_length()) ob_end_clean();

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="listado_sin_asistencia.xlsx"');
header('Cache-Control: max-age=0');

// Crear archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;