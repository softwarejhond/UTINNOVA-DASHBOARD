<?php

header('Content-Type: application/json');
require 'conexion.php'; // Conexión a la base de datos

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$whereClause = "WHERE departamentos.id_departamento=11
    AND user_register.status = 1 AND user_register.statusAdmin = '' ";

$params = [];
$types = '';

if (!empty($search)) {
    $whereClause .= " AND (user_register.first_name LIKE ? 
                          OR user_register.last_name LIKE ? 
                          OR user_register.email LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = &$searchTerm;
    $params[] = &$searchTerm;
    $params[] = &$searchTerm;
    $types .= 'sss';
}

$sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    $whereClause
    ORDER BY user_register.first_name ASC;";

$stmt = $conn->prepare($sql);

// Solo se ejecuta bind_param si hay parámetros
if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
$conn->close();

echo json_encode(['data' => $users]);

?>
