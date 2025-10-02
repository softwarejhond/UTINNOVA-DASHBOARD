<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function calcularHorasActualesPorEstudiante($conn, $studentId)
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

        // Aplica el límite de horas máximas del curso
        $totalHoras += min($horasCurso, $horasMaximas);
    }
    $stmt->close();
    return $totalHoras;
}

try {
    if (isset($_GET['action']) && $_GET['action'] === 'pagos') {
        // Devuelve los pagos disponibles para lote 1
        $pagos = [];
        $sqlPagos = "SELECT DISTINCT payment_number FROM payments WHERE lote = 1 ORDER BY payment_number";
        $resPagos = $conn->query($sqlPagos);
        while ($row = $resPagos->fetch_assoc()) {
            $pagos[] = $row;
        }
        header('Content-Type: application/json');
        echo json_encode($pagos);
        exit;
    }

    // Parámetros de filtro
    $pago = isset($_GET['pago']) ? intval($_GET['pago']) : 1;
    $modalidad = isset($_GET['modalidad']) ? $_GET['modalidad'] : 'Presencial';
    $contrapartida = isset($_GET['contrapartida']) ? $_GET['contrapartida'] : '0';

    // Obtener meta desde payments (sumar si es "Todas")
    $metaGoal = 0;
    if ($modalidad === 'Todas' && $contrapartida === 'Todas') {
        // Sumar todas las metas
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 1 AND payment_number = ?";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param("i", $pago);
    } elseif ($modalidad === 'Todas') {
        // Sumar por contrapartida
        $contrapartidaInt = intval($contrapartida);
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 1 AND payment_number = ? AND is_counterpart = ?";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param("ii", $pago, $contrapartidaInt);
    } elseif ($contrapartida === 'Todas') {
        // Sumar por modalidad
        $sqlMeta = "SELECT SUM(goal) as total_goal FROM payments WHERE lote = 1 AND payment_number = ? AND mode = ?";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param("is", $pago, $modalidad);
    } else {
        // Meta específica
        $contrapartidaInt = intval($contrapartida);
        $sqlMeta = "SELECT goal as total_goal FROM payments WHERE lote = 1 AND payment_number = ? AND mode = ? AND is_counterpart = ?";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param("isi", $pago, $modalidad, $contrapartidaInt);
    }
    
    $stmtMeta->execute();
    $resMeta = $stmtMeta->get_result();
    if ($rowMeta = $resMeta->fetch_assoc()) {
        $metaGoal = intval($rowMeta['total_goal']);
    }
    $stmtMeta->close();

    // Obtener bootcamps válidos
    $sqlPeriodos = "
        SELECT DISTINCT cp.bootcamp_code, cp.bootcamp_name 
        FROM course_periods cp
        INNER JOIN groups g ON cp.bootcamp_code = g.id_bootcamp
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE cp.payment_number = ? 
        AND cp.status = 1
        AND ur.lote = 1
    ";
    
    // Agregar filtro de modalidad si no es "Todas"
    if ($modalidad !== 'Todas') {
        $sqlPeriodos .= " AND g.mode = ?";
    }

    $stmtPeriodos = $conn->prepare($sqlPeriodos);
    if ($modalidad !== 'Todas') {
        $stmtPeriodos->bind_param("is", $pago, $modalidad);
    } else {
        $stmtPeriodos->bind_param("i", $pago);
    }
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

    // Procesar cada bootcamp
    foreach ($bootcamps as $code => $name) {
        // Obtener fechas del curso
        $sqlFechas = "SELECT start_date, end_date FROM course_periods WHERE bootcamp_code = ? AND payment_number = ?";
        $stmtFechas = $conn->prepare($sqlFechas);
        $stmtFechas->bind_param("si", $code, $pago);
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
            WHERE ur.lote = 1
            AND g.id_bootcamp = ?
            AND ur.statusAdmin = 10
        ";
        if ($modalidad !== 'Todas') {
            $sqlFormados .= " AND g.mode = ?";
            $stmtFormados = $conn->prepare($sqlFormados);
            $stmtFormados->bind_param("ss", $code, $modalidad);
        } else {
            $stmtFormados = $conn->prepare($sqlFormados);
            $stmtFormados->bind_param("s", $code);
        }
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

        // Construir consulta dinámica
        $sqlInscritos = "
            SELECT ur.number_id
            FROM groups g
            INNER JOIN user_register ur ON g.number_id = ur.number_id
            LEFT JOIN participantes p ON ur.number_id = p.numero_documento
            WHERE ur.lote = 1
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
                $sqlInscritos .= " AND p.numero_documento IS NOT NULL";
            } else {
                $sqlInscritos .= " AND p.numero_documento IS NULL";
            }
        }

        if ($modalidad !== 'Todas') {
            $sqlInscritos .= " AND g.mode = ?";
            $params[] = $modalidad;
            $paramTypes .= "s";
        }

        $stmtInscritos = $conn->prepare($sqlInscritos);
        $stmtInscritos->bind_param($paramTypes, ...$params);
        $stmtInscritos->execute();
        $resInscritos = $stmtInscritos->get_result();

        while ($row = $resInscritos->fetch_assoc()) {
            $studentId = $row['number_id'];
            $totalesPorCurso[$name]['inscritos']++;
            $totalInscritosGeneral++;

            $horas = calcularHorasActualesPorEstudiante($conn, $studentId);
            $porcentaje = ($horas / 159) * 100;

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
        'metaGoal' => $metaGoal
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
?>