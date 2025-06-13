<?php
require '../../controller/conexion.php'; // Asegúrate de incluir la conexión a la BD
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
        'type_id',
        'number_id',
        'full_name',
        'email',
        'institutional_email',
        'password',
        'department',
        'headquarters',
        'program',
        'mode',
        'id_bootcamp',
        'bootcamp_name',
        'id_leveling_english',
        'leveling_english_name',
        'id_english_code',
        'english_code_name',
        'id_skills',
        'skills_name',
        'cohort'
    ];

    foreach ($required as $field) {
        if (empty($input[$field]) && $field != 'cohort') {
            throw new Exception("Falta el campo requerido: $field");
        }
    }

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

    // Preparar la inserción
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

    // Agregar esta verificación antes del bind_param
    // Si la modalidad es Virtual, establecer headquarters como "No aplica"
    if ($input['mode'] === 'Virtual') {
        $input['headquarters'] = 'No aplica';
    }

    $stmt->bind_param(
        'sssssssssssssssssss',
        $input['type_id'],
        $input['number_id'],
        $input['full_name'],
        $input['email'],
        $input['institutional_email'],
        $input['password'],
        $input['department'],
        $input['headquarters'],  // Ahora usará "No aplica" si es modalidad Virtual
        $input['program'],
        $input['mode'],
        $input['id_bootcamp'],
        $input['bootcamp_name'],
        $input['id_leveling_english'],
        $input['leveling_english_name'],
        $input['id_english_code'],
        $input['english_code_name'],
        $input['id_skills'],
        $input['skills_name'],
        $cohort_number
    );

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar datos: " . $stmt->error);
    }

    // Crear usuario en Moodle
    $moodleAPI = new MoodleAPI();
    $nombres = array_slice(explode(' ', $input['full_name']), 0, 2); // Obtiene los dos primeros nombres
    $apellidos = array_slice(explode(' ', $input['full_name']), 2); // Obtiene los apellidos

    $moodleUser = [
        'username' => $input['number_id'],
        'password' => $input['password'],
        'firstname' => implode(' ', $nombres), // Concatena nombres con espacio
        'lastname' => implode(' ', $apellidos), // Concatena apellidos con espacio
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

    // **NUEVA FUNCIONALIDAD: Registrar en plataforma de empleos**
    try {
        // Obtener datos del usuario desde user_register para obtener todos los campos necesarios
        $userQuery = "SELECT * FROM user_register WHERE number_id = ?";
        $userStmt = $conn->prepare($userQuery);
        $userStmt->bind_param('s', $input['number_id']);
        $userStmt->execute();
        $userData = $userStmt->get_result()->fetch_assoc();
        
        if ($userData) {
            // Incluir la conexión a plataforma_empleos (ahora usa $connEmpleos)
            require_once '../../controller/conexion_empleos.php';
            
            // Verificar que la conexión de empleos esté disponible
            if (!$connEmpleos || $connEmpleos->connect_error) {
                throw new Exception("Error en la conexión a base de datos de empleos");
            }
            
            // Verificar si el usuario ya existe en plataforma_empleos
            $checkQuery = "SELECT id FROM usuarios WHERE numero_id = ? OR email = ?";
            $checkStmt = $connEmpleos->prepare($checkQuery);
            $checkStmt->bind_param('ss', $input['number_id'], $input['email']);
            $checkStmt->execute();
            
            if ($checkStmt->get_result()->num_rows == 0) {
                // Preparar datos para insertar
                $hashedPassword = password_hash($input['number_id'], PASSWORD_DEFAULT);
                
                // Limpiar y normalizar nombres (remover tildes y convertir a mayúsculas)
                $primer_nombre = strtoupper(str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'], ['A','E','I','O','U','N','A','E','I','O','U','N'], trim($userData['first_name'])));
                $segundo_nombre = strtoupper(str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'], ['A','E','I','O','U','N','A','E','I','O','U','N'], trim($userData['second_name'])));
                $primer_apellido = strtoupper(str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'], ['A','E','I','O','U','N','A','E','I','O','U','N'], trim($userData['first_last'])));
                $segundo_apellido = strtoupper(str_replace(['á','é','í','ó','ú','ñ','Á','É','Í','Ó','Ú','Ñ'], ['A','E','I','O','U','N','A','E','I','O','U','N'], trim($userData['second_last'])));
                
                $queryEmpleos = "INSERT INTO usuarios (
                    email,
                    numero_id,
                    password,
                    primer_nombre,
                    segundo_nombre,
                    primer_apellido,
                    segundo_apellido,
                    telefono,
                    tipo,
                    foto_perfil
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'candidato', 'default.jpg')";
                
                $stmtEmpleos = $connEmpleos->prepare($queryEmpleos);
                if (!$stmtEmpleos) {
                    error_log("Error preparando consulta empleos: " . $connEmpleos->error);
                } else {
                    $stmtEmpleos->bind_param(
                        'ssssssss',
                        $input['email'],
                        $input['number_id'],
                        $hashedPassword,
                        $primer_nombre,
                        $segundo_nombre,
                        $primer_apellido,
                        $segundo_apellido,
                        $userData['first_phone']
                    );
                    
                    if (!$stmtEmpleos->execute()) {
                        error_log("Error en INSERT a plataforma_empleos: " . $stmtEmpleos->error);
                        error_log("Datos enviados: " . json_encode([
                            'email' => $input['email'],
                            'numero_id' => $input['number_id'],
                            'primer_nombre' => $primer_nombre,
                            'segundo_nombre' => $segundo_nombre,
                            'primer_apellido' => $primer_apellido,
                            'segundo_apellido' => $segundo_apellido,
                            'telefono' => $userData['first_phone']
                        ]));
                    } else {
                        error_log("Usuario registrado exitosamente en plataforma de empleos: " . $input['number_id']);
                    }
                    
                    $stmtEmpleos->close();
                }
            } else {
                error_log("Usuario ya existe en plataforma de empleos: " . $input['number_id']);
            }
            
            $checkStmt->close();
            $connEmpleos->close();
        } else {
            error_log("No se encontraron datos del usuario en user_register: " . $input['number_id']);
        }
        
        $userStmt->close();
    } catch (Exception $empleosError) {
        // Log del error pero no interrumpir el proceso principal
        error_log("Error al registrar en plataforma de empleos: " . $empleosError->getMessage());
    }

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

    error_log("Error en enroll_multiple_user.php: " . $e->getMessage());

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
} finally {
    // Cerrar conexiones
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
