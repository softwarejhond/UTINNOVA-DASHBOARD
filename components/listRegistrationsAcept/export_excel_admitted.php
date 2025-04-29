<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si se solicita la exportación
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    exportDataToExcel($conn); // Ejecutar la función de exportación
    exit;
}

function exportDataToExcel($conn)
{
    // Obtener niveles de usuarios
    $nivelesUsuarios = obtenerNivelesUsuarios($conn);

    // Consulta principal
    $sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
            FROM user_register
            INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
            INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
            WHERE departamentos.id_departamento = 11
            AND user_register.status = '1' AND user_register.statusAdmin = '1'
            ORDER BY user_register.first_name ASC";

    $result = $conn->query($sql);
    $data = [];

    // Consulta para obtener todos los asesores
    $sqlAsesores = "SELECT idAdvisor, name FROM advisors ORDER BY name ASC";
    $resultAsesores = $conn->query($sqlAsesores);
    $asesores = [];
    if ($resultAsesores && $resultAsesores->num_rows > 0) {
        while ($asesor = $resultAsesores->fetch_assoc()) {
            $asesores[$asesor['idAdvisor']] = $asesor['name'];
        }
    }

    // Consulta para contact_log
    $sqlContactLog = "SELECT cl.*, a.name AS advisor_name
                      FROM contact_log cl
                      LEFT JOIN advisors a ON cl.idAdvisor = a.id
                      WHERE cl.number_id = ?";

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Procesar contact_logs
            $stmtContactLog = $conn->prepare($sqlContactLog);
            $stmtContactLog->bind_param('i', $row['number_id']);
            $stmtContactLog->execute();
            $resultContactLog = $stmtContactLog->get_result();
            $contactLogs = $resultContactLog->fetch_all(MYSQLI_ASSOC);

            $lastLog = !empty($contactLogs) ? end($contactLogs) : [
                'advisor_name' => 'Sin asignar',
                'details' => 'Sin detalles',
                'contact_established' => 0,
                'continues_interested' => 0,
                'observation' => 'Sin observaciones'
            ];

            // Verificar y asignar nombre del asesor
            if (!empty($lastLog['idAdvisor']) && isset($asesores[$lastLog['idAdvisor']])) {
                $lastLog['advisor_name'] = $asesores[$lastLog['idAdvisor']];
            }

            // Calcular edad
            $birthday = new DateTime($row['birthdate']);
            $now = new DateTime();
            $age = $now->diff($birthday)->y;

            // Determinar estado CUMPLE/NO CUMPLE
            $isAccepted = false;
            if ($row['mode'] === 'Presencial') {
                $isAccepted = (
                    $row['typeID'] === 'C.C' &&
                    $age > 17 &&
                    in_array(strtoupper($row['departamento']), ['CUNDINAMARCA', 'BOYACÁ']) &&
                    $row['internet'] === 'Sí'
                );
            } elseif ($row['mode'] === 'Virtual') {
                $isAccepted = (
                    $row['typeID'] === 'C.C' &&
                    $age > 17 &&
                    in_array(strtoupper($row['departamento']), ['CUNDINAMARCA', 'BOYACÁ']) &&
                    $row['internet'] === 'Sí' &&
                    $row['technologies'] === 'computador'
                );
            }

            // Determinar estado de prueba
            $puntaje = $nivelesUsuarios[$row['number_id']] ?? '';
            $estadoPrueba = 'No presentó prueba';
            if ($puntaje) {
                if ($puntaje >= 1 && $puntaje <= 5) {
                    $estadoPrueba = 'Básico';
                } elseif ($puntaje >= 6 && $puntaje <= 10) {
                    $estadoPrueba = 'Intermedio';
                } elseif ($puntaje >= 11 && $puntaje <= 15) {
                    $estadoPrueba = 'Avanzado';
                }
            }

            //Asignacion a estado de admision
            $estadoAdmision = 'PENDIENTE';
            if ($row['statusAdmin'] === '1') {
                $estadoAdmision = 'ACEPTADO';
            } elseif ($row['statusAdmin'] === '0') {
                $estadoAdmision = 'RECHAZADO';
            }


            // Construir fila de datos
            $data[] = [
                'Tipo ID' => $row['typeID'],
                'Número' => $row['number_id'],
                'Foto Frente' => $row['file_front_id'],
                'Foto Reverso' => $row['file_back_id'],
                'Nombre Completo' => trim("{$row['first_name']} {$row['second_name']} {$row['first_last']} {$row['second_last']}"),
                'Edad' => $age,
                'Correo' => $row['email'],
                'Teléfono principal' => $row['first_phone'],
                'Teléfono secundario' => $row['second_phone'],
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
                'Horario' => $row['schedules'],
                'Dispositivo' => $row['technologies'],
                'Internet' => $row['internet'],
                'Estado' => $isAccepted ? 'CUMPLE' : 'NO CUMPLE',
                'Estado de admisión' => $estadoAdmision,
                'Puntaje de prueba' => $puntaje,
                'Estado de prueba' => $estadoPrueba,
                'Asesor' => $lastLog['advisor_name'],
                'Detalle Llamada' => $lastLog['details'],
                'Contacto Establecido' => $lastLog['contact_established'] ? 'Sí' : 'No',
                'Continúa Interesado' => $lastLog['continues_interested'] ? 'Sí' : 'No',
                'Observación' => $lastLog['observation']
            ];
        }
    }

    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Admitidos');

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

    // Autoajuste para la primera hoja
    foreach (range('A', $lastColumn) as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Agregar hojas adicionales
    $tablas = ['user_register', 'usuarios'];

    // Primero obtener todos los number_id de user_register con statusAdmin = '1'
    $sqlNumberIds = "SELECT number_id FROM user_register WHERE statusAdmin = '1'";
    $resultNumberIds = $conn->query($sqlNumberIds);
    $validNumberIds = [];
    
    if ($resultNumberIds && $resultNumberIds->num_rows > 0) {
        while ($row = $resultNumberIds->fetch_assoc()) {
            $validNumberIds[] = $row['number_id'];
        }
    }

    // Agregar una hoja por cada tabla
    foreach ($tablas as $tabla) {
        // Consulta específica para cada tabla
        if ($tabla === 'user_register') {
            $sql = "SELECT * FROM user_register WHERE statusAdmin = '1'";
        } else { // tabla usuarios
            $numberIdsList = implode(',', $validNumberIds);
            $sql = "SELECT * FROM usuarios WHERE cedula IN ($numberIdsList)";
        }

        $resultado = $conn->query($sql);

        if ($resultado && $resultado->num_rows > 0) {
            $hoja = $spreadsheet->createSheet();
            $hoja->setTitle(substr($tabla, 0, 31));

            // Obtener datos
            $datosTabla = [];
            while ($fila = $resultado->fetch_assoc()) {
                $datosTabla[] = $fila;
            }

            // Escribir encabezados
            if (!empty($datosTabla)) {
                $encabezados = array_keys($datosTabla[0]);
                $hoja->fromArray($encabezados, NULL, 'A1');

                // Escribir datos
                $filaIndex = 2;
                foreach ($datosTabla as $filaDatos) {
                    $hoja->fromArray(array_values($filaDatos), NULL, "A{$filaIndex}");
                    $filaIndex++;
                }

                $lastColumnTabla = Coordinate::stringFromColumnIndex(count($encabezados));

                // Estilo encabezados
                $headerRangeTabla = 'A1:' . $lastColumnTabla . '1';
                $headerStyleTabla = $hoja->getStyle($headerRangeTabla);
                $headerStyleTabla->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setARGB('FFD3D3D3');
                $headerStyleTabla->getFont()->setBold(true);

                // Autoajuste
                $hoja->getStyle('A1:' . $lastColumnTabla . $filaIndex)->getAlignment();
                foreach (range('A', $lastColumnTabla) as $col) {
                    $hoja->getColumnDimension($col)->setAutoSize(true);
                }
            }
        }
    }


    ob_clean(); // Limpia cualquier salida previa
    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="inscritos_admitidos.xlsx"');
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}

function obtenerNivelesUsuarios($conn)
{
    $sql = "SELECT cedula, nivel FROM usuarios";
    $result = $conn->query($sql);
    $niveles = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $niveles[$row['cedula']] = $row['nivel'];
        }
    }
    return $niveles;
}