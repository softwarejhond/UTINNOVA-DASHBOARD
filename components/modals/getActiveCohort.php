<?php
include '../../controller/conexion.php';

header('Content-Type: application/json');

try {
    // Consultar la cohorte activa
    $query = "SELECT * FROM cohorts WHERE state = 1 LIMIT 1";
    $result = $conn->query($query);
    
    if ($result && $result->num_rows > 0) {
        $cohort = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'cohort' => $cohort
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'cohort' => null
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    if (isset($conn)) $conn->close();
}
?>