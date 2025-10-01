<?php
// filepath: c:\xampp\htdocs\DASBOARD-ADMIN-MINTICS\components\scanDocs\exportVerificationResults.php
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="resultados_verificacion_' . date('Y-m-d_H-i-s') . '.xlsx"');
header('Cache-Control: max-age=0');

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

try {
    // Consultar datos
    $sql = "SELECT 
                dv.*,
                ur.first_name as db_first_name,
                ur.second_name as db_second_name,
                ur.first_last as db_first_last,
                ur.second_last as db_second_last,
                ur.birthdate as db_birthdate,
                ur.number_id as db_number_id
            FROM document_verification dv
            LEFT JOIN user_register ur ON dv.number_id = ur.number_id
            ORDER BY dv.verification_date DESC";

    $result = $conn->query($sql);

    // Crear nuevo archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Resultados Verificación');

    // Encabezados
    $headers = [
        'A' => 'Documento',
        'B' => 'Valid. Doc',
        'C' => 'Nombre 1',
        'D' => 'Valid. N1',
        'E' => 'Nombre 2',
        'F' => 'Valid. N2',
        'G' => 'Apellido 1',
        'H' => 'Valid. A1',
        'I' => 'Apellido 2',
        'J' => 'Valid. A2',
        'K' => 'Fecha Nac.',
        'L' => 'Valid. Fecha',
        'M' => '% Total',
        'N' => 'Estado',
        'O' => 'Mensaje',
        'P' => 'Fecha Verificación'
    ];

    // Escribir encabezados
    foreach ($headers as $col => $header) {
        $sheet->setCellValue($col . '1', $header);
    }

    // Estilo para encabezados
    $sheet->getStyle('A1:P1')->applyFromArray([
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '4CAF50']
        ],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
    ]);

    // Escribir datos
    $row = 2;
    if ($result && $result->num_rows > 0) {
        while ($data = $result->fetch_assoc()) {
            // Funciones para convertir valores
            $getValidIcon = function ($value) {
                return (int)$value === 1 ? '✅' : '❌';
            };

            $getStatus = function ($percentage) {
                $pct = (float)$percentage;
                if ($pct >= 80) return 'Válido';
                if ($pct >= 60) return 'Revisar';
                return 'Fallido';
            };

            // Llenar datos
            $sheet->setCellValue('A' . $row, $data['number_id']);
            $sheet->setCellValue('B' . $row, $getValidIcon($data['document_match']));
            $sheet->setCellValue('C' . $row, $data['db_first_name'] ?: '-');
            $sheet->setCellValue('D' . $row, $getValidIcon($data['name1_match']));
            $sheet->setCellValue('E' . $row, $data['db_second_name'] ?: '-');
            $sheet->setCellValue('F' . $row, $getValidIcon($data['name2_match']));
            $sheet->setCellValue('G' . $row, $data['db_first_last'] ?: '-');
            $sheet->setCellValue('H' . $row, $getValidIcon($data['lastname1_match']));
            $sheet->setCellValue('I' . $row, $data['db_second_last'] ?: '-');
            $sheet->setCellValue('J' . $row, $getValidIcon($data['lastname2_match']));
            $sheet->setCellValue('K' . $row, $data['db_birthdate'] ?: 'No encontrado');
            $sheet->setCellValue('L' . $row, $getValidIcon($data['birthdate_match']));
            $sheet->setCellValue('M' . $row, number_format($data['overall_match_percentage'], 1) . '%');
            $sheet->setCellValue('N' . $row, $getStatus($data['overall_match_percentage']));
            $sheet->setCellValue('O' . $row, $data['verification_message']);
            $sheet->setCellValue('P' . $row, $data['verification_date']);

            $row++;
        }
    }

    // Ajustar ancho de columnas
    foreach (range('A', 'P') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Agregar filtros
    $sheet->setAutoFilter('A1:P' . ($row - 1));

    // Crear writer y descargar
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    // Si hay error, devolver JSON en lugar de Excel
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error al generar Excel: ' . $e->getMessage()]);
}
