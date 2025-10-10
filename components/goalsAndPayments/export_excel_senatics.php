<?php
require __DIR__ . '../../../vendor/autoload.php';
require '../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('memory_limit', '1024M');

function exportDataToExcel($conn)
{
    // Consulta principal basada en getSenaTICSList.php
    $sql = "
        SELECT 
            ur.number_id,
            CONCAT(ur.first_name, ' ', COALESCE(ur.second_name, ''), ' ', ur.first_last, ' ', COALESCE(ur.second_last, '')) AS nombre_completo,
            COALESCE(ur.birthdate, 'N/A') AS birth_date,
            COALESCE(ur.email, 'N/A') AS email,
            COALESCE(ur.first_phone, 'N/A') AS phone,
            COALESCE(ur.program, 'N/A') AS program,
            COALESCE(g.mode, 'N/A') AS modalidad,
            CASE ur.statusAdmin
                WHEN 1 THEN 'BENEFICIARIO'
                WHEN 0 THEN 'SIN ESTADO'
                WHEN 2 THEN 'RECHAZADO'
                WHEN 3 THEN 'MATRICULADO'
                WHEN 4 THEN 'SIN CONTACTO'
                WHEN 5 THEN 'EN PROCESO'
                WHEN 6 THEN 'CERTIFICADO'
                WHEN 7 THEN 'INACTIVO'
                WHEN 8 THEN 'BENEFICIARIO CONTRAPARTIDA'
                WHEN 9 THEN 'APLAZADO'
                WHEN 10 THEN 'FORMADO'
                WHEN 11 THEN 'NO VALIDO'
                WHEN 12 THEN 'NO APROBADO'
                ELSE 'DESCONOCIDO'
            END AS statusAdmin,
            COALESCE(g.bootcamp_name, 'N/A') AS bootcamp_name,
            COALESCE(g.leveling_english_name, 'N/A') AS leveling_english_name,
            COALESCE(g.english_code_name, 'N/A') AS english_code_name,
            COALESCE(g.skills_name, 'N/A') AS skills_name,
            COALESCE(g.creation_date, 'N/A') AS fecha_matricula,
            CASE 
                WHEN u.nivel IS NULL THEN 'N/A'
                WHEN CAST(u.nivel AS UNSIGNED) >= 0 AND CAST(u.nivel AS UNSIGNED) < 5 THEN 'Básico'
                WHEN CAST(u.nivel AS UNSIGNED) >= 5 AND CAST(u.nivel AS UNSIGNED) < 11 THEN 'Intermedio'
                ELSE 'Avanzado'
            END AS nivel,
            COALESCE(u.fecha_registro, 'N/A') AS fecha_prueba
        FROM user_register ur
        LEFT JOIN groups g ON ur.number_id = g.number_id
        LEFT JOIN usuarios u ON ur.number_id = u.cedula
        WHERE ur.institution = 'SenaTICS'
    ";

    $result = $conn->query($sql);
    $data = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }

    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Matriz general SenaTICS');

    // Encabezados
    $headers = array_keys($data[0] ?? []);
    $sheet->fromArray($headers, NULL, 'A1');

    // Datos
    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), NULL, "A{$rowIndex}");
        $rowIndex++;
    }

    // Estilo para la primera hoja
    $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastColumn . '1';
    $headerStyle = $sheet->getStyle($headerRange);
    $headerStyle->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFD3D3D3');
    $headerStyle->getFont()->setBold(true);

    // Ajustar ancho de columnas
    foreach ($headers as $colIndex => $headerText) {
        $column = Coordinate::stringFromColumnIndex($colIndex + 1);
        $width = mb_strlen($headerText) + 2;
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    // Crear segunda hoja con todos los datos de user_register para SenaTICS
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Usuarios Registrados SenaTICS');

    $sql2 = "SELECT * FROM user_register WHERE institution = 'SenaTICS'";
    $result2 = $conn->query($sql2);
    $data2 = [];

    if ($result2 && $result2->num_rows > 0) {
        $headers2 = array_keys($result2->fetch_assoc());
        $result2->data_seek(0); // Reset pointer
        $sheet2->fromArray($headers2, NULL, 'A1');

        $rowIndex2 = 2;
        while ($row = $result2->fetch_assoc()) {
            $sheet2->fromArray(array_values($row), NULL, "A{$rowIndex2}");
            $rowIndex2++;
        }

        // Aplicar estilos
        $lastColumn2 = Coordinate::stringFromColumnIndex(count($headers2));
        $headerRange2 = 'A1:' . $lastColumn2 . '1';
        $sheet2->getStyle($headerRange2)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet2->getStyle($headerRange2)->getFont()->setBold(true);

        // Ajustar ancho de columnas
        foreach ($headers2 as $colIndex => $headerText) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $width = mb_strlen($headerText) + 2;
            $sheet2->getColumnDimension($column)->setWidth($width);
        }
    }

    // Crear tercera hoja con estadísticas
    $sheet3 = $spreadsheet->createSheet();
    $sheet3->setTitle('Estadísticas SenaTICS');

    // Estadísticas por programa
    $sqlPrograma = "SELECT program, COUNT(*) as total FROM user_register WHERE institution = 'SenaTICS' GROUP BY program";
    $resultPrograma = $conn->query($sqlPrograma);
    $totalUsuarios = count($data);

    $sheet3->setCellValue('A1', 'ESTADÍSTICAS DE INSCRITOS SENA TICS');
    $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $sheet3->setCellValue('A3', 'DISTRIBUCIÓN POR PROGRAMA');
    $sheet3->getStyle('A3')->getFont()->setBold(true);
    $sheet3->setCellValue('A4', 'Programa');
    $sheet3->setCellValue('B4', 'Cantidad');
    $sheet3->setCellValue('C4', 'Porcentaje');

    $row = 5;
    if ($resultPrograma && $resultPrograma->num_rows > 0) {
        while ($programa = $resultPrograma->fetch_assoc()) {
            $porcentaje = round(($programa['total'] / $totalUsuarios) * 100, 2);
            $sheet3->setCellValue("A{$row}", $programa['program'] ?: 'Sin especificar');
            $sheet3->setCellValue("B{$row}", $programa['total']);
            $sheet3->setCellValue("C{$row}", $porcentaje . '%');
            $row++;
        }
    }

    // Estadísticas por statusAdmin
    $sqlStatus = "SELECT statusAdmin, COUNT(*) as total FROM user_register WHERE institution = 'SenaTICS' GROUP BY statusAdmin";
    $resultStatus = $conn->query($sqlStatus);

    $row += 2;
    $sheet3->setCellValue("A{$row}", 'DISTRIBUCIÓN POR ESTADO ADMINISTRATIVO');
    $sheet3->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    $sheet3->setCellValue("A{$row}", 'Estado');
    $sheet3->setCellValue("B{$row}", 'Cantidad');
    $sheet3->setCellValue("C{$row}", 'Porcentaje');
    $row++;

    $statusMap = [
        1 => 'BENEFICIARIO',
        0 => 'SIN ESTADO',
        2 => 'RECHAZADO',
        3 => 'MATRICULADO',
        4 => 'SIN CONTACTO',
        5 => 'EN PROCESO',
        6 => 'CERTIFICADO',
        7 => 'INACTIVO',
        8 => 'BENEFICIARIO CONTRAPARTIDA',
        9 => 'APLAZADO',
        10 => 'FORMADO',
        11 => 'NO VALIDO',
        12 => 'NO APROBADO'
    ];

    if ($resultStatus && $resultStatus->num_rows > 0) {
        while ($status = $resultStatus->fetch_assoc()) {
            $estado = $statusMap[$status['statusAdmin']] ?? 'DESCONOCIDO';
            $porcentaje = round(($status['total'] / $totalUsuarios) * 100, 2);
            $sheet3->setCellValue("A{$row}", $estado);
            $sheet3->setCellValue("B{$row}", $status['total']);
            $sheet3->setCellValue("C{$row}", $porcentaje . '%');
            $row++;
        }
    }

    // Total
    $row += 2;
    $sheet3->setCellValue("A{$row}", 'TOTAL DE INSCRITOS: ' . $totalUsuarios);
    $sheet3->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);

    // Aplicar estilos
    $sheet3->getStyle('A4:C4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFD3D3D3');
    $sheet3->getStyle('A4:C4')->getFont()->setBold(true);

    $sheet3->getColumnDimension('A')->setWidth(30);
    $sheet3->getColumnDimension('B')->setWidth(15);
    $sheet3->getColumnDimension('C')->setWidth(15);

    ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="senatics_inscritos.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportDataToExcel($conn);
    exit;
}
?>