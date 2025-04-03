<?php
include_once('../../controller/conexion.php');

if(isset($_POST['id'])) {
    $id = $_POST['id'];
    
    $query = "SELECT ur.department, ur.municipality, ur.address, 
              d.departamento, m.municipio 
              FROM user_register ur 
              LEFT JOIN departamentos d ON ur.department = d.id_departamento 
              LEFT JOIN municipios m ON ur.municipality = m.id_municipio 
              WHERE ur.number_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($row = $result->fetch_assoc()) {
        echo json_encode([
            'success' => true,
            'data' => [
                'department_id' => $row['department'],
                'department_name' => $row['departamento'],
                'municipality_id' => $row['municipality'],
                'municipality_name' => $row['municipio'],
                'address' => $row['address']
            ]
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No se encontraron datos para este usuario'
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'ID de usuario no proporcionado'
    ]);
}

$conn->close();
?>