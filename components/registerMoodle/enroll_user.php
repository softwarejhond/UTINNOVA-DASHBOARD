<?php
require  '../../controller/conexion.php';// Asegúrate de incluir la conexión a la BD
require_once 'moodleFunctions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Obtener cohorte activo
$sqlcohort = "SELECT * FROM cohorts WHERE state = 1 LIMIT 1";
$resultcohort = $conn->query($sqlcohort);
$cohort = $resultcohort->fetch_assoc();

// Agregamos esto para tener el dato disponible en JavaScript
$cohort_number = $cohort['cohort_number'];


try {
    // Verificar conexión
    if (!$conn || $conn->connect_error) {
        throw new Exception("Error de conexión: " . ($conn ? $conn->connect_error : "No se pudo establecer la conexión"));
    }

    // Obtener y validar datos JSON
    $input = json_decode(file_get_contents('php://input'), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    // Validar campos requeridos
    $required = [
        'type_id', 'number_id', 'full_name', 'email', 'institutional_email', 'password',
        'department', 'headquarters', 'program', 'mode',
        'id_bootcamp', 'bootcamp_name', 'id_leveling_english', 'leveling_english_name',
        'id_english_code', 'english_code_name', 'id_skills', 'skills_name', 'cohort'
    ];

    

    // Iniciar transacción
    $conn->begin_transaction();

    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id FROM groups WHERE email = ? OR institutional_email = ?");
    if (!$stmt) {
        throw new Exception("Error en la preparación de la consulta: " . $conn->error);
    }

    $stmt->bind_param('ss', $input['email'], $input['institutional_email']);
    if (!$stmt->execute()) {
        throw new Exception("Error al ejecutar la consulta: " . $stmt->error);
    }

    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception('El correo ya está registrado');
    }

    // Preparar la inserción con campo de cohorte
    $sql = "INSERT INTO groups (
        type_id, number_id, full_name, email, institutional_email, password,
        department, headquarters, program, mode,
        id_bootcamp, bootcamp_name, id_leveling_english, leveling_english_name,
        id_english_code, english_code_name, id_skills, skills_name, cohort
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Error en la preparación de la inserción: " . $conn->error);
    }

    $stmt->bind_param(
        'sssssssssssssssssss',
        $input['type_id'], $input['number_id'], $input['full_name'],
        $input['email'], $input['institutional_email'], $input['password'],
        $input['department'], $input['headquarters'], $input['program'],
        $input['mode'], $input['id_bootcamp'], $input['bootcamp_name'],
        $input['id_leveling_english'], $input['leveling_english_name'],
        $input['id_english_code'], $input['english_code_name'],
        $input['id_skills'], $input['skills_name'], $cohort_number
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar datos: " . $stmt->error);
    }

    // Crear usuario en Moodle
    $moodleAPI = new MoodleAPI();
    $moodleUser = [
        'username' => $input['number_id'],
        'password' => $input['password'],
        'firstname' => explode(' ', $input['full_name'])[0],
        'lastname' => explode(' ', $input['full_name'])[2],
        'email' => $input['institutional_email']
    ];

    $createResponse = $moodleAPI->createUser($moodleUser);

    if (!isset($createResponse[0]['id'])) {
        throw new Exception("Error creando usuario en Moodle");
    }

    $moodleUserId = $createResponse[0]['id'];

    // Matricular en los cursos seleccionados
    $courses = [
        $input['id_bootcamp'],
        $input['id_leveling_english'],
        $input['id_english_code'],
        $input['id_skills']
    ];

    $enrollResponse = $moodleAPI->enrollUserInCourses($moodleUserId, $courses);

    // Actualizar statusAdmin
    $moodleAPI->updateUserStatus($input['number_id'], $conn);

    // Confirmar transacción
    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Usuario matriculado exitosamente en Moodle',
        'moodle_user_id' => $moodleUserId
    ]);

} catch (Exception $e) {
    // Revertir cambios si hay error
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }

    error_log("Error en enroll_user.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    // Cerrar conexiones
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($conn)) $conn->close();
}

