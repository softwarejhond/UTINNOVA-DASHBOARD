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

$courseCode = $_POST['courseCode'] ?? '';
$data = json_decode($_POST['data'] ?? '{}', true);
$classes = json_decode($_POST['classes'] ?? '{}', true);

if (empty($courseCode) || empty($data)) {
    http_response_code(400);
    exit('Datos insuficientes');
}

try {
    $spreadsheet = new Spreadsheet();
    
    // Función para obtener el estado de asistencia codificado
    function getAttendanceCode($attendanceStatus) {
        switch($attendanceStatus) {
            case 'presente':
                return 1;
            case 'ausente':
                return 0;
            case 'tarde':
                return 2;
            default:
                return '-'; // Sin registro
        }
    }

    // Función para obtener el estado de asistencia de un estudiante en una clase específica
    function getAttendanceStatus($conn, $studentId, $courseId, $classDate) {
        $sql = "SELECT attendance_status 
                FROM attendance_records 
                WHERE student_id = ? AND course_id = ? AND class_date = ?
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('sis', $studentId, $courseId, $classDate);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        
        return $row ? $row['attendance_status'] : null;
    }

    $courseTypes = [
        'tecnico' => 'Técnico',
        'ingles_nivelado' => 'Inglés Nivelado', 
        'english_code' => 'English Code',
        'habilidades' => 'Habilidades'
    ];

    $sheetIndex = 0;
    foreach ($courseTypes as $type => $typeName) {
        if ($sheetIndex === 0) {
            $sheet = $spreadsheet->getActiveSheet();
        } else {
            $sheet = $spreadsheet->createSheet();
        }
        
        $sheet->setTitle($typeName);
        
        $students = $data[$type] ?? [];
        $classData = $classes[$type] ?? [];
        
        if (empty($students)) {
            // Si no hay estudiantes, crear una hoja vacía con encabezados básicos
            $sheet->setCellValue('A1', 'No hay estudiantes registrados para ' . $typeName);
            $sheet->getStyle('A1')->getFont()->setBold(true);
            $sheetIndex++;
            continue;
        }

        // Crear encabezados básicos
        $headers = [
            'Documento', 'Nombre', 'Celular', 'Correo Institucional', 
            'Correo Personal', 'Horario', 'Grupo', 'Estado Admisión'
        ];
        
        // Agregar una columna por cada fecha de clase
        $classDates = [];
        foreach ($classData as $classInfo) {
            $classDate = $classInfo['class_date'];
            
            // Convertir fecha a formato DMY
            $dateObj = DateTime::createFromFormat('Y-m-d', $classDate);
            $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : $classDate;
            
            $headers[] = $formattedDate;
            $classDates[] = $classDate; // Mantener la fecha original para las consultas
        }

        // Escribir encabezados
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1', $header);
            $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1')->getFont()->setBold(true);
            $col++;
        }

        // Escribir datos de estudiantes
        $row = 2;
        foreach ($students as $student) {
            $col = 1;
            
            // Datos básicos del estudiante
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['number_id'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['full_name'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['celular'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['institutional_email'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['email'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['horario'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['group_name'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['estado_admision_texto'] ?? 'N/A');
            
            // Datos de asistencia por fecha
            foreach ($classDates as $classDate) {
                $attendanceStatus = getAttendanceStatus($conn, $student['number_id'], $student['course_code'], $classDate);
                $attendanceCode = getAttendanceCode($attendanceStatus);
                
                // Establecer el valor de la celda
                $cellIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                $sheet->setCellValue($cellIndex, $attendanceCode);
                
                // Centrar el contenido
                $sheet->getStyle($cellIndex)
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);
                
                $col++;
            }
            
            $row++;
        }

        // Ajustar ancho de columnas
        foreach (range('A', $sheet->getHighestColumn()) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Aplicar bordes a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Aplicar alineación central a los encabezados
        $sheet->getStyle('A1:' . $highestCol . '1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);

        // Hacer que las columnas de fechas tengan un ancho fijo para mejor visualización
        $startDateCol = 9; // Columna I (después de Estado Admisión)
        for ($i = 0; $i < count($classDates); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startDateCol + $i);
            $sheet->getColumnDimension($colLetter)->setWidth(12);
        }

        $sheetIndex++;
    }

    // Agregar una hoja de leyenda (sin colores)
    $legendSheet = $spreadsheet->createSheet();
    $legendSheet->setTitle('Leyenda');
    
    // Crear la leyenda
    $legendSheet->setCellValue('A1', 'LEYENDA DE CÓDIGOS DE ASISTENCIA');
    $legendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    $legendSheet->setCellValue('A3', 'Código');
    $legendSheet->setCellValue('B3', 'Significado');
    
    // Aplicar estilo a los encabezados de la leyenda
    $legendSheet->getStyle('A3:B3')->getFont()->setBold(true);
    
    // Presente
    $legendSheet->setCellValue('A4', '1');
    $legendSheet->setCellValue('B4', 'Presente');
    
    // Ausente
    $legendSheet->setCellValue('A5', '0');
    $legendSheet->setCellValue('B5', 'Ausente');
    
    // Tarde
    $legendSheet->setCellValue('A6', '2');
    $legendSheet->setCellValue('B6', 'Llegada tardía');
    
    // Sin registro
    $legendSheet->setCellValue('A7', '-');
    $legendSheet->setCellValue('B7', 'Sin registro');
    
    // Aplicar bordes a la leyenda
    $legendSheet->getStyle('A3:B7')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    // Ajustar ancho de columnas de la leyenda
    $legendSheet->getColumnDimension('A')->setWidth(10);
    $legendSheet->getColumnDimension('B')->setWidth(20);
    
    // Centrar contenido de la leyenda
    $legendSheet->getStyle('A3:B7')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    // Configurar el nombre del archivo y descargar
    $filename = 'Asistencia_registro_' . $courseCode . '_' . date('Y-m-d') . '.xlsx';
    
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>