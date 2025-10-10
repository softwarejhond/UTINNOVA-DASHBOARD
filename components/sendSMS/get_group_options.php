<?php
require __DIR__ . '/../../conexion.php';

header('Content-Type: application/json');

$headquarters = [];
$bootcamps = [];

try {
    $stmtHq = $conn->prepare("SELECT DISTINCT headquarters FROM groups");
    $stmtHq->execute();
    $resultHq = $stmtHq->get_result();
    while ($row = $resultHq->fetch_assoc()) {
        $headquarters[] = $row['headquarters'];
    }
    $stmtHq->close();

    $stmtBc = $conn->prepare("SELECT DISTINCT bootcamp_name FROM groups");
    $stmtBc->execute();
    $resultBc = $stmtBc->get_result();
    while ($row = $resultBc->fetch_assoc()) {
        $bootcamps[] = $row['bootcamp_name'];
    }
    $stmtBc->close();

    echo json_encode(['headquarters' => $headquarters, 'bootcamps' => $bootcamps]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

$conn->close();
?>