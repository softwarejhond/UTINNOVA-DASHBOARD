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
    
    // Función para obtener información de gestión de asistencia con nombre del responsable
    function getAttendanceManagement($conn, $studentId, $courseId) {
        $sql = "SELECT sam.*, u.nombre as responsible_name 
                FROM student_attendance_management sam
                LEFT JOIN users u ON sam.responsible_username = u.username
                WHERE sam.student_id = ? AND sam.course_id = ?";
        
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

    // Función para obtener estadísticas de asistencia
    function getAttendanceStats($conn, $studentId, $courseId) {
        // Obtener total de clases
        $sqlTotalClasses = "SELECT COUNT(DISTINCT class_date) AS total_classes
                           FROM attendance_records
                           WHERE course_id = ? AND class_date <= CURRENT_DATE()";
        
        $stmtClasses = $conn->prepare($sqlTotalClasses);
        $stmtClasses->bind_param('i', $courseId);
        $stmtClasses->execute();
        $resultClasses = $stmtClasses->get_result();
        $rowClasses = $resultClasses->fetch_assoc();
        $totalClasses = $rowClasses['total_classes'];
        $stmtClasses->close();
        
        // Obtener asistencias (presente o tarde)
        $sqlAttendance = "SELECT COUNT(*) AS total_attendance
                          FROM attendance_records
                          WHERE student_id = ? 
                          AND course_id = ?
                          AND class_date <= CURRENT_DATE() 
                          AND (attendance_status = 'presente' OR attendance_status = 'tarde')";
        
        $stmtAttendance = $conn->prepare($sqlAttendance);
        $stmtAttendance->bind_param('si', $studentId, $courseId);
        $stmtAttendance->execute();
        $resultAttendance = $stmtAttendance->get_result();
        $rowAttendance = $resultAttendance->fetch_assoc();
        $totalAttendance = $rowAttendance['total_attendance'];
        $stmtAttendance->close();
        
        // Obtener ausencias (solo cuando el status es 'ausente')
        $sqlAbsences = "SELECT COUNT(*) AS total_absences
                        FROM attendance_records
                        WHERE student_id = ? 
                        AND course_id = ?
                        AND class_date <= CURRENT_DATE() 
                        AND attendance_status = 'ausente'";
        
        $stmtAbsences = $conn->prepare($sqlAbsences);
        $stmtAbsences->bind_param('si', $studentId, $courseId);
        $stmtAbsences->execute();
        $resultAbsences = $stmtAbsences->get_result();
        $rowAbsences = $resultAbsences->fetch_assoc();
        $totalAbsences = $rowAbsences['total_absences'];
        $stmtAbsences->close();
        
        // Calcular porcentajes
        $totalRecordsForStudent = $totalAttendance + $totalAbsences;
        
        if ($totalRecordsForStudent > 0) {
            $attendancePercentage = round(($totalAttendance / $totalRecordsForStudent) * 100, 1);
            $absencePercentage = round(($totalAbsences / $totalRecordsForStudent) * 100, 1);
        } else {
            $attendancePercentage = 0;
            $absencePercentage = 0;
        }
        
        return [
            'totalClasses' => $totalClasses,
            'totalAttendance' => $totalAttendance,
            'totalAbsences' => $totalAbsences,
            'attendancePercentage' => $attendancePercentage,
            'absencePercentage' => $absencePercentage
        ];
    }

    // Agregar esta función para obtener el estado de asistencia de un estudiante en una clase
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

    // Función para obtener el color según estado de asistencia
    function getAttendanceColor($attendanceStatus) {
        switch($attendanceStatus) {
            case 'presente':
                return '9CCC65'; // Verde más intenso
            case 'tarde':
                return 'FFD54F'; // Amarillo más intenso
            case 'ausente':
                return 'EF5350'; // Rojo más intenso
            default:
                return 'CFD8DC'; // Gris más visible
        }
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
        
        // Agregar columnas para cada clase (tipo y observación separadas)
        $classDates = [];
        foreach ($classData as $index => $classInfo) {
            $classNumber = $index + 1;
            $classDate = $classInfo['class_date'];
            $headers[] = "Clase {$classNumber} - Tipo Observación ({$classDate})";
            $headers[] = "Clase {$classNumber} - Observación ({$classDate})";
            $classDates[] = $classDate;
        }
        
        // Agregar columnas de estadísticas de asistencia
        $headers = array_merge($headers, [
            'Total Asistencias',
            '% Asistencia',
            '% Inasistencias'
        ]);
        
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
            
            // Aplicar colores diferentes según el tipo de columna
            if (strpos($header, 'Asistencia') !== false || strpos($header, '%') !== false) {
                // Color verde claro para estadísticas de asistencia
                $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1')->getFill()
                    ->getStartColor()->setRGB('D1FAE5');
            } elseif (strpos($header, 'Clase') !== false) {
                // Color azul claro para columnas de clases
                $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1')->getFill()
                    ->getStartColor()->setRGB('DBEAFE');
            } elseif (strpos($header, 'Requiere') !== false || strpos($header, 'Observación') !== false || strpos($header, 'Estrategia') !== false || strpos($header, 'Retiro') !== false || strpos($header, 'Responsable') !== false) {
                // Color amarillo claro para gestión
                $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1')->getFill()
                    ->getStartColor()->setRGB('FEF3C7');
            }
            
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
            
            // Datos de observaciones por clase (separadas en dos columnas)
            foreach ($classDates as $classDate) {
                // Obtener el estado de asistencia para este estudiante y esta clase
                $attendanceStatus = getAttendanceStatus($conn, $student['number_id'], $student['course_code'], $classDate);
                $cellColor = getAttendanceColor($attendanceStatus);
                
                // Columna para el tipo de observación
                $typeColIndex = $col++;
                if (isset($observations[$classDate])) {
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($typeColIndex) . $row, $observations[$classDate]['type'] ?? '');
                } else {
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($typeColIndex) . $row, '');
                }
                // Aplicar color de fondo según estado de asistencia
                $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($typeColIndex) . $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($cellColor);
                
                // Columna para el texto de la observación
                $textColIndex = $col++;
                if (isset($observations[$classDate])) {
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($textColIndex) . $row, $observations[$classDate]['text'] ?? '');
                } else {
                    $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($textColIndex) . $row, '');
                }
                // Aplicar color de fondo según estado de asistencia
                $sheet->getStyle(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($textColIndex) . $row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB($cellColor);
            }
            
            // Obtener estadísticas de asistencia
            $stats = getAttendanceStats($conn, $student['number_id'], $student['course_code']);
            
            // Datos de estadísticas de asistencia
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $stats['totalAttendance']);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $stats['attendancePercentage'] . '%');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $stats['absencePercentage'] . '%');
            
            // Obtener información de gestión
            $management = getAttendanceManagement($conn, $student['number_id'], $student['course_code']);
            
            // Datos de gestión de asistencia (ahora usando el nombre del responsable en lugar del username)
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['requires_intervention'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['intervention_observation'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['is_resolved'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['requires_additional_strategy'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['strategy_observation'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['strategy_fulfilled'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['withdrawal_reason'] ?? '');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['withdrawal_date'] ?? '');
            // Usar el nombre del responsable en lugar del username
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $management['responsible_name'] ?? '');
            
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

        // Aplicar wrap text a las columnas de observaciones
        foreach (range('A', $highestCol) as $colLetter) {
            $sheet->getStyle($colLetter . '1:' . $colLetter . $highestRow)->getAlignment()
                ->setWrapText(true);
        }

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