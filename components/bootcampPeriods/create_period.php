<?php
session_start();
include '../../controller/conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Obtener datos del formulario
        $period_name = trim($_POST['period_name'] ?? '');
        $cohort = intval($_POST['cohort'] ?? 0);
        $start_date = $_POST['start_date'] ?? '';
        $end_date = $_POST['end_date'] ?? '';
        $created_by = $_SESSION['username'] ?? ''; // Usar el username de la sesión
        $status = intval($_POST['status'] ?? 1);
        
        // Cursos (opcionales)
        $bootcamp_code = trim($_POST['bootcamp_code'] ?? '');
        $bootcamp_name = trim($_POST['bootcamp_name'] ?? '');
        $leveling_english_code = trim($_POST['leveling_english_code'] ?? '');
        $leveling_english_name = trim($_POST['leveling_english_name'] ?? '');
        $english_code_code = trim($_POST['english_code_code'] ?? '');
        $english_code_name = trim($_POST['english_code_name'] ?? '');
        $skills_code = trim($_POST['skills_code'] ?? '');
        $skills_name = trim($_POST['skills_name'] ?? '');

        // Validar campos obligatorios
        if (empty($period_name) || $cohort <= 0 || empty($start_date) || empty($end_date) || empty($created_by)) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben ser completados']);
            exit;
        }

        // Validar fechas
        if (strtotime($end_date) <= strtotime($start_date)) {
            echo json_encode(['success' => false, 'message' => 'La fecha de fin debe ser posterior a la fecha de inicio']);
            exit;
        }

        // Verificar si ya existe un período con el mismo nombre y cohorte
        $check_sql = "SELECT id FROM course_periods WHERE period_name = ? AND cohort = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $period_name, $cohort);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un período con ese nombre y cohorte']);
            exit;
        }

        // Preparar la consulta de inserción
        $sql = "INSERT INTO course_periods (
            period_name, cohort, start_date, end_date, created_by, status,
            bootcamp_code, bootcamp_name, 
            leveling_english_code, leveling_english_name,
            english_code_code, english_code_name,
            skills_code, skills_name
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Error en la preparación de la consulta: " . $conn->error);
        }

        // Convertir campos vacíos a NULL
        $bootcamp_code = $bootcamp_code ?: null;
        $bootcamp_name = $bootcamp_name ?: null;
        $leveling_english_code = $leveling_english_code ?: null;
        $leveling_english_name = $leveling_english_name ?: null;
        $english_code_code = $english_code_code ?: null;
        $english_code_name = $english_code_name ?: null;
        $skills_code = $skills_code ?: null;
        $skills_name = $skills_name ?: null;

        $stmt->bind_param("sisssissssssss", 
            $period_name, $cohort, $start_date, $end_date, $created_by, $status,
            $bootcamp_code, $bootcamp_name,
            $leveling_english_code, $leveling_english_name,
            $english_code_code, $english_code_name,
            $skills_code, $skills_name
        );

        if ($stmt->execute()) {
            echo json_encode([
                'success' => true, 
                'message' => 'Período creado exitosamente',
                'period_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
        }

        $stmt->close();
        $check_stmt->close();

    } catch (Exception $e) {
        error_log("Error en create_period.php: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
}

$conn->close();
?>