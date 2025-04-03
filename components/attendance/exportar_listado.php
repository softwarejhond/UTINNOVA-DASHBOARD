<?php
session_start(); // Iniciar sesión para acceder a los datos del profesor
require_once __DIR__ . '/../../vendor/autoload.php'; // Incluye el autoload de Composer
require_once __DIR__ . '/../../controller/conexion.php'; // Incluye la conexión a la base de datos

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Verificar que el usuario está autenticado
if (!isset($_SESSION['username'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

// Obtener el ID del profesor de la sesión
$teacher_id = $_SESSION['username'];

// Verificar que se reciba una solicitud POST con el curso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'] ?? null;
    $course_type = $_POST['course_type'] ?? null;
    $course_name = $_POST['course_name'] ?? 'Curso sin nombre';

    if (empty($course_id) || empty($course_type)) {
        echo json_encode(['error' => 'Faltan datos requeridos (curso y tipo de curso)']);
        exit;
    }

    $course_id_field = '';
    switch ($course_type) {
        case 'bootcamp':
            $course_id_field = 'id_bootcamp';
            break;
        case 'leveling_english':
            $course_id_field = 'id_leveling_english';
            break;
        case 'english_code':
            $course_id_field = 'id_english_code';
            break;
        case 'skills':
            $course_id_field = 'id_skills';
            break;
        default:
            echo json_encode(['error' => 'Tipo de curso inválido']);
            exit;
    }

    // Obtener información del curso y el profesor
    $course_query = "SELECT 
                       c.name,
                       c.teacher,
                       u.nombre as teacher_name
                     FROM courses c
                     LEFT JOIN users u ON c.teacher = u.username
                     WHERE c.code = ?
                     LIMIT 1";

    $stmt_course = mysqli_prepare($conn, $course_query);
    if (!$stmt_course) {
        echo json_encode(['error' => 'Error en la preparación de la consulta del curso: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt_course, "i", $course_id);
    mysqli_stmt_execute($stmt_course);
    $course_result = mysqli_stmt_get_result($stmt_course);
    $course_data = mysqli_fetch_assoc($course_result);

    if (!$course_data) {
        echo json_encode(['error' => 'No se encontró información del curso o profesor']);
        exit;
    }

    $teacher_id = $course_data['teacher'];
    $teacher_name = $course_data['teacher_name'] ?? 'Sin asignar';

    // Obtener los datos de los estudiantes del curso especificado
    $sql = "SELECT 
                g.number_id as student_id,
                g.full_name,
                g.institutional_email,
                ur.email,
                ur.first_phone,
                ur.second_phone,
                g.mode,
                g.headquarters
            FROM groups g
            LEFT JOIN user_register ur ON g.number_id = ur.number_id
            WHERE g.{$course_id_field} = ?
            ORDER BY g.full_name ASC";

    $stmt = mysqli_prepare($conn, $sql);
    if (!$stmt) {
        echo json_encode(['error' => 'Error en la preparación de la consulta: ' . mysqli_error($conn)]);
        exit;
    }

    mysqli_stmt_bind_param($stmt, "i", $course_id);
    if (!mysqli_stmt_execute($stmt)) {
        echo json_encode(['error' => 'Error en la ejecución de la consulta: ' . mysqli_stmt_error($stmt)]);
        exit;
    }

    $result = mysqli_stmt_get_result($stmt);
    if (!$result) {
        echo json_encode(['error' => 'Error al obtener resultados: ' . mysqli_error($conn)]);
        exit;
    }

    // Crear un nuevo archivo de Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Establecer títulos y encabezados con el formato solicitado
    $sheet->setCellValue('B1', 'CURSO:');
    $sheet->setCellValue('B2', 'DATOS DEL DOCENTE (ID - NOMBRE):');

    $sheet->setCellValue('C1', " {$course_id} - {$course_name}");
    $sheet->setCellValue('C2', " {$teacher_id} - {$teacher_name}");

    // Aplicar formato a los títulos
    $sheet->getStyle('B1:C2')->getFont()->setBold(true);
    $sheet->getStyle('B1')->getFont()->setSize(14);

    // Dejar una fila vacía y agregar los encabezados de la tabla en la fila 4
    $sheet->setCellValue('A4', 'ID Estudiante');
    $sheet->setCellValue('B4', 'Nombre Completo');
    $sheet->setCellValue('C4', 'Correo Institucional');
    $sheet->setCellValue('D4', 'Correo Personal');
    $sheet->setCellValue('E4', 'Teléfono Principal');
    $sheet->setCellValue('F4', 'Teléfono Secundario');
    $sheet->setCellValue('G4', 'Modalidad');
    $sheet->setCellValue('H4', 'Sede');

    // Dar formato a los encabezados
    $sheet->getStyle('A4:H4')->getFont()->setBold(true);
    $sheet->getStyle('A4:H4')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $sheet->getStyle('A4:H4')->getFill()->getStartColor()->setRGB('D9D9D9');

    // Llenar la hoja con los datos a partir de la fila 5
    $row = 5;
    $hasData = false;
    while ($data = mysqli_fetch_assoc($result)) {
        $hasData = true;
        $sheet->setCellValue('A' . $row, $data['student_id']);
        $sheet->setCellValue('B' . $row, $data['full_name']);
        $sheet->setCellValue('C' . $row, $data['institutional_email']);
        $sheet->setCellValue('D' . $row, $data['email']);
        $sheet->setCellValue('E' . $row, $data['first_phone']);
        $sheet->setCellValue('F' . $row, $data['second_phone']);
        $sheet->setCellValue('G' . $row, $data['mode']);
        $sheet->setCellValue('H' . $row, $data['headquarters']);
        $row++;
    }

    if (!$hasData) {
        // Si no hay datos, agregar mensaje informativo
        $sheet->setCellValue('A5', 'No hay estudiantes registrados para este curso');
        $sheet->mergeCells('A5:H5');
    }

    // Ajustar el ancho de las columnas automáticamente
    foreach (range('A', 'H') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Guardar el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'listado_estudiantes_' . $course_id . '.xlsx';

    // Enviar el archivo al navegador
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;
} else {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}
