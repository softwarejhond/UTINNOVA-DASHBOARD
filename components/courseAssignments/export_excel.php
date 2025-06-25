<?php
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require __DIR__ . '../../../vendor/autoload.php';
require '../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Crear nuevo objeto Spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Establecer encabezados
$headers = [
    'A' => 'ID',
    'B' => 'Nombre Completo',
    'C' => 'Departamento',
    'D' => 'Ciudad/Municipio',
    'E' => 'Email',
    'F' => 'Teléfono',
    'G' => 'Programa',
    'H' => 'Nivel',
    'I' => 'Modalidad',
    'J' => 'Lote',
    'K' => 'Estado',
    'L' => 'Bootcamp',
    'M' => 'Inglés Nivelador',
    'N' => 'English Code',
    'O' => 'Habilidades',
    'P' => 'Fecha Asignación',
    'Q' => 'Asignado por'
];

// Aplicar encabezados
foreach ($headers as $column => $header) {
    $sheet->setCellValue($column . '1', $header);
}

// Consulta para obtener los datos
$sql = "SELECT 
            ca.*,
            ur.first_name,
            ur.second_name,
            ur.first_last,
            ur.second_last,
            ur.email,
            ur.first_phone,
            ur.statusAdmin,
            ur.department,
            ur.municipality,
            ur.program,
            ur.level,
            ur.mode,
            ur.lote,
            d.departamento AS department_name,
            m.municipio AS municipality_name,
            u.nombre AS assigned_by_name  
        FROM 
            course_assignments ca
        JOIN 
            user_register ur ON ca.student_id = ur.number_id
        LEFT JOIN 
            departamentos d ON ur.department = d.id_departamento
        LEFT JOIN 
            municipios m ON ur.municipality = m.id_municipio AND m.departamento_id = d.id_departamento
        LEFT JOIN 
            users u ON ca.assigned_by = u.username  
        WHERE 
            ur.statusAdmin IN (1, 8)
        ORDER BY 
            ca.assigned_date DESC";

$result = $conn->query($sql);
$row = 2; // Comenzar desde la fila 2 después de los encabezados

// Mapeo de estados
$statusLabels = [
    '0' => 'Pendiente',
    '1' => 'Beneficiario',
    '2' => 'Rechazado',
    '3' => 'Matriculado',
    '4' => 'Sin contacto',
    '5' => 'En proceso',
    '6' => 'Culminó proceso',
    '7' => 'Inactivo',
    '8' => 'Beneficiario contrapartida'
];

while ($data = $result->fetch_assoc()) {
    // Construir nombre completo
    $fullName = $data['first_name'] . ' ' . $data['second_name'] . ' ' . 
                $data['first_last'] . ' ' . $data['second_last'];

    // Asignar valores a las celdas
    $sheet->setCellValue('A' . $row, $data['student_id']);
    $sheet->setCellValue('B' . $row, $fullName);
    $sheet->setCellValue('C' . $row, $data['department_name']);
    $sheet->setCellValue('D' . $row, $data['municipality_name']);
    $sheet->setCellValue('E' . $row, $data['email']);
    $sheet->setCellValue('F' . $row, $data['first_phone']);
    $sheet->setCellValue('G' . $row, $data['program']);
    $sheet->setCellValue('H' . $row, $data['level']);
    $sheet->setCellValue('I' . $row, $data['mode']);
    $sheet->setCellValue('J' . $row, $data['lote']);
    $sheet->setCellValue('K' . $row, $statusLabels[$data['statusAdmin']] ?? 'Desconocido');
    $sheet->setCellValue('L' . $row, $data['bootcamp_name']);
    $sheet->setCellValue('M' . $row, $data['leveling_english_name']);
    $sheet->setCellValue('N' . $row, $data['english_code_name']);
    $sheet->setCellValue('O' . $row, $data['skills_name']);
    $sheet->setCellValue('P' . $row, date('d/m/Y H:i', strtotime($data['assigned_date'])));
    $sheet->setCellValue('Q' . $row, $data['assigned_by_name']);
    
    $row++;
}

// Autoajustar ancho de columnas
foreach(range('A', 'Q') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Establecer estilo para encabezados
$sheet->getStyle('A1:Q1')->getFill()
    ->setFillType(Fill::FILL_SOLID)
    ->getStartColor()
    ->setARGB('FF808080');

// Establecer bordes para todas las celdas
$sheet->getStyle('A1:Q' . ($row - 1))
    ->getBorders()
    ->getAllBorders()
    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Establecer encabezados para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="asignaciones_cursos.xlsx"');
header('Cache-Control: max-age=0');

// Crear archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>