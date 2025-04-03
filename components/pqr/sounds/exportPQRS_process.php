<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../controller/conexion.php';

// Recoger los filtros
$estado = isset($_POST['estado']) ? $_POST['estado'] : '';
$tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
$mes = isset($_POST['mes']) ? $_POST['mes'] : '';

// Construir la consulta SQL con los filtros
$sql_pqrs = "SELECT * FROM pqrs WHERE 1=1";
if (!empty($estado)) {
    $sql_pqrs .= " AND estado_id = " . intval($estado);
}
if (!empty($tipo)) {
    $sql_pqrs .= " AND tipo = '" . $conn->real_escape_string($tipo) . "'";
}
if (!empty($mes)) {
    $sql_pqrs .= " AND MONTH(fecha_registro) = " . intval($mes);
}
$resultado_pqrs = $conn->query($sql_pqrs);

// Generar el archivo Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="PQRS_export.xls"');
header('Cache-Control: max-age=0');

echo "<table border='1'>";
echo "<tr>
        <th>Fecha</th>
        <th>Radicado</th>
        <th>Tipo</th>
        <th>Asunto</th>
        <th>Fecha de Creación</th>
        <th>Estado</th>
    </tr>";

while ($fila = $resultado_pqrs->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . (isset($fila["fecha_registro"]) ? htmlspecialchars($fila["fecha_registro"]) : 'N/A') . "</td>";
    echo "<td>" . (isset($fila["numero_radicado"]) ? htmlspecialchars($fila["numero_radicado"]) : 'N/A') . "</td>";
    echo "<td>" . (isset($fila["tipo"]) ? htmlspecialchars($fila["tipo"]) : 'N/A') . "</td>";
    echo "<td>" . (isset($fila["asunto"]) ? htmlspecialchars($fila["asunto"]) : 'N/A') . "</td>";
    echo "<td>" . (isset($fila["fecha_creacion"]) ? htmlspecialchars($fila["fecha_creacion"]) : 'N/A') . "</td>";
    echo "<td>" . (isset($fila["estado_nombre"]) ? htmlspecialchars($fila["estado_nombre"]) : 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
