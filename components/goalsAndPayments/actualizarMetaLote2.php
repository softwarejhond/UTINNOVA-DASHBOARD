<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

function calcularHorasActualesPorEstudianteL2($conn, $studentId)
{
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

        $totalHoras += min($horasCurso, $horasMaximas);
    }
    $stmt->close();
    return $totalHoras;
}

try {
    if (isset($_GET['action']) && $_GET['action'] === 'pagos') {
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

    $pago = isset($_GET['pago']) ? intval($_GET['pago']) : 1;
    $modalidad = isset($_GET['modalidad']) ? $_GET['modalidad'] : 'Presencial';
    $contrapartida = isset($_GET['contrapartida']) ? intval($_GET['contrapartida']) : 0;

    $sqlMeta = "SELECT goal FROM payments WHERE lote = 2 AND payment_number = ? AND mode = ? AND is_counterpart = ?";
    $stmtMeta = $conn->prepare($sqlMeta);
    $stmtMeta->bind_param("isi", $pago, $modalidad, $contrapartida);
    $stmtMeta->execute();
    $resMeta = $stmtMeta->get_result();
    $metaGoal = 0;
    if ($rowMeta = $resMeta->fetch_assoc()) {
        $metaGoal = intval($rowMeta['goal']);
    }
    $stmtMeta->close();

    $sqlPeriodos = "SELECT bootcamp_code, bootcamp_name FROM course_periods WHERE payment_number = ? AND status = 1";
    $stmtPeriodos = $conn->prepare($sqlPeriodos);
    $stmtPeriodos->bind_param("i", $pago);
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

    foreach ($bootcamps as $code => $name) {
        // Obtener total de formados (statusAdmin = 10)
        $sqlFormados = "
            SELECT COUNT(*) as total_formados
            FROM groups g
            INNER JOIN user_register ur ON g.number_id = ur.number_id
            WHERE ur.lote = 2
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
            'formados' => $totalFormados
        ];

        $sqlInscritos = "
            SELECT ur.number_id
            FROM groups g
            INNER JOIN user_register ur ON g.number_id = ur.number_id
            LEFT JOIN participantes p ON ur.number_id = p.numero_documento
            WHERE ur.lote = 2
            AND g.id_bootcamp = ?
            AND ur.statusAdmin IN (3, 10, 6)
            AND " . ($contrapartida ? "p.numero_documento IS NOT NULL" : "p.numero_documento IS NULL") . "
            AND g.mode = ?
        ";
        $stmtInscritos = $conn->prepare($sqlInscritos);
        $stmtInscritos->bind_param("ss", $code, $modalidad);
        $stmtInscritos->execute();
        $resInscritos = $stmtInscritos->get_result();

        while ($row = $resInscritos->fetch_assoc()) {
            $studentId = $row['number_id'];
            $totalesPorCurso[$name]['inscritos']++;
            $totalInscritosGeneral++;

            $horas = calcularHorasActualesPorEstudianteL2($conn, $studentId);
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