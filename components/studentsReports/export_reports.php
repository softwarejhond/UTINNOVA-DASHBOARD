<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Cabeceras
$sheet->setCellValue('A1', 'ID Reporte');
$sheet->setCellValue('B1', 'Número ID');
$sheet->setCellValue('C1', 'Nombre completo');
$sheet->setCellValue('D1', 'Código Curso');
$sheet->setCellValue('E1', 'Grupo');
$sheet->setCellValue('F1', 'Gestión');
$sheet->setCellValue('G1', 'Estado');
$sheet->setCellValue('H1', 'Responsable');
$sheet->setCellValue('I1', 'Fecha registro');
$sheet->setCellValue('J1', 'Gestión a realizar');
$sheet->setCellValue('K1', 'Resultado de la gestión');
$sheet->setCellValue('L1', 'Estado gestión');
$sheet->setCellValue('M1', 'Responsable gestión');
$sheet->setCellValue('N1', 'Fecha gestión');

$sql = "
SELECT 
    sr.id,
    sr.number_id,
    CONCAT_WS(' ', ur.first_name, ur.second_name, ur.first_last, ur.second_last) AS nombre_completo,
    sr.code,
    sr.grupo,
    sr.gestion,
    sr.status,
    u.nombre AS nombre_responsable,
    sr.fecha_registro
FROM student_reports sr
LEFT JOIN user_register ur ON sr.number_id = ur.number_id
LEFT JOIN users u ON sr.responsable = u.username
ORDER BY sr.fecha_registro DESC
";
$result = $conn->query($sql);
$row = 2;

if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        // Consulta SOLO la última gestión asociada a este reporte
        $sqlGest = "SELECT * FROM gestiones_reportes WHERE id_reporte = ? ORDER BY fecha_gestion DESC LIMIT 1";
        $stmtGest = $conn->prepare($sqlGest);
        $stmtGest->bind_param("i", $data['id']);
        $stmtGest->execute();
        $resGest = $stmtGest->get_result();
        $g = $resGest->fetch_assoc();
        $stmtGest->close();

        $sheet->setCellValue('A' . $row, $data['id']);
        $sheet->setCellValue('B' . $row, $data['number_id']);
        $sheet->setCellValue('C' . $row, $data['nombre_completo']);
        $sheet->setCellValue('D' . $row, $data['code']);
        $sheet->setCellValue('E' . $row, $data['grupo']);
        $sheet->setCellValue('F' . $row, $data['gestion']);
        $sheet->setCellValue('G' . $row, $data['status']);
        $sheet->setCellValue('H' . $row, $data['nombre_responsable']);
        $sheet->setCellValue('I' . $row, date('d/m/Y H:i:s', strtotime($data['fecha_registro'])));
        if ($g) {
            $sheet->setCellValue('J' . $row, $g['gestion_a_realizar']);
            $sheet->setCellValue('K' . $row, $g['resultado_gestion']);
            $sheet->setCellValue('L' . $row, $g['status']);
            $sheet->setCellValue('M' . $row, $g['responsable']);
            $sheet->setCellValue('N' . $row, date('d/m/Y H:i:s', strtotime($g['fecha_gestion'])));
        } else {
            $sheet->setCellValue('J' . $row, '');
            $sheet->setCellValue('K' . $row, '');
            $sheet->setCellValue('L' . $row, '');
            $sheet->setCellValue('M' . $row, '');
            $sheet->setCellValue('N' . $row, '');
        }
        $row++;
    }
}

foreach(range('A','N') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

$sheet->getStyle('A1:N1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF008080');
$sheet->getStyle('A1:N1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle('A1:N' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="reportes.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>