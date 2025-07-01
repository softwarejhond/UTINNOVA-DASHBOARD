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
    
    // Función para obtener observaciones de un estudiante
    function getStudentObservations($conn, $studentId, $courseId, $classDates) {
        $observations = [];
        
        if (empty($classDates)) {
            return $observations;
        }
        
        $placeholders = str_repeat('?,', count($classDates) - 1) . '?';
        $sql = "SELECT class_date, observation_type, observation_text 
                FROM class_observations 
                WHERE student_id = ? AND course_id = ? AND class_date IN ($placeholders)
                ORDER BY class_date";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return $observations;
        }
        
        $params = array_merge([$studentId, $courseId], $classDates);
        $types = 's' . 'i' . str_repeat('s', count($classDates));
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $observations[$row['class_date']] = [
                'type' => $row['observation_type'],
                'text' => $row['observation_text']
            ];
        }
        
        $stmt->close();
        return $observations;
    }
    
    // Función para obtener información de gestión de asistencia
    function getAttendanceManagement($conn, $studentId, $courseId) {
        $sql = "SELECT * FROM student_attendance_management 
                WHERE student_id = ? AND course_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return null;
        }
        
        $stmt->bind_param('si', $studentId, $courseId);
        $stmt->execute();
        $result = $stmt->get_result();
        $management = $result->fetch_assoc();
        $stmt->close();
        
        return $management;
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

        // Crear encabezados
        $headers = [
            'Documento', 'Nombre', 'Celular', 'Correo Institucional', 
            'Correo Personal', 'Horario', 'Grupo', 'Estado Admisión'
        ];
        
        // Agregar columnas para cada clase
        $classDates = [];
        foreach ($classData as $index => $classInfo) {
            $headers[] = 'Clase ' . ($index + 1) . ' (' . $classInfo['class_date'] . ')';
            $classDates[] = $classInfo['class_date'];
        }
        
        // Agregar columnas de gestión
        $headers = array_merge($headers, [
            'Requiere Intervención',
            'Observación Intervención', 
            'Está Resuelta',
            'Requiere Estrategia Adicional',
            'Observación Estrategia',
            'Cumple Estrategia',
            'Motivo Retiro',
            'Fecha Retiro',
            'Responsable'
        ]);

        // Escribir encabezados
        $col = 1;
        foreach ($headers as $header) {
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1', $header);
            $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1')->getFont()->setBold(true);
            $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1')->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
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
            
            // Obtener observaciones para este estudiante
            $observations = getStudentObservations($conn, $student['number_id'], $student['course_code'], $classDates);
            
            // Datos de observaciones por clase
            foreach ($classDates as $classDate) {
                $obsText = 'Sin observación';
                if (isset($observations[$classDate])) {
                    $obs = $observations[$classDate];
                    $obsText = $obs['type'];
                    if (!empty($obs['text'])) {
                        $obsText .= ': ' . $obs['text'];
                    }
                }
                $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $obsText);
            }
            
            // Obtener información de gestión
            $management = getAttendanceManagement($conn, $student['number_id'], $student['course_code']);
            
            // Datos de gestión de asistencia
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['requires_intervention'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['intervention_observation'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['is_resolved'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['requires_additional_strategy'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['strategy_observation'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['strategy_fulfilled'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['withdrawal_reason'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['withdrawal_date'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['responsible_username'] ?? '');
            
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

        $sheetIndex++;
    }

    // Configurar el nombre del archivo y descargar
    $filename = 'Seguimiento_Asistencia_' . $courseCode . '_' . date('Y-m-d') . '.xlsx';
    
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>