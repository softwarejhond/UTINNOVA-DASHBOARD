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
                    $row['typeID'] === 'CC' &&
                    $age > 17 &&
                    (strtoupper($row['department']) === '11')
                );
            } elseif ($row['mode'] === 'Virtual') {
                $isAccepted = (
                    $row['typeID'] === 'CC' &&
                    $age > 17 &&
                    (strtoupper($row['department']) === '11') &&
                    $row['internet'] === 'Sí'
                );
            }

            // Determinar estado de prueba
            $puntaje = $nivelesUsuarios[$row['number_id']] ?? '';
            $estadoPrueba = 'No presentó prueba';
            if ($puntaje !== '') {
                if ($puntaje >= 0 && $puntaje <= 5) {
                    $estadoPrueba = 'Básico';
                } elseif ($puntaje >= 6 && $puntaje <= 10) {
                    $estadoPrueba = 'Intermedio';
                } elseif ($puntaje >= 11 && $puntaje <= 15) {
                    $estadoPrueba = 'Avanzado';
                }
            }

            //Asignacion a estado de admision
            $estadoAdmision = 'SIN ESTADO';
            if ($row['statusAdmin'] === '1') {
                $estadoAdmision = 'BENEFICIARIO';
            } elseif ($row['statusAdmin'] === '2') {
                $estadoAdmision = 'RECHAZADO';
            } elseif ($row['statusAdmin'] === '3') {
                $estadoAdmision = 'MATRICULADO';
            } elseif ($row['statusAdmin'] === '4') {
                $estadoAdmision = 'PENDIENTE';
            } elseif ($row['statusAdmin'] === '5') {
                $estadoAdmision = 'EN PROCESO';
            } elseif ($row['statusAdmin'] === '6') {
                $estadoAdmision = 'CERTIFICADO';
            } elseif ($row['statusAdmin'] === '7') {
                $estadoAdmision = 'INACTIVO';
            } elseif ($row['statusAdmin'] === '8') {
                $estadoAdmision = 'BENEFICIARIO CONTRAPARTIDA';
            } elseif ($row['statusAdmin'] === '9') {
                $estadoAdmision = 'PENDIENTE MINTIC';
            } elseif ($row['statusAdmin'] === '0') {
                $estadoAdmision = 'SIN ESTADO';
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
                'Lote' => $row['lote'],
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
            'Lote',
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

            $estadoAdmision = 'SIN ESTADO';
            if ($row['statusAdmin'] === '1') {
                $estadoAdmision = 'BENEFICIARIO';
            } elseif ($row['statusAdmin'] === '2') {
                $estadoAdmision = 'RECHAZADO';
            } elseif ($row['statusAdmin'] === '3') {
                $estadoAdmision = 'MATRICULADO';
            } elseif ($row['statusAdmin'] === '4') {
                $estadoAdmision = 'PENDIENTE';
            } elseif ($row['statusAdmin'] === '5') {
                $estadoAdmision = 'EN PROCESO';
            } elseif ($row['statusAdmin'] === '6') {
                $estadoAdmision = 'CERTIFICADO';
            } elseif ($row['statusAdmin'] === '7') {
                $estadoAdmision = 'INACTIVO';
            } elseif ($row['statusAdmin'] === '8') {
                $estadoAdmision = 'BENEFICIARIO CONTRAPARTIDA';
            } elseif ($row['statusAdmin'] === '9') {
                $estadoAdmision = 'PENDIENTE MINTIC';
            } elseif ($row['statusAdmin'] === '0') {
                $estadoAdmision = 'SIN ESTADO';
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
                $row['lote'],
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

    // Crear cuarta hoja con estadísticas
    $sheet4 = $spreadsheet->createSheet();
    $sheet4->setTitle('Estadísticas');

    // Consultas para estadísticas
    
    // 1. Estadísticas por género
    $sqlGenero = "SELECT gender, COUNT(*) as total FROM user_register GROUP BY gender";
    $resultGenero = $conn->query($sqlGenero);
    $totalUsuarios = array_sum(array_column($data, 0)) ?: count($data); // Total de usuarios
    
    // Obtener total real de usuarios
    $sqlTotal = "SELECT COUNT(*) as total FROM user_register";
    $resultTotal = $conn->query($sqlTotal);
    $totalUsuarios = $resultTotal->fetch_assoc()['total'];

    // Escribir título y estadísticas de género
    $sheet4->setCellValue('A1', 'ESTADÍSTICAS DE INSCRITOS');
    $sheet4->getStyle('A1')->getFont()->setBold(true)->setSize(14);
    
    $sheet4->setCellValue('A3', 'DISTRIBUCIÓN POR GÉNERO');
    $sheet4->getStyle('A3')->getFont()->setBold(true);
    $sheet4->setCellValue('A4', 'Género');
    $sheet4->setCellValue('B4', 'Cantidad');
    $sheet4->setCellValue('C4', 'Porcentaje');
    
    $row = 5;
    if ($resultGenero && $resultGenero->num_rows > 0) {
        while ($genero = $resultGenero->fetch_assoc()) {
            $porcentaje = round(($genero['total'] / $totalUsuarios) * 100, 2);
            $sheet4->setCellValue("A{$row}", $genero['gender'] ?: 'Sin especificar');
            $sheet4->setCellValue("B{$row}", $genero['total']);
            $sheet4->setCellValue("C{$row}", $porcentaje . '%');
            $row++;
        }
    }

    // 2. Estadísticas por programa
    $sqlPrograma = "SELECT program, COUNT(*) as total FROM user_register GROUP BY program";
    $resultPrograma = $conn->query($sqlPrograma);
    
    $row += 2; // Espacio
    $sheet4->setCellValue("A{$row}", 'DISTRIBUCIÓN POR PROGRAMA');
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    $sheet4->setCellValue("A{$row}", 'Programa');
    $sheet4->setCellValue("B{$row}", 'Cantidad');
    $sheet4->setCellValue("C{$row}", 'Porcentaje');
    $row++;
    
    if ($resultPrograma && $resultPrograma->num_rows > 0) {
        while ($programa = $resultPrograma->fetch_assoc()) {
            $porcentaje = round(($programa['total'] / $totalUsuarios) * 100, 2);
            $sheet4->setCellValue("A{$row}", $programa['program'] ?: 'Sin especificar');
            $sheet4->setCellValue("B{$row}", $programa['total']);
            $sheet4->setCellValue("C{$row}", $porcentaje . '%');
            $row++;
        }
    }

    // 3. Estadísticas por área de residencia (Rural/Urbana)
    $sqlArea = "SELECT residence_area, COUNT(*) as total FROM user_register GROUP BY residence_area";
    $resultArea = $conn->query($sqlArea);
    
    $row += 2; // Espacio
    $sheet4->setCellValue("A{$row}", 'DISTRIBUCIÓN POR ÁREA DE RESIDENCIA');
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true);
    $row++;
    $sheet4->setCellValue("A{$row}", 'Área');
    $sheet4->setCellValue("B{$row}", 'Cantidad');
    $sheet4->setCellValue("C{$row}", 'Porcentaje');
    $row++;
    
    if ($resultArea && $resultArea->num_rows > 0) {
        while ($area = $resultArea->fetch_assoc()) {
            $porcentaje = round(($area['total'] / $totalUsuarios) * 100, 2);
            $sheet4->setCellValue("A{$row}", $area['residence_area'] ?: 'Sin especificar');
            $sheet4->setCellValue("B{$row}", $area['total']);
            $sheet4->setCellValue("C{$row}", $porcentaje . '%');
            $row++;
        }
    }

    // 4. Total de inscritos
    $row += 2;
    $sheet4->setCellValue("A{$row}", 'TOTAL DE INSCRITOS: ' . $totalUsuarios);
    $sheet4->getStyle("A{$row}")->getFont()->setBold(true)->setSize(12);

    // Aplicar estilos a la hoja de estadísticas
    $sheet4->getStyle('A4:C4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFD3D3D3');
    $sheet4->getStyle('A4:C4')->getFont()->setBold(true);

    // Encontrar la fila donde están los encabezados de programa
    $programRow = 0;
    for ($i = 1; $i <= $row; $i++) {
        if ($sheet4->getCell("A{$i}")->getValue() === 'Programa') {
            $programRow = $i;
            break;
        }
    }
    if ($programRow > 0) {
        $sheet4->getStyle("A{$programRow}:C{$programRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet4->getStyle("A{$programRow}:C{$programRow}")->getFont()->setBold(true);
    }

    // Encontrar la fila donde están los encabezados de área
    $areaRow = 0;
    for ($i = 1; $i <= $row; $i++) {
        if ($sheet4->getCell("A{$i}")->getValue() === 'Área') {
            $areaRow = $i;
            break;
        }
    }
    if ($areaRow > 0) {
        $sheet4->getStyle("A{$areaRow}:C{$areaRow}")->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setARGB('FFD3D3D3');
        $sheet4->getStyle("A{$areaRow}:C{$areaRow}")->getFont()->setBold(true);
    }

    // Ajustar ancho de columnas
    $sheet4->getColumnDimension('A')->setWidth(30);
    $sheet4->getColumnDimension('B')->setWidth(15);
    $sheet4->getColumnDimension('C')->setWidth(15);

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
