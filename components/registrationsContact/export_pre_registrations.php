<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Cabeceras
$sheet->setCellValue('A1', 'Tipo ID');
$sheet->setCellValue('B1', 'Número ID');
$sheet->setCellValue('C1', 'Nombre completo');
$sheet->setCellValue('D1', 'Email');
$sheet->setCellValue('E1', 'Email alterno');
$sheet->setCellValue('F1', 'Teléfono 1');
$sheet->setCellValue('G1', 'Teléfono 2');
$sheet->setCellValue('H1', 'Sede');
$sheet->setCellValue('I1', 'Programa');
$sheet->setCellValue('J1', 'Horario');
$sheet->setCellValue('K1', 'Requisitos');
$sheet->setCellValue('L1', 'Políticas');
$sheet->setCellValue('M1', 'Fecha de registro');

$query = "SELECT * FROM pre_registrations ORDER BY created_at DESC";
$result = $conn->query($query);
$row = 2;

if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $nombreCompleto = trim(
            ($data['first_name'] ?? '') . ' ' .
            (($data['second_name'] ?? '') ? ($data['second_name'] . ' ') : '') .
            ($data['first_last'] ?? '') . ' ' .
            ($data['second_last'] ?? '')
        );
        $sheet->setCellValue('A' . $row, $data['type_id'] ?: '-');
        $sheet->setCellValue('B' . $row, $data['number_id'] ?: '-');
        $sheet->setCellValue('C' . $row, $nombreCompleto ?: '-');
        $sheet->setCellValue('D' . $row, $data['email'] ?: '-');
        $sheet->setCellValue('E' . $row, $data['email2'] ?: '-');
        $sheet->setCellValue('F' . $row, $data['phone1'] ?: '-');
        $sheet->setCellValue('G' . $row, $data['phone2'] ?: '-');
        $sheet->setCellValue('H' . $row, $data['sede_name'] ?: '-');
        $sheet->setCellValue('I' . $row, $data['programa'] ?: '-');
        $sheet->setCellValue('J' . $row, $data['horario'] ?: '-');
        $sheet->setCellValue('K' . $row, $data['accept_requirements'] ? 'Sí' : 'No');
        $sheet->setCellValue('L' . $row, $data['accept_data_policies'] ? 'Sí' : 'No');
        $sheet->setCellValue('M' . $row, date('d/m/Y', strtotime($data['created_at'])));
        $row++;
    }
}

foreach(range('A','M') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

$sheet->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF008080');
$sheet->getStyle('A1:M1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
$sheet->getStyle('A1:M' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="pre_registros.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>