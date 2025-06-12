<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Corregir ruta del autoload
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Crear nueva instancia de Spreadsheet
$spreadsheet = new Spreadsheet();

// Nombres de los estados
$estados = [
    1 => 'Pendiente',
    2 => 'En proceso',
    3 => 'Atendido',
    4 => 'Cerrado'
];

// Títulos de las columnas
$titulos = [
    'ID', 'Tipo', 'Asunto', 'Descripción', 'Fecha Registro', 
    'Nombre', 'Cédula', 'Email', 'Teléfono 1', 'Teléfono 2', 
    'Programa', 'Lote', 'Fecha Creación', 'Fecha Resolución', 'Respuesta', 
    'ID Administrador', 'Nombre Asesor', 'Número Radicado', 'Estado'
];

// Para cada estado, crear una hoja y llenarla con datos
$sheetIndex = 0;
foreach ($estados as $estadoId => $estadoNombre) {
    // Crear una nueva hoja para cada estado
    if ($sheetIndex > 0) {
        $spreadsheet->createSheet();
    }
    $spreadsheet->setActiveSheetIndex($sheetIndex);
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle($estadoNombre);
    
    // Agregar títulos
    foreach ($titulos as $col => $titulo) {
        $sheet->setCellValue(chr(65 + $col) . '1', $titulo);
    }
    
    // Estilo para títulos
    $lastColumn = count($titulos);
    $headerRange = 'A1:' . chr(64 + $lastColumn) . '1';
    
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2C75B3'],
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ]);
    
    // Obtener datos de PQRS según el estado
    $sql = "SELECT p.*, u.nombre AS nombre_asesor, ur.program, ur.lote
            FROM pqr p
            LEFT JOIN users u ON p.admin_id = u.id
            LEFT JOIN user_register ur ON CAST(p.cedula AS UNSIGNED) = ur.number_id
            WHERE p.estado = $estadoId
            ORDER BY p.fecha_creacion DESC";
    
    $resultado = mysqli_query($conn, $sql);
    
    if (!$resultado) {
        die("Error en la consulta: " . mysqli_error($conn));
    }
    
    $row = 2; // Empezar desde la fila 2 (después de los títulos)
    
    while ($pqr = mysqli_fetch_assoc($resultado)) {
        $sheet->setCellValue('A' . $row, $pqr['id']);
        $sheet->setCellValue('B' . $row, $pqr['tipo']);
        $sheet->setCellValue('C' . $row, $pqr['asunto']);
        $sheet->setCellValue('D' . $row, $pqr['descripcion']);
        $sheet->setCellValue('E' . $row, $pqr['fecha_registro']);
        $sheet->setCellValue('F' . $row, $pqr['nombre']);
        $sheet->setCellValue('G' . $row, $pqr['cedula']);
        $sheet->setCellValue('H' . $row, $pqr['email']);
        $sheet->setCellValue('I' . $row, $pqr['telefono1']);
        $sheet->setCellValue('J' . $row, $pqr['telefono2']);
        $sheet->setCellValue('K' . $row, $pqr['program']);
        $sheet->setCellValue('L' . $row, $pqr['lote']);
        $sheet->setCellValue('M' . $row, $pqr['fecha_creacion']);
        $sheet->setCellValue('N' . $row, $pqr['fecha_resolucion']);
        $sheet->setCellValue('O' . $row, $pqr['respuesta']);
        $sheet->setCellValue('P' . $row, $pqr['admin_id']);
        $sheet->setCellValue('Q' . $row, $pqr['nombre_asesor']);
        $sheet->setCellValue('R' . $row, $pqr['numero_radicado']);
        $sheet->setCellValue('S' . $row, $estadoNombre);
        
        $row++;
    }
    
    // Ajustar ancho de columnas automáticamente
    foreach (range('A', chr(64 + $lastColumn)) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Formato para fechas
    $sheet->getStyle('E2:E' . ($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
    $sheet->getStyle('M2:M' . ($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
    $sheet->getStyle('N2:N' . ($row-1))->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DATETIME);
    
    $sheetIndex++;
}

// Establecer la primera hoja como activa
$spreadsheet->setActiveSheetIndex(0);

// Nombre del archivo
$filename = 'Reporte_PQRS_' . date('Y-m-d_H-i-s') . '.xlsx';

// Crear el writer
$writer = new Xlsx($spreadsheet);

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Limpiar cualquier salida previa
ob_end_clean();

// Guardar el archivo al output
$writer->save('php://output');
exit;

