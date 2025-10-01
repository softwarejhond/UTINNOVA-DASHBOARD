<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Corregir ruta del autoload
require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Función actualizada para calcular horas basadas en asistencia
function calcularHorasAsistencia($conn, $studentId, $courseId) {
    if (empty($courseId)) return 0;
    
    $sql = "SELECT ar.class_date, 
                   CASE 
                      WHEN ar.attendance_status = 'presente' THEN 
                          CASE DAYOFWEEK(ar.class_date)
                              WHEN 2 THEN c.monday_hours    -- Lunes
                              WHEN 3 THEN c.tuesday_hours   -- Martes
                              WHEN 4 THEN c.wednesday_hours -- Miércoles
                              WHEN 5 THEN c.thursday_hours  -- Jueves
                              WHEN 6 THEN c.friday_hours    -- Viernes
                              WHEN 7 THEN c.saturday_hours  -- Sábado
                              WHEN 1 THEN c.sunday_hours    -- Domingo
                              ELSE 0
                          END
                      WHEN ar.attendance_status = 'tarde' THEN ar.recorded_hours
                      ELSE 0 
                   END as horas,
                   ar.attendance_status
            FROM attendance_records ar
            JOIN courses c ON ar.course_id = c.code
            WHERE ar.student_id = ? 
            AND ar.course_id = ?
            ORDER BY ar.class_date, FIELD(ar.attendance_status, 'presente', 'tarde')";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta: " . $conn->error);
        return 0;
    }
    
    $stmt->bind_param("si", $studentId, $courseId);
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        return 0;
    }
    
    $result = $stmt->get_result();
    
    $fechasContadas = [];
    $totalHoras = 0;
    
    while($asistencia = $result->fetch_assoc()) {
        $fecha = $asistencia['class_date'];
        
        // Solo contar una asistencia por fecha (la primera después de ordenar)
        if (!in_array($fecha, $fechasContadas)) {
            $totalHoras += $asistencia['horas'];
            $fechasContadas[] = $fecha;
        }
    }
    
    $stmt->close();
    return $totalHoras;
}

// Función para configurar encabezados de una hoja
function configurarEncabezados($sheet) {
    $sheet->setCellValue('A1', 'Número de Identificación');
    $sheet->setCellValue('B1', 'Nombre del Estudiante');
    $sheet->setCellValue('C1', 'Correo Personal');
    $sheet->setCellValue('D1', 'Correo Institucional');
    $sheet->setCellValue('E1', 'Teléfono Principal');
    $sheet->setCellValue('F1', 'Teléfono Secundario');
    $sheet->setCellValue('G1', 'Programa');
    $sheet->setCellValue('H1', 'Modalidad');

    // Títulos específicos por área
    $sheet->setCellValue('I1', 'Técnico - Horas Actuales');
    $sheet->setCellValue('J1', 'Técnico - Horas Reales');
    $sheet->setCellValue('K1', 'Técnico - Total Horas');

    $sheet->setCellValue('L1', 'Inglés - Horas Actuales');
    $sheet->setCellValue('M1', 'Inglés - Horas Reales');
    $sheet->setCellValue('N1', 'Inglés - Total Horas');

    $sheet->setCellValue('O1', 'Habilidades - Horas Actuales');
    $sheet->setCellValue('P1', 'Habilidades - Horas Reales');
    $sheet->setCellValue('Q1', 'Habilidades - Total Horas');

    $sheet->setCellValue('R1', 'Total - Horas Actuales');
    $sheet->setCellValue('S1', 'Total - Horas Reales');
    $sheet->setCellValue('T1', 'Total - Horas Programa');

    $sheet->setCellValue('U1', 'Porcentaje Actuales');
    $sheet->setCellValue('V1', 'Porcentaje Reales');
    $sheet->setCellValue('W1', 'Porcentaje Faltante');
}

// Función para aplicar estilos a una hoja
function aplicarEstilos($sheet, $lastRow) {
    // Estilos por área
    $basicHeaderStyle = [
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    // Estilos para datos (sin negrita)
    $basicDataStyle = [
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ];

    $tecnicoHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFE6E6']], // Rojo claro
    ]);

    $tecnicoDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2F2']], // Rojo más claro para datos
    ]);

    $inglesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E6FFE6']], // Verde claro
    ]);

    $inglesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2FFF2']], // Verde más claro para datos
    ]);

    $habilidadesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF0E6']], // Naranja claro
    ]);

    $habilidadesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF8F2']], // Naranja más claro para datos
    ]);

    $totalesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F0E6FF']], // Púrpura claro
    ]);

    $totalesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F8F2FF']], // Púrpura más claro para datos
    ]);

    $porcentajesHeaderStyle = array_merge($basicHeaderStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFCC']], // Amarillo claro
    ]);

    $porcentajesDataStyle = array_merge($basicDataStyle, [
        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFFF2']], // Amarillo más claro para datos
    ]);

    // Aplicar estilos específicos por área - Headers
    $sheet->getStyle('A1:H1')->applyFromArray($basicHeaderStyle); // Información básica
    $sheet->getStyle('I1:K1')->applyFromArray($tecnicoHeaderStyle);     // Técnico
    $sheet->getStyle('L1:N1')->applyFromArray($inglesHeaderStyle);      // Inglés
    $sheet->getStyle('O1:Q1')->applyFromArray($habilidadesHeaderStyle); // Habilidades
    $sheet->getStyle('R1:T1')->applyFromArray($totalesHeaderStyle);     // Totales
    $sheet->getStyle('U1:W1')->applyFromArray($porcentajesHeaderStyle); // Porcentajes

    // Aplicar estilos específicos por área - Datos
    if ($lastRow >= 2) {
        $sheet->getStyle('A2:H' . $lastRow)->applyFromArray($basicDataStyle); // Información básica
        $sheet->getStyle('I2:K' . $lastRow)->applyFromArray($tecnicoDataStyle);     // Técnico
        $sheet->getStyle('L2:N' . $lastRow)->applyFromArray($inglesDataStyle);      // Inglés
        $sheet->getStyle('O2:Q' . $lastRow)->applyFromArray($habilidadesDataStyle); // Habilidades
        $sheet->getStyle('R2:T' . $lastRow)->applyFromArray($totalesDataStyle);     // Totales
        $sheet->getStyle('U2:W' . $lastRow)->applyFromArray($porcentajesDataStyle); // Porcentajes
    }

    // Asegurar que los porcentajes tengan el formato correcto
    if ($lastRow >= 2) {
        $sheet->getStyle('U2:W' . $lastRow)->getNumberFormat()
              ->setFormatCode(NumberFormat::FORMAT_PERCENTAGE_00);
    }

    // Autoajustar columnas
    foreach(range('A','W') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
}

// Función para llenar datos de una hoja con un lote específico
function llenarDatosLote($conn, $sheet, $lote) {
    // Consulta SQL actualizada para incluir información de usuario y filtro por lote
    $sql = "SELECT g.*, 
           b.real_hours AS bootcamp_hours, b.code AS bootcamp_code,
           e.real_hours AS english_hours, e.code AS english_code, 
           s.real_hours AS skills_hours, s.code AS skills_code,
           u.email AS personal_email, u.first_phone, u.second_phone
    FROM groups g
    LEFT JOIN courses b ON g.id_bootcamp = b.code 
    LEFT JOIN courses e ON g.id_english_code = e.code 
    LEFT JOIN courses s ON g.id_skills = s.code
    LEFT JOIN user_register u ON g.number_id = u.number_id
    WHERE u.lote = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $lote);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = 2; // Comenzar datos en fila 2
    $lastRow = $row; // Para mantener un registro de la última fila

    // En el bucle while, verificar que los valores existan antes de usarlos
    while($data = $result->fetch_assoc()) {
        // Datos básicos
        $sheet->setCellValue('A' . $row, $data['number_id'] ?? '');
        $sheet->setCellValue('B' . $row, $data['full_name'] ?? '');
        $sheet->setCellValue('C' . $row, $data['personal_email'] ?? '');
        $sheet->setCellValue('D' . $row, $data['institutional_email'] ?? '');
        $sheet->setCellValue('E' . $row, str_replace('+57', '', $data['first_phone'] ?? ''));
        $sheet->setCellValue('F' . $row, str_replace('+57', '', $data['second_phone'] ?? ''));
        $sheet->setCellValue('G' . $row, ($data['id_bootcamp'] ?? '') . ' - ' . ($data['bootcamp_name'] ?? ''));
        $sheet->setCellValue('H' . $row, $data['mode'] ?? '');
        
        // Obtener horas reales de courses con comprobación de valores null
        $horasTecnico = isset($data['bootcamp_hours']) ? intval($data['bootcamp_hours']) : 0;
        $horasIngles = isset($data['english_hours']) ? intval($data['english_hours']) : 0;
        $horasHabilidades = isset($data['skills_hours']) ? intval($data['skills_hours']) : 0;
        
        // Calcular horas actuales con comprobación adicional
        $horasActualesTecnico = isset($data['bootcamp_code']) && !empty($data['bootcamp_code']) ? 
            calcularHorasAsistencia($conn, $data['number_id'], $data['bootcamp_code']) : 0;
        
        $horasActualesIngles = isset($data['english_code']) && !empty($data['english_code']) ? 
            calcularHorasAsistencia($conn, $data['number_id'], $data['english_code']) : 0;
        
        $horasActualesHabilidades = isset($data['skills_code']) && !empty($data['skills_code']) ? 
            calcularHorasAsistencia($conn, $data['number_id'], $data['skills_code']) : 0;
        
        // Técnico
        $sheet->setCellValue('I' . $row, $horasActualesTecnico);
        $sheet->setCellValue('J' . $row, $horasTecnico);
        $sheet->setCellValue('K' . $row, 120);
        
        // Inglés
        $sheet->setCellValue('L' . $row, $horasActualesIngles);
        $sheet->setCellValue('M' . $row, $horasIngles);
        $sheet->setCellValue('N' . $row, 24);
        
        // Habilidades
        $sheet->setCellValue('O' . $row, $horasActualesHabilidades);
        $sheet->setCellValue('P' . $row, $horasHabilidades);
        $sheet->setCellValue('Q' . $row, 15);
        
        // Totales - Asegurar conversión a números
        $totalActual = intval($horasActualesTecnico) + intval($horasActualesIngles) + intval($horasActualesHabilidades);
        $totalReales = intval($horasTecnico) + intval($horasIngles) + intval($horasHabilidades);
        $sheet->setCellValue('R' . $row, $totalActual);
        $sheet->setCellValue('S' . $row, $totalReales);
        $sheet->setCellValue('T' . $row, 159);
        
        // Porcentajes
        $sheet->setCellValue('U' . $row, '=R' . $row . '/T' . $row);
        $sheet->setCellValue('V' . $row, '=S' . $row . '/T' . $row);
        $sheet->setCellValue('W' . $row, '=1-(S' . $row . '/T' . $row . ')');
        
        $lastRow = $row; // Actualizar la última fila
        $row++;
    }

    $stmt->close();
    return $lastRow;
}

// Crear nueva hoja de cálculo
$spreadsheet = new Spreadsheet();

// HOJA 1: Reporte Horas Lote 1
$sheet1 = $spreadsheet->getActiveSheet();
$sheet1->setTitle('Reporte Horas Lote 1');
configurarEncabezados($sheet1);
$lastRow1 = llenarDatosLote($conn, $sheet1, 1);
aplicarEstilos($sheet1, $lastRow1);

// HOJA 2: Reporte Horas Lote 2
$spreadsheet->createSheet();
$sheet2 = $spreadsheet->getSheet(1);
$sheet2->setTitle('Reporte Horas Lote 2');
configurarEncabezados($sheet2);
$lastRow2 = llenarDatosLote($conn, $sheet2, 2);
aplicarEstilos($sheet2, $lastRow2);

// HOJA 3: Conteo Bootcamps
$spreadsheet->createSheet();
$sheet3 = $spreadsheet->getSheet(2);
$sheet3->setTitle('Conteo Bootcamps');

// Establecer títulos
$sheet3->setCellValue('A1', 'Bootcamp');
$sheet3->setCellValue('B1', 'Inscritos');

// Consulta SQL para obtener bootcamps únicos y su conteo
$sqlBootcamps = "SELECT bootcamp_name, COUNT(*) as total 
                 FROM groups g
                 LEFT JOIN user_register u ON g.number_id = u.number_id
                 GROUP BY bootcamp_name 
                 ORDER BY bootcamp_name";
                 
$resultBootcamps = $conn->query($sqlBootcamps);

$row = 2; // Comenzar datos en fila 2
$totalInscritos = 0; // Variable para el total

while($bootcampData = $resultBootcamps->fetch_assoc()) {
    $sheet3->setCellValue('A' . $row, $bootcampData['bootcamp_name']);
    $sheet3->setCellValue('B' . $row, $bootcampData['total']);
    $totalInscritos += $bootcampData['total']; // Sumar al total
    $row++;
}

// Añadir fila de total
$sheet3->setCellValue('A' . $row, 'TOTAL INSCRITOS');
$sheet3->setCellValue('B' . $row, $totalInscritos);

// Estilo para la fila de total
$totalStyle = [
    'font' => [
        'bold' => true,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'E6E6E6'],
    ],
    'borders' => [
        'top' => [
            'borderStyle' => Border::BORDER_MEDIUM,
        ],
    ],
];

// Definir headerStyle para la tercera hoja
$headerStyle = [
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'CCCCCC']]
];

// Aplicar estilos
$sheet3->getStyle('A1:B1')->applyFromArray($headerStyle);
$sheet3->getStyle('A' . $row . ':B' . $row)->applyFromArray($totalStyle);
$sheet3->getColumnDimension('A')->setAutoSize(true);
$sheet3->getColumnDimension('B')->setAutoSize(true);

// Limpiar cualquier salida anterior
ob_end_clean();

// Asegurarse de que el archivo se guarde correctamente al final
try {
    $writer = new Xlsx($spreadsheet);
    $filename = 'Reporte_Horas_Por_Lotes_' . date('Y-m-d_H-i-s') . '.xlsx';
    
    // Encabezados HTTP para la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Pragma: public');
    
    // Guardar en la salida
    $writer->save('php://output');
    exit;
} catch (Exception $e) {
    ob_end_clean();
    echo "Error al generar el archivo: " . $e->getMessage();
}