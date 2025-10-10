<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $programa = isset($_GET['programa']) ? $_GET['programa'] : '';
    $busqueda = isset($_GET['busqueda']) ? $_GET['busqueda'] : '';

    $sql = "
        SELECT 
            ur.number_id,
            CONCAT(ur.first_name, ' ', COALESCE(ur.second_name, ''), ' ', ur.first_last, ' ', COALESCE(ur.second_last, '')) AS nombre_completo,
            COALESCE(ur.birthdate, 'N/A') AS birth_date,
            COALESCE(ur.email, 'N/A') AS email,
            COALESCE(ur.first_phone, 'N/A') AS phone,
            COALESCE(ur.program, 'N/A') AS program,
            COALESCE(g.mode, 'N/A') AS modalidad,
            CASE ur.statusAdmin
                WHEN 1 THEN 'BENEFICIARIO'
                WHEN 0 THEN 'SIN ESTADO'
                WHEN 2 THEN 'RECHAZADO'
                WHEN 3 THEN 'MATRICULADO'
                WHEN 4 THEN 'SIN CONTACTO'
                WHEN 5 THEN 'EN PROCESO'
                WHEN 6 THEN 'CERTIFICADO'
                WHEN 7 THEN 'INACTIVO'
                WHEN 8 THEN 'BENEFICIARIO CONTRAPARTIDA'
                WHEN 9 THEN 'APLAZADO'
                WHEN 10 THEN 'FORMADO'
                WHEN 11 THEN 'NO VALIDO'
                WHEN 12 THEN 'NO APROBADO'
                ELSE 'DESCONOCIDO'
            END AS statusAdmin,
            COALESCE(g.bootcamp_name, 'N/A') AS bootcamp_name,
            COALESCE(g.leveling_english_name, 'N/A') AS leveling_english_name,
            COALESCE(g.english_code_name, 'N/A') AS english_code_name,
            COALESCE(g.skills_name, 'N/A') AS skills_name,
            COALESCE(g.creation_date, 'N/A') AS fecha_matricula,
            CASE 
                WHEN u.nivel IS NULL THEN 'N/A'
                WHEN CAST(u.nivel AS UNSIGNED) >= 0 AND CAST(u.nivel AS UNSIGNED) < 5 THEN 'Básico'
                WHEN CAST(u.nivel AS UNSIGNED) >= 5 AND CAST(u.nivel AS UNSIGNED) < 11 THEN 'Intermedio'
                ELSE 'Avanzado'
            END AS nivel,
            COALESCE(u.fecha_registro, 'N/A') AS fecha_prueba
        FROM user_register ur
        LEFT JOIN groups g ON ur.number_id = g.number_id
        LEFT JOIN usuarios u ON ur.number_id = u.cedula
        WHERE ur.institution = 'SenaTICS'
    ";

    $params = [];
    $types = '';

    if (!empty($programa)) {
        $sql .= " AND ur.program = ?";
        $params[] = $programa;
        $types .= 's';
    }

    if (!empty($busqueda)) {
        $sql .= " AND ur.number_id = ?";
        $params[] = $busqueda;
        $types .= 's';
    }

    $stmt = $conn->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($data);

} catch (Exception $e) {
    error_log("Error en getSenaTICSList.php: " . $e->getMessage() . " en línea " . $e->getLine());
    http_response_code(500);
    echo json_encode([
        "error" => $e->getMessage(),
        "line" => $e->getLine(),
        "file" => basename($e->getFile())
    ]);
}
?>