<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Presenciales Matriculados Lote 2
    $queryBootcampsPresencialesLote2 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Presencial' AND ur.lote = 2 AND ur.statusAdmin = 3
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultado = $conn->query($queryBootcampsPresencialesLote2);
    $bootcampsPresencialesLote2 = [];
    while ($row = $resultado->fetch_assoc()) {
        $bootcampsPresencialesLote2[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Presenciales Aprobados Lote 2
    $queryBootcampsPresencialesLote2Aprobados = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Presencial' AND ur.lote = 2 AND ur.statusAdmin = 10
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoAprobados = $conn->query($queryBootcampsPresencialesLote2Aprobados);
    $bootcampsPresencialesLote2Aprobados = [];
    while ($row = $resultadoAprobados->fetch_assoc()) {
        $bootcampsPresencialesLote2Aprobados[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Presenciales Pendientes Lote 2
    $queryProgramasPresencialesPendientesLote2 = "
        SELECT ur.program AS program, COUNT(*) AS cantidad
        FROM user_register ur
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE ur.mode = 'Presencial' AND ur.statusAdmin = 0 AND ur.lote = 2
        AND p.numero_documento IS NULL
        GROUP BY ur.program
        ORDER BY cantidad DESC
    ";
    $resultadoPendientes = $conn->query($queryProgramasPresencialesPendientesLote2);
    $programasPresencialesPendientesLote2 = [];
    while ($row = $resultadoPendientes->fetch_assoc()) {
        $programasPresencialesPendientesLote2[] = [
            'program' => $row['program'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Virtuales Matriculados Lote 2
    $queryBootcampsVirtualesLote2 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Virtual' AND ur.lote = 2 AND ur.statusAdmin = 3
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoVirtual = $conn->query($queryBootcampsVirtualesLote2);
    $bootcampsVirtualesLote2 = [];
    while ($row = $resultadoVirtual->fetch_assoc()) {
        $bootcampsVirtualesLote2[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Virtuales Aprobados Lote 2
    $queryBootcampsVirtualesLote2Aprobados = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Virtual' AND ur.lote = 2 AND ur.statusAdmin = 10
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoVirtualAprobados = $conn->query($queryBootcampsVirtualesLote2Aprobados);
    $bootcampsVirtualesLote2Aprobados = [];
    while ($row = $resultadoVirtualAprobados->fetch_assoc()) {
        $bootcampsVirtualesLote2Aprobados[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Virtuales Pendientes Lote 2
    $queryProgramasVirtualesPendientesLote2 = "
        SELECT ur.program AS program, COUNT(*) AS cantidad
        FROM user_register ur
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE ur.mode = 'Virtual' AND ur.statusAdmin = 0 AND ur.lote = 2
        AND p.numero_documento IS NULL
        GROUP BY ur.program
        ORDER BY cantidad DESC
    ";
    $resultadoVirtualPendientes = $conn->query($queryProgramasVirtualesPendientesLote2);
    $programasVirtualesPendientesLote2 = [];
    while ($row = $resultadoVirtualPendientes->fetch_assoc()) {
        $programasVirtualesPendientesLote2[] = [
            'program' => $row['program'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Cursos sin asistencia Lote 2 (Presencial)
    $queryCursosSinAsistenciaLote2 = "
        SELECT 
            g.bootcamp_name AS nombre,
            COUNT(ur.number_id) AS inscritos
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE ur.lote = 2
          AND g.mode = 'Presencial'
          AND p.numero_documento IS NULL
          AND (
            (g.id_bootcamp IS NOT NULL AND g.id_bootcamp NOT IN (SELECT course_id FROM attendance_records))
            AND (g.id_english_code IS NOT NULL AND g.id_english_code NOT IN (SELECT course_id FROM attendance_records))
            AND (g.id_skills IS NOT NULL AND g.id_skills NOT IN (SELECT course_id FROM attendance_records))
          )
        GROUP BY g.bootcamp_name
        ORDER BY inscritos DESC
    ";
    $resultadoCursosSinAsistenciaLote2 = $conn->query($queryCursosSinAsistenciaLote2);
    $cursosSinAsistenciaLote2 = [];
    $totalSinAsistenciaPresencialL2 = 0;
    while ($row = $resultadoCursosSinAsistenciaLote2->fetch_assoc()) {
        $cursosSinAsistenciaLote2[] = [
            'nombre' => $row['nombre'],
            'inscritos' => $row['inscritos']
        ];
        $totalSinAsistenciaPresencialL2 += intval($row['inscritos']);
    }

    // Cursos sin asistencia Lote 2 (Virtual)
    $queryCursosSinAsistenciaLote2Virtual = "
        SELECT 
            g.bootcamp_name AS nombre,
            COUNT(ur.number_id) AS inscritos
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE ur.lote = 2
          AND g.mode = 'Virtual'
          AND p.numero_documento IS NULL
          AND (
            (g.id_bootcamp IS NOT NULL AND g.id_bootcamp NOT IN (SELECT course_id FROM attendance_records))
            AND (g.id_english_code IS NOT NULL AND g.id_english_code NOT IN (SELECT course_id FROM attendance_records))
            AND (g.id_skills IS NOT NULL AND g.id_skills NOT IN (SELECT course_id FROM attendance_records))
          )
        GROUP BY g.bootcamp_name
        ORDER BY inscritos DESC
    ";
    $resultadoCursosSinAsistenciaLote2Virtual = $conn->query($queryCursosSinAsistenciaLote2Virtual);
    $cursosSinAsistenciaLote2Virtual = [];
    $totalSinAsistenciaVirtualL2 = 0;
    while ($row = $resultadoCursosSinAsistenciaLote2Virtual->fetch_assoc()) {
        $cursosSinAsistenciaLote2Virtual[] = [
            'nombre' => $row['nombre'],
            'inscritos' => $row['inscritos']
        ];
        $totalSinAsistenciaVirtualL2 += intval($row['inscritos']);
    }

    // Contrapartida Presenciales Matriculados Lote 2
    $queryContrapartidaPresencialMatriculadosL2 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE g.mode = 'Presencial' AND ur.lote = 2 AND ur.statusAdmin = 3
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $contrapartidaPresencialMatriculadosL2 = [];
    $resContrapartidaPresencialMatriculadosL2 = $conn->query($queryContrapartidaPresencialMatriculadosL2);
    while ($row = $resContrapartidaPresencialMatriculadosL2->fetch_assoc()) {
        $contrapartidaPresencialMatriculadosL2[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Contrapartida Presenciales Aprobados Lote 2
    $queryContrapartidaPresencialAprobadosL2 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE g.mode = 'Presencial' AND ur.lote = 2 AND ur.statusAdmin = 10
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $contrapartidaPresencialAprobadosL2 = [];
    $resContrapartidaPresencialAprobadosL2 = $conn->query($queryContrapartidaPresencialAprobadosL2);
    while ($row = $resContrapartidaPresencialAprobadosL2->fetch_assoc()) {
        $contrapartidaPresencialAprobadosL2[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Contrapartida Virtuales Matriculados Lote 2
    $queryContrapartidaVirtualMatriculadosL2 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE g.mode = 'Virtual' AND ur.lote = 2 AND ur.statusAdmin = 3
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $contrapartidaVirtualMatriculadosL2 = [];
    $resContrapartidaVirtualMatriculadosL2 = $conn->query($queryContrapartidaVirtualMatriculadosL2);
    while ($row = $resContrapartidaVirtualMatriculadosL2->fetch_assoc()) {
        $contrapartidaVirtualMatriculadosL2[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Contrapartida Virtuales Aprobados Lote 2
    $queryContrapartidaVirtualAprobadosL2 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE g.mode = 'Virtual' AND ur.lote = 2 AND ur.statusAdmin = 10
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $contrapartidaVirtualAprobadosL2 = [];
    $resContrapartidaVirtualAprobadosL2 = $conn->query($queryContrapartidaVirtualAprobadosL2);
    while ($row = $resContrapartidaVirtualAprobadosL2->fetch_assoc()) {
        $contrapartidaVirtualAprobadosL2[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        "bootcampsPresencialesLote2" => $bootcampsPresencialesLote2,
        "bootcampsPresencialesLote2Aprobados" => $bootcampsPresencialesLote2Aprobados,
        "programasPresencialesPendientesLote2" => $programasPresencialesPendientesLote2,
        "bootcampsVirtualesLote2" => $bootcampsVirtualesLote2,
        "bootcampsVirtualesLote2Aprobados" => $bootcampsVirtualesLote2Aprobados,
        "programasVirtualesPendientesLote2" => $programasVirtualesPendientesLote2,
        "cursosSinAsistenciaLote2" => $cursosSinAsistenciaLote2,
        "cursosSinAsistenciaLote2Virtual" => $cursosSinAsistenciaLote2Virtual,
        "totalSinAsistenciaPresencialL2" => $totalSinAsistenciaPresencialL2,
        "totalSinAsistenciaVirtualL2" => $totalSinAsistenciaVirtualL2,
        "totalSinAsistenciaGeneralL2" => $totalSinAsistenciaPresencialL2 + $totalSinAsistenciaVirtualL2,
        // Contrapartida Lote 2
        "contrapartidaPresencialMatriculadosL2" => $contrapartidaPresencialMatriculadosL2,
        "contrapartidaPresencialAprobadosL2" => $contrapartidaPresencialAprobadosL2,
        "contrapartidaVirtualMatriculadosL2" => $contrapartidaVirtualMatriculadosL2,
        "contrapartidaVirtualAprobadosL2" => $contrapartidaVirtualAprobadosL2
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}