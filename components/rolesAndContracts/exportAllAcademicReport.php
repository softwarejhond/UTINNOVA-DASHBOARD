<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Limpiar cualquier salida anterior
ob_clean();

// Consulta para obtener todos los datos del personal académico con sus asignaciones
$sql = "SELECT 
    fa.username as cedula,
    u.nombre,
    fa.filing_number as radicado_aprobacion,
    fa.filing_date as fecha_radicado_aprobacion,
    fa.contract_role as rol,
    'Sin asignación específica' as curso_asignado
FROM filing_assignments fa
LEFT JOIN users u ON fa.username = u.username
WHERE u.rol IN (5,7,8)
ORDER BY fa.username";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

// Crear nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar título del documento
$sheet->setCellValue('A1', "Reporte Completo de Personal Académico");
$sheet->mergeCells('A1:F1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Fecha de generación: " . date('Y-m-d H:i:s'));
$sheet->mergeCells('A2:F2');
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Configurar encabezados
$sheet->setCellValue('A4', 'Cédula');
$sheet->setCellValue('B4', 'Nombre');
$sheet->setCellValue('C4', 'Radicado Aprobación');
$sheet->setCellValue('D4', 'Fecha Radicado Aprobación');
$sheet->setCellValue('E4', 'Rol');
$sheet->setCellValue('F4', 'Curso Asignado');

// Dar formato a los encabezados
$headerRange = 'A4:F4';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID);
$sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('D9D9D9');

// Llenar datos
$row = 5;
$hasData = false;

while ($data = mysqli_fetch_assoc($result)) {
    $hasData = true;
    
    $sheet->setCellValue('A' . $row, $data['cedula']);
    $sheet->setCellValue('B' . $row, strtoupper($data['nombre'] ?? 'Nombre no disponible'));
    $sheet->setCellValue('C' . $row, $data['radicado_aprobacion'] ?? 'No disponible');
    $sheet->setCellValue('D' . $row, $data['fecha_radicado_aprobacion'] ?? 'No disponible');
    $sheet->setCellValue('E' . $row, $data['rol'] ?? 'No disponible');
    $sheet->setCellValue('F' . $row, $data['curso_asignado']);
    
    // Alternar colores de filas para mejor legibilidad
    if ($row % 2 == 0) {
        $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle("A{$row}:F{$row}")->getFill()->getStartColor()->setRGB('F8F9FA');
    }
    
    $row++;
}

// Si no hay datos, mostrar mensaje
if (!$hasData) {
    $sheet->setCellValue('A5', 'No se encontraron datos de personal académico con asignaciones');
    $sheet->mergeCells('A5:F5');
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A5')->getFont()->setItalic(true);
}

// Agregar información adicional
$sheet->setCellValue('A' . ($row + 1), "Reporte generado para todo el personal académico registrado");
$sheet->mergeCells('A' . ($row + 1) . ':F' . ($row + 1));
$sheet->getStyle('A' . ($row + 1))->getFont()->setItalic(true)->setSize(10);

// Ajustar ancho de columnas
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Asegurarse de que no hay salida previa
if (ob_get_length()) ob_end_clean();

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_completo_personal_academico_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

// Crear el writer después de los headers
$writer = new Xlsx($spreadsheet);

// Guardar directamente a PHP output
$writer->save('php://output');
exit();
?>