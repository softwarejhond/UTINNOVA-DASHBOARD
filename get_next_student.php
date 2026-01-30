<?php
// filepath: c:\xampp\htdocs\UTINNOVA-DASHBOARD\get_next_student.php
include("conexion.php");

// Obtener todos los IDs de estudiantes con statusAdmin = 6
$sql = "SELECT DISTINCT ur.number_id 
        FROM user_register ur
        LEFT JOIN groups g ON ur.number_id = g.number_id
        WHERE ur.statusAdmin = 6 AND g.number_id IS NOT NULL
        ORDER BY ur.number_id";

// Para probar, puedes limitar los resultados:
// $sql .= " LIMIT 10";

$result = mysqli_query($conn, $sql);

if (!$result) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Error en la consulta: ' . mysqli_error($conn)]);
    exit;
}

$student_ids = [];
while ($row = mysqli_fetch_assoc($result)) {
    $student_ids[] = $row['number_id'];
}

header('Content-Type: application/json');
echo json_encode($student_ids);
?>