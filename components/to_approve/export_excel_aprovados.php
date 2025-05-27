<?php
// Control de errores para prevenir salida inesperada
ob_start();
error_reporting(E_ALL);
ini_set('display_errors', 0);

require __DIR__ . '/../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

// Obtener parámetros
$bootcamp = isset($_POST['bootcamp']) ? $_POST['bootcamp'] : null;
$courseType = isset($_POST['courseType']) ? $_POST['courseType'] : null;
$modalidad = isset($_POST['modalidad']) ? $_POST['modalidad'] : null;
$sede = isset($_POST['sede']) ? $_POST['sede'] : null;

if (!$bootcamp || !$courseType || !$modalidad || !$sede) {
    http_response_code(400);
    echo json_encode(['error' => 'Faltan datos requeridos']);
    exit;
}

if (strtolower($modalidad) === 'virtual') {
    $sede = 'No aplica';
}

// Copiar las funciones de buscar_aprovados.php
function calcularHorasAsistencia($conn, $studentId, $courseId) {
    if (empty($courseId)) return 0;
    
    $sql = "SELECT ar.class_date, 
                   CASE 
                      WHEN ar.attendance_status = 'presente' THEN 
                          CASE DAYOFWEEK(ar.class_date)
                              WHEN 2 THEN c.monday_hours
                              WHEN 3 THEN c.tuesday_hours
                              WHEN 4 THEN c.wednesday_hours
                              WHEN 5 THEN c.thursday_hours
                              WHEN 6 THEN c.friday_hours
                              WHEN 7 THEN c.saturday_hours
                              WHEN 1 THEN c.sunday_hours
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
        return 0;
    }
    
    $stmt->bind_param("si", $studentId, $courseId);
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    
    $fechasContadas = [];
    $totalHoras = 0;
    
    while($asistencia = $result->fetch_assoc()) {
        $fecha = $asistencia['class_date'];
        
        if (!in_array($fecha, $fechasContadas)) {
            $totalHoras += $asistencia['horas'];
            $fechasContadas[] = $fecha;
        }
    }
    
    $stmt->close();
    return $totalHoras;
}

function calcularHorasTotalesEstudiante($conn, $studentId) {
    $sql = "SELECT id_bootcamp, id_english_code, id_skills 
            FROM groups WHERE number_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("s", $studentId);
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    if (!$row) {
        return 0;
    }
    
    $totalHoras = 0;
    
    if (!empty($row['id_bootcamp'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_bootcamp']);
    }
    
    if (!empty($row['id_english_code'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_english_code']);
    }
    
    if (!empty($row['id_skills'])) {
        $totalHoras += calcularHorasAsistencia($conn, $studentId, $row['id_skills']);
    }
    
    return $totalHoras;
}

function obtenerNotaFinal($conn, $studentId, $courseCode) {
    if (empty($courseCode)) return 0;
    
    $sql = "SELECT final_grade FROM student_grades 
            WHERE student_number_id = ? AND course_code = ? 
            ORDER BY updated_at DESC LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 0;
    }
    
    $stmt->bind_param("ss", $studentId, $courseCode);
    if (!$stmt->execute()) {
        return 0;
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? floatval($row['final_grade']) : 0;
}

function estaAprobado($conn, $studentId, $courseCode) {
    $sql = "SELECT id FROM course_approvals WHERE student_number_id = ? AND course_code = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return false;
    }
    
    $stmt->bind_param("ss", $studentId, $courseCode);
    if (!$stmt->execute()) {
        return false;
    }
    
    $result = $stmt->get_result();
    $approved = $result->num_rows > 0;
    $stmt->close();
    
    return $approved;
}

function obtenerNombrePrograma($conn, $courseType, $courseId) {
    if (empty($courseId)) return 'No asignado';
    
    $sql = "SELECT name FROM courses WHERE code = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return 'Error al consultar';
    }
    
    $stmt->bind_param("s", $courseId);
    if (!$stmt->execute()) {
        return 'Error al ejecutar';
    }
    
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return $row ? $row['name'] : 'Programa no encontrado';
}

// Determinar columna del curso
$courseIdColumn = '';
$horasRequeridas = 159;

switch ($courseType) {
    case 'bootcamp':
        $courseIdColumn = 'id_bootcamp';
        break;
    case 'leveling_english':
        $courseIdColumn = 'id_leveling_english';
        break;
    case 'english_code':
        $courseIdColumn = 'id_english_code';
        break;
    case 'skills':
        $courseIdColumn = 'id_skills';
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Tipo de curso inválido']);
        exit;
}

try {
    // Crear nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Estudiantes Aprobados');

    // Establecer encabezados
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Número de Identificación');
    $sheet->setCellValue('C1', 'Nombre Completo');
    $sheet->setCellValue('D1', 'Correo Institucional');
    $sheet->setCellValue('E1', 'Programa');
    $sheet->setCellValue('F1', 'Modalidad');
    $sheet->setCellValue('G1', 'Sede');
    $sheet->setCellValue('H1', 'Horas Asistidas');
    $sheet->setCellValue('I1', 'Porcentaje Asistencia');
    $sheet->setCellValue('J1', 'Nota Final');
    $sheet->setCellValue('K1', 'Estado');
    $sheet->setCellValue('L1', 'Fecha Exportación');

    // Estilo del encabezado
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF']
        ],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => '30336b']
        ],
        'alignment' => [
            'horizontal' => Alignment::HORIZONTAL_CENTER,
            'vertical' => Alignment::VERTICAL_CENTER
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN
            ]
        ]
    ];

    $sheet->getStyle('A1:L1')->applyFromArray($headerStyle);

    // Consultar estudiantes aprobados
    $sql = "SELECT g.*, c.real_hours 
            FROM groups g
            LEFT JOIN courses c ON g.$courseIdColumn = c.code
            WHERE g.$courseIdColumn = ? 
            AND g.mode = ? 
            AND g.headquarters = ?
            ORDER BY g.full_name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        throw new Exception('Error en la preparación: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "iss", $bootcamp, $modalidad, $sede);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Error en la ejecución: ' . mysqli_stmt_error($stmt));
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        throw new Exception('Error al obtener resultados: ' . mysqli_error($conn));
    }

    $row = 2;
    $contador = 1;
    $estudiantesExportados = 0;

    while ($data = mysqli_fetch_assoc($result)) {
        // Calcular horas totales
        $horasAsistidas = calcularHorasTotalesEstudiante($conn, $data['number_id']);
        $porcentajeAsistencia = ($horasAsistidas / $horasRequeridas) * 100;
        
        // Obtener nota final
        $notaFinal = obtenerNotaFinal($conn, $data['number_id'], $bootcamp);
        
        // Verificar criterios
        $cumpleAsistencia = $porcentajeAsistencia >= 70;
        $cumpleNota = $notaFinal >= 3.0;
        $cumpleCriterios = $cumpleAsistencia && $cumpleNota;
        
        // Verificar si está aprobado
        $yaAprobado = estaAprobado($conn, $data['number_id'], $bootcamp);
        
        // Solo exportar estudiantes que cumplan criterios
        if ($cumpleCriterios) {
            $nombrePrograma = obtenerNombrePrograma($conn, $courseType, $bootcamp);
            $estado = $yaAprobado ? 'Aprobado' : 'Apto';
            
            $sheet->setCellValue('A' . $row, $contador);
            $sheet->setCellValue('B' . $row, $data['number_id']);
            $sheet->setCellValue('C' . $row, $data['full_name']);
            $sheet->setCellValue('D' . $row, $data['institutional_email']);
            $sheet->setCellValue('E' . $row, $nombrePrograma);
            $sheet->setCellValue('F' . $row, $data['mode']);
            $sheet->setCellValue('G' . $row, $data['headquarters']);
            $sheet->setCellValue('H' . $row, $horasAsistidas . '/' . $horasRequeridas);
            $sheet->setCellValue('I' . $row, number_format($porcentajeAsistencia, 1) . '%');
            $sheet->setCellValue('J' . $row, number_format($notaFinal, 1));
            $sheet->setCellValue('K' . $row, $estado);
            $sheet->setCellValue('L' . $row, date('Y-m-d H:i:s'));
            
            // Aplicar color según el estado
            if ($yaAprobado) {
                $sheet->getStyle('K' . $row)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FFFFD700'); // Dorado
            } else {
                $sheet->getStyle('K' . $row)->getFill()
                      ->setFillType(Fill::FILL_SOLID)
                      ->getStartColor()->setARGB('FF66CC00'); // Verde
            }
            
            $row++;
            $contador++;
            $estudiantesExportados++;
        }
    }

    if ($estudiantesExportados === 0) {
        $sheet->setCellValue('A2', 'No hay estudiantes que cumplan los criterios');
        $sheet->mergeCells('A2:L2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    // Auto-ajustar columnas
    foreach(range('A','L') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }

    // Aplicar bordes a toda la tabla
    $totalRows = $row - 1;
    $sheet->getStyle('A1:L' . $totalRows)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    // Generar nombre del archivo
    $tiposCurso = [
        'bootcamp' => 'Tecnico',
        'leveling_english' => 'Ingles_Nivelatorio',
        'english_code' => 'English_Code',
        'skills' => 'Habilidades'
    ];
    
    $tipoCursoNombre = $tiposCurso[$courseType] ?? 'Curso';
    $fechaHora = date('Y-m-d_H-i-s');
    $sedeClean = str_replace(' ', '_', $sede);
    $filename = "estudiantes_aprobados_{$tipoCursoNombre}_{$modalidad}_{$sedeClean}_{$fechaHora}.xlsx";

    // Limpiar buffer de salida
    ob_end_clean();

    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate');
    header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
    header('Pragma: public');
    header('Expires: 0');

    // Crear y enviar archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');

} catch (Exception $e) {
    ob_end_clean();
    
    // Configurar headers para respuesta de error JSON
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode([
        'error' => true,
        'message' => 'Error al generar el archivo: ' . $e->getMessage()
    ]);
}

exit;
?>