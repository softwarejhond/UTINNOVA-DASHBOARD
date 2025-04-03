<?php
require './././conexion.php';

if (isset($_POST['user_id'])) {
    $userId = $_POST['user_id'];

    $sql = "SELECT call_information.*, advisors.name as advisor_name
            FROM call_information
            INNER JOIN advisors ON call_information.advisor_id = advisors.idAdvisor
            WHERE call_information.user_register_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $data = $result->fetch_assoc();
        echo json_encode($data);
    } else {
        echo json_encode(['error' => 'No se encontró información de la llamada.']);
    }
}
?>