<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    $studentId = $_GET['studentId'] ?? null;

    if (!$studentId) {
        throw new Exception('ID de estudiante no proporcionado');
    }

    // Obtener información del estudiante desde la tabla groups
    $sqlStudent = "SELECT full_name, number_id FROM groups WHERE number_id = ?";
    $stmtStudent = mysqli_prepare($conn, $sqlStudent);
    mysqli_stmt_bind_param($stmtStudent, "s", $studentId);
    mysqli_stmt_execute($stmtStudent);
    $resultStudent = mysqli_stmt_get_result($stmtStudent);
    $studentData = mysqli_fetch_assoc($resultStudent);

    // Crear nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Información del estudiante en la parte superior
    $sheet->setCellValue('A1', 'Nombre del Estudiante:');
    $sheet->setCellValue('B1', $studentData['full_name']);
    $sheet->setCellValue('A2', 'Cédula:');
    $sheet->setCellValue('B2', $studentData['number_id']);

    // Estilo para la información del estudiante
    $sheet->getStyle('A1:A2')->getFont()->setBold(true);
    $sheet->getStyle('A1:B2')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('E8E8E8');

    // Establecer títulos de columnas empezando en fila 4
    $sheet->setCellValue('A4', 'Fecha de Clase');
    $sheet->setCellValue('B4', 'Contacto Establecido');
    $sheet->setCellValue('C4', 'Compromiso');
    $sheet->setCellValue('D4', 'Seguimiento');
    $sheet->setCellValue('E4', 'Retiro');
    $sheet->setCellValue('F4', 'Motivo de Retiro');
    $sheet->setCellValue('G4', 'Observación');
    $sheet->setCellValue('H4', 'Fecha de Registro');

    // Estilo para encabezados
    $sheet->getStyle('A4:H4')->getFont()->setBold(true);
    $sheet->getStyle('A4:H4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setRGB('CCCCCC');

    // Consulta SQL para el historial
    $sql = "SELECT 
                class_date,
                contact_established,
                compromiso,
                seguimiento_compromiso,
                retiro,
                motivo_retiro,
                observacion,
                creation_date
            FROM absence_log 
            WHERE number_id = ?
            ORDER BY class_date DESC";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $studentId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $row = 5; // Empezar después de los encabezados
    while ($data = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $row, date('d/m/Y', strtotime($data['class_date'])));
        $sheet->setCellValue('B' . $row, $data['contact_established'] == 1 ? 'Sí' : 'No');
        $sheet->setCellValue('C' . $row, $data['compromiso']);
        $sheet->setCellValue('D' . $row, $data['seguimiento_compromiso']);
        $sheet->setCellValue('E' . $row, $data['retiro']);
        $sheet->setCellValue('F' . $row, $data['motivo_retiro']);
        $sheet->setCellValue('G' . $row, $data['observacion']);
        $sheet->setCellValue('H' . $row, date('d/m/Y H:i', strtotime($data['creation_date'])));
        $row++;
    }

    // Ajustar anchos de columna
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Configurar cabeceras para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="Historial_Ausencias_' . $studentData['number_id'] . '.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    error_log("Error en exportar_historial.php: " . $e->getMessage());
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

mysqli_close($conn);
