<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

try {
    // Consulta principal para obtener períodos con información detallada
    $sql = "SELECT 
                cp.id,
                cp.period_name,
                cp.cohort,
                cp.payment_number,
                cp.bootcamp_code,
                cp.bootcamp_name,
                cp.leveling_english_code,
                cp.leveling_english_name,
                cp.english_code_code,
                cp.english_code_name,
                cp.skills_code,
                cp.skills_name,
                cp.start_date,
                cp.end_date,
                cp.status,
                cp.created_at,
                cp.updated_at
            FROM course_periods cp 
            WHERE cp.bootcamp_code IS NOT NULL 
            ORDER BY cp.created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        throw new Exception("Error en la consulta: " . $conn->error);
    }

    // Crear nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Períodos de Bootcamp');

    // Configurar encabezados (ahora con documentos)
    $headers = [
        'A1' => 'Nombre del Período',
        'B1' => 'Cohorte',
        'C1' => 'Número de Pago',
        'D1' => 'Fecha Inicio',
        'E1' => 'Fecha Fin',
        'F1' => 'Estado',
        'G1' => 'Fecha Creación',
        'H1' => 'Bootcamp - Código',
        'I1' => 'Bootcamp - Nombre',
        'J1' => 'Bootcamp - Doc. Profesor',
        'K1' => 'Bootcamp - Profesor',
        'L1' => 'Bootcamp - Doc. Mentor',
        'M1' => 'Bootcamp - Mentor',
        'N1' => 'Bootcamp - Doc. Monitor',
        'O1' => 'Bootcamp - Monitor',
        'P1' => 'Inglés Nivelador - Código',
        'Q1' => 'Inglés Nivelador - Nombre',
        'R1' => 'Inglés Nivelador - Doc. Profesor',
        'S1' => 'Inglés Nivelador - Profesor',
        'T1' => 'Inglés Nivelador - Doc. Mentor',
        'U1' => 'Inglés Nivelador - Mentor',
        'V1' => 'Inglés Nivelador - Doc. Monitor',
        'W1' => 'Inglés Nivelador - Monitor',
        'X1' => 'English Code - Código',
        'Y1' => 'English Code - Nombre',
        'Z1' => 'English Code - Doc. Profesor',
        'AA1' => 'English Code - Profesor',
        'AB1' => 'English Code - Doc. Mentor',
        'AC1' => 'English Code - Mentor',
        'AD1' => 'English Code - Doc. Monitor',
        'AE1' => 'English Code - Monitor',
        'AF1' => 'Habilidades - Código',
        'AG1' => 'Habilidades - Nombre',
        'AH1' => 'Habilidades - Doc. Profesor',
        'AI1' => 'Habilidades - Profesor',
        'AJ1' => 'Habilidades - Doc. Mentor',
        'AK1' => 'Habilidades - Mentor',
        'AL1' => 'Habilidades - Doc. Monitor',
        'AM1' => 'Habilidades - Monitor'
    ];

    // Aplicar encabezados
    foreach ($headers as $cell => $value) {
        $sheet->setCellValue($cell, $value);
    }

    // Estilo para encabezados
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
            'size' => 10
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '2C3E50']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => '000000']
            ]
        ]
    ];

    $sheet->getStyle('A1:AM1')->applyFromArray($headerStyle);

    // Función para obtener información del personal de un curso (incluyendo documentos)
    function getCoursePersonnel($conn, $courseCode) {
        if (!$courseCode) {
            return [
                'teacher_doc' => 'No asignado',
                'teacher_name' => 'No asignado',
                'mentor_doc' => 'No asignado',
                'mentor_name' => 'No asignado',
                'monitor_doc' => 'No asignado',
                'monitor_name' => 'No asignado'
            ];
        }

        $sql = "SELECT 
                    c.teacher,
                    c.mentor,
                    c.monitor,
                    ut.nombre as teacher_name,
                    um.nombre as mentor_name,
                    umo.nombre as monitor_name
                FROM courses c
                LEFT JOIN users ut ON c.teacher = ut.username
                LEFT JOIN users um ON c.mentor = um.username
                LEFT JOIN users umo ON c.monitor = umo.username
                WHERE c.code = ?
                LIMIT 1";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            return [
                'teacher_doc' => 'Error consulta',
                'teacher_name' => 'Error consulta',
                'mentor_doc' => 'Error consulta',
                'mentor_name' => 'Error consulta',
                'monitor_doc' => 'Error consulta',
                'monitor_name' => 'Error consulta'
            ];
        }

        $stmt->bind_param("s", $courseCode);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return [
                'teacher_doc' => $row['teacher'] ?: 'No asignado',
                'teacher_name' => $row['teacher_name'] ?: 'No asignado',
                'mentor_doc' => $row['mentor'] ?: 'No asignado',
                'mentor_name' => $row['mentor_name'] ?: 'No asignado',
                'monitor_doc' => $row['monitor'] ?: 'No asignado',
                'monitor_name' => $row['monitor_name'] ?: 'No asignado'
            ];
        }

        $stmt->close();
        return [
            'teacher_doc' => 'No encontrado',
            'teacher_name' => 'No encontrado',
            'mentor_doc' => 'No encontrado',
            'mentor_name' => 'No encontrado',
            'monitor_doc' => 'No encontrado',
            'monitor_name' => 'No encontrado'
        ];
    }

    // Llenar datos
    $row = 2;
    while ($period = $result->fetch_assoc()) {
        // Obtener información del personal para cada curso
        $bootcampPersonnel = getCoursePersonnel($conn, $period['bootcamp_code']);
        $englishPersonnel = getCoursePersonnel($conn, $period['leveling_english_code']);
        $englishCodePersonnel = getCoursePersonnel($conn, $period['english_code_code']);
        $skillsPersonnel = getCoursePersonnel($conn, $period['skills_code']);

        // Datos básicos del período
        $sheet->setCellValue('A' . $row, $period['period_name']);
        $sheet->setCellValue('B' . $row, $period['cohort']);
        $sheet->setCellValue('C' . $row, $period['payment_number'] ?: 0);
        $sheet->setCellValue('D' . $row, date('d/m/Y', strtotime($period['start_date'])));
        $sheet->setCellValue('E' . $row, date('d/m/Y', strtotime($period['end_date'])));
        $sheet->setCellValue('F' . $row, $period['status'] == 1 ? 'Activo' : 'Inactivo');
        $sheet->setCellValue('G' . $row, date('d/m/Y H:i', strtotime($period['created_at'])));

        // Bootcamp (Técnico) - con documentos
        $sheet->setCellValue('H' . $row, $period['bootcamp_code'] ?: 'No asignado');
        $sheet->setCellValue('I' . $row, $period['bootcamp_name'] ?: 'No asignado');
        $sheet->setCellValue('J' . $row, $bootcampPersonnel['teacher_doc']);
        $sheet->setCellValue('K' . $row, $bootcampPersonnel['teacher_name']);
        $sheet->setCellValue('L' . $row, $bootcampPersonnel['mentor_doc']);
        $sheet->setCellValue('M' . $row, $bootcampPersonnel['mentor_name']);
        $sheet->setCellValue('N' . $row, $bootcampPersonnel['monitor_doc']);
        $sheet->setCellValue('O' . $row, $bootcampPersonnel['monitor_name']);

        // Inglés Nivelador - con documentos
        $sheet->setCellValue('P' . $row, $period['leveling_english_code'] ?: 'No asignado');
        $sheet->setCellValue('Q' . $row, $period['leveling_english_name'] ?: 'No asignado');
        $sheet->setCellValue('R' . $row, $englishPersonnel['teacher_doc']);
        $sheet->setCellValue('S' . $row, $englishPersonnel['teacher_name']);
        $sheet->setCellValue('T' . $row, $englishPersonnel['mentor_doc']);
        $sheet->setCellValue('U' . $row, $englishPersonnel['mentor_name']);
        $sheet->setCellValue('V' . $row, $englishPersonnel['monitor_doc']);
        $sheet->setCellValue('W' . $row, $englishPersonnel['monitor_name']);

        // English Code - con documentos
        $sheet->setCellValue('X' . $row, $period['english_code_code'] ?: 'No asignado');
        $sheet->setCellValue('Y' . $row, $period['english_code_name'] ?: 'No asignado');
        $sheet->setCellValue('Z' . $row, $englishCodePersonnel['teacher_doc']);
        $sheet->setCellValue('AA' . $row, $englishCodePersonnel['teacher_name']);
        $sheet->setCellValue('AB' . $row, $englishCodePersonnel['mentor_doc']);
        $sheet->setCellValue('AC' . $row, $englishCodePersonnel['mentor_name']);
        $sheet->setCellValue('AD' . $row, $englishCodePersonnel['monitor_doc']);
        $sheet->setCellValue('AE' . $row, $englishCodePersonnel['monitor_name']);

        // Habilidades - con documentos
        $sheet->setCellValue('AF' . $row, $period['skills_code'] ?: 'No asignado');
        $sheet->setCellValue('AG' . $row, $period['skills_name'] ?: 'No asignado');
        $sheet->setCellValue('AH' . $row, $skillsPersonnel['teacher_doc']);
        $sheet->setCellValue('AI' . $row, $skillsPersonnel['teacher_name']);
        $sheet->setCellValue('AJ' . $row, $skillsPersonnel['mentor_doc']);
        $sheet->setCellValue('AK' . $row, $skillsPersonnel['mentor_name']);
        $sheet->setCellValue('AL' . $row, $skillsPersonnel['monitor_doc']);
        $sheet->setCellValue('AM' . $row, $skillsPersonnel['monitor_name']);

        $row++;
    }

    // Aplicar estilos a los datos
    $dataStyle = [
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'CCCCCC']
            ]
        ]
    ];

    if ($row > 2) {
        $sheet->getStyle('A2:AM' . ($row - 1))->applyFromArray($dataStyle);
    }

    // Ajustar ancho de columnas
    $columnWidths = [
        'A' => 20,  // Nombre del Período
        'B' => 10,  // Cohorte
        'C' => 12,  // Número de Pago
        'D' => 12,  // Fecha Inicio
        'E' => 12,  // Fecha Fin
        'F' => 10,  // Estado
        'G' => 18,  // Fecha Creación
        'H' => 15,  // Bootcamp - Código
        'I' => 30,  // Bootcamp - Nombre
        'J' => 15,  // Bootcamp - Doc. Profesor
        'K' => 20,  // Bootcamp - Profesor
        'L' => 15,  // Bootcamp - Doc. Mentor
        'M' => 20,  // Bootcamp - Mentor
        'N' => 15,  // Bootcamp - Doc. Monitor
        'O' => 20,  // Bootcamp - Monitor
        'P' => 15,  // Inglés - Código
        'Q' => 30,  // Inglés - Nombre
        'R' => 15,  // Inglés - Doc. Profesor
        'S' => 20,  // Inglés - Profesor
        'T' => 15,  // Inglés - Doc. Mentor
        'U' => 20,  // Inglés - Mentor
        'V' => 15,  // Inglés - Doc. Monitor
        'W' => 20,  // Inglés - Monitor
        'X' => 15,  // English Code - Código
        'Y' => 30,  // English Code - Nombre
        'Z' => 15,  // English Code - Doc. Profesor
        'AA' => 20, // English Code - Profesor
        'AB' => 15, // English Code - Doc. Mentor
        'AC' => 20, // English Code - Mentor
        'AD' => 15, // English Code - Doc. Monitor
        'AE' => 20, // English Code - Monitor
        'AF' => 15, // Habilidades - Código
        'AG' => 30, // Habilidades - Nombre
        'AH' => 15, // Habilidades - Doc. Profesor
        'AI' => 20, // Habilidades - Profesor
        'AJ' => 15, // Habilidades - Doc. Mentor
        'AK' => 20, // Habilidades - Mentor
        'AL' => 15, // Habilidades - Doc. Monitor
        'AM' => 20  // Habilidades - Monitor
    ];

    foreach ($columnWidths as $column => $width) {
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    // Aplicar filtros automáticos
    $sheet->setAutoFilter('A1:AM1');

    // Congelar primera fila
    $sheet->freezePane('A2');

    // Configurar propiedades del documento
    $spreadsheet->getProperties()
        ->setCreator('UTINNOVA Dashboard')
        ->setTitle('Reporte de Períodos de Bootcamp')
        ->setSubject('Períodos de Bootcamp con Personal Asignado')
        ->setDescription('Reporte detallado de todos los períodos de bootcamp con información de profesores, mentores y monitores incluyendo documentos')
        ->setKeywords('períodos, bootcamp, cursos, personal, documentos')
        ->setCategory('Reportes');

    // Configurar headers para descarga
    $filename = 'Reporte_Periodos_Bootcamp_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Cache-Control: max-age=1');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Cache-Control: cache, must-revalidate');
    header('Pragma: public');

    // Crear writer y descargar
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

    // Limpiar memoria
    $spreadsheet->disconnectWorksheets();
    unset($spreadsheet);

} catch (Exception $e) {
    // En caso de error, mostrar mensaje
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error al generar el reporte: ' . $e->getMessage()
    ]);
    exit;
}

// Cerrar conexión
if (isset($conn)) {
    $conn->close();
}
?>