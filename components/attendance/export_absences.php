<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Limpiar cualquier salida anterior
ob_clean();

$sql = "WITH FaltasConsecutivas AS (
    SELECT DISTINCT student_id
    FROM (
        SELECT 
            student_id,
            class_date,
            LAG(class_date,1) OVER (PARTITION BY student_id ORDER BY class_date) as fecha1,
            LAG(class_date,2) OVER (PARTITION BY student_id ORDER BY class_date) as fecha2
        FROM attendance_records
        WHERE attendance_status = 'ausente'
    ) subconsulta
    WHERE DATEDIFF(class_date, fecha1) = 1 
    AND DATEDIFF(fecha1, fecha2) = 1
)
SELECT 
    g.number_id,
    g.full_name,
    ur.email,
    ur.first_phone,
    COUNT(DISTINCT ar.class_date) as total_faltas,
    GROUP_CONCAT(DISTINCT CONCAT(c.code, ' - ', c.name) SEPARATOR ', ') as courses
FROM FaltasConsecutivas fc
JOIN groups g ON fc.student_id = g.number_id
LEFT JOIN user_register ur ON g.number_id = ur.number_id
LEFT JOIN attendance_records ar ON g.number_id = ar.student_id AND ar.attendance_status = 'ausente'
LEFT JOIN courses c ON ar.course_id = c.code
GROUP BY g.number_id, g.full_name, ur.email, ur.first_phone
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
$sheet->setCellValue('E1', 'Cursos');
$sheet->setCellValue('F1', 'Total Faltas');

// Dar formato a los encabezados
$sheet->getStyle('A1:F1')->getFont()->setBold(true);
$sheet->getStyle('A1:F1')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
$sheet->getStyle('A1:F1')->getFill()->getStartColor()->setRGB('D9D9D9');

// Llenar datos
$row = 2;
while ($data = $result->fetch_assoc()) {
    $sheet->setCellValue('A' . $row, $data['number_id']);
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

// Asegurarse de que no hay salida previa
if (ob_get_length()) ob_end_clean();

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="listado_ausentes.xlsx"');
header('Cache-Control: max-age=0');

// Crear el writer después de los headers
$writer = new Xlsx($spreadsheet);

// Guardar directamente a PHP output
$writer->save('php://output');
exit();