<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar cabeceras
$sheet->setCellValue('A1', 'Tipo ID');
$sheet->setCellValue('B1', 'Identificación');
$sheet->setCellValue('C1', 'Lote');
$sheet->setCellValue('D1', 'Nombre Completo');
$sheet->setCellValue('E1', 'Email');
$sheet->setCellValue('F1', 'Interés');
$sheet->setCellValue('G1', 'Fecha inicio formación');
$sheet->setCellValue('H1', 'Descripción personal');
$sheet->setCellValue('I1', 'Localidad');
$sheet->setCellValue('J1', 'Nivel educativo');
$sheet->setCellValue('K1', 'Género');
$sheet->setCellValue('L1', 'Experiencia laboral');
$sheet->setCellValue('M1', 'Estado laboral');
$sheet->setCellValue('N1', 'Experiencia tech');
$sheet->setCellValue('O1', 'Perfil laboral');
$sheet->setCellValue('P1', 'Años exp. tech');
$sheet->setCellValue('Q1', 'Último rol tech');
$sheet->setCellValue('R1', 'Conocimientos');
$sheet->setCellValue('S1', 'Habilidades digitales');
$sheet->setCellValue('T1', 'Habilidades blandas');
$sheet->setCellValue('U1', 'Redes profesionales');
$sheet->setCellValue('V1', 'Rol deseado');
$sheet->setCellValue('W1', 'Fecha registro');

// Consulta para obtener datos
$sql = "SELECT * FROM employability ORDER BY fecha_registro DESC";
$result = $conn->query($sql);
$row = 2;

if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $nombreCompleto = trim(
            $data['first_name'] . ' ' .
            ($data['second_name'] ? $data['second_name'] . ' ' : '') .
            $data['first_last'] . ' ' .
            $data['second_last']
        );

        $fechaInicio = $data['start_training_date'];
        if ($fechaInicio && $fechaInicio !== '0000-00-00' && $fechaInicio !== '0000-00-00 00:00:00') {
            $fechaInicio = date('d/m/Y', strtotime($fechaInicio));
        } else {
            $fechaInicio = '';
        }

        $fechaRegistro = $data['fecha_registro'];
        if ($fechaRegistro && $fechaRegistro !== '0000-00-00' && $fechaRegistro !== '0000-00-00 00:00:00') {
            $fechaRegistro = date('d/m/Y', strtotime($fechaRegistro));
        } else {
            $fechaRegistro = '';
        }

        $sheet->setCellValue('A' . $row, $data['typeID']);
        $sheet->setCellValue('B' . $row, $data['number_id']);
        $sheet->setCellValue('C' . $row, $data['lote']);
        $sheet->setCellValue('D' . $row, $nombreCompleto);
        $sheet->setCellValue('E' . $row, $data['email']);
        $sheet->setCellValue('F' . $row, $data['interest']);
        $sheet->setCellValue('G' . $row, $fechaInicio);
        $sheet->setCellValue('H' . $row, $data['personal_description']);
        $sheet->setCellValue('I' . $row, $data['localidad']);
        $sheet->setCellValue('J' . $row, $data['nivel_educativo']);
        $sheet->setCellValue('K' . $row, $data['gender']);
        $sheet->setCellValue('L' . $row, $data['work_experience']);
        $sheet->setCellValue('M' . $row, $data['current_employment_status']);
        $sheet->setCellValue('N' . $row, $data['tech_experience']);
        $sheet->setCellValue('O' . $row, $data['job_profile']);
        $sheet->setCellValue('P' . $row, $data['tech_experience_years']);
        $sheet->setCellValue('Q' . $row, $data['last_tech_role']);
        $sheet->setCellValue('R' . $row, $data['skills_knowledge']);
        $sheet->setCellValue('S' . $row, $data['digital_skills']);
        $sheet->setCellValue('T' . $row, $data['soft_skills']);
        $sheet->setCellValue('U' . $row, $data['professional_networks']);
        $sheet->setCellValue('V' . $row, $data['desired_role']);
        $sheet->setCellValue('W' . $row, $fechaRegistro);
        
        $row++;
    }
}

// Auto-ajustar columnas
foreach(range('A','W') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Establecer color de fondo para la cabecera
$sheet->getStyle('A1:W1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
$sheet->getStyle('A1:W1')->getFont()->setBold(true);
$sheet->getStyle('A1:W1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

// Establecer borde para todas las celdas
$sheet->getStyle('A1:W' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Establecer cabecera para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="encuestas_ingreso.xlsx"');
header('Cache-Control: max-age=0');

// Crear archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>