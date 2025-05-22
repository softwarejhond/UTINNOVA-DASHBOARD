<?php
include_once('../../controller/conexion.php');

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

// Recibir el ID del estudiante
if (!isset($_GET['student_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'ID de estudiante no proporcionado'
    ]);
    exit;
}

$studentId = $_GET['student_id'];

// Consulta para verificar si ya tiene cursos asignados
$sql = "SELECT * FROM course_assignments WHERE student_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $studentId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Obtener los datos de asignación
    $assignment = $result->fetch_assoc();
    
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'tiene_cursos' => true,
        'cursos' => [
            'bootcamp' => [
                'id' => $assignment['bootcamp_id'],
                'name' => $assignment['bootcamp_name']
            ],
            'english' => [
                'id' => $assignment['leveling_english_id'],
                'name' => $assignment['leveling_english_name'] 
            ],
            'english_code' => [
                'id' => $assignment['english_code_id'],
                'name' => $assignment['english_code_name']
            ],
            'skills' => [
                'id' => $assignment['skills_id'],
                'name' => $assignment['skills_name']
            ]
        ]
    ]);
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'tiene_cursos' => false
    ]);
}
?>