<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Cache-Control: max-age=0');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido');
}

// Recibir los datos
$studentId = $_POST['student_id'] ?? '';
$studentName = $_POST['student_name'] ?? '';
$courseId = $_POST['course_id'] ?? '';
$historyData = json_decode($_POST['history_data'] ?? '[]', true);

if (empty($studentId) || empty($courseId)) {
    http_response_code(400);
    exit('Datos insuficientes');
}

try {
    // Obtener información del curso
    $courseInfo = null;
    try {
        // Intentar obtener el nombre del curso de la misma manera que getStudents.php
        // Buscar primero en bootcamp (técnico)
        $courseStmt = $conn->prepare("SELECT bootcamp_name as course_name FROM groups WHERE id_bootcamp = ? LIMIT 1");
        $courseStmt->bind_param("i", $courseId);
        $courseStmt->execute();
        $courseResult = $courseStmt->get_result();
        
        if ($courseRow = $courseResult->fetch_assoc()) {
            $courseInfo = $courseRow['course_name'];
        } else {
            // Intentar con inglés nivelado
            $courseStmt->close();
            $courseStmt = $conn->prepare("SELECT leveling_english_name as course_name FROM groups WHERE id_leveling_english = ? LIMIT 1");
            $courseStmt->bind_param("i", $courseId);
            $courseStmt->execute();
            $courseResult = $courseStmt->get_result();
            
            if ($courseRow = $courseResult->fetch_assoc()) {
                $courseInfo = $courseRow['course_name'];
            } else {
                // Intentar con english code
                $courseStmt->close();
                $courseStmt = $conn->prepare("SELECT english_code_name as course_name FROM groups WHERE id_english_code = ? LIMIT 1");
                $courseStmt->bind_param("i", $courseId);
                $courseStmt->execute();
                $courseResult = $courseStmt->get_result();
                
                if ($courseRow = $courseResult->fetch_assoc()) {
                    $courseInfo = $courseRow['course_name'];
                } else {
                    // Intentar con habilidades
                    $courseStmt->close();
                    $courseStmt = $conn->prepare("SELECT skills_name as course_name FROM groups WHERE id_skills = ? LIMIT 1");
                    $courseStmt->bind_param("i", $courseId);
                    $courseStmt->execute();
                    $courseResult = $courseStmt->get_result();
                    
                    if ($courseRow = $courseResult->fetch_assoc()) {
                        $courseInfo = $courseRow['course_name'];
                    }
                }
            }
        }
        
        $courseStmt->close();
    } catch (Exception $e) {
        // Si hay error, simplemente usar el ID del curso
        $courseInfo = 'Curso ID: ' . $courseId;
    }
    
    // Crear un nuevo objeto Spreadsheet
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Historial de Gestiones');
    
    // Establecer información del estudiante en el encabezado
    $sheet->setCellValue('A1', 'HISTORIAL DE GESTIONES');
    $sheet->setCellValue('A2', 'Estudiante: ' . $studentName);
    $sheet->setCellValue('A3', 'Documento: ' . $studentId);
    $sheet->setCellValue('A4', 'Curso: ' . ($courseInfo ?? 'ID ' . $courseId));
    $sheet->setCellValue('A5', 'Fecha de exportación: ' . date('d/m/Y H:i'));
    
    // Dar formato a la cabecera
    $sheet->getStyle('A1:A5')->getFont()->setBold(true);
    $sheet->getStyle('A1')->getFont()->setSize(16);
    $sheet->mergeCells('A1:J1');
    $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Establecer encabezados de tabla en la fila 7
    $headers = [
        'Fecha', 'Responsable', 'Requiere Subsanación', 'Observación Subsanación', 
        'Resuelta', 'Requiere Estrategia', 'Observación Estrategia', 
        'Estrategia Cumplida', 'Motivo de Retiro', 'Fecha de Retiro'
    ];
    
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . '7', $header);
        $sheet->getStyle($col . '7')->getFont()->setBold(true);
        $sheet->getStyle($col . '7')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('4BACC6'); // Color azul para encabezados
        $sheet->getStyle($col . '7')->getFont()->getColor()->setRGB('FFFFFF'); // Texto blanco
        $col++;
    }
    
    // Llenar datos
    $row = 8;
    foreach ($historyData as $item) {
        $col = 'A';
        
        // Fecha formateada
        $sheet->setCellValue($col++ . $row, $item['formatted_date'] ?? $item['created_at'] ?? '');
        
        // Responsable (nombre o username)
        $sheet->setCellValue($col++ . $row, $item['responsible_name'] ?? $item['responsible_username'] ?? 'N/A');
        
        // Requiere subsanación
        $sheet->setCellValue($col++ . $row, $item['requires_intervention'] ?? 'N/A');
        
        // Observación subsanación
        $sheet->setCellValue($col++ . $row, $item['intervention_observation'] ?? 'N/A');
        
        // Resuelta
        $sheet->setCellValue($col++ . $row, $item['is_resolved'] ?? 'N/A');
        
        // Requiere estrategia
        $sheet->setCellValue($col++ . $row, $item['requires_additional_strategy'] ?? 'N/A');
        
        // Observación estrategia
        $sheet->setCellValue($col++ . $row, $item['strategy_observation'] ?? 'N/A');
        
        // Estrategia cumplida
        $sheet->setCellValue($col++ . $row, $item['strategy_fulfilled'] ?? 'N/A');
        
        // Motivo de retiro
        $sheet->setCellValue($col++ . $row, $item['withdrawal_reason'] ?? 'N/A');
        
        // Fecha de retiro
        $withdrawalDate = !empty($item['withdrawal_date']) ? 
            date('d/m/Y', strtotime($item['withdrawal_date'])) : 'N/A';
        $sheet->setCellValue($col++ . $row, $withdrawalDate);
        
        // Añadir colores condicionales
        // Si requiere intervención es "Si"
        if (($item['requires_intervention'] ?? '') == 'Si') {
            $sheet->getStyle('C' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB9C'); // Amarillo claro
        }
        
        // Si está resuelto es "Si"
        if (($item['is_resolved'] ?? '') == 'Si') {
            $sheet->getStyle('E' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('C6EFCE'); // Verde claro
        }
        
        // Si requiere estrategia adicional es "Si"
        if (($item['requires_additional_strategy'] ?? '') == 'Si') {
            $sheet->getStyle('F' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFEB9C'); // Amarillo claro
        }
        
        // Si hay motivo de retiro
        if (!empty($item['withdrawal_reason']) && $item['withdrawal_reason'] != 'N/A') {
            $sheet->getStyle('I' . $row)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('FFC7CE'); // Rojo claro
        }
        
        $row++;
    }
    
    // Si no hay datos, mostrar un mensaje
    if (empty($historyData)) {
        $sheet->setCellValue('A8', 'No hay registros de gestión para este estudiante');
        $sheet->mergeCells('A8:J8');
        $sheet->getStyle('A8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }
    
    // Ajustar ancho de columnas
    foreach (range('A', 'J') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Aplicar bordes a la tabla
    $lastRow = $row - 1;
    if ($lastRow < 8) $lastRow = 8; // En caso de que no haya datos
    
    $sheet->getStyle('A7:J' . $lastRow)->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    // Aplicar wrap text a las columnas de observaciones para mejor legibilidad
    $sheet->getStyle('D7:D' . $lastRow)->getAlignment()->setWrapText(true);
    $sheet->getStyle('G7:G' . $lastRow)->getAlignment()->setWrapText(true);
    
    // Centrar contenido en algunas columnas
    $sheet->getStyle('A7:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C7:C' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('E7:E' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F7:F' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('H7:H' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('J7:J' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Configurar el nombre del archivo y descargar
    $filename = 'Historial_Gestiones_' . $studentId . '_' . date('Y-m-d') . '.xlsx';
    
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>