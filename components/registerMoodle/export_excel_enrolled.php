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
$sheet->setCellValue('G1', 'Modalidad');
$sheet->setCellValue('H1', 'Lote');
$sheet->setCellValue('I1', 'Sede');
$sheet->setCellValue('J1', 'Contraseña');
$sheet->setCellValue('K1', 'ID Bootcamp');
$sheet->setCellValue('L1', 'Bootcamp');
$sheet->setCellValue('M1', 'ID Inglés Nivelatorio');
$sheet->setCellValue('N1', 'Inglés Nivelatorio');
$sheet->setCellValue('O1', 'ID English Code');
$sheet->setCellValue('P1', 'English Code');
$sheet->setCellValue('Q1', 'ID Habilidades');
$sheet->setCellValue('R1', 'Habilidades');
// Agrega el encabezado para Cohorte
$sheet->setCellValue('S1', 'Cohorte');

// Query to get data
// Cambia la consulta para incluir el LEFT JOIN con course_periods y obtener el cohort
$query = "SELECT 
            g.*, 
            ur.first_phone, 
            ur.lote, 
            cp.cohort AS course_cohort
          FROM groups g 
          LEFT JOIN user_register ur ON g.number_id = ur.number_id
          LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code";
$stmt = $conn->query($query);
$row = 2;

while ($data = mysqli_fetch_assoc($stmt)) {
    $sheet->setCellValue('A' . $row, $data['type_id']);
    $sheet->setCellValue('B' . $row, $data['number_id']);
    $sheet->setCellValue('C' . $row, str_replace(['Á','É','Í','Ó','Ú','á','é','í','ó','ú'], ['A','E','I','O','U','a','e','i','o','u'], mb_strtoupper($data['full_name'], 'UTF-8')));
    $sheet->setCellValue('D' . $row, str_replace('+57', '', $data['first_phone'])); // Nueva columna
    $sheet->setCellValue('E' . $row, $data['email']);
    $sheet->setCellValue('F' . $row, $data['institutional_email']);
    $sheet->setCellValue('G' . $row, $data['mode']);
    $sheet->setCellValue('H' . $row, $data['lote']);
    $sheet->setCellValue('I' . $row, $data['headquarters']);
    $sheet->setCellValue('J' . $row, $data['password']);
    $sheet->setCellValue('K' . $row, $data['id_bootcamp']);
    $sheet->setCellValue('L' . $row, $data['bootcamp_name']);
    $sheet->setCellValue('M' . $row, $data['id_leveling_english']);
    $sheet->setCellValue('N' . $row, $data['leveling_english_name']);
    $sheet->setCellValue('O' . $row, $data['id_english_code']);
    $sheet->setCellValue('P' . $row, $data['english_code_name']);
    $sheet->setCellValue('Q' . $row, $data['id_skills']);
    $sheet->setCellValue('R' . $row, $data['skills_name']);
    // Nueva columna para Cohorte
    $sheet->setCellValue('S' . $row, $data['course_cohort']);
    $row++;
}

// Auto size columns
foreach(range('A','S') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Set background color for header
$sheet->getStyle('A1:S1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
// Set border for all cells
$sheet->getStyle('A1:S' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="matriculados_moodle.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>