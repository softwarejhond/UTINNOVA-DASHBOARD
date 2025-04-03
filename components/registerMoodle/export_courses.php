<?php
require __DIR__ . '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Función para llamar a la API de Moodle
function callMoodleAPI($function, $params = [])
{
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();

function exportCoursesToExcel($courses_data) {
    // Crear una nueva hoja de cálculo
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Establecer los encabezados
    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Nombre del Curso');
    
    // Estilo para los encabezados
    $sheet->getStyle('A1:B1')->getFont()->setBold(true);
    
    // Iniciar desde la fila 2 para los datos
    $row = 2;
    
    // Llenar los datos
    foreach ($courses_data as $course) {
        $sheet->setCellValue('A' . $row, $course['id']);
        $sheet->setCellValue('B' . $row, $course['fullname']);
        $row++;
    }
    
    // Autoajustar el ancho de las columnas
    foreach(range('A','B') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Crear el archivo Excel
    $writer = new Xlsx($spreadsheet);
    
    // Establecer headers para la descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="cursos_moodle.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Guardar el archivo
    $writer->save('php://output');
    exit;
}

// Para usar la función, primero obtén los cursos y luego llama a la función de exportación
if (isset($_POST['export'])) {
    $courses_data = getCourses();
    exportCoursesToExcel($courses_data);
}
