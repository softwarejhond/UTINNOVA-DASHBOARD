<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

// Limpiar cualquier salida anterior
ob_clean();

// Recuperar parámetros
$bootcamp = isset($_GET['bootcamp']) ? (int)$_GET['bootcamp'] : 0;
$modalidad = $_GET['modalidad'] ?? '';
$sede = $_GET['sede'] ?? '';
$class_date = $_GET['class_date'] ?? '';
$courseType = $_GET['courseType'] ?? '';

if (empty($bootcamp) || empty($modalidad) || empty($sede) || empty($class_date) || empty($courseType)) {
    die("Parámetros incompletos");
}

// Si la modalidad es virtual, se fuerza la sede a 'No aplica'
if (strtolower($modalidad) === 'virtual') {
    $sede = 'No aplica';
}

// Definir la columna según el tipo de curso
$courseIdColumn = '';
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
}

// Consulta para obtener los datos del grupo con el estado de asistencia para la fecha seleccionada
$sql = "SELECT g.*, 
        ur.first_phone,
        (SELECT attendance_status FROM attendance_records 
        WHERE student_id = g.number_id AND class_date = ? AND course_id = ? LIMIT 1) as attendance_status
        FROM groups g 
        LEFT JOIN user_register ur ON g.number_id = ur.number_id
        WHERE g.$courseIdColumn = ? 
        AND g.mode = ? 
        AND g.headquarters = ?
        ORDER BY g.full_name ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "siiss", $class_date, $bootcamp, $bootcamp, $modalidad, $sede);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Crear nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Obtener nombre del curso para el título
$courseNameSql = "SELECT name FROM courses WHERE code = ?";
$courseStmt = mysqli_prepare($conn, $courseNameSql);
mysqli_stmt_bind_param($courseStmt, "i", $bootcamp);
mysqli_stmt_execute($courseStmt);
$courseResult = mysqli_stmt_get_result($courseStmt);
$courseName = "Curso ID: $bootcamp";
if ($courseRow = mysqli_fetch_assoc($courseResult)) {
    $courseName = $courseRow['name'];
}

// Configurar título del documento
$sheet->setCellValue('A1', "Reporte de Asistencia: $courseName");
$sheet->mergeCells('A1:I1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Fecha: $class_date | Modalidad: $modalidad | Sede: $sede");
$sheet->mergeCells('A2:I2');
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Configurar encabezados
$sheet->setCellValue('A4', '#');
$sheet->setCellValue('B4', 'Tipo ID');
$sheet->setCellValue('C4', 'Número ID');
$sheet->setCellValue('D4', 'Nombre Completo');
$sheet->setCellValue('E4', 'Teléfono');
$sheet->setCellValue('F4', 'Correo Institucional');
$sheet->setCellValue('G4', 'Estado Asistencia');
$sheet->setCellValue('H4', 'Registrado');
$sheet->setCellValue('I4', 'Observaciones');

// Dar formato a los encabezados
$headerRange = 'A4:I4';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID);
$sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('D9D9D9');

// Llenar datos
$row = 5;
$counter = 1;
while ($data = mysqli_fetch_assoc($result)) {
    $attendanceStatus = $data['attendance_status'] ?? null;
    $registered = !empty($attendanceStatus) ? 'Sí' : 'No';
    
    // Definir estado legible
    $statusText = '';
    if (!empty($attendanceStatus)) {
        switch(strtolower($attendanceStatus)) {
            case 'presente':
                $statusText = 'Presente';
                break;
            case 'tarde':
                $statusText = 'Tarde';
                break;
            case 'ausente':
                $statusText = 'Ausente';
                break;
            default:
                $statusText = ucfirst($attendanceStatus);
        }
    } else {
        $statusText = 'Sin registro';
    }

    $sheet->setCellValue('A' . $row, $counter);
    $sheet->setCellValue('B' . $row, $data['type_id']);
    $sheet->setCellValue('C' . $row, $data['number_id']);
    $sheet->setCellValue('D' . $row, $data['full_name']);
    $sheet->setCellValue('E' . $row, $data['first_phone'] ?? 'No registrado');
    $sheet->setCellValue('F' . $row, $data['institutional_email']);
    $sheet->setCellValue('G' . $row, $statusText);
    $sheet->setCellValue('H' . $row, $registered);
    
    // Colorear filas según estado de asistencia
    if (!empty($attendanceStatus)) {
        if (strtolower($attendanceStatus) === 'ausente') {
            $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle("A{$row}:I{$row}")->getFill()->getStartColor()->setRGB('FFCCCC'); // Rojo claro
        } elseif (strtolower($attendanceStatus) === 'tarde') {
            $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
            $sheet->getStyle("A{$row}:I{$row}")->getFill()->getStartColor()->setRGB('FFFFCC'); // Amarillo claro
        }
    } else {
        // Sin registro de asistencia
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID);
        $sheet->getStyle("A{$row}:I{$row}")->getFill()->getStartColor()->setRGB('E6E6E6'); // Gris claro
        $sheet->setCellValue('I' . $row, 'Sin registro de asistencia');
    }

    $row++;
    $counter++;
}

// Ajustar ancho de columnas
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Asegurarse de que no hay salida previa
if (ob_get_length()) ob_end_clean();

// Configurar headers para descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="asistencia_grupo_' . $bootcamp . '_' . $class_date . '.xlsx"');
header('Cache-Control: max-age=0');

// Crear el writer después de los headers
$writer = new Xlsx($spreadsheet);

// Guardar directamente a PHP output
$writer->save('php://output');
exit();