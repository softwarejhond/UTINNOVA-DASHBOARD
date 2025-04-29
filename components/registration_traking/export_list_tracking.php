<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportDataToExcel($conn);
    exit;
}

function exportDataToExcel($conn) {
    // Consulta principal
    $sql = "SELECT user_register.*, 
            municipios.municipio, 
            departamentos.departamento,
            g.creation_date as matriculation_date
        FROM user_register
        INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
        INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
        LEFT JOIN groups g ON user_register.number_id = g.number_id
        WHERE departamentos.id_departamento = 11
        AND user_register.status = '1'";

    $result = $conn->query($sql);
    
    // Consulta para contact_log
    $sqlContactLog = "SELECT cl.*, a.name AS advisor_name,
                      MIN(cl.contact_date) as first_contact_date,
                      MAX(cl.contact_date) as last_contact_date
                      FROM contact_log cl
                      LEFT JOIN advisors a ON cl.idAdvisor = a.id
                      WHERE cl.number_id = ?
                      GROUP BY cl.number_id";

    $data = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Obtener datos de contact_log
            $stmtContactLog = $conn->prepare($sqlContactLog);
            $stmtContactLog->bind_param('i', $row['number_id']);
            $stmtContactLog->execute();
            $resultContactLog = $stmtContactLog->get_result();
            $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

            // Calcular edad
            $birthday = new DateTime($row['birthdate']);
            $now = new DateTime();
            $age = $now->diff($birthday)->y;

            // Estado de admisión
            $estadoAdmision = 'PENDIENTE';
            switch($row['statusAdmin']) {
                case '1': $estadoAdmision = 'BENEFICIARIO'; break;
                case '2': $estadoAdmision = 'RECHAZADO'; break;
                case '3': $estadoAdmision = 'MATRICULADO'; break;
                case '4': $estadoAdmision = 'SIN CONTACTO'; break;
                case '5': $estadoAdmision = 'EN PROCESO'; break;
                case '0': $estadoAdmision = 'SIN ESTADO'; break;
            }

            // Construir fila de datos
            $data[] = [
                'Tipo ID' => $row['typeID'],
                'Número' => $row['number_id'],
                'Nombre Completo' => trim("{$row['first_name']} {$row['second_name']} {$row['first_last']} {$row['second_last']}"),
                'Edad' => $age,
                'Correo' => $row['email'],
                'Teléfono 1' => $row['first_phone'],
                'Teléfono 2' => $row['second_phone'],
                'Medio de contacto' => $row['contactMedium'],
                'Contacto de emergencia' => $row['emergency_contact_name'],
                'Teléfono del contacto' => $row['emergency_contact_number'],
                'Nacionalidad' => $row['nationality'],
                'Departamento' => $row['departamento'],
                'Municipio' => $row['municipio'],
                'Ocupación' => $row['occupation'],
                'Tiempo de obligaciones' => $row['time_obligations'],
                'Sede de elección' => $row['headquarters'],
                'Modalidad' => $row['mode'],
                'Programa de interés' => $row['program'],
                'Nivel de preferencia' => $row['level'],
                'Dispositivo' => $row['technologies'],
                'Internet' => $row['internet'],
                'Estado de admisión' => $estadoAdmision,
                'Fecha de registro' => $row['creationDate'],
                'Fecha primer contacto' => $contactLogs[0]['first_contact_date'] ?? 'Sin contacto',
                'Fecha último contacto' => $contactLogs[0]['last_contact_date'] ?? 'Sin contacto',
                'Fecha de matrícula o rechazo' => $row['statusAdmin'] == '2' ? 
                    ($contactLogs[0]['last_contact_date'] ?? 'Pendiente') : 
                    ($row['matriculation_date'] ?? 'Pendiente')
            ];
        }
    }

    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Lista de Seguimiento');

    // Escribir encabezados
    $headers = array_keys($data[0] ?? []);
    $sheet->fromArray($headers, NULL, 'A1');

    // Escribir datos
    $rowIndex = 2;
    foreach ($data as $row) {
        $sheet->fromArray(array_values($row), NULL, "A{$rowIndex}");
        $rowIndex++;
    }

    // Estilo para encabezados
    $lastColumn = Coordinate::stringFromColumnIndex(count($headers));
    $headerRange = 'A1:' . $lastColumn . '1';
    $headerStyle = $sheet->getStyle($headerRange);
    $headerStyle->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFD3D3D3');
    $headerStyle->getFont()->setBold(true);

    // Autoajustar columnas
    foreach (range('A', $lastColumn) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="lista_seguimiento.xlsx"');
    header('Cache-Control: max-age=0');

    // Limpiar buffer de salida
    ob_clean();

    // Guardar archivo
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>