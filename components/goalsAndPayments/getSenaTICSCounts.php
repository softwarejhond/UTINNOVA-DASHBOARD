<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require '../../controller/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Inscritos: total de registros en user_register con institution = 'SenaTICS'
    $sqlInscritos = "SELECT COUNT(*) as count FROM user_register WHERE institution = 'SenaTICS'";
    $resInscritos = $conn->query($sqlInscritos);
    $inscritos = $resInscritos->fetch_assoc()['count'];

    // Beneficiarios: total de registros en user_register con institution = 'SenaTICS' y statusAdmin = 1
    $sqlBeneficiarios = "SELECT COUNT(*) as count FROM user_register WHERE institution = 'SenaTICS' AND statusAdmin = 1";
    $resBeneficiarios = $conn->query($sqlBeneficiarios);
    $beneficiarios = $resBeneficiarios->fetch_assoc()['count'];

    // Matriculados: total de registros en user_register con institution = 'SenaTICS', statusAdmin = 3, y number_id en groups
    $sqlMatriculados = "SELECT COUNT(*) as count FROM user_register ur INNER JOIN groups g ON ur.number_id = g.number_id WHERE ur.institution = 'SenaTICS' AND ur.statusAdmin = 3";
    $resMatriculados = $conn->query($sqlMatriculados);
    $matriculados = $resMatriculados->fetch_assoc()['count'];

    // Formados: total de registros en user_register con institution = 'SenaTICS', statusAdmin = 10
    $sqlFormados = "SELECT COUNT(*) as count FROM user_register WHERE institution = 'SenaTICS' AND statusAdmin = 10";
    $resFormados = $conn->query($sqlFormados);
    $formados = $resFormados->fetch_assoc()['count'];

    // No Aprobados: total de registros en user_register con institution = 'SenaTICS', statusAdmin = 3, y number_id en course_approvals como student_number_id
    $sqlNoAprobados = "SELECT COUNT(*) as count FROM user_register ur INNER JOIN course_approvals ca ON ur.number_id = ca.student_number_id WHERE ur.institution = 'SenaTICS' AND ur.statusAdmin = 3";
    $resNoAprobados = $conn->query($sqlNoAprobados);
    $noAprobados = $resNoAprobados->fetch_assoc()['count'];

    header('Content-Type: application/json');
    echo json_encode([
        'inscritos' => $inscritos,
        'beneficiarios' => $beneficiarios,
        'matriculados' => $matriculados,
        'formados' => $formados,
        'noAprobados' => $noAprobados
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>