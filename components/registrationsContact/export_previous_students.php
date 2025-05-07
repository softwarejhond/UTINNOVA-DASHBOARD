<?php
require_once __DIR__ . '/../../controller/conexion.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Asegúrate de que PhpSpreadsheet esté instalado

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Establecer encabezados para la descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="listado_estudiantes_certificados.xlsx"');
header('Cache-Control: max-age=0');

// Obtener datos
$sql = "SELECT 
    u.number_id, 
    CONCAT(u.first_name, ' ', u.second_name, ' ', u.first_last, ' ', u.second_last) AS nombre_completo,
    u.email,
    u.first_phone,
    m.municipio,
    d.departamento,
    p.programa_de_formacion,
    p.eje_final,
    p.nivel_educacion,
    p.fecha_inicio
    FROM user_register u
    JOIN participantes p ON u.number_id = p.numero_documento
    LEFT JOIN municipios m ON u.municipality = m.id_municipio
    LEFT JOIN departamentos d ON u.department = d.id_departamento
    WHERE u.status = '1'
    ORDER BY u.first_name ASC";

$result = $conn->query($sql);

// Crear una nueva hoja de cálculo
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Establecer los encabezados
$sheet->setCellValue('A1', 'Número de Documento');
$sheet->setCellValue('B1', 'Nombre Completo');
$sheet->setCellValue('C1', 'Correo Electrónico');
$sheet->setCellValue('D1', 'Teléfono');
$sheet->setCellValue('E1', 'Municipio');
$sheet->setCellValue('F1', 'Departamento');
$sheet->setCellValue('G1', 'Programa de Formación');
$sheet->setCellValue('H1', 'Tema Final');
$sheet->setCellValue('I1', 'Nivel Educación');
$sheet->setCellValue('J1', 'Fecha Inicio');

// Estilos para el encabezado
$styleArray = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => [
            'rgb' => 'EFEFEF',
        ],
    ],
    'borders' => [
        'bottom' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];

$sheet->getStyle('A1:J1')->applyFromArray($styleArray);

// Llenar los datos
$row = 2;
if ($result && $result->num_rows > 0) {
    while ($data = $result->fetch_assoc()) {
        $sheet->setCellValue('A' . $row, $data['number_id']);
        $sheet->setCellValue('B' . $row, $data['nombre_completo']);
        $sheet->setCellValue('C' . $row, $data['email']);
        $sheet->setCellValue('D' . $row, $data['first_phone']);
        $sheet->setCellValue('E' . $row, $data['municipio']);
        $sheet->setCellValue('F' . $row, $data['departamento']);
        $sheet->setCellValue('G' . $row, $data['programa_de_formacion']);
        $sheet->setCellValue('H' . $row, $data['eje_final']);
        $sheet->setCellValue('I' . $row, $data['nivel_educacion']);
        $sheet->setCellValue('J' . $row, $data['fecha_inicio']);
        $row++;
    }
}

// Auto ajustar el ancho de las columnas
foreach(range('A', 'J') as $column) {
    $sheet->getColumnDimension($column)->setAutoSize(true);
}

// Crear el archivo Excel
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;