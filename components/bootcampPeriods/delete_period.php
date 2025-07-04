<?php
session_start();
include '../../controller/conexion.php';

// Verificar que el usuario esté logueado y tenga permisos
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['rol'], ['Administrador', 'Control maestro'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No tienes permisos para realizar esta acción']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $period_id = intval($_POST['period_id'] ?? 0);

        if ($period_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de período inválido']);
            exit;
        }

        // Verificar que el período existe
        $check_sql = "SELECT id, period_name FROM course_periods WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $period_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows === 0) {
            echo json_encode(['success' => false, 'message' => 'El período no existe']);
            exit;
        }

        $period_data = $check_result->fetch_assoc();

        // Verificar si hay estudiantes asociados a este período
        // Esto es opcional, dependiendo de si quieres evitar eliminar períodos con estudiantes
        $students_check_sql = "SELECT COUNT(*) as student_count FROM `groups` g 
                              INNER JOIN course_periods cp ON 
                              g.id_bootcamp = cp.bootcamp_code OR 
                              g.id_leveling_english = cp.leveling_english_code OR 
                              g.id_english_code = cp.english_code_code OR 
                              g.id_skills = cp.skills_code 
                              WHERE cp.id = ?";
        
        $students_stmt = $conn->prepare($students_check_sql);
        $students_stmt->bind_param("i", $period_id);
        $students_stmt->execute();
        $students_result = $students_stmt->get_result();
        $student_count = $students_result->fetch_assoc()['student_count'];

        if ($student_count > 0) {
            echo json_encode([
                'success' => false, 
                'message' => "No se puede eliminar el período '{$period_data['period_name']}' porque tiene {$student_count} estudiante(s) asociado(s)"
            ]);
            exit;
        }

        // Eliminar el período
        $delete_sql = "DELETE FROM course_periods WHERE id = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("i", $period_id);

        if ($delete_stmt->execute()) {
            if ($delete_stmt->affected_rows > 0) {
                // Log de la eliminación (opcional)
                error_log("Período eliminado - ID: {$period_id}, Nombre: {$period_data['period_name']}, Usuario: {$_SESSION['full_name']}");
                
                echo json_encode([
                    'success' => true, 
                    'message' => "Período '{$period_data['period_name']}' eliminado exitosamente"
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el período']);
            }
        } else {
            throw new Exception("Error al ejecutar la consulta de eliminación: " . $delete_stmt->error);
        }

        $check_stmt->close();
        $students_stmt->close();
        $delete_stmt->close();

    } catch (Exception $e) {
        error_log("Error en delete_period.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>