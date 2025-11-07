<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Limpiar cualquier salida anterior
ob_clean();

// Función para reordenar nombres
function reorderName($fullName) {
    if (empty($fullName) || $fullName == 'NOMBRE NO DISPONIBLE') {
        return 'NOMBRE NO DISPONIBLE';
    }
    
    $parts = explode(' ', trim($fullName));
    $count = count($parts);
    
    switch ($count) {
        case 2: // Nombre Apellido
            return $parts[1] . ' ' . $parts[0];
        case 3: // Nombre Apellido1 Apellido2 OR Nombre1 Nombre2 Apellido
            return $parts[1] . ' ' . $parts[2] . ' ' . $parts[0];
        case 4: // Nombre1 Nombre2 Apellido1 Apellido2
            return $parts[2] . ' ' . $parts[3] . ' ' . $parts[0] . ' ' . $parts[1];
        default:
            return $fullName; // Si no coincide con los casos, devolver original
    }
}

// Consulta para obtener todos los datos del personal administrativo con sus asignaciones
$sql = "SELECT 
    fa.username as cedula,
    u.nombre,
    fa.contract_role as cargo,
    fa.filing_number as radicado_aprobacion,
    fa.filing_date as fecha_radicado_aprobacion
FROM filing_assignments fa
LEFT JOIN users u ON fa.username = u.username
WHERE u.rol NOT IN (5,7,8)
ORDER BY fa.username";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Error en la consulta: " . mysqli_error($conn));
}

// Crear nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar título del documento
$sheet->setCellValue('A1', "Reporte Personal Administrativo");
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Fecha de generación: " . date('Y-m-d H:i:s'));
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Configurar encabezados
$sheet->setCellValue('A4', 'Apellidos y Nombres');
$sheet->setCellValue('B4', 'Cédula');
$sheet->setCellValue('C4', 'Cargo');
$sheet->setCellValue('D4', 'Radicado Aprobación');
$sheet->setCellValue('E4', 'Fecha Radicado Aprobación');

// Dar formato a los encabezados
$headerRange = 'A4:E4';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID);
$sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('D9D9D9');

// Llenar datos
$row = 5;
$hasData = false;

while ($data = mysqli_fetch_assoc($result)) {
    $hasData = true;
    
    // Reordenar el nombre
    $nombreReordenado = reorderName(strtoupper($data['nombre'] ?? ''));
    
    $sheet->setCellValue('A' . $row, $nombreReordenado);
    $sheet->setCellValue('B' . $row, $data['cedula']);
    $sheet->setCellValue('C' . $row, $data['cargo'] ?? 'No disponible');
    $sheet->setCellValue('D' . $row, $data['radicado_aprobacion'] ?? 'No disponible');
    $sheet->setCellValue('E' . $row, $data['fecha_radicado_aprobacion'] ?? 'No disponible');
    
    // Alternar colores de filas para mejor legibilidad
    if ($row % 2 == 0) {
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle("A{$row}:E{$row}")->getFill()->getStartColor()->setRGB('F8F9FA');
    }
    
    $row++;
}

// Si no hay datos, mostrar mensaje
if (!$hasData) {
    $sheet->setCellValue('A5', 'No se encontraron datos de personal administrativo con asignaciones');
    $sheet->mergeCells('A5:E5');
    $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('A5')->getFont()->setItalic(true);
}

// Agregar información adicional
$sheet->setCellValue('A' . ($row + 1), "Reporte generado para todo el personal administrativo registrado");
$sheet->mergeCells('A' . ($row + 1) . ':E' . ($row + 1));
$sheet->getStyle('A' . ($row + 1))->getFont()->setItalic(true)->setSize(10);

// Ajustar ancho de columnas
foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Asegurarse de que no hay salida previa
if (ob_get_length()) ob_end_clean();

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte_personal_administrativo_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

// Crear el writer después de los headers
$writer = new Xlsx($spreadsheet);

// Guardar directamente a PHP output
$writer->save('php://output');
exit();
?>