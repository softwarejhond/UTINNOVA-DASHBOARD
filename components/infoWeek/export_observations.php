<?php
// filepath: c:\xampp\htdocs\UTINNOVA-DASHBOARD\components\infoWeek\export_observations.php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si se solicita la exportación
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportObservationsToExcel($conn);
    exit;
}

function exportObservationsToExcel($conn)
{
    // Primera consulta: obtener todos los cursos únicos
    $sqlCourses = "SELECT DISTINCT id_bootcamp AS course_id, bootcamp_name
                   FROM groups
                   WHERE id_bootcamp IS NOT NULL AND bootcamp_name IS NOT NULL
                   ORDER BY bootcamp_name";
    $resultCourses = $conn->query($sqlCourses);
    
    // Segunda consulta: obtener solo observaciones que existen
    $sqlObservations = "SELECT co.course_id, co.observation_type, COUNT(*) as count 
                       FROM class_observations co
                       GROUP BY co.course_id, co.observation_type
                       ORDER BY co.course_id, count DESC";
    $resultObservations = $conn->query($sqlObservations);
    
    // Organizar observaciones por curso_id
    $observationsByCourse = [];
    if ($resultObservations && $resultObservations->num_rows > 0) {
        while ($row = $resultObservations->fetch_assoc()) {
            $observationsByCourse[$row['course_id']][] = [
                'type' => $row['observation_type'],
                'count' => $row['count']
            ];
        }
    }
    
    $data = [];
    
    if ($resultCourses && $resultCourses->num_rows > 0) {
        while ($course = $resultCourses->fetch_assoc()) {
            $courseId = $course['course_id'];
            $courseName = $course['bootcamp_name'];
            
            if (isset($observationsByCourse[$courseId]) && !empty($observationsByCourse[$courseId])) {
                // Curso con observaciones - una fila por tipo de observación
                foreach ($observationsByCourse[$courseId] as $obs) {
                    $data[] = [
                        'Curso' => $courseName,
                        'Tipo de Observación' => $obs['type'],
                        'Cantidad' => $obs['count']
                    ];
                }
            } else {
                // Curso sin observaciones - una sola fila (solo si realmente no tiene observaciones)
                $data[] = [
                    'Curso' => $courseName,
                    'Tipo de Observación' => 'Sin observaciones',
                    'Cantidad' => 0
                ];
            }
        }
    }
    
    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Observaciones Asistencia');
    
    // Encabezados
    $headers = ['Curso', 'Tipo de Observación', 'Cantidad'];
    $sheet->fromArray($headers, NULL, 'A1');
    
    // Datos
    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), NULL, "A{$rowIndex}");
        $rowIndex++;
    }
    
    // Estilo para encabezados - siguiendo el patrón de otros archivos de exportación
    $sheet->getStyle('A1:C1')->getFont()->setBold(true);
    $sheet->getStyle('A1:C1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDAB9'); // Color durazno como en exportAll.php
    
    // Ajustar ancho de columnas automáticamente
    $sheet->getColumnDimension('A')->setAutoSize(true); // Curso
    $sheet->getColumnDimension('B')->setAutoSize(true); // Tipo de Observación
    $sheet->getColumnDimension('C')->setAutoSize(true); // Cantidad
    
    // Aplicar filtros a los encabezados (opcional, útil para análisis)
    $sheet->setAutoFilter('A1:C' . ($rowIndex - 1));
    
    ob_clean();
    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="observaciones_asistencia_' . date('Y-m-d') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>