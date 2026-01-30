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

try {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Asistencias Comprobantes');

    // Función para obtener el color según el estado de asistencia
    function getAttendanceColor($status) {
        switch (strtolower($status)) {
            case 'presente':
            case 'present':
                return 'FF28A745'; // Verde
            case 'ausente':
            case 'absent':
                return 'FFDC3545'; // Rojo
            case 'tarde':
            case 'late':
                return 'FFFFC107'; // Amarillo
            case 'excusa':
            case 'excused':
                return 'FF17A2B8'; // Azul
            default:
                return 'FF6C757D'; // Gris para sin registro
        }
    }

    // Función para formatear el estado de asistencia (SIMPLIFICADA - solo fecha y estado)
    function formatAttendanceStatus($date, $status) {
        if (!$date || !$status) {
            return '-';
        }
        
        // Formatear fecha
        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : $date;
        
        // Formatear estado
        $statusText = '';
        switch (strtolower($status)) {
            case 'presente':
            case 'present':
                $statusText = 'Presente';
                break;
            case 'ausente':
            case 'absent':
                $statusText = 'Ausente';
                break;
            case 'tarde':
            case 'late':
                $statusText = 'Tarde';
                break;
            case 'excusa':
            case 'excused':
                $statusText = 'Excusa';
                break;
            default:
                $statusText = 'N/A';
                break;
        }
        
        return $formattedDate . "\n" . $statusText;
    }

    // Consulta para obtener estudiantes con statusAdmin = 6 y modo Presencial
    $sql = "SELECT DISTINCT 
        ur.number_id,
        ur.lote,
        ur.level,
        g.full_name,
        g.program,
        g.headquarters,
        g.id_bootcamp,
        g.bootcamp_name,
        g.id_english_code,
        g.english_code_name,
        g.id_skills,
        g.skills_name
    FROM user_register ur
    LEFT JOIN groups g ON ur.number_id = g.number_id
    WHERE ur.statusAdmin = 6 AND g.number_id IS NOT NULL AND g.mode = 'Virtual'
    ORDER BY ur.number_id";

    $result = mysqli_query($conn, $sql);
    if (!$result) {
        throw new Exception('Error en la consulta: ' . mysqli_error($conn));
    }

    $students = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $students[] = $row;
    }

    if (empty($students)) {
        // Si no hay estudiantes, crear una hoja vacía
        $sheet->setCellValue('A1', 'No hay estudiantes con statusAdmin = 6');
        $sheet->getStyle('A1')->getFont()->setBold(true);
    } else {
        // Crear encabezados básicos
        $headers = [
            'Cédula',
            'Región',
            'Lote',
            'Curso Técnico',
            'Nivel',
            'Año Certificación',
            'Sede Formación'
        ];

        // Definir los componentes a procesar
        $components = [
            'tecnico' => [
                'field' => 'id_bootcamp',
                'name_field' => 'bootcamp_name',
                'label' => 'Técnico'
            ],
            'english_code' => [
                'field' => 'id_english_code', 
                'name_field' => 'english_code_name',
                'label' => 'English Code'
            ],
            'habilidades' => [
                'field' => 'id_skills',
                'name_field' => 'skills_name', 
                'label' => 'Habilidades'
            ]
        ];

        // Agrupar estudiantes por cada componente y obtener fechas máximas
        $componentMaxDates = [];
        $studentsAttendanceByComponent = [];

        foreach ($components as $componentType => $componentConfig) {
            $studentsByComponent = [];
            
            // Agrupar estudiantes por curso de este componente
            foreach ($students as $student) {
                $course_id = $student[$componentConfig['field']];
                if (!empty($course_id)) {
                    if (!isset($studentsByComponent[$course_id])) {
                        $studentsByComponent[$course_id] = [];
                    }
                    $studentsByComponent[$course_id][] = $student;
                }
            }

            // Para cada curso de este componente, obtener todas las fechas de asistencia únicas
            $componentCourseMaxDates = [];
            foreach ($studentsByComponent as $course_id => $courseStudents) {
                $allCourseDates = [];
                
                // Recopilar todas las fechas de asistencia del curso
                foreach ($courseStudents as $student) {
                    $number_id = $student['number_id'];
                    
                    $attendanceSql = "SELECT DISTINCT class_date 
                                     FROM attendance_records 
                                     WHERE student_id = ? AND course_id = ? 
                                     ORDER BY class_date ASC";
                    
                    $stmt = $conn->prepare($attendanceSql);
                    if ($stmt) {
                        $stmt->bind_param('si', $number_id, $course_id);
                        $stmt->execute();
                        $attendanceResult = $stmt->get_result();
                        
                        while ($dateRow = $attendanceResult->fetch_assoc()) {
                            if (!in_array($dateRow['class_date'], $allCourseDates)) {
                                $allCourseDates[] = $dateRow['class_date'];
                            }
                        }
                        $stmt->close();
                    }
                }
                
                sort($allCourseDates);
                $componentCourseMaxDates[$course_id] = $allCourseDates;
            }
            
            $componentMaxDates[$componentType] = $componentCourseMaxDates;

            // Obtener asistencias completas para cada estudiante en este componente
            $componentStudentsAttendance = [];
            foreach ($students as $student) {
                $number_id = $student['number_id'];
                $course_id = $student[$componentConfig['field']];
                
                if (!empty($course_id)) {
                    $courseDates = $componentCourseMaxDates[$course_id] ?? [];

                    // Obtener registros de asistencia existentes para este estudiante
                    $attendanceSql = "SELECT class_date, attendance_status 
                                     FROM attendance_records 
                                     WHERE student_id = ? AND course_id = ? 
                                     ORDER BY class_date ASC";

                    $stmt = $conn->prepare($attendanceSql);
                    $existingAttendances = [];
                    
                    if ($stmt) {
                        $stmt->bind_param('si', $number_id, $course_id);
                        $stmt->execute();
                        $attendanceResult = $stmt->get_result();
                        
                        while ($attendanceRow = $attendanceResult->fetch_assoc()) {
                            $existingAttendances[$attendanceRow['class_date']] = $attendanceRow['attendance_status'];
                        }
                        $stmt->close();
                    }

                    // Crear array completo de asistencias para este estudiante
                    $completeAttendances = [];
                    foreach ($courseDates as $date) {
                        if (isset($existingAttendances[$date])) {
                            $completeAttendances[] = [
                                'class_date' => $date,
                                'attendance_status' => $existingAttendances[$date]
                            ];
                        } else {
                            $completeAttendances[] = [
                                'class_date' => $date,
                                'attendance_status' => 'ausente'
                            ];
                        }
                    }

                    $componentStudentsAttendance[$number_id] = $completeAttendances;
                } else {
                    $componentStudentsAttendance[$number_id] = [];
                }
            }
            
            $studentsAttendanceByComponent[$componentType] = $componentStudentsAttendance;
        }

        // Calcular el número máximo de asistencias por componente
        $maxAttendancesByComponent = [];
        foreach ($components as $componentType => $componentConfig) {
            $maxAttendances = 0;
            if (isset($componentMaxDates[$componentType])) {
                foreach ($componentMaxDates[$componentType] as $dates) {
                    $maxAttendances = max($maxAttendances, count($dates));
                }
            }
            $maxAttendancesByComponent[$componentType] = $maxAttendances;
        }

        // Agregar columnas de asistencias a los encabezados para cada componente
        foreach ($components as $componentType => $componentConfig) {
            $maxAttendances = $maxAttendancesByComponent[$componentType];
            for ($i = 1; $i <= $maxAttendances; $i++) {
                $headers[] = $componentConfig['label'] . " - Asistencia $i";
            }
        }

        // Escribir encabezados
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
            $sheet->setCellValue($cellRef, $header);
            
            // Estilo del encabezado
            $sheet->getStyle($cellRef)->getFont()->setBold(true);
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE9ECEF');
            $sheet->getStyle($cellRef)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);
            
            $col++;
        }

        // Escribir datos de estudiantes
        $row = 2;
        foreach ($students as $student) {
            $col = 1;
            $number_id = $student['number_id'];

            // Datos básicos del estudiante
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['number_id']);
            
            // Región (formato: Región 8 - Lote X)
            $regionText = 'Región 8 - Lote ' . ($student['lote'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $regionText);
            
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['lote'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['bootcamp_name'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['level'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, '2025'); // Año fijo
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['headquarters'] ?? 'N/A');

            // Datos de asistencias por componente
            foreach ($components as $componentType => $componentConfig) {
                $attendances = $studentsAttendanceByComponent[$componentType][$number_id] ?? [];
                $maxAttendances = $maxAttendancesByComponent[$componentType];
                
                for ($i = 0; $i < $maxAttendances; $i++) {
                    $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;
                    
                    if (isset($attendances[$i])) {
                        $attendance = $attendances[$i];
                        $cellValue = formatAttendanceStatus(
                            $attendance['class_date'], 
                            $attendance['attendance_status']
                        );
                        $sheet->setCellValue($cellRef, $cellValue);
                        
                        // Aplicar color según el estado
                        $color = getAttendanceColor($attendance['attendance_status']);
                        $sheet->getStyle($cellRef)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB($color);
                        
                        // Texto en blanco para mejor contraste
                        $sheet->getStyle($cellRef)->getFont()->getColor()->setARGB('FFFFFFFF');
                    } else {
                        $sheet->setCellValue($cellRef, '-');
                        // No aplicar color de fondo para celdas sin registro
                        $sheet->getStyle($cellRef)->getFont()->getColor()->setARGB('FF000000');
                    }
                    
                    // Centrar contenido y permitir salto de línea
                    $sheet->getStyle($cellRef)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);
                    
                    $col++;
                }
            }
            
            $row++;
        }

        // Ajustar ancho de columnas básicas
        $sheet->getColumnDimension('A')->setWidth(15); // Cédula
        $sheet->getColumnDimension('B')->setWidth(20); // Región
        $sheet->getColumnDimension('C')->setWidth(8);  // Lote
        $sheet->getColumnDimension('D')->setWidth(25); // Curso
        $sheet->getColumnDimension('E')->setWidth(12); // Nivel
        $sheet->getColumnDimension('F')->setWidth(12); // Año
        $sheet->getColumnDimension('G')->setWidth(20); // Sede

        // Ajustar ancho de columnas de asistencias
        $totalBasicColumns = 7;
        $totalAttendanceColumns = array_sum($maxAttendancesByComponent);
        
        for ($i = $totalBasicColumns + 1; $i <= ($totalBasicColumns + $totalAttendanceColumns); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setWidth(15);
        }

        // Aplicar bordes a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Configurar altura de filas para mejor visualización
        for ($i = 2; $i <= $highestRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(40);
        }
        $sheet->getRowDimension(1)->setRowHeight(25); // Encabezado
    }

    // Crear hoja de leyenda (ACTUALIZADA)
    $legendSheet = $spreadsheet->createSheet();
    $legendSheet->setTitle('Leyenda');
    
    $legendSheet->setCellValue('A1', 'LEYENDA DE COLORES DE ASISTENCIA');
    $legendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    $legendSheet->setCellValue('A3', 'Color');
    $legendSheet->setCellValue('B3', 'Estado');
    $legendSheet->setCellValue('C3', 'Descripción');
    
    // Aplicar estilo a encabezados
    $legendSheet->getStyle('A3:C3')->getFont()->setBold(true);
    $legendSheet->getStyle('A3:C3')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE9ECEF');
    
    // Presente
    $legendSheet->setCellValue('A4', '');
    $legendSheet->setCellValue('B4', 'Presente');
    $legendSheet->setCellValue('C4', 'El estudiante asistió a la clase');
    $legendSheet->getStyle('A4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF28A745');
    
    // Ausente
    $legendSheet->setCellValue('A5', '');
    $legendSheet->setCellValue('B5', 'Ausente');
    $legendSheet->setCellValue('C5', 'El estudiante no asistió a la clase');
    $legendSheet->getStyle('A5')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDC3545');
    
    // Tarde
    $legendSheet->setCellValue('A6', '');
    $legendSheet->setCellValue('B6', 'Tarde');
    $legendSheet->setCellValue('C6', 'El estudiante llegó tarde a la clase');
    $legendSheet->getStyle('A6')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFC107');
    
    // Excusa
    $legendSheet->setCellValue('A7', '');
    $legendSheet->setCellValue('B7', 'Excusa');
    $legendSheet->setCellValue('C7', 'El estudiante tuvo una excusa justificada');
    $legendSheet->getStyle('A7')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF17A2B8');
    
    // Sin registro
    $legendSheet->setCellValue('A8', '');
    $legendSheet->setCellValue('B8', 'Sin registro');
    $legendSheet->setCellValue('C8', 'No hay registro de asistencia para esta clase');
    $legendSheet->getStyle('A8')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF6C757D');
        
    // Información sobre componentes
    $legendSheet->setCellValue('A10', 'INFORMACIÓN SOBRE COMPONENTES:');
    $legendSheet->getStyle('A10')->getFont()->setBold(true)->setSize(12);
    $legendSheet->setCellValue('A11', '• Técnico: Curso principal de formación técnica');
    $legendSheet->setCellValue('A12', '• English Code: Curso de inglés técnico especializado');
    $legendSheet->setCellValue('A13', '• Habilidades: Curso de habilidades blandas y complementarias');
    
    // Nota sobre la normalización de asistencias por curso
    $legendSheet->setCellValue('A15', 'NOTA IMPORTANTE:');
    $legendSheet->getStyle('A15')->getFont()->setBold(true)->setSize(12);
    $legendSheet->setCellValue('A16', 'Todos los estudiantes del mismo curso tienen el mismo número de registros.');
    $legendSheet->setCellValue('A17', 'Las fechas faltantes se marcan automáticamente como "Ausente".');
    $legendSheet->setCellValue('A18', 'Esto garantiza que cada grupo tenga una matriz completa de asistencias.');
    $legendSheet->setCellValue('A19', 'Cada componente (Técnico, English Code, Habilidades) se procesa independientemente.');
    
    // Aplicar bordes a la leyenda
    $legendSheet->getStyle('A3:C8')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);
    
    // Ajustar ancho de columnas de la leyenda
    $legendSheet->getColumnDimension('A')->setWidth(10);
    $legendSheet->getColumnDimension('B')->setWidth(15);
    $legendSheet->getColumnDimension('C')->setWidth(50);
    
    // Centrar contenido de la leyenda
    $legendSheet->getStyle('A3:C8')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    // Activar la primera hoja
    $spreadsheet->setActiveSheetIndex(0);

    // Configurar el nombre del archivo y descargar
    $filename = 'Asistencias_Comprobantes_Completas_StatusAdmin6_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
?>