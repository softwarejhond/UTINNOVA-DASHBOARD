<?php
include_once('../../controller/conexion.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $department = $_POST['department'];
    $municipality = $_POST['municipality'];
    $address = $_POST['address'];

    // Preparar la consulta SQL
    $query = "UPDATE user_register SET 
              department = ?, 
              municipality = ?, 
              address = ? 
              WHERE number_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $department, $municipality, $address, $id);

    if ($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }

    $stmt->close();
    $conn->close();
}
?>