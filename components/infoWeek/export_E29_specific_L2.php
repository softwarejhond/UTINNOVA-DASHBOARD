<?php
require __DIR__ . '../../../vendor/autoload.php';
require __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Configuraciones para evitar timeout
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '1024M'); // 1GB
ini_set('max_execution_time', 300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar si se solicita la exportación
if (isset($_GET['action']) && $_GET['action'] === 'export') {
    // Obtener documentos del parámetro GET
    $documentsParam = $_GET['docs'] ?? '';
    $documents = array_filter(array_map('trim', explode(',', $documentsParam)), 'is_numeric');

    if (empty($documents)) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'No se proporcionaron documentos válidos']);
        exit;
    }

    exportDataToExcelSpecific($conn, $documents);
    exit;
}

function exportDataToExcelSpecific($conn, $documents)
{
    // Configuraciones adicionales dentro de la función
    set_time_limit(300);
    ini_set('memory_limit', '1024M');

    define('CURRENT_YEAR', '2007');
    define('CURRENT_DATE', date('Y-m-d'));

    // Obtener niveles de usuarios
    $nivelesUsuarios = obtenerNivelesUsuarios($conn);

    // Crear placeholders para la consulta IN
    $placeholders = str_repeat('?,', count($documents) - 1) . '?';

    // Consulta principal MODIFICADA con WHERE IN específico
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
    cp.cohort as bootcamp_cohort,
    cp.start_date as bootcamp_start_date,
    cp.end_date as bootcamp_end_date,
    u.fecha_registro,
    bc.teacher as bootcamp_teacher_id,
    UPPER(bc_teacher.nombre) as bootcamp_teacher_name,
    bc.mentor as bootcamp_mentor_id,
    UPPER(bc_mentor.nombre) as bootcamp_mentor_name,
    bc.monitor as bootcamp_monitor_id, 
    UPPER(bc_monitor.nombre) as bootcamp_monitor_name,
    ec.teacher as ec_teacher_id,
    UPPER(ec_teacher.nombre) as ec_teacher_name,
    ec.mentor as ec_mentor_id,
    UPPER(ec_mentor.nombre) as ec_mentor_name,
    ec.monitor as ec_monitor_id,
    UPPER(ec_monitor.nombre) as ec_monitor_name,
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
    LEFT JOIN course_periods cp ON g.id_bootcamp = cp.bootcamp_code
    LEFT JOIN courses bc ON g.id_bootcamp = bc.code
    LEFT JOIN users bc_teacher ON bc.teacher = bc_teacher.username
    LEFT JOIN users bc_mentor ON bc.mentor = bc_mentor.username
    LEFT JOIN users bc_monitor ON bc.monitor = bc_monitor.username
    LEFT JOIN courses ec ON g.id_english_code = ec.code
    LEFT JOIN users ec_teacher ON ec.teacher = ec_teacher.username
    LEFT JOIN users ec_mentor ON ec.mentor = ec_mentor.username
    LEFT JOIN users ec_monitor ON ec.monitor = ec_monitor.username
    LEFT JOIN courses sk ON g.id_skills = sk.code
    LEFT JOIN users sk_teacher ON sk.teacher = sk_teacher.username
    LEFT JOIN users sk_mentor ON sk.mentor = sk_mentor.username
    LEFT JOIN users sk_monitor ON sk.monitor = sk_monitor.username
    LEFT JOIN usuarios u ON user_register.number_id = u.cedula 
    WHERE user_register.number_id IN ($placeholders)
    ORDER BY user_register.first_name ASC";

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare($sql);

    // Crear array de tipos (todos strings)
    $types = str_repeat('s', count($documents));
    $stmt->bind_param($types, ...$documents);

    $stmt->execute();
    $result = $stmt->get_result();
    $data = [];

    // Consulta para obtener el conteo de asistencias por estudiante
    $sqlAttendance = "SELECT student_id, COUNT(*) as total_attendance 
                        FROM attendance_records 
                        WHERE student_id IN ($placeholders)
                        GROUP BY student_id";
    $stmtAttendance = $conn->prepare($sqlAttendance);
    $stmtAttendance->bind_param($types, ...$documents);
    $stmtAttendance->execute();
    $resultAttendance = $stmtAttendance->get_result();
    $attendanceCount = [];

    if ($resultAttendance && $resultAttendance->num_rows > 0) {
        while ($attendance = $resultAttendance->fetch_assoc()) {
            $attendanceCount[$attendance['student_id']] = $attendance['total_attendance'];
        }
    }
    $stmtAttendance->close();

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
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
                if ($puntaje >= 0 && $puntaje <= 5) {
                    $estadoPrueba = 'Básico';
                } elseif ($puntaje >= 6 && $puntaje <= 10) {
                    $estadoPrueba = 'Intermedio';
                } elseif ($puntaje >= 11 && $puntaje <= 15) {
                    $estadoPrueba = 'Avanzado';
                }
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
                } elseif ($row['statusAdmin'] === '10') {
                    $tieneProfesor = 'No en formación';
                } elseif (empty($row['bootcamp_teacher_id'])) {
                    $tieneProfesor = 'Beneficiario en programación';
                } else {
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

            // Grupo étnico
            $grupoEtnico = match ($row['ethnic_group']) {
                'Negro', 'Mulato', 'Afrodescendiente', 'Afrocolombiano', 'Palenquero'
                => 'Negro(a), mulato(a), afrodescendiente, afrocolombiano(a), Palenquero(a)',
                'Raizal del Archipielago de San Andres y Providencia y Santa Catalina'
                => 'Raizal del Archipiélago de San Andrés, Providencia y Santa Catalina',
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
                'Tipo_documento' => $row['typeID'] === 'C.C' ? 'CC' : $row['typeID'],
                'Numero_documento' => $row['number_id'],
                'Nombre1' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'Á', 'É', 'Í', 'Ó', 'Ú'], $row['first_name'])),
                'Nombre2' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'Á', 'É', 'Í', 'Ó', 'Ú'], $row['second_name'])),
                'Apellido1' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'Á', 'É', 'Í', 'Ó', 'Ú'], $row['first_last'])),
                'Apellido2' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['A', 'E', 'I', 'O', 'U', 'Á', 'É', 'Í', 'Ó', 'Ú'], $row['second_last'])),
                'Fecha_nacimiento' => fechaAExcel($row['birthdate']),
                'Correo' => $row['email'],
                'Codigo_departamento' => $row['department'],
                'Departamento' => strtoupper($row['departamento']),
                'Región' => 'Región 7 Lote 1',
                'Codigo_municipio' => $row['municipality'],
                'Municipio' => strtoupper($row['municipio']),
                'Telefono_movil' => str_replace('+57', '', $row['first_phone']),
                'Genero' => ($row['gender'] === 'LGBIQ+') ? 'LGBTIQ+' : (($row['gender'] === 'No binario' || $row['gender'] === 'No reporta') ? 'Otro' : $row['gender']),
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
                'Compromiso_10_horas' => ($row['availability'] === 'Sí') ? 'SI' : $row['availability'],
                'Tipo_formacion' => $row['mode'],
                'Acepta_requisitos_convotaria' => ($row['accepts_tech_talent'] === 'Sí') ? 'SI' : $row['accepts_tech_talent'],
                'Victima_del_conflicto' => $victimaConflictoArmado,
                'Autoriza_manejo_datos_personales' => ($row['accept_data_policies'] === 'Sí') ? 'SI' : $row['accept_data_policies'],
                'Disponibilidad_Equipo' => match ($row['technologies']) {
                    'computador' => 'SI',
                    'tablet' => 'SI',
                    'smartphone' => 'SI',
                    default => 'NO'
                },
                'Presento_prueba' => ($puntaje !== null && $puntaje !== '') ? 'SI' : 'NO',
                'Curso_bootcamp_al_que_se_inscribio' => $row['program'],
                'Origen' => 'UTI-R8L2',
                'Fecha inscripción' => fechaAExcel($row['creationDate']),
                'Cumple requisitos' => 'SI',
                'Cohorte asignación' => $row['bootcamp_cohort'] ?? '',
                'Año cohorte asignación' => !empty($row['bootcamp_start_date']) ? date('Y', strtotime($row['bootcamp_start_date'])) : '',
                'Fecha asignación' => fechaAExcel($row['bootcamp_start_date']),
                'Matriculado' => $estaEnGroups ? 'SI' : 'NO',
                'Fecha de matrícula' => fechaAExcel($row['bootcamp_start_date']),
                'Curso al que se matriculo' => $row['bootcamp_name'] ?? '',
                'Nivel' => $estaEnGroups ? match ($row['level']) {
                    'Explorador' => 'Basico',
                    'Integrador' => 'Intermedio',
                    'Innovador' => 'Avanzado',
                    default => $row['level'],
                } : '',
                'En formación' => ($row['statusAdmin'] == 3 ? 'SI' : 'NO'),
                'Cohorte formacion' => $row['bootcamp_cohort'] ?? '',
                'Año cohorte formacion' => !empty($row['bootcamp_start_date']) ? date('Y', strtotime($row['bootcamp_start_date'])) : '',
                'Fecha inicio formación' => fechaAExcel($row['bootcamp_start_date']),
                'Certificado' => '',
                'Fecha de terminación' => fechaAExcel($row['bootcamp_end_date']),
                '% de asistencia' => $estaEnGroups ? round((calcularHorasActualesPorEstudiante($conn, $row['number_id']) / 159) * 100, 2) . '%' : '',
                'cod_curso' => $row['id_bootcamp'] ?? '',
                'Nom_curso' => $row['bootcamp_name'] ?? '',
                'Fecha de impresión de certificado' => '',
                'Nombre del archivo' => '',
                'Observación' => '',
                'Link documento soporte' =>
                'https://dashboard.utinnova.co/files/idFilesFront/' . ($row['file_front_id'] ?? '') .
                    ' - https://dashboard.utinnova.co/files/idFilesBack/' . ($row['file_back_id'] ?? ''),
                'Estado Admision' => match (intval($row['statusAdmin'])) {
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
                    12 => 'NO APROBADO',
                    default => ''
                },
            ];
        }
    }
    $stmt->close();

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

    $sheet->getStyle('A1:BF1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDAB9');

    $sheet->getStyle('BG1:BQ1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF00FF00');

    $sheet->getStyle('BR1:BS1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF90EE90');

    $sheet->getStyle('BT1:CG1')
        ->getFill()->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFFF00');

    $sheet->getStyle('A1:' . $lastColumn . '1')->getFont()->setBold(true);

    foreach ($headers as $colIndex => $headerText) {
        $column = Coordinate::stringFromColumnIndex($colIndex + 1);
        $width = mb_strlen($headerText) + 2;
        $sheet->getColumnDimension($column)->setWidth($width);
    }

    ob_clean();
    // Configurar headers para descarga con nombre específico
    $filename = 'informe_E29_especifico_' . count($documents) . '_docs_' . date('Y-m-d_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
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

function calcularHorasActualesPorEstudiante($conn, $studentId)
{
    // Verificar si el estudiante está en certificados_senatics
    $sqlCertified = "SELECT COUNT(*) as is_certified FROM certificados_senatics WHERE number_id = ?";
    $stmtCertified = $conn->prepare($sqlCertified);
    $stmtCertified->bind_param("s", $studentId);
    $stmtCertified->execute();
    $resultCertified = $stmtCertified->get_result();
    $certifiedData = $resultCertified->fetch_assoc();
    $isCertified = $certifiedData['is_certified'] > 0;
    $stmtCertified->close();

    // Selecciona todos los cursos del estudiante excepto inglés nivelatorio
    $sql = "SELECT c.code, c.real_hours
            FROM groups g
            JOIN courses c ON (
                c.code = g.id_bootcamp OR 
                c.code = g.id_english_code OR 
                c.code = g.id_skills
            )
            WHERE g.number_id = ?
            AND c.code != g.id_leveling_english";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();

    $totalHoras = 0;
    while ($curso = $result->fetch_assoc()) {
        $cursoId = $curso['code'];
        $horasMaximas = intval($curso['real_hours']);

        // Consulta las asistencias y suma las horas actuales
        $sqlHoras = "SELECT ar.class_date, 
                            CASE 
                                WHEN ar.attendance_status = 'presente' THEN 
                                    CASE DAYOFWEEK(ar.class_date)
                                        WHEN 2 THEN c.monday_hours
                                        WHEN 3 THEN c.tuesday_hours
                                        WHEN 4 THEN c.wednesday_hours
                                        WHEN 5 THEN c.thursday_hours
                                        WHEN 6 THEN c.friday_hours
                                        WHEN 7 THEN c.saturday_hours
                                        WHEN 1 THEN c.sunday_hours
                                        ELSE 0
                                    END
                                WHEN ar.attendance_status = 'tarde' THEN ar.recorded_hours
                                ELSE 0 
                            END as horas
                    FROM attendance_records ar
                    JOIN courses c ON ar.course_id = c.code
                    WHERE ar.student_id = ? AND ar.course_id = ?";
        $stmtHoras = $conn->prepare($sqlHoras);
        $stmtHoras->bind_param("si", $studentId, $cursoId);
        $stmtHoras->execute();
        $resultHoras = $stmtHoras->get_result();

        $fechasContadas = [];
        $horasCurso = 0;
        while ($asistencia = $resultHoras->fetch_assoc()) {
            $fecha = $asistencia['class_date'];
            if (!in_array($fecha, $fechasContadas)) {
                $horasCurso += $asistencia['horas'];
                $fechasContadas[] = $fecha;
            }
        }
        $stmtHoras->close();

        // Verificar el tipo de curso
        $sqlTipo = "SELECT 
            CASE 
                WHEN c.code = g.id_bootcamp THEN 'bootcamp'
                WHEN c.code = g.id_skills THEN 'skills'
                WHEN c.code = g.id_english_code THEN 'english'
                ELSE 'other'
            END as tipo_curso
            FROM groups g
            JOIN courses c ON c.code IN (g.id_bootcamp, g.id_english_code, g.id_skills)
            WHERE g.number_id = ? AND c.code = ?";
        $stmtTipo = $conn->prepare($sqlTipo);
        $stmtTipo->bind_param("si", $studentId, $cursoId);
        $stmtTipo->execute();
        $resultTipo = $stmtTipo->get_result();
        $tipoData = $resultTipo->fetch_assoc();
        $tipoCurso = $tipoData['tipo_curso'];
        $stmtTipo->close();

        // Aplicar límites específicos por tipo de curso
        if ($tipoCurso === 'bootcamp') {
            // LÍMITE TÉCNICO: 120 horas máximo
            if ($isCertified) {
                $horasCurso = min($horasCurso + 40, 120); // Con homologación pero limitado
            } else {
                $horasCurso = min($horasCurso, 120); // Sin homologación pero limitado
            }
        } elseif ($tipoCurso === 'skills') {
            // LÍMITE HABILIDADES: 15 horas máximo
            if ($isCertified) {
                $horasCurso = 15; // Homologación completa
            } else {
                $horasCurso = min($horasCurso, 15); // Sin homologación pero limitado
            }
        } elseif ($tipoCurso === 'english') {
            // LÍMITE INGLÉS: 24 horas máximo
            $horasCurso = min($horasCurso, 24);
        } else {
            // Para otros cursos, aplicar límite del curso
            $horasCurso = min($horasCurso, $horasMaximas);
        }

        $totalHoras += $horasCurso;
    }
    $stmt->close();
    return $totalHoras;
}
function fechaAExcel($fecha)
{
    if (!$fecha) return '';
    $fechaExcel = (new DateTime($fecha))->diff(new DateTime('1899-12-30'))->days;
    return $fechaExcel;
}
