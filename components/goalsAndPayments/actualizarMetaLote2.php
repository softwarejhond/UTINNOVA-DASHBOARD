<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function calcularHorasActualesPorEstudianteL2($conn, $studentId)
{
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
    $tienePresente = false;
    while ($curso = $result->fetch_assoc()) {
        $cursoId = $curso['code'];
        $horasMaximas = intval($curso['real_hours']);

        // Consulta las asistencias y suma las horas actuales
        $sqlHoras = "SELECT ar.class_date, ar.attendance_status,
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
                if ($asistencia['attendance_status'] === 'presente') {
                    $tienePresente = true;
                }
            }
        }
        $stmtHoras->close();

        // Aplica el límite de horas máximas del curso
        $totalHoras += min($horasCurso, $horasMaximas);
    }
    $stmt->close();
    return ['horas' => $totalHoras, 'tienePresente' => $tienePresente];
}

try {
    if (isset($_GET['action']) && $_GET['action'] === 'pagos') {
        // Devuelve los pagos disponibles para lote 2
        $pagos = [];
        $sqlPagos = "SELECT DISTINCT payment_number FROM payments WHERE lote = 2 ORDER BY payment_number";
        $resPagos = $conn->query($sqlPagos);
        while ($row = $resPagos->fetch_assoc()) {
            $pagos[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($pagos);
        exit;
    }

    // NUEVO: Acción para obtener instituciones
    if (isset($_GET['action']) && $_GET['action'] === 'instituciones') {
        $instituciones = [];
        
        // Agregar opción "Sin institución" al principio
        $instituciones[] = ['institution' => 'Sin institución'];
        
        // Obtener instituciones existentes para lote 2
        $sqlInstituciones = "SELECT DISTINCT institution FROM user_register WHERE lote = 2 AND institution IS NOT NULL AND institution != '' ORDER BY institution";
        $resInstituciones = $conn->query($sqlInstituciones);
        while ($row = $resInstituciones->fetch_assoc()) {
            $instituciones[] = $row;
        }
        
        header('Content-Type: application/json');
        echo json_encode($instituciones);
        exit;
    }

    // Parámetros de filtro
    $pagosParam = isset($_GET['pagos']) ? $_GET['pagos'] : '';
    $institucionesParam = isset($_GET['instituciones']) ? $_GET['instituciones'] : '';
    $modalidad = isset($_GET['modalidad']) ? $_GET['modalidad'] : 'Presencial';
    $contrapartida = isset($_GET['contrapartida']) ? $_GET['contrapartida'] : '0';

    // Validar y procesar múltiples pagos
    if (empty($pagosParam)) {
        throw new Exception("Debe seleccionar al menos un número de pago");
    }

    $pagosArray = array_map('intval', explode(',', $pagosParam));
    $pagosArray = array_filter($pagosArray, function($p) { return $p > 0; });
    
    if (empty($pagosArray)) {
        throw new Exception("Números de pago inválidos");
    }

    // Procesar instituciones seleccionadas
    $institucionesArray = [];
    $incluirSinInstitucion = false;
    if (!empty($institucionesParam)) {
        $institucionesTempArray = array_map('trim', explode(',', $institucionesParam));
        $institucionesTempArray = array_filter($institucionesTempArray, function($inst) { return !empty($inst); });
        
        foreach ($institucionesTempArray as $inst) {
            if ($inst === 'Sin institución') {
                $incluirSinInstitucion = true;
            } else {
                $institucionesArray[] = $inst;
            }
        }
    }

    // Crear placeholders para IN clause
    $placeholders = str_repeat('?,', count($pagosArray) - 1) . '?';

    // Obtener meta desde payments (sumar para múltiples pagos)
    $metaGoal = 0;
    if ($modalidad === 'Todas' && $contrapartida === 'Todas') {
        // Sumar todas las metas de todos los pagos seleccionados
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 2 AND payment_number IN ($placeholders)";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param(str_repeat('i', count($pagosArray)), ...$pagosArray);
    } elseif ($modalidad === 'Todas') {
        // Sumar por contrapartida
        $contrapartidaInt = intval($contrapartida);
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 2 AND payment_number IN ($placeholders) AND is_counterpart = ?";
        $params = array_merge($pagosArray, [$contrapartidaInt]);
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param(str_repeat('i', count($pagosArray)) . 'i', ...$params);
    } elseif ($contrapartida === 'Todas') {
        // Sumar por modalidad
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 2 AND payment_number IN ($placeholders) AND mode = ?";
        $params = array_merge($pagosArray, [$modalidad]);
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param(str_repeat('i', count($pagosArray)) . 's', ...$params);
    } else {
        // Meta específica
        $contrapartidaInt = intval($contrapartida);
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 2 AND payment_number IN ($placeholders) AND mode = ? AND is_counterpart = ?";
        $params = array_merge($pagosArray, [$modalidad, $contrapartidaInt]);
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param(str_repeat('i', count($pagosArray)) . 'si', ...$params);
    }
    
    $stmtMeta->execute();
    $resMeta = $stmtMeta->get_result();
    if ($rowMeta = $resMeta->fetch_assoc()) {
        $metaGoal = intval($rowMeta['total_goal']);
    }
    $stmtMeta->close();

    // Obtener bootcamps válidos para múltiples pagos
    $sqlPeriodos = "
        SELECT DISTINCT cp.bootcamp_code, cp.bootcamp_name 
        FROM course_periods cp
        INNER JOIN groups g ON cp.bootcamp_code = g.id_bootcamp
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE cp.payment_number IN ($placeholders)
        AND cp.status = 1
        AND ur.lote = 2
    ";
    
    // Agregar filtro de modalidad si no es "Todas"
    $params = $pagosArray;
    $paramTypes = str_repeat('i', count($pagosArray));
    
    if ($modalidad !== 'Todas') {
        $sqlPeriodos .= " AND g.mode = ?";
        $params[] = $modalidad;
        $paramTypes .= 's';
    }

    // Agregar filtro de instituciones si hay seleccionadas
    if (!empty($institucionesArray) || $incluirSinInstitucion) {
        $condicionesInstitucion = [];
        $parametrosInstitucion = [];
        
        if (!empty($institucionesArray)) {
            $institutionPlaceholders = str_repeat('?,', count($institucionesArray) - 1) . '?';
            $condicionesInstitucion[] = "ur.institution IN ($institutionPlaceholders)";
            $parametrosInstitucion = array_merge($parametrosInstitucion, $institucionesArray);
        }
        
        if ($incluirSinInstitucion) {
            $condicionesInstitucion[] = "(ur.institution IS NULL OR ur.institution = '')";
        }
        
        $sqlPeriodos .= " AND (" . implode(' OR ', $condicionesInstitucion) . ")";
        $params = array_merge($params, $parametrosInstitucion);
        $paramTypes .= str_repeat('s', count($parametrosInstitucion));
    }

    $stmtPeriodos = $conn->prepare($sqlPeriodos);
    $stmtPeriodos->bind_param($paramTypes, ...$params);
    $stmtPeriodos->execute();
    $resPeriodos = $stmtPeriodos->get_result();

    $bootcamps = [];
    while ($row = $resPeriodos->fetch_assoc()) {
        $bootcamps[$row['bootcamp_code']] = $row['bootcamp_name'];
    }
    $stmtPeriodos->close();

    $totalesPorCurso = [];
    $totalInscritosGeneral = 0;
    $total75General = 0;
    $totalMenos75General = 0;
    $totalAlMenosUnaPresente = 0;

    // Procesar cada bootcamp
    foreach ($bootcamps as $code => $name) {
        // Obtener fechas del curso (usar el primer pago para fechas)
        $sqlFechas = "SELECT start_date, end_date FROM course_periods WHERE bootcamp_code = ? AND payment_number = ? LIMIT 1";
        $stmtFechas = $conn->prepare($sqlFechas);
        $stmtFechas->bind_param("si", $code, $pagosArray[0]);
        $stmtFechas->execute();
        $resFechas = $stmtFechas->get_result();
        $startDate = '';
        $endDate = '';
        if ($rowFechas = $resFechas->fetch_assoc()) {
            $startDate = $rowFechas['start_date'];
            $endDate = $rowFechas['end_date'];
        }
        $stmtFechas->close();

        // Obtener total de formados (statusAdmin = 10)
        $sqlFormados = "
            SELECT COUNT(*) as total_formados
            FROM groups g
            INNER JOIN user_register ur ON g.number_id = ur.number_id
            LEFT JOIN participantes p ON ur.number_id = p.numero_documento
            WHERE ur.lote = 2
            AND g.id_bootcamp = ?
            AND ur.statusAdmin = 10
        ";
        $params = [$code];
        $paramTypes = "s";
        
        if ($modalidad !== 'Todas') {
            $sqlFormados .= " AND g.mode = ?";
            $params[] = $modalidad;
            $paramTypes .= "s";
        }

        // NUEVO: Agregar filtro de contrapartida (aplicar aquí, después de modalidad)
        if ($contrapartida !== 'Todas') {
            $contrapartidaInt = intval($contrapartida);
            if ($contrapartidaInt) {
                $sqlFormados .= " AND (p.numero_documento IS NOT NULL OR ur.directed_base = 1)";
            } else {
                $sqlFormados .= " AND (p.numero_documento IS NULL AND ur.directed_base != 1)";
            }
        }

        // Agregar filtro de instituciones en formados
        if (!empty($institucionesArray) || $incluirSinInstitucion) {
            $condicionesInstitucion = [];
            $parametrosInstitucion = [];
            
            if (!empty($institucionesArray)) {
                $institutionPlaceholders = str_repeat('?,', count($institucionesArray) - 1) . '?';
                $condicionesInstitucion[] = "ur.institution IN ($institutionPlaceholders)";
                $parametrosInstitucion = array_merge($parametrosInstitucion, $institucionesArray);
            }
            
            if ($incluirSinInstitucion) {
                $condicionesInstitucion[] = "(ur.institution IS NULL OR ur.institution = '')";
            }
            
            $sqlFormados .= " AND (" . implode(' OR ', $condicionesInstitucion) . ")";
            $params = array_merge($params, $parametrosInstitucion);
            $paramTypes .= str_repeat('s', count($parametrosInstitucion));
        }
        
        $stmtFormados = $conn->prepare($sqlFormados);
        $stmtFormados->bind_param($paramTypes, ...$params);
        $stmtFormados->execute();
        $resFormados = $stmtFormados->get_result();
        $totalFormados = 0;
        if ($rowFormados = $resFormados->fetch_assoc()) {
            $totalFormados = intval($rowFormados['total_formados']);
        }
        $stmtFormados->close();

        $totalesPorCurso[$name] = [
            'inscritos' => 0,
            'mayor75' => 0,
            'menor75' => 0,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'formados' => $totalFormados
        ];

        // Construir consulta dinámica para inscritos
        $sqlInscritos = "
            SELECT ur.number_id
            FROM groups g
            INNER JOIN user_register ur ON g.number_id = ur.number_id
            LEFT JOIN participantes p ON ur.number_id = p.numero_documento
            WHERE ur.lote = 2
            AND g.id_bootcamp = ?
            AND ur.statusAdmin IN (3, 10, 6)
        ";

        // Agregar filtros según selección
        $params = [$code];
        $paramTypes = "s";

        if ($contrapartida === 'Todas') {
            // No agregar filtro de contrapartida
        } else {
            $contrapartidaInt = intval($contrapartida);
            if ($contrapartidaInt) {
                $sqlInscritos .= " AND (p.numero_documento IS NOT NULL OR ur.directed_base = 1)";
            } else {
                $sqlInscritos .= " AND (p.numero_documento IS NULL AND ur.directed_base != 1)";
            }
        }

        if ($modalidad !== 'Todas') {
            $sqlInscritos .= " AND g.mode = ?";
            $params[] = $modalidad;
            $paramTypes .= "s";
        }

        // Agregar filtro de instituciones en inscritos
        if (!empty($institucionesArray) || $incluirSinInstitucion) {
            $condicionesInstitucion = [];
            $parametrosInstitucion = [];
            
            if (!empty($institucionesArray)) {
                $institutionPlaceholders = str_repeat('?,', count($institucionesArray) - 1) . '?';
                $condicionesInstitucion[] = "ur.institution IN ($institutionPlaceholders)";
                $parametrosInstitucion = array_merge($parametrosInstitucion, $institucionesArray);
            }
            
            if ($incluirSinInstitucion) {
                $condicionesInstitucion[] = "(ur.institution IS NULL OR ur.institution = '')";
            }
            
            $sqlInscritos .= " AND (" . implode(' OR ', $condicionesInstitucion) . ")";
            $params = array_merge($params, $parametrosInstitucion);
            $paramTypes .= str_repeat('s', count($parametrosInstitucion));
        }

        $stmtInscritos = $conn->prepare($sqlInscritos);
        $stmtInscritos->bind_param($paramTypes, ...$params);
        $stmtInscritos->execute();
        $resInscritos = $stmtInscritos->get_result();

        while ($row = $resInscritos->fetch_assoc()) {
            $studentId = $row['number_id'];
            $totalesPorCurso[$name]['inscritos']++;
            $totalInscritosGeneral++;

            // Obtener las horas reales del curso actual y verificar si tiene presente
            $resultado = calcularHorasActualesPorEstudianteL2($conn, $studentId);
            $horas = $resultado['horas'];
            $tienePresente = $resultado['tienePresente'];

            if ($tienePresente) {
                $totalAlMenosUnaPresente++;
            }

            // Consulta para obtener las horas totales del curso (real_hours)
            $sqlHorasCurso = "SELECT real_hours FROM courses WHERE code = ?";
            $stmtHorasCurso = $conn->prepare($sqlHorasCurso);
            $stmtHorasCurso->bind_param("s", $code);
            $stmtHorasCurso->execute();
            $resHorasCurso = $stmtHorasCurso->get_result();
            $realHours = 0;
            if ($rowHorasCurso = $resHorasCurso->fetch_assoc()) {
                $realHours = floatval($rowHorasCurso['real_hours']);
            }
            $stmtHorasCurso->close();

            // Evita división por cero
            $porcentaje = ($realHours > 0) ? ($horas / $realHours) * 100 : 0;

            if ($porcentaje >= 75) {
                $totalesPorCurso[$name]['mayor75']++;
                $total75General++;
            } else {
                $totalesPorCurso[$name]['menor75']++;
                $totalMenos75General++;
            }
        }
        $stmtInscritos->close();
    }

    header('Content-Type: application/json');
    echo json_encode([
        'totalesPorCurso' => $totalesPorCurso,
        'totalInscritosGeneral' => $totalInscritosGeneral,
        'total75General' => $total75General,
        'totalMenos75General' => $totalMenos75General,
        'metaGoal' => $metaGoal,
        'totalAlMenosUnaPresente' => $totalAlMenosUnaPresente
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>