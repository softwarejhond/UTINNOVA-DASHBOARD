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
    $sql = "SELECT user_register.*, 
       COALESCE(municipios.municipio, 'Sin municipio') as municipio, 
       COALESCE(departamentos.departamento, 'Sin departamento') as departamento
        FROM user_register
        LEFT JOIN municipios ON user_register.municipality = municipios.id_municipio
        LEFT JOIN departamentos ON user_register.department = departamentos.id_departamento";

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
                $estadoAdmision = 'BENEFICIARIO';
            } elseif ($row['statusAdmin'] === '2') {
                $estadoAdmision = 'RECHAZADO';
            } elseif ($row['statusAdmin'] === '3') {
                $estadoAdmision = 'MATRICULADO';
            } elseif ($row['statusAdmin'] === '4') {
                $estadoAdmision = 'SIN CONTACTO';
            } elseif ($row['statusAdmin'] === '5') {
                $estadoAdmision = 'EN PROCESO';
            } elseif ($row['statusAdmin'] === '0') {
                $estadoAdmision = 'PENDIENTE';
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
    $sheet->setTitle('Matriz general de inscritos');


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

    // Ajustar ancho de columnas según el texto del encabezado
    foreach ($headers as $colIndex => $headerText) {
        $column = Coordinate::stringFromColumnIndex($colIndex + 1);
        $width = mb_strlen($headerText) + 2; // +2 para un poco de padding
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    // Crear segunda hoja con todos los datos de user_register
    $sheet2 = $spreadsheet->createSheet();
    $sheet2->setTitle('Usuarios Registrados');

    // Consulta para obtener todos los datos
    $sql2 = "SELECT user_register.*, 
       COALESCE(municipios.municipio, 'Sin municipio') as municipio, 
       COALESCE(departamentos.departamento, 'Sin departamento') as departamento
        FROM user_register
        LEFT JOIN municipios ON user_register.municipality = municipios.id_municipio
        LEFT JOIN departamentos ON user_register.department = departamentos.id_departamento";

    $result2 = $conn->query($sql2);
    $data2 = [];

    if ($result2 && $result2->num_rows > 0) {
        // Definir encabezados en español
        $headers2 = [
            'Tipo de Documento',
            'Número de Identificación',
            'Primer Nombre',
            'Segundo Nombre',
            'Primer Apellido',
            'Segundo Apellido',
            'Fecha de Nacimiento',
            'Fecha de Expedición',
            'Género',
            'Estado Civil',
            'Correo Electrónico',
            'Teléfono Principal',
            'Teléfono Secundario',
            'Contraseña',
            'Nombre Contacto Emergencia',
            'Teléfono Contacto Emergencia',
            'Nacionalidad',
            'Departamento',
            'Municipio',
            'Dirección',
            'Latitud',
            'Longitud',
            'Personas a Cargo',
            'Población Vulnerable',
            'Tipo Vulnerabilidad',
            'Grupo Étnico',
            'Estrato',
            'Área de Residencia',
            'Nivel de Formación',
            'Ocupación',
            'Tiempo Obligaciones',
            'Motivación',
            'Situación Actual',
            'Impedimento para Completar',
            'Disponibilidad',
            'Modalidad',
            'Sede',
            'Programa',
            'Horarios',
            'Conocimientos Previos',
            'Nivel',
            'Idiomas',
            'Nivel de Idiomas',
            'Condición Médica',
            'Discapacidad',
            'Tipo de Discapacidad',
            'Embarazo',
            'Dispositivos',
            'Internet',
            'Conocimiento del Programa',
            'Acepta Requisitos',
            'Acepta Tech Talent',
            'Acepta Políticas',
            'Foto Frontal ID',
            'Foto Reverso ID',
            'Estado',
            'Estado Administrativo',
            'ID Curso',
            'Medio de Contacto',
            'Institución',
            'Fecha Creación',
            'Fecha Actualización'
        ];

        $sheet2->fromArray($headers2, NULL, 'A1');

        // Escribir datos
        $rowIndex2 = 2;
        while ($row = $result2->fetch_assoc()) {

            $estadoAdmision = 'PENDIENTE';
            if ($row['statusAdmin'] === '1') {
                $estadoAdmision = 'BENEFICIARIO';
            } elseif ($row['statusAdmin'] === '2') {
                $estadoAdmision = 'RECHAZADO';
            } elseif ($row['statusAdmin'] === '3') {
                $estadoAdmision = 'MATRICULADO';
            } elseif ($row['statusAdmin'] === '4') {
                $estadoAdmision = 'SIN CONTACTO';
            } elseif ($row['statusAdmin'] === '5') {
                $estadoAdmision = 'EN PROCESO';
            } elseif ($row['statusAdmin'] === '0') {
                $estadoAdmision = 'PENDIENTE';
            }
            
            $sheet2->fromArray([
                $row['typeID'],
                $row['number_id'],
                $row['first_name'],
                $row['second_name'],
                $row['first_last'],
                $row['second_last'],
                $row['birthdate'],
                $row['expedition_date'],
                $row['gender'],
                $row['marital_status'],
                $row['email'],
                $row['first_phone'],
                $row['second_phone'],
                $row['password'],
                $row['emergency_contact_name'],
                $row['emergency_contact_number'],
                $row['nationality'],
                $row['departamento'],
                $row['municipio'],
                $row['address'],
                $row['latitud'],
                $row['longitud'],
                $row['people_charge'],
                $row['vulnerable_population'],
                $row['vulnerable_type'],
                $row['ethnic_group'],
                $row['stratum'],
                $row['residence_area'],
                $row['training_level'],
                $row['occupation'],
                $row['time_obligations'],
                $row['motivations_belong_program'],
                $row['current_situation'],
                $row['impediment_complete_course'],
                $row['availability'],
                $row['mode'],
                $row['headquarters'],
                $row['program'],
                $row['schedules'],
                $row['prior_knowledge'],
                $row['level'],
                $row['languages'],
                $row['languages_level'],
                $row['medical_condition'],
                $row['disability'],
                $row['type_disability'],
                $row['pregnancy'],
                $row['technologies'],
                $row['internet'],
                $row['knowledge_program'],
                $row['accept_requirements'],
                $row['accepts_tech_talent'],
                $row['accept_data_policies'],
                $row['file_front_id'],
                $row['file_back_id'],
                $row['status'],
                $estadoAdmision,
                $row['idCourse'],
                $row['contactMedium'],
                $row['institution'],
                $row['creationDate'],
                $row['dayUpdate']
            ], NULL, "A{$rowIndex2}");
            $rowIndex2++;
        }

        // Aplicar estilos
        $lastColumn2 = Coordinate::stringFromColumnIndex(count($headers2));
        $headerRange2 = 'A1:' . $lastColumn2 . '1';
        $sheet2->getStyle($headerRange2)->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet2->getStyle($headerRange2)->getFont()->setBold(true);

        // Ajustar ancho de columnas según el texto del encabezado
        foreach ($headers2 as $colIndex => $headerText) {
            $column = Coordinate::stringFromColumnIndex($colIndex + 1);
            $width = mb_strlen($headerText) + 2; // +2 para un poco de padding
            $sheet2->getColumnDimension($column)->setWidth($width);
        }
    }

    // Lista de tablas a exportar
    $tablas = [
        'usuarios'
    ];

    // Agregar una hoja por cada tabla
    foreach ($tablas as $tabla) {
        $resultado = $conn->query("SELECT * FROM $tabla");

        if ($resultado && $resultado->num_rows > 0) {
            $hoja = $spreadsheet->createSheet();
            $hoja->setTitle(substr($tabla, 0, 31)); // Limitar a 31 caracteres

            // Obtener datos
            $datosTabla = [];
            while ($fila = $resultado->fetch_assoc()) {
                $datosTabla[] = $fila;
            }

            // Escribir encabezados
            $encabezados = array_keys($datosTabla[0]);
            $hoja->fromArray($encabezados, NULL, 'A1');

            // Escribir datos
            $filaIndex = 2;
            foreach ($datosTabla as $filaDatos) {
                $hoja->fromArray(array_values($filaDatos), NULL, "A{$filaIndex}");
                $filaIndex++;
            }

            if (!empty($encabezados)) {
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
    header('Content-Disposition: attachment; filename="inscritos.xlsx"');
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
