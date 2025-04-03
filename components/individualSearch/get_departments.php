<?php
include_once('../../controller/conexion.php');

$query = "SELECT * FROM departamentos ORDER BY departamento";
$result = $conn->query($query);

echo '<option value="">Seleccione un departamento</option>';
while ($depRow = $result->fetch_assoc()) {
    echo "<option value='" . $depRow['id_departamento'] . "'>" . $depRow['departamento'] . "</option>";
}

$conn->close();
?>