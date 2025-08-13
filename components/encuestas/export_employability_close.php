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
$sheet->setCellValue('D1', 'Cohorte');
$sheet->setCellValue('E1', 'Nombre Completo');
$sheet->setCellValue('F1', 'Email');
$sheet->setCellValue('G1', 'Interés');
$sheet->setCellValue('H1', 'Fecha inicio formación');
$sheet->setCellValue('I1', 'Grupos poblacionales');
$sheet->setCellValue('J1', 'Nivel educativo');
$sheet->setCellValue('K1', 'Género');
$sheet->setCellValue('L1', 'Estado laboral');
$sheet->setCellValue('M1', '¿Trabaja en tech?');
$sheet->setCellValue('N1', 'Empleo conseguido por');
$sheet->setCellValue('O1', 'Tipo de contrato');
$sheet->setCellValue('P1', 'Nivel de ingresos');
$sheet->setCellValue('Q1', 'Rol actual');
$sheet->setCellValue('R1', 'Espacios ruta empleabilidad');
$sheet->setCellValue('S1', 'Utilidad del contenido');
$sheet->setCellValue('T1', 'Apoyo empleabilidad');
$sheet->setCellValue('U1', 'Satisfacción general');
$sheet->setCellValue('V1', 'Acción de mejora');
$sheet->setCellValue('W1', 'Fecha registro');

// Consulta para obtener datos
$sql = "SELECT * FROM employability_close ORDER BY created_at DESC";
$result = $conn->query($sql);
$row = 2;

if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $nombreCompleto = trim(
            ($data['first_name'] ?? '') . ' ' .
            (($data['second_name'] ?? '') ? ($data['second_name'] . ' ') : '') .
            ($data['first_last'] ?? '') . ' ' .
            ($data['second_last'] ?? '')
        );
        $nombreCompleto = $nombreCompleto ?: '-';

        $number_id = $data['number_id'] ?? '-';

        // Obtener lote
        $lote = '-';
        $sqlLote = "SELECT lote FROM user_register WHERE number_id = '$number_id' LIMIT 1";
        $resLote = $conn->query($sqlLote);
        if ($resLote && $resLote->num_rows > 0) {
            $loteRow = $resLote->fetch_assoc();
            $lote = $loteRow['lote'] ?: '-';
        }

        // Obtener cohorte y fecha inicio formación
        $cohorte = '-';
        $fechaInicioFormacion = '-';
        $sqlGroup = "SELECT id_bootcamp FROM groups WHERE number_id = '$number_id' LIMIT 1";
        $resGroup = $conn->query($sqlGroup);
        if ($resGroup && $resGroup->num_rows > 0) {
            $groupRow = $resGroup->fetch_assoc();
            $id_bootcamp = $groupRow['id_bootcamp'] ?? '';
            $sqlCohorte = "SELECT cohort, start_date FROM course_periods WHERE bootcamp_code = '$id_bootcamp' LIMIT 1";
            $resCohorte = $conn->query($sqlCohorte);
            if ($resCohorte && $resCohorte->num_rows > 0) {
                $cohorteRow = $resCohorte->fetch_assoc();
                $cohorte = $cohorteRow['cohort'] ?: '-';
                $fechaInicioFormacion = (
                    $cohorteRow['start_date'] &&
                    $cohorteRow['start_date'] !== '0000-00-00' &&
                    $cohorteRow['start_date'] !== '0000-00-00 00:00:00'
                ) ? date('d/m/Y', strtotime($cohorteRow['start_date'])) : '-';
            }
        }

        $fechaRegistro = (
            !empty($data['created_at']) &&
            $data['created_at'] !== '0000-00-00' &&
            $data['created_at'] !== '0000-00-00 00:00:00'
        ) ? date('d/m/Y', strtotime($data['created_at'])) : '-';

        $sheet->setCellValue('A' . $row, $data['typeID'] ?: '-');
        $sheet->setCellValue('B' . $row, $data['number_id'] ?: '-');
        $sheet->setCellValue('C' . $row, $lote);
        $sheet->setCellValue('D' . $row, $cohorte);
        $sheet->setCellValue('E' . $row, $nombreCompleto);
        $sheet->setCellValue('F' . $row, $data['email'] ?: '-');
        $sheet->setCellValue('G' . $row, $data['interest'] ?: '-');
        $sheet->setCellValue('H' . $row, $fechaInicioFormacion);
        $sheet->setCellValue('I' . $row, $data['grupos_poblacionales'] ?: '-');
        $sheet->setCellValue('J' . $row, $data['nivel_educativo'] ?: '-');
        $sheet->setCellValue('K' . $row, $data['gender'] ?: '-');
        $sheet->setCellValue('L' . $row, $data['current_employment_status'] ?: '-');
        $sheet->setCellValue('M' . $row, $data['current_tech_job'] ?: '-');
        $sheet->setCellValue('N' . $row, $data['employment_obtained_by'] ?: '-');
        $sheet->setCellValue('O' . $row, $data['contract_type'] ?: '-');
        $sheet->setCellValue('P' . $row, $data['income_level'] ?: '-');
        $sheet->setCellValue('Q' . $row, $data['current_job_role'] ?: '-');
        $sheet->setCellValue('R' . $row, $data['employment_route_spaces'] ?: '-');
        $sheet->setCellValue('S' . $row, ($data['content_usefulness'] ? $data['content_usefulness'] . ' de 5' : '-'));
        $sheet->setCellValue('T' . $row, ($data['employment_support'] ? $data['employment_support'] . ' de 5' : '-'));
        $sheet->setCellValue('U' . $row, $data['general_satisfaction'] ?: '-');
        $sheet->setCellValue('V' . $row, $data['improvement_action'] ?: '-');
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
header('Content-Disposition: attachment;filename="encuestas_cierre.xlsx"');
header('Cache-Control: max-age=0');

// Crear archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>