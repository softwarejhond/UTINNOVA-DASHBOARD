<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

$spreadsheet = new Spreadsheet();

// Primera hoja - Resumen
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Resumen');

// Encabezados del resumen
$sheet->setCellValue('A1', 'Fecha');
$sheet->setCellValue('B1', 'Asunto');
$sheet->setCellValue('C1', 'Enviado por');
$sheet->setCellValue('D1', 'Desde');
$sheet->setCellValue('E1', 'Total');
$sheet->setCellValue('F1', 'Exitosos');
$sheet->setCellValue('G1', 'Fallidos');

// Estilo para encabezados
$headerStyle = [
    'font' => ['bold' => true],
    'fill' => [
        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
        'startColor' => ['rgb' => '4A148C']
    ],
    'font' => ['color' => ['rgb' => 'FFFFFF']]
];
$sheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// Segunda hoja - Detalles
$detailSheet = $spreadsheet->createSheet();
$detailSheet->setTitle('Detalles');

// Encabezados de detalles
$detailSheet->setCellValue('A1', 'ID');
$detailSheet->setCellValue('B1', 'Fecha');
$detailSheet->setCellValue('C1', 'Asunto');
$detailSheet->setCellValue('D1', 'Contenido');
$detailSheet->setCellValue('E1', 'Enviado por');
$detailSheet->setCellValue('F1', 'Destinatarios');
$detailSheet->setCellValue('G1', 'Estado');

$detailSheet->getStyle('A1:G1')->applyFromArray($headerStyle);

// Obtener datos
$sql = "SELECT h.*, GROUP_CONCAT(
            CONCAT(r.recipient_email, ' (', 
                  CASE r.status 
                      WHEN 'success' THEN 'Enviado'
                      ELSE 'Fallido'
                  END, ')'
            ) SEPARATOR '\n'
        ) as recipients_list
        FROM email_history h
        LEFT JOIN email_recipients r ON h.id = r.email_id
        GROUP BY h.id
        ORDER BY h.created_at DESC";

$result = mysqli_query($conn, $sql);
$row = 2;
$detailRow = 2;

while ($data = mysqli_fetch_assoc($result)) {
    // Llenar hoja de resumen
    $sheet->setCellValue('A' . $row, date('d/m/Y H:i', strtotime($data['created_at'])));
    $sheet->setCellValue('B' . $row, $data['subject']);
    $sheet->setCellValue('C' . $row, $data['sent_by']);
    $sheet->setCellValue('D' . $row, $data['sent_from'] === 'float' ? 'Editor Flotante' : 'Página Principal');
    $sheet->setCellValue('E' . $row, $data['recipients_count']);
    $sheet->setCellValue('F' . $row, $data['successful_count']);
    $sheet->setCellValue('G' . $row, $data['failed_count']);

    // Llenar hoja de detalles
    $detailSheet->setCellValue('A' . $detailRow, $data['id']);
    $detailSheet->setCellValue('B' . $detailRow, date('d/m/Y H:i', strtotime($data['created_at'])));
    $detailSheet->setCellValue('C' . $detailRow, $data['subject']);
    $detailSheet->setCellValue('D' . $detailRow, strip_tags($data['content'])); // Eliminar HTML
    $detailSheet->setCellValue('E' . $detailRow, $data['sent_by']);
    $detailSheet->setCellValue('F' . $detailRow, $data['recipients_list']);
    $detailSheet->setCellValue('G' . $detailRow, 
        "Total: {$data['recipients_count']}\n" .
        "Exitosos: {$data['successful_count']}\n" .
        "Fallidos: {$data['failed_count']}"
    );

    // Ajustar altura de fila para contenido largo
    $detailSheet->getRowDimension($detailRow)->setRowHeight(-1);
    
    $row++;
    $detailRow++;
}

// Autoajustar columnas en ambas hojas
foreach(range('A','G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $detailSheet->getColumnDimension($col)->setAutoSize(true);
}

// Ajustar formato de texto para contenido largo
$detailSheet->getStyle('D2:G' . ($detailRow-1))->getAlignment()
    ->setWrapText(true)
    ->setVertical(Alignment::VERTICAL_TOP);

// Configurar la primera hoja como activa
$spreadsheet->setActiveSheetIndex(0);

// Generar archivo
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="historial_correos_detallado.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');