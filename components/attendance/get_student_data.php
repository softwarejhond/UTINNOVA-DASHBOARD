<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../controller/conexion.php';

$student_id = $_POST['student_id'] ?? '';

if (empty($student_id)) {
    echo json_encode(['success' => false, 'error' => 'ID de estudiante requerido']);
    exit;
}

try {
    $query = "SELECT 
        id_bootcamp,
        bootcamp_name,
        id_leveling_english,
        leveling_english_name,
        id_english_code,
        english_code_name,
        id_skills,
        skills_name,
        mode,
        headquarters
    FROM groups 
    WHERE number_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $courses = [];
        
        // Agregar todos los cursos del estudiante
        if (!empty($row['id_bootcamp'])) {
            $courses[] = [
                'id' => $row['id_bootcamp'],
                'name' => $row['bootcamp_name']
            ];
        }
        if (!empty($row['id_leveling_english'])) {
            $courses[] = [
                'id' => $row['id_leveling_english'],
                'name' => $row['leveling_english_name']
            ];
        }
        if (!empty($row['id_english_code'])) {
            $courses[] = [
                'id' => $row['id_english_code'],
                'name' => $row['english_code_name']
            ];
        }
        if (!empty($row['id_skills'])) {
            $courses[] = [
                'id' => $row['id_skills'],
                'name' => $row['skills_name']
            ];
        }

        echo json_encode([
            'success' => true,
            'data' => [
                'courses' => $courses,
                'mode' => $row['mode'],
                'headquarters' => $row['headquarters']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Estudiante no encontrado']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}