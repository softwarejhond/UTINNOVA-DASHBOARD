<?php
require __DIR__ . '../../../vendor/autoload.php';
require  '../../controller/conexion.php';// Asegúrate de incluir la conexión a la BD


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Tipo ID');
$sheet->setCellValue('B1', 'Número ID');
$sheet->setCellValue('C1', 'Nombre Completo');
$sheet->setCellValue('D1', 'Teléfono'); // Nuevo encabezado
$sheet->setCellValue('E1', 'Email');
$sheet->setCellValue('F1', 'Email Institucional');
$sheet->setCellValue('G1', 'Contraseña');
$sheet->setCellValue('H1', 'ID Bootcamp');
$sheet->setCellValue('I1', 'Bootcamp');
$sheet->setCellValue('J1', 'ID Inglés Nivelatorio');
$sheet->setCellValue('K1', 'Inglés Nivelatorio');
$sheet->setCellValue('L1', 'ID English Code');
$sheet->setCellValue('M1', 'English Code');
$sheet->setCellValue('N1', 'ID Habilidades');
$sheet->setCellValue('O1', 'Habilidades');

// Query to get data
$query = "SELECT g.*, ur.first_phone 
          FROM groups g 
          LEFT JOIN user_register ur ON g.number_id = ur.number_id";
$stmt = $conn->query($query);
$row = 2;

while ($data = mysqli_fetch_assoc($stmt)) {
    $sheet->setCellValue('A' . $row, $data['type_id']);
    $sheet->setCellValue('B' . $row, $data['number_id']);
    $sheet->setCellValue('C' . $row, $data['full_name']);
    $sheet->setCellValue('D' . $row, $data['first_phone']); // Nueva columna
    $sheet->setCellValue('E' . $row, $data['email']);
    $sheet->setCellValue('F' . $row, $data['institutional_email']);
    $sheet->setCellValue('G' . $row, $data['password']);
    $sheet->setCellValue('H' . $row, $data['id_bootcamp']);
    $sheet->setCellValue('I' . $row, $data['bootcamp_name']);
    $sheet->setCellValue('J' . $row, $data['id_leveling_english']);
    $sheet->setCellValue('K' . $row, $data['leveling_english_name']);
    $sheet->setCellValue('L' . $row, $data['id_english_code']);
    $sheet->setCellValue('M' . $row, $data['english_code_name']);
    $sheet->setCellValue('N' . $row, $data['id_skills']);
    $sheet->setCellValue('O' . $row, $data['skills_name']);
    $row++;
}

// Auto size columns
foreach(range('A','O') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Set background color for header
$sheet->getStyle('A1:O1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
// Set border for all cells
$sheet->getStyle('A1:O' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="matriculados_moodle.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>