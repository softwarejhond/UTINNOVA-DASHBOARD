<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Limpiar cualquier salida anterior
ob_clean();

// Recuperar parámetros
$selectedCourses = isset($_POST['courses']) ? $_POST['courses'] : [];

if (empty($selectedCourses)) {
    die("No se han seleccionado cursos");
}

// Convertir array de códigos a string para la consulta
$courseCodes = implode(',', array_map('intval', $selectedCourses));

// Consulta para obtener los datos
$sql = "SELECT DISTINCT
    fa.username as cedula,
    u.nombre,
    fa.filing_number as radicado_aprobacion,
    fa.filing_date as fecha_radicado_aprobacion,
    fa.contract_role as rol,
    c.name as curso_asignado,
    c.code as codigo_curso
FROM courses c
LEFT JOIN filing_assignments fa ON (
    c.teacher = fa.username OR 
    c.mentor = fa.username OR 
    c.monitor = fa.username
)
LEFT JOIN users u ON fa.username = u.username
WHERE c.code IN ($courseCodes)
AND fa.username IS NOT NULL
ORDER BY fa.username, c.code";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

// Crear nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar título del documento
$sheet->setCellValue('A1', "Reporte de Personal Académico por Cursos");
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
    $sheet->setCellValue('F' . $row, $data['curso_asignado'] ?? 'No disponible');
    
    // Alternar colores de filas para mejor legibilidad
    if ($row % 2 == 0) {
        $sheet->getStyle("A{$row}:F{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle("A{$row}:F{$row}")->getFill()->getStartColor()->setRGB('F8F9FA');
    }
    
    $row++;
}

// Si no hay datos, mostrar mensaje
if (!$hasData) {
    $sheet->setCellValue('A5', 'No se encontraron datos para los cursos seleccionados');
    $sheet->mergeCells('A5:F5');
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A5')->getFont()->setItalic(true);
}

// Agregar información de cursos seleccionados
$coursesInfo = "Cursos seleccionados: " . implode(', ', $selectedCourses);
$sheet->setCellValue('A' . ($row + 1), $coursesInfo);
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
header('Content-Disposition: attachment; filename="reporte_personal_academico_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

// Crear el writer después de los headers
$writer = new Xlsx($spreadsheet);

// Guardar directamente a PHP output
$writer->save('php://output');
exit();
?>