<?php
include_once('../../controller/conexion.php');

header('Content-Type: text/html; charset=utf-8');

if(isset($_POST['department_id'])) {
    $department_id = mysqli_real_escape_string($conn, $_POST['department_id']);
    
    $query = "SELECT id_municipio, municipio 
              FROM municipios 
              WHERE departamento_id = ? 
              ORDER BY municipio ASC";
              
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    echo "<option value=''>Seleccione un municipio</option>";
    
    while($row = $result->fetch_assoc()) {
        echo "<option value='" . $row['id_municipio'] . "'>" . 
             htmlspecialchars($row['municipio']) . "</option>";
    }
    
    $stmt->close();
} else {
    echo "<option value=''>Error: No se recibió el departamento</option>";
}

$conn->close();
?>