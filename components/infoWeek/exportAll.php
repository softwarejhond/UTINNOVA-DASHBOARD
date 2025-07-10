<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

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

    define('CURRENT_YEAR', '2007');
    define('CURRENT_DATE', date('Y-m-d'));

    // Obtener niveles de usuarios
    $nivelesUsuarios = obtenerNivelesUsuarios($conn);

    // Consulta principal
    $sql = "SELECT 
    user_register.*, 
    municipios.municipio, 
    departamentos.departamento,
    g.id_bootcamp,
    g.bootcamp_name,
    g.id_leveling_english,
    g.leveling_english_name,
    g.id_english_code,
    g.english_code_name,
    g.id_skills,
    g.skills_name,
    g.creation_date,
    -- Obtener cohorte y fechas desde course_periods
    cp.cohort as bootcamp_cohort,
    cp.start_date as bootcamp_start_date,
    u.fecha_registro,
    -- Bootcamp staff (convertir a mayúsculas en la consulta)
    bc.teacher as bootcamp_teacher_id,
    UPPER(bc_teacher.nombre) as bootcamp_teacher_name,
    bc.mentor as bootcamp_mentor_id,
    UPPER(bc_mentor.nombre) as bootcamp_mentor_name,
    bc.monitor as bootcamp_monitor_id, 
    UPPER(bc_monitor.nombre) as bootcamp_monitor_name,
    -- English Code staff (convertir a mayúsculas en la consulta)
    ec.teacher as ec_teacher_id,
    UPPER(ec_teacher.nombre) as ec_teacher_name,
    ec.mentor as ec_mentor_id,
    UPPER(ec_mentor.nombre) as ec_mentor_name,
    ec.monitor as ec_monitor_id,
    UPPER(ec_monitor.nombre) as ec_monitor_name,
    -- Skills staff (convertir a mayúsculas en la consulta)
    sk.teacher as skills_teacher_id,
    UPPER(sk_teacher.nombre) as skills_teacher_name,
    sk.mentor as skills_mentor_id,
    UPPER(sk_mentor.nombre) as skills_mentor_name,
    sk.monitor as skills_monitor_id,
    UPPER(sk_monitor.nombre) as skills_monitor_name,
    g.mode as group_mode,
    (SELECT COALESCE(SUM(b_intensity), 0) + COALESCE(SUM(ec_intensity), 0) + COALESCE(SUM(s_intensity), 0) 
     FROM groups 
     WHERE number_id = user_register.number_id) as total_intensities
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    LEFT JOIN groups g ON user_register.number_id = g.number_id
    -- Join con course_periods para obtener cohorte y fechas
    LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code
    -- Bootcamp joins
    LEFT JOIN courses bc ON g.id_bootcamp = bc.code
    LEFT JOIN users bc_teacher ON bc.teacher = bc_teacher.username
    LEFT JOIN users bc_mentor ON bc.mentor = bc_mentor.username
    LEFT JOIN users bc_monitor ON bc.monitor = bc_monitor.username
    -- English Code joins
    LEFT JOIN courses ec ON g.id_english_code = ec.code
    LEFT JOIN users ec_teacher ON ec.teacher = ec_teacher.username
    LEFT JOIN users ec_mentor ON ec.mentor = ec_mentor.username
    LEFT JOIN users ec_monitor ON ec.monitor = ec_monitor.username
    -- Skills joins
    LEFT JOIN courses sk ON g.id_skills = sk.code
    LEFT JOIN users sk_teacher ON sk.teacher = sk_teacher.username
    LEFT JOIN users sk_mentor ON sk.mentor = sk_mentor.username
    LEFT JOIN users sk_monitor ON sk.monitor = sk_monitor.username
    
    -- Remover este join ya que ahora usamos course_periods
    -- LEFT JOIN cohorts c ON g.cohort = c.cohort_number
    LEFT JOIN usuarios u ON user_register.number_id = u.cedula 
    WHERE departamentos.id_departamento IN (11)
    AND user_register.status = '1' 
    AND user_register.lote = '1'
    AND user_register.birthdate < '" . CURRENT_YEAR . "-" . date('m-d') . "'
    AND user_register.typeID = 'CC'
    AND user_register.number_id NOT IN (
        SELECT p.numero_documento 
        FROM participantes p
        INNER JOIN groups g ON p.numero_documento = g.number_id
    )
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

    // Consulta para obtener el conteo de asistencias por estudiante
    $sqlAttendance = "SELECT student_id, COUNT(*) as total_attendance 
                        FROM attendance_records 
                        GROUP BY student_id";
    $resultAttendance = $conn->query($sqlAttendance);
    $attendanceCount = [];

    if ($resultAttendance && $resultAttendance->num_rows > 0) {
        while ($attendance = $resultAttendance->fetch_assoc()) {
            $attendanceCount[$attendance['student_id']] = $attendance['total_attendance'];
        }
    }

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
                    in_array(strtoupper($row['departamento']), ['CUNDINAMARCA', 'BOYACÁ']) &&
                    $row['internet'] === 'Sí'
                );
            } elseif ($row['mode'] === 'Virtual') {
                $isAccepted = (
                    $row['typeID'] === 'CC' &&
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
                if ($puntaje >= 0 && $puntaje <= 5) {
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

            //Victima del conflicto armado
            $victimaConflictoArmado = ($row['vulnerable_type'] === 'Victima del conflicto armado') ? 'SI' : 'NO';


            // Verificar si el usuario está en la tabla groups
            $estaEnGroups = !empty($row['id_bootcamp']) || !empty($row['id_leveling_english']) || !empty($row['id_english_code']) || !empty($row['id_skills']);


            //tieneProfesor
            $tieneProfesor = '';
            if (!$estaEnGroups) {
                $tieneProfesor = '';
            } else {
                if ($row['statusAdmin'] === '6') {
                    $tieneProfesor = 'Culmino proceso';
                } elseif ($row['statusAdmin'] === '2') {
                    $tieneProfesor = 'Rechazado';
                } elseif (empty($row['bootcamp_teacher_id'])) {
                    $tieneProfesor = 'Beneficiario en programación';
                } else {
                    // Verifica si tiene al menos un curso con profesor asignado
                    $tieneCursoConProfesor = false;

                    if (!empty($row['id_bootcamp'])) {
                        $tieneCursoConProfesor = $tieneCursoConProfesor || !empty($row['bootcamp_teacher_id']);
                    }

                    if (!empty($row['id_english_code'])) {
                        $tieneCursoConProfesor = $tieneCursoConProfesor || !empty($row['ec_teacher_id']);
                    }

                    if (!empty($row['id_skills'])) {
                        $tieneCursoConProfesor = $tieneCursoConProfesor || !empty($row['skills_teacher_id']);
                    }

                    $tieneProfesor = $tieneCursoConProfesor ? 'En formación' : 'Beneficiario en programación';
                }
            }

            //Asignar opcion de grupo etnico
            $grupoEtnico = match ($row['ethnic_group']) {
                'Negro', 'Mulato', 'Afrodescendiente', 'Afrocolombiano', 'Palenquero' => 'Negro(a), mulato(a), afrodescendiente, afrocolombiano(a), palenquero(a)',
                'Raizal del Archipielago de San Andres y Providencia y Santa Catalina' => 'Raizal del Archipiélago de San Andrés, Providencia y Santa Catalina',
                'Gitano (Rom)' => 'Gitano(a) o Rrom',
                'No aplica' => 'Ninguna de las anteriores',
                default => $row['ethnic_group']
            };

            //Condicional de discapacidad
            $discapacidad = match ($row['type_disability']) {
                'No aplica' => 'Sin discapacidad',
                'Discapacidad física' => 'Física',
                'Sordoceguera' => 'Sordoceguera',
                'Discapacidad visual' => 'Visual',
                'Discapacidad auditiva' => 'Auditiva',
                'Discapacidad intelectual' => 'Intelectual (Cognitiva)',
                'Discapacidad psicosocial' => 'Psicosocial (Mental)',
                'Discapacidad múltiple' => 'Múltiple',
                default => $row['type_disability']
            };

            // Construir fila de datos
            $data[] = [
                'Ejecutor (contratista)' => 'UNIÓN TEMPORAL INNOVA DIGITAL',
                'id' => $row['id'],
                'Tipo_documento' => $row['typeID'] === 'CC' ? 'CC' : $row['typeID'], // Cambio: normalizar CC
                'Número_documento' => $row['number_id'],
                'Nombre1' => strtoupper(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['', 'A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['first_name'])), // Cambio: agregar espacio en blanco
                'Nombre2' => strtoupper(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['', 'A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['second_name'])), // Cambio: agregar espacio en blanco
                'Apellido1' => strtoupper(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['', 'A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['first_last'])), // Cambio: agregar espacio en blanco
                'Apellido2' => strtoupper(str_replace([' ', 'á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['', 'A', 'E', 'I', 'O', 'U', 'A', 'E', 'I', 'O', 'U'], $row['second_last'])), // Cambio: agregar espacio en blanco
                'Fecha_nacimiento' => date('d/m/Y', strtotime($row['birthdate'])),
                'Correo' => $row['email'],

                'Codigo_epartamento' => $row['department'],
                'Departamento' => strtoupper($row['departamento']),

                'Region' => 'Región 8 Lote 1',

                'Codigo_municipio' => $row['municipality'],
                'Municipio' => mb_strtoupper($row['municipio']), // Cambio: usar mb_strtoupper

                'Telefono_movil' => str_replace('+57', '', $row['first_phone']),

                'Genero' => ($row['gender'] === 'LGBIQ+') ? 'LGBTIQ+' : (($row['gender'] === 'No binario' || $row['gender'] === 'No reporta') ? 'Otro' : $row['gender']), // Cambio: simplificar lógica

                'Campesino' => '',

                'Estrato' => ($row['stratum'] == '0' ? 'Sin estratificar' : ($row['residence_area'] == 'Rural' ? '1' : $row['stratum'])),

                'Autoidentificacion_Etnica' => $grupoEtnico,

                'Nivel_educacion' => match ($row['training_level']) {
                    'Primaria (hasta 5°)' => 'Básica Primaria (1-5)',
                    'Secundaria (Hasta 9°)' => 'Básica Secundaria (6-9)',
                    'Media (Bachiller)' => 'Media (10-11)',
                    'Técnico', 'Tecnico' => 'Técnico Profesional',
                    'Pregrado' => 'Profesional Universitario',
                    'Especialización', 'Maestria', 'Doctorado' => 'Posgrado',
                    default => 'No registra/No aplica'
                },
                'Discapacidad' => $discapacidad,

                'IP' => '',
                'Motivaciones' => $row['motivations_belong_program'],
                'Compromiso_10_horas' => ($row['availability'] === 'Sí') ? 'SI' : $row['availability'],
                'Tipo_formacion' => $row['mode'],
                'Acepta_requisitos_convotaria' => ($row['accepts_tech_talent'] === 'Sí') ? 'SI' : $row['accepts_tech_talent'],
                'Victima_del_conflicto' => $victimaConflictoArmado,
                'Autoriza_manejo_datos_personales' => ($row['accept_data_policies'] === 'Sí') ? 'SI' : $row['accept_data_policies'],
                'Disponibilidad_d_Equipo' => !empty($row['technologies']) ? 'SI' : '',
                'creationdate' => $row['creationDate'] ? date('d/m/Y', strtotime($row['creationDate'])) : '',
                'Presento' => ($puntaje !== null && $puntaje !== '') ? 'SI' : 'NO',
                'fecha_ini' => $row['fecha_registro'] ? date('d/m/Y', strtotime($row['fecha_registro'])) : '',
                'tiempo_segundos' => '',
                'Eje_tematico' => $row['program'],
                'Eje final' => $row['program'],
                'Puntaje_eje_tematico_seleccionado' => ($puntaje !== null && $puntaje !== '') ? $puntaje : 'Sin presentar',
                'linea_1_programacion' => '',
                'linea_2_inteligecia_artificial' => '',
                'linea_3_analisis_de_datos' => '',
                'linea_4_blockchain' => '',
                'linea_5_arquitectura_en_la_nube' => '',
                'linea_6_ciberseguridad' => '',
                'linea_1_des_programacion' => '',
                'linea_2_des_inteligecia_artificial' => '',
                'linea_3_des_analisis_de_datos' => '',
                'linea_4_des_blockchain' => '',
                'linea_5_des_arquitectura_en_la_nube' => '',
                'linea_6_des_ciberseguridad' => '',
                'area_1_alfabetizacion_datos' => '',
                'area_2_comunicacion_y_colaboracion' => '',
                'area_3_contenidos_digitales' => '',
                'area_4_seguridad' => '',
                'area_5_solucion_de_problemas' => '',
                'area_6_ingles' => '',
                'area_1_des_alfabetizacion_datos' => '',
                'area_2_des_comunicacion_y_colaboracion' => '',
                'area_3_des_contenidos_digitales' => '',
                'Origen' => 'UTI-R8L1',
                'Matriculado' => $estaEnGroups ? 'SI' : 'NO',
                'Estado' => ($row['statusAdmin'] === '10') ? 'Formado' : $tieneProfesor, // Cambio: agregar verificación de estado 10
                'Programa de Formación' => $estaEnGroups ? $row['program'] : '',
                'Nivel' => $estaEnGroups ? match ($row['level']) {
                    'Explorador' => 'Básico',
                    'Integrador' => 'Intermedio',
                    'Innovador' => 'Avanzado',
                    default => $row['level'],
                } : '',
                'Documento_Profesor principal a cargo del programa de formación' => $row['bootcamp_teacher_id'],
                'Profesor principal a cargo del programa de formación' => $row['bootcamp_teacher_name'],
                'Fecha Inicio de la formación (dd/mm/aaaa)' => $row['bootcamp_start_date'] ? date('d/m/Y', strtotime($row['bootcamp_start_date'])) : '',
                'Cohorte (1,2,3,4,5,6,7 o 8)' => $row['bootcamp_cohort'], // Cambio: usar bootcamp_cohort en lugar de cohort
                'Año Cohorte' => $row['bootcamp_start_date'] ? date('Y', strtotime($row['bootcamp_start_date'])) : '',
                'Tipo de formación' => $row['group_mode'],
                'Enlace al certificado en Sharepoint' => '',
                'Observaciones (menos de 50 cracteres)' => '',
                'codigo del curso' => $row['id_bootcamp'],
                'Nombre del curso' => $row['bootcamp_name'],
                'Asistencias' => $estaEnGroups ? ($attendanceCount[$row['number_id']] ?? 0) : '',
                'Asistencias programadas' => $estaEnGroups ? '159' : '',
                'Documento_Mentor' => $row['bootcamp_mentor_id'],
                'Mentor' => $row['bootcamp_mentor_name'],
                'Documento_Monitor' => $row['bootcamp_monitor_id'],
                'Monitor' => $row['bootcamp_monitor_name'],
                'Documento_Ejecutor_ingles' => $row['ec_teacher_id'],
                'Ejecutor de ingles' => $row['ec_teacher_name'],
                'Documento_Ejecutor de habilidades de poder' => $row['skills_teacher_id'],
                'Ejecutor de habilidades de poder' => $row['skills_teacher_name'],
                // Nuevo campo agregado
                'Estado Admision' => match ($row['statusAdmin']) {
                    '1' => 'BENEFICIARIO',
                    '0' => 'SIN ESTADO', 
                    '2' => 'RECHAZADO',
                    '3' => 'MATRICULADO',
                    '4' => 'SIN CONTACTO',
                    '5' => 'EN PROCESO',
                    '6' => 'CERTIFICADO',
                    '7' => 'INACTIVO',
                    '8' => 'BENEFICIARIO CONTRAPARTIDA',
                    '9' => 'APLAZADO',
                    '10' => 'FORMADO',
                    '11' => 'NO VALIDO',
                    default => ''
                },
            ];
        }
    }

    // Crear archivo Excel
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle('Data');


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

    // Columnas A a BK: color durazno (peach)
    $sheet->getStyle('A1:BF1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDAB9');

    // Columnas BO a BU: color verde
    $sheet->getStyle('BG1:BQ1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF00FF00');

    // Columnas BV a BW: color verde claro
    $sheet->getStyle('BR1:BS1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF90EE90');

    // Columnas BX a CI: color amarillo
    $sheet->getStyle('BT1:CG1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFFF00');

    // Aplicar fuente en negrita a todos los encabezados
    $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);

    // Ajustar ancho de columnas según el texto del encabezado
    foreach ($headers as $colIndex => $headerText) {
        $column = Coordinate::stringFromColumnIndex($colIndex + 1);
        $width = mb_strlen($headerText) + 2; // +2 para un poco de padding
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    ob_clean(); // Limpia cualquier salida previa
    // Configurar headers para descarga
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="informe_semanal_' . date('Y-m-d') . '.xlsx"');
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

// Agregar esta función helper para normalizar strings
function normalizeString($string)
{
    $unwanted_array = array(
        'Š' => 'S',
        'š' => 's',
        'Ž' => 'Z',
        'ž' => 'z',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y'
    );
    return strtr($string, $unwanted_array);
}
