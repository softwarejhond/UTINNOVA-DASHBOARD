<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si se solicita la exportación
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportDataToExcel($conn);
    exit;
}

function exportDataToExcel($conn)
{
    // Si se pasa el parámetro id, filtrar por el estudiante en pantalla
    if (isset($_GET['id'])) {
        $number_id = $_GET['id'];
        $sql = "SELECT cl.*, 
                       ur.first_name, ur.second_name, ur.first_last, ur.second_last,
                       adv.name as advisor_name
                FROM contact_log cl
                LEFT JOIN user_register ur ON cl.number_id = ur.number_id
                LEFT JOIN advisors adv ON cl.idAdvisor = adv.idAdvisor
                WHERE cl.number_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $number_id);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Consulta para exportar todos los registros (si no se especifica id)
        $sql = "SELECT cl.*, 
                       ur.first_name, ur.second_name, ur.first_last, ur.second_last,
                       adv.name as advisor_name
                FROM contact_log cl
                LEFT JOIN user_register ur ON cl.number_id = ur.number_id
                LEFT JOIN advisors adv ON cl.idAdvisor = adv.idAdvisor";
        $result = $conn->query($sql);
    }
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'C.C' => $row['number_id'],
                'Estudiante' => $row['first_name'] . ' ' . $row['second_name'] . ' ' . $row['first_last'] . ' ' . $row['second_last'],
                'ID de Asesor' => $row['idAdvisor'],
                'Asesor' => $row['advisor_name'],
                'Detalles' => $row['details'],
                'Contacto Establecido' => $row['contact_established'] ? 'Sí' : 'No',
                'Continúa Interesado' => $row['continues_interested'] ? 'Sí' : 'No',
                'Observaciones' => $row['observation'],
                'Fecha de Contacto' => $row['contact_date']
            ];
        }
    }
    
    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Contact Logs');
    
    // Encabezados
    $headers = array_keys($data[0] ?? []);
    $sheet->fromArray($headers, NULL, 'A1');
    
    // Datos
    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), NULL, "A{$rowIndex}");
        $rowIndex++;
    }
    
    // Estilo para encabezados
    $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
    $sheet->getStyle('A1:' . $lastColumn . '1')
          ->getFill()->setFillType(Fill::FILL_SOLID)
          ->getStartColor()->setARGB('FFD3D3D3');
    
    $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);
    
    foreach ($headers as $colIndex => $headerText) {
        $column = Coordinate::stringFromColumnIndex($colIndex + 1);
        $width = mb_strlen($headerText) + 2;
        $sheet->getColumnDimension($column)->setWidth($width);
    }
    
    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="contact_logs_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
