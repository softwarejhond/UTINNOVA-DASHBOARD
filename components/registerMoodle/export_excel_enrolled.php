<?php
require __DIR__ . '../../../vendor/autoload.php';
require  '../../controller/conexion.php';// Asegúrate de incluir la conexión a la BD


use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Función para obtener los niveles de los usuarios 
function obtenerNivelesUsuarios($conn)
{
    $sql = "SELECT cedula, nivel FROM usuarios";
    $result = $conn->query($sql);

    $niveles = array();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $niveles[$row['cedula']] = $row['nivel'];
        }
    }

    return $niveles;
}

// Obtener los niveles de usuarios
$nivelesUsuarios = obtenerNivelesUsuarios($conn);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set headers
$sheet->setCellValue('A1', 'Tipo ID');
$sheet->setCellValue('B1', 'Número ID');
$sheet->setCellValue('C1', 'Nombre Completo');
$sheet->setCellValue('D1', 'Teléfono');
$sheet->setCellValue('E1', 'Email');
$sheet->setCellValue('F1', 'Email Institucional');
$sheet->setCellValue('G1', 'Modalidad');
$sheet->setCellValue('H1', 'Lote');
$sheet->setCellValue('I1', 'Sede');
$sheet->setCellValue('J1', 'Contraseña');
$sheet->setCellValue('K1', 'Fecha Inicio Bootcamp');
$sheet->setCellValue('L1', 'Fecha Fin Bootcamp');
$sheet->setCellValue('M1', 'ID Bootcamp');
$sheet->setCellValue('N1', 'Bootcamp');
$sheet->setCellValue('O1', 'ID Inglés Nivelatorio');
$sheet->setCellValue('P1', 'Inglés Nivelatorio');
$sheet->setCellValue('Q1', 'ID English Code');
$sheet->setCellValue('R1', 'English Code');
$sheet->setCellValue('S1', 'ID Habilidades');
$sheet->setCellValue('T1', 'Habilidades');
$sheet->setCellValue('U1', 'Cohorte');
$sheet->setCellValue('V1', 'Horario principal');         // NUEVO
$sheet->setCellValue('W1', 'Horario alternativo');       // NUEVO
$sheet->setCellValue('X1', 'Aula');                     // NUEVO
$sheet->setCellValue('Y1', 'Nivel Elegido');
$sheet->setCellValue('Z1', 'Puntaje de Prueba');
$sheet->setCellValue('AA1', 'Nivel Obtenido');

// Query to get data (agregando DISTINCT)
$query = "SELECT DISTINCT
            g.*, 
            ur.first_phone, 
            ur.lote, 
            ur.level,
            ur.schedules,
            ur.schedules_alternative,
            c.classroom_name,
            cp.cohort AS course_cohort,
            cp.start_date,
            cp.end_date
          FROM groups g 
          LEFT JOIN user_register ur ON g.number_id = ur.number_id
          LEFT JOIN classrooms c ON g.id_bootcamp = c.bootcamp_id
          LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code";
$stmt = $conn->query($query);
$row = 2;

// Array para controlar los number_id ya procesados
$procesados = [];

while ($data = mysqli_fetch_assoc($stmt)) {
    // Evitar duplicados por number_id
    if (in_array($data['number_id'], $procesados)) {
        continue;
    }
    $procesados[] = $data['number_id'];

    $sheet->setCellValue('A' . $row, $data['type_id']);
    $sheet->setCellValue('B' . $row, $data['number_id']);
    $sheet->setCellValue('C' . $row, str_replace(['Á','É','Í','Ó','Ú','á','é','í','ó','ú'], ['A','E','I','O','U','a','e','i','o','u'], mb_strtoupper($data['full_name'], 'UTF-8')));
    $sheet->setCellValue('D' . $row, str_replace('+57', '', $data['first_phone']));
    $sheet->setCellValue('E' . $row, $data['email']);
    $sheet->setCellValue('F' . $row, $data['institutional_email']);
    $sheet->setCellValue('G' . $row, $data['mode']);
    $sheet->setCellValue('H' . $row, $data['lote']);
    $sheet->setCellValue('I' . $row, $data['headquarters']);
    $sheet->setCellValue('J' . $row, $data['password']);
    $sheet->setCellValue('K' . $row, $data['start_date']);
    $sheet->setCellValue('L' . $row, $data['end_date']);
    $sheet->setCellValue('M' . $row, $data['id_bootcamp']);
    $sheet->setCellValue('N' . $row, $data['bootcamp_name']);
    $sheet->setCellValue('O' . $row, $data['id_leveling_english']);
    $sheet->setCellValue('P' . $row, $data['leveling_english_name']);
    $sheet->setCellValue('Q' . $row, $data['id_english_code']);
    $sheet->setCellValue('R' . $row, $data['english_code_name']);
    $sheet->setCellValue('S' . $row, $data['id_skills']);
    $sheet->setCellValue('T' . $row, $data['skills_name']);
    $sheet->setCellValue('U' . $row, $data['course_cohort']);
    $sheet->setCellValue('V' . $row, $data['schedules'] ?? '');
    $sheet->setCellValue('W' . $row, $data['schedules_alternative'] ?? '');
    $sheet->setCellValue('X' . $row, $data['classroom_name'] ?? '');
    $sheet->setCellValue('Y' . $row, $data['level'] ?? '');

    // Puntaje de prueba y nivel obtenido
    if (isset($nivelesUsuarios[$data['number_id']])) {
        $puntaje = $nivelesUsuarios[$data['number_id']];
        $sheet->setCellValue('Z' . $row, $puntaje);
        if ($puntaje >= 0 && $puntaje <= 5) {
            $sheet->setCellValue('AA' . $row, 'Básico');
        } elseif ($puntaje >= 6 && $puntaje <= 10) {
            $sheet->setCellValue('AA' . $row, 'Intermedio');
        } elseif ($puntaje >= 11 && $puntaje <= 15) {
            $sheet->setCellValue('AA' . $row, 'Avanzado');
        } else {
            $sheet->setCellValue('AA' . $row, 'Sin clasificar');
        }
    } else {
        $sheet->setCellValue('Z' . $row, 'No presentó');
        $sheet->setCellValue('AA' . $row, 'No presentó');
    }
    $row++;
}

// Auto size columns
foreach(range('A','AA') as $columnID) {
    $sheet->getColumnDimension($columnID)->setAutoSize(true);
}

// Set background color for header
$sheet->getStyle('A1:AA1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF808080');
// Set border for all cells
$sheet->getStyle('A1:AA' . ($row - 1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

// Set header for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="matriculados_moodle.xlsx"');
header('Cache-Control: max-age=0');

// Create Excel file
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>