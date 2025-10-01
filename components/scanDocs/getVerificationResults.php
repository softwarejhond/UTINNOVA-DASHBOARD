<?php
header('Content-Type: application/json');
require __DIR__ . '/../../controller/conexion.php';

try {
    $sql = "SELECT 
                dv.*,
                ur.first_name as db_first_name,
                ur.second_name as db_second_name,
                ur.first_last as db_first_last,
                ur.second_last as db_second_last,
                ur.birthdate as db_birthdate,
                ur.number_id as db_number_id
            FROM document_verification dv
            LEFT JOIN user_register ur ON dv.number_id = ur.number_id
            ORDER BY dv.verification_date DESC";
    
    $result = $conn->query($sql);
    
    $data = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
    }
    
    echo json_encode($data);
    
} catch (Exception $e) {
    echo json_encode([
        'error' => 'Error al obtener los datos: ' . $e->getMessage()
    ]);
}
?>