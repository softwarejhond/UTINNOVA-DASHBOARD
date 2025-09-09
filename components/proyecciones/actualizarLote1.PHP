<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Presenciales Matriculados
    $queryBootcampsPresencialesLote1 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Presencial' AND ur.lote = 1 AND ur.statusAdmin = 3
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultado = $conn->query($queryBootcampsPresencialesLote1);
    $bootcampsPresencialesLote1 = [];
    while ($row = $resultado->fetch_assoc()) {
        $bootcampsPresencialesLote1[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Presenciales Aprobados
    $queryBootcampsPresencialesLote1Aprobados = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Presencial' AND ur.lote = 1 AND ur.statusAdmin = 10
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoAprobados = $conn->query($queryBootcampsPresencialesLote1Aprobados);
    $bootcampsPresencialesLote1Aprobados = [];
    while ($row = $resultadoAprobados->fetch_assoc()) {
        $bootcampsPresencialesLote1Aprobados[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Presenciales Pendientes
    $queryProgramasPresencialesPendientes = "
        SELECT ur.program AS program, COUNT(*) AS cantidad
        FROM user_register ur
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE ur.mode = 'Presencial' AND ur.statusAdmin = 0 AND ur.lote = 1
        AND p.numero_documento IS NULL
        GROUP BY ur.program
        ORDER BY cantidad DESC
    ";
    $resultadoPendientes = $conn->query($queryProgramasPresencialesPendientes);
    $programasPresencialesPendientes = [];
    while ($row = $resultadoPendientes->fetch_assoc()) {
        $programasPresencialesPendientes[] = [
            'program' => $row['program'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Virtuales Matriculados
    $queryBootcampsVirtualesLote1 = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Virtual' AND ur.lote = 1 AND ur.statusAdmin = 3
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoVirtual = $conn->query($queryBootcampsVirtualesLote1);
    $bootcampsVirtualesLote1 = [];
    while ($row = $resultadoVirtual->fetch_assoc()) {
        $bootcampsVirtualesLote1[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Virtuales Aprobados
    $queryBootcampsVirtualesLote1Aprobados = "
        SELECT g.bootcamp_name AS bootcamp, COUNT(*) AS cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE g.mode = 'Virtual' AND ur.lote = 1 AND ur.statusAdmin = 10
        AND p.numero_documento IS NULL
        GROUP BY g.bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoVirtualAprobados = $conn->query($queryBootcampsVirtualesLote1Aprobados);
    $bootcampsVirtualesLote1Aprobados = [];
    while ($row = $resultadoVirtualAprobados->fetch_assoc()) {
        $bootcampsVirtualesLote1Aprobados[] = [
            'bootcamp' => $row['bootcamp'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Virtuales Pendientes
    $queryProgramasVirtualesPendientes = "
        SELECT ur.program AS program, COUNT(*) AS cantidad
        FROM user_register ur
        LEFT JOIN participantes p ON ur.number_id = p.numero_documento
        WHERE ur.mode = 'Virtual' AND ur.statusAdmin = 0 AND ur.lote = 1
        AND p.numero_documento IS NULL
        GROUP BY ur.program
        ORDER BY cantidad DESC
    ";
    $resultadoVirtualPendientes = $conn->query($queryProgramasVirtualesPendientes);
    $programasVirtualesPendientes = [];
    while ($row = $resultadoVirtualPendientes->fetch_assoc()) {
        $programasVirtualesPendientes[] = [
            'program' => $row['program'],
            'cantidad' => $row['cantidad']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode([
        "bootcampsPresencialesLote1" => $bootcampsPresencialesLote1,
        "bootcampsPresencialesLote1Aprobados" => $bootcampsPresencialesLote1Aprobados,
        "programasPresencialesPendientes" => $programasPresencialesPendientes,
        "bootcampsVirtualesLote1" => $bootcampsVirtualesLote1,
        "bootcampsVirtualesLote1Aprobados" => $bootcampsVirtualesLote1Aprobados,
        "programasVirtualesPendientes" => $programasVirtualesPendientes
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}