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
$sheet->setCellValue('C1', 'Nombre Completo');
$sheet->setCellValue('D1', 'Email');
$sheet->setCellValue('E1', 'Interés');
$sheet->setCellValue('F1', 'Fecha inicio formación');
$sheet->setCellValue('G1', 'Grupos poblacionales');
$sheet->setCellValue('H1', 'Nivel educativo');
$sheet->setCellValue('I1', 'Género');
$sheet->setCellValue('J1', 'Estado laboral');
$sheet->setCellValue('K1', '¿Trabaja en tech?');
$sheet->setCellValue('L1', 'Empleo conseguido por');
$sheet->setCellValue('M1', 'Tipo de contrato');
$sheet->setCellValue('N1', 'Nivel de ingresos');
$sheet->setCellValue('O1', 'Rol actual');
$sheet->setCellValue('P1', 'Espacios ruta empleabilidad');
$sheet->setCellValue('Q1', 'Utilidad del contenido');
$sheet->setCellValue('R1', 'Apoyo empleabilidad');
$sheet->setCellValue('S1', 'Satisfacción general');
$sheet->setCellValue('T1', 'Acción de mejora');
$sheet->setCellValue('U1', 'Fecha registro');

// Consulta para obtener datos
$sql = "SELECT * FROM employability_close ORDER BY created_at DESC";
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

        $fechaRegistro = $data['created_at'];
        if ($fechaRegistro && $fechaRegistro !== '0000-00-00' && $fechaRegistro !== '0000-00-00 00:00:00') {
            $fechaRegistro = date('d/m/Y', strtotime($fechaRegistro));
        } else {
            $fechaRegistro = '';
        }

        $sheet->setCellValue('A' . $row, $data['typeID']);
        $sheet->setCellValue('B' . $row, $data['number_id']);
        $sheet->setCellValue('C' . $row, $nombreCompleto);
        $sheet->setCellValue('D' . $row, $data['email']);
        $sheet->setCellValue('E' . $row, $data['interest']);
        $sheet->setCellValue('F' . $row, $fechaInicio);
        $sheet->setCellValue('G' . $row, $data['grupos_poblacionales']);
        $sheet->setCellValue('H' . $row, $data['nivel_educativo']);
        $sheet->setCellValue('I' . $row, $data['gender']);
        $sheet->setCellValue('J' . $row, $data['current_employment_status']);
        $sheet->setCellValue('K' . $row, $data['current_tech_job']);
        $sheet->setCellValue('L' . $row, $data['employment_obtained_by']);
        $sheet->setCellValue('M' . $row, $data['contract_type']);
        $sheet->setCellValue('N' . $row, $data['income_level']);
        $sheet->setCellValue('O' . $row, $data['current_job_role']);
        $sheet->setCellValue('P' . $row, $data['employment_route_spaces']);
        $sheet->setCellValue('Q' . $row, $data['content_usefulness'] . ' de 5');
        $sheet->setCellValue('R' . $row, $data['employment_support'] . ' de 5');
        $sheet->setCellValue('S' . $row, $data['general_satisfaction']);
        $sheet->setCellValue('T' . $row, $data['improvement_action']);
        $sheet->setCellValue('U' . $row, $fechaRegistro);
        
        $row++;
    }
}

// Auto-ajustar columnas
foreach(range('A','U') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Establecer color de fondo para la cabecera
$sheet->getStyle('A1:U1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
$sheet->getStyle('A1:U1')->getFont()->setBold(true);
$sheet->getStyle('A1:U1')->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

// Establecer borde para todas las celdas
$sheet->getStyle('A1:U' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Establecer cabecera para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="encuestas_cierre.xlsx"');
header('Cache-Control: max-age=0');

// Crear archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>