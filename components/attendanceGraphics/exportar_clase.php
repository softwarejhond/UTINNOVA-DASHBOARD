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
$courseCode = $_GET['courseCode'] ?? '';
$classDate = $_GET['classDate'] ?? '';
$courseType = $_GET['courseType'] ?? 'bootcamp';

if (empty($courseCode) || empty($classDate)) {
    die("Parámetros incompletos");
}

// Buscar el curso en Moodle para obtener el ID
$moodleCourseId = findMoodleCourseId($courseCode);
if (!$moodleCourseId) {
    die("Curso no encontrado en Moodle");
}

// Definir la columna según el tipo de curso
$courseIdColumn = '';
$courseTypeName = '';
switch ($courseType) {
    case 'bootcamp':
        $courseIdColumn = 'id_bootcamp';
        $courseTypeName = 'Técnico';
        break;
    case 'leveling_english':
        $courseIdColumn = 'id_leveling_english';
        $courseTypeName = 'Inglés Nivelador';
        break;
    case 'english_code':
        $courseIdColumn = 'id_english_code';
        $courseTypeName = 'English Code';
        break;
    case 'skills':
        $courseIdColumn = 'id_skills';
        $courseTypeName = 'Habilidades';
        break;
    default:
        die("Tipo de curso no válido");
}

// Consulta IGUAL que exportar_grupo.php pero sin filtros de modalidad y sede
$sql = "SELECT g.*, 
        ur.first_phone,
        (SELECT attendance_status FROM attendance_records 
        WHERE student_id = g.number_id AND class_date = ? AND course_id = ? LIMIT 1) as attendance_status
        FROM groups g 
        LEFT JOIN user_register ur ON g.number_id = ur.number_id
        WHERE g.$courseIdColumn = ?
        ORDER BY g.full_name ASC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "sii", $classDate, $moodleCourseId, $moodleCourseId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Crear nuevo documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Obtener nombre del curso desde la base de datos courses (IGUAL que exportar_grupo.php)
$courseNameSql = "SELECT name FROM courses WHERE code = ?";
$courseStmt = mysqli_prepare($conn, $courseNameSql);
mysqli_stmt_bind_param($courseStmt, "i", $moodleCourseId);
mysqli_stmt_execute($courseStmt);
$courseResult = mysqli_stmt_get_result($courseStmt);
$courseName = "Curso ID: $moodleCourseId";
if ($courseRow = mysqli_fetch_assoc($courseResult)) {
    $courseName = $courseRow['name'];
}

// Formatear la fecha
$formattedDate = date('d/m/Y', strtotime($classDate));
$dayNames = [
    'Monday' => 'Lunes',
    'Tuesday' => 'Martes',
    'Wednesday' => 'Miércoles',
    'Thursday' => 'Jueves',
    'Friday' => 'Viernes',
    'Saturday' => 'Sábado',
    'Sunday' => 'Domingo'
];
$englishDayName = date('l', strtotime($classDate));
$spanishDayName = $dayNames[$englishDayName] ?? $englishDayName;

// Configurar título del documento (SIMILAR a exportar_grupo.php)
$sheet->setCellValue('A1', "Reporte de Asistencia: $courseName");
$sheet->mergeCells('A1:I1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
$sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sheet->setCellValue('A2', "Fecha: $formattedDate - $spanishDayName | Tipo: $courseTypeName");
$sheet->mergeCells('A2:I2');
$sheet->getStyle('A2')->getFont()->setBold(true);
$sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Configurar encabezados (EXACTAMENTE IGUAL que exportar_grupo.php)
$sheet->setCellValue('A4', '#');
$sheet->setCellValue('B4', 'Tipo ID');
$sheet->setCellValue('C4', 'Número ID');
$sheet->setCellValue('D4', 'Nombre Completo');
$sheet->setCellValue('E4', 'Teléfono');
$sheet->setCellValue('F4', 'Correo Institucional');
$sheet->setCellValue('G4', 'Estado Asistencia');
$sheet->setCellValue('H4', 'Registrado');
$sheet->setCellValue('I4', 'Observaciones');

// Dar formato a los encabezados (EXACTAMENTE IGUAL que exportar_grupo.php)
$headerRange = 'A4:I4';
$sheet->getStyle($headerRange)->getFont()->setBold(true);
$sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID);
$sheet->getStyle($headerRange)->getFill()->getStartColor()->setRGB('D9D9D9');

// Contadores para estadísticas
$totalStudents = 0;
$presentCount = 0;
$lateCount = 0;
$absentCount = 0;
$noRecordCount = 0;

// Llenar datos (EXACTAMENTE IGUAL que exportar_grupo.php)
$row = 5;
$counter = 1;
while ($data = mysqli_fetch_assoc($result)) {
    $attendanceStatus = $data['attendance_status'] ?? null;
    $registered = !empty($attendanceStatus) ? 'Sí' : 'No';
    
    // Definir estado legible (EXACTAMENTE IGUAL que exportar_grupo.php)
    $statusText = '';
    if (!empty($attendanceStatus)) {
        switch(strtolower($attendanceStatus)) {
            case 'presente':
                $statusText = 'Presente';
                $presentCount++;
                break;
            case 'tarde':
                $statusText = 'Tarde';
                $lateCount++;
                break;
            case 'ausente':
                $statusText = 'Ausente';
                $absentCount++;
                break;
            default:
                $statusText = ucfirst($attendanceStatus);
        }
    } else {
        $statusText = 'Sin registro';
        $noRecordCount++;
    }

    $sheet->setCellValue('A' . $row, $counter);
    $sheet->setCellValue('B' . $row, $data['type_id']);
    $sheet->setCellValue('C' . $row, $data['number_id']);
    $sheet->setCellValue('D' . $row, $data['full_name']);
    $sheet->setCellValue('E' . $row, $data['first_phone'] ?? 'No registrado');
    $sheet->setCellValue('F' . $row, $data['institutional_email']);
    $sheet->setCellValue('G' . $row, $statusText);
    $sheet->setCellValue('H' . $row, $registered);
    
    // Colorear filas según estado de asistencia (EXACTAMENTE IGUAL que exportar_grupo.php)
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
    $totalStudents++;
}

// Agregar estadísticas al final (ADICIONAL para mejorar el reporte)
$row += 2;
$sheet->setCellValue('A' . $row, 'RESUMEN DE ASISTENCIA');
$sheet->mergeCells("A{$row}:I{$row}");
$sheet->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$row++;
$sheet->setCellValue('B' . $row, 'Total Estudiantes:');
$sheet->setCellValue('C' . $row, $totalStudents);
$sheet->setCellValue('E' . $row, 'Presentes:');
$sheet->setCellValue('F' . $row, $presentCount);
$sheet->setCellValue('G' . $row, '(' . ($totalStudents > 0 ? round(($presentCount / $totalStudents) * 100, 1) : 0) . '%)');

$row++;
$sheet->setCellValue('E' . $row, 'Tardíos:');
$sheet->setCellValue('F' . $row, $lateCount);
$sheet->setCellValue('G' . $row, '(' . ($totalStudents > 0 ? round(($lateCount / $totalStudents) * 100, 1) : 0) . '%)');

$row++;
$sheet->setCellValue('E' . $row, 'Ausentes:');
$sheet->setCellValue('F' . $row, $absentCount);
$sheet->setCellValue('G' . $row, '(' . ($totalStudents > 0 ? round(($absentCount / $totalStudents) * 100, 1) : 0) . '%)');

$row++;
$sheet->setCellValue('E' . $row, 'Sin Registro:');
$sheet->setCellValue('F' . $row, $noRecordCount);
$sheet->setCellValue('G' . $row, '(' . ($totalStudents > 0 ? round(($noRecordCount / $totalStudents) * 100, 1) : 0) . '%)');

// Dar formato a las estadísticas
$statsRange = "B" . ($row - 4) . ":G{$row}";
$sheet->getStyle($statsRange)->getFont()->setBold(true);

// Ajustar ancho de columnas (EXACTAMENTE IGUAL que exportar_grupo.php)
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Asegurarse de que no hay salida previa (EXACTAMENTE IGUAL que exportar_grupo.php)
if (ob_get_length()) ob_end_clean();

// Configurar headers para descarga (EXACTAMENTE IGUAL que exportar_grupo.php)
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="asistencia_clase_' . $courseCode . '_' . $classDate . '.xlsx"');
header('Cache-Control: max-age=0');

// Crear el writer después de los headers (EXACTAMENTE IGUAL que exportar_grupo.php)
$writer = new Xlsx($spreadsheet);

// Guardar directamente a PHP output (EXACTAMENTE IGUAL que exportar_grupo.php)
$writer->save('php://output');
exit();

// Función para buscar el ID del curso en Moodle
function findMoodleCourseId($courseCode) {
    $api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
    $token = "3f158134506350615397c83d861c2104";
    $format = "json";

    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_course_get_courses',
        'moodlewsrestformat' => $format
    ];

    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $courses = json_decode($response, true);
    
    if (!$courses) {
        return null;
    }

    foreach ($courses as $course) {
        if (strpos($course['fullname'], $courseCode) !== false) {
            return $course['id'];
        }
    }

    return null;
}
?>