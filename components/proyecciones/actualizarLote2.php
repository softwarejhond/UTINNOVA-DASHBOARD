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

    header('Content-Type: application/json');
    echo json_encode([
        "bootcampsPresencialesLote2" => $bootcampsPresencialesLote2,
        "bootcampsPresencialesLote2Aprobados" => $bootcampsPresencialesLote2Aprobados,
        "programasPresencialesPendientesLote2" => $programasPresencialesPendientesLote2,
        "bootcampsVirtualesLote2" => $bootcampsVirtualesLote2,
        "bootcampsVirtualesLote2Aprobados" => $bootcampsVirtualesLote2Aprobados,
        "programasVirtualesPendientesLote2" => $programasVirtualesPendientesLote2
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}