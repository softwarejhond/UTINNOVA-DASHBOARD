<?php
include_once('../../controller/conexion.php');
session_start();

// Configurar zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit;
}

// Verificar si el usuario está autenticado
if (!isset($_SESSION['username'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No hay sesión activa'
    ]);
    exit;
}

// Recibir datos del formulario
$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['student_id']) ||
    !isset($data['bootcamp']) ||
    !isset($data['english']) ||
    !isset($data['english_code']) ||
    !isset($data['skills']) ||
    !isset($data['estado'])
) {

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Datos incompletos'
    ]);
    exit;
}

// Iniciar transacción
$conn->begin_transaction();

try {
    // Obtener fecha y hora actual de Colombia
    $fechaHoraColombia = date('Y-m-d H:i:s');

    // Actualizar el estado de admisión
    $updateSql = "UPDATE user_register SET statusAdmin = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);
    $stmt->bind_param('ss', $data['estado'], $data['student_id']);

    if (!$stmt->execute()) {
        throw new Exception("Error al actualizar el estado del estudiante");
    }

    // Registrar en historial de cambios
    $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
    $stmtHistorial = $conn->prepare($historialSql);
    $descripcion = "Se actualiza estado de admision a Beneficiario y se asignan cursos";
    $stmtHistorial->bind_param('sss', $data['student_id'], $_SESSION['username'], $descripcion);

    if (!$stmtHistorial->execute()) {
        throw new Exception("Error al registrar el historial");
    }

    // Verificar si ya existe una asignación previa
    $checkSql = "SELECT id FROM course_assignments WHERE student_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param('s', $data['student_id']);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        // Si existe, actualizar la asignación
        $updateAssignmentSql = "UPDATE course_assignments 
            SET bootcamp_id = ?, bootcamp_name = ?, 
                leveling_english_id = ?, leveling_english_name = ?,
                english_code_id = ?, english_code_name = ?,
                skills_id = ?, skills_name = ?,
                assigned_by = ?, assigned_date = ?
            WHERE student_id = ?";

        $assignmentStmt = $conn->prepare($updateAssignmentSql);
        $assignmentStmt->bind_param(
            'sssssssssss',
            $data['bootcamp']['id'],
            $data['bootcamp']['name'],
            $data['english']['id'],
            $data['english']['name'],
            $data['english_code']['id'],
            $data['english_code']['name'],
            $data['skills']['id'],
            $data['skills']['name'],
            $_SESSION['username'],
            $fechaHoraColombia,
            $data['student_id']
        );
    } else {
        // Si no existe, crear una nueva asignación
        $insertAssignmentSql = "INSERT INTO course_assignments 
            (student_id, bootcamp_id, bootcamp_name, leveling_english_id, leveling_english_name, 
             english_code_id, english_code_name, skills_id, skills_name, assigned_by, assigned_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $assignmentStmt = $conn->prepare($insertAssignmentSql);
        $assignmentStmt->bind_param(
            'sssssssssss',
            $data['student_id'],
            $data['bootcamp']['id'],
            $data['bootcamp']['name'],
            $data['english']['id'],
            $data['english']['name'],
            $data['english_code']['id'],
            $data['english_code']['name'],
            $data['skills']['id'],
            $data['skills']['name'],
            $_SESSION['username'],
            $fechaHoraColombia
        );
    }

    if (!$assignmentStmt->execute()) {
        throw new Exception("Error al guardar la asignación de cursos");
    }

    // 1. Obtener start_date del curso
    $bootcamp_id = $data['bootcamp']['id'];
    $stmtPeriod = $conn->prepare("SELECT start_date FROM course_periods WHERE bootcamp_code = ?");
    $stmtPeriod->bind_param('s', $bootcamp_id);
    $stmtPeriod->execute();
    $resultPeriod = $stmtPeriod->get_result();
    if ($resultPeriod->num_rows === 0) {
        throw new Exception("No se encontró periodo para el bootcamp seleccionado");
    }
    $periodData = $resultPeriod->fetch_assoc();
    $start_date = $periodData['start_date'];
    $stmtPeriod->close();

    // 2. Obtener creationDate del usuario
    $stmtUser = $conn->prepare("SELECT creationDate FROM user_register WHERE number_id = ?");
    $stmtUser->bind_param('s', $data['student_id']);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();
    if ($resultUser->num_rows === 0) {
        throw new Exception("No se encontró el usuario en user_register");
    }
    $userData = $resultUser->fetch_assoc();
    $creationDate = $userData['creationDate'];
    $stmtUser->close();

    // 3. Comparar fechas
    if (strtotime($creationDate) > strtotime($start_date)) {
        // Frenar el proceso y devolver advertencia
        $conn->rollback();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => "No se puede asignar el curso porque el usuario se registró después de la fecha de inicio del curso. Fecha inicio: $start_date, fecha registro: $creationDate"
        ]);
        exit;
    }

    // Confirmar la transacción
    $conn->commit();

    // Respuesta exitosa
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'message' => 'Datos guardados correctamente'
    ]);
} catch (Exception $e) {
    // Revertir en caso de error
    $conn->rollback();

    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
