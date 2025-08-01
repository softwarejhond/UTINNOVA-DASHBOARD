<?php
require '../../controller/conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

$number_id = $data['number_id'] ?? null;
$new_leveling_english = $data['id_leveling_english'] ?? null;
$new_english_code = $data['id_english_code'] ?? null;
$new_skills = $data['id_skills'] ?? null;

if (!$number_id) {
    echo json_encode(['success' => false, 'message' => 'number_id requerido']);
    exit;
}

// Obtener los IDs anteriores desde enrollment_history (el registro más reciente)
$stmt = $conn->prepare("SELECT id_leveling_english, id_english_code, id_skills 
                        FROM enrollment_history 
                        WHERE number_id = ? 
                        ORDER BY unenrollment_date DESC LIMIT 1");
$stmt->bind_param("s", $number_id);
$stmt->execute();
$result = $stmt->get_result();
$history = $result->fetch_assoc();
$stmt->close();

if (!$history) {
    echo json_encode(['success' => false, 'message' => 'No se encontró historial de matrícula para este usuario']);
    exit;
}

try {
    // Actualizar registros de asistencia para inglés nivelador
    if ($new_leveling_english && $history['id_leveling_english']) {
        $stmt = $conn->prepare("UPDATE attendance_records SET course_id = ? WHERE student_id = ? AND course_id = ?");
        $stmt->bind_param("sis", $new_leveling_english, $number_id, $history['id_leveling_english']);
        $stmt->execute();
        $stmt->close();
    }
    // Actualizar registros de asistencia para english code
    if ($new_english_code && $history['id_english_code']) {
        $stmt = $conn->prepare("UPDATE attendance_records SET course_id = ? WHERE student_id = ? AND course_id = ?");
        $stmt->bind_param("sis", $new_english_code, $number_id, $history['id_english_code']);
        $stmt->execute();
        $stmt->close();
    }
    // Actualizar registros de asistencia para habilidades de poder
    if ($new_skills && $history['id_skills']) {
        $stmt = $conn->prepare("UPDATE attendance_records SET course_id = ? WHERE student_id = ? AND course_id = ?");
        $stmt->bind_param("sis", $new_skills, $number_id, $history['id_skills']);
        $stmt->execute();
        $stmt->close();
    }

    echo json_encode(['success' => true, 'message' => 'Registros de asistencia actualizados']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>