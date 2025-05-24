<?php
session_start();
require __DIR__ . '../../../controller/conexion.php';
require_once 'moodleFunctions.php';

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0); // Cambiado a 0 para evitar output no JSON

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

    $moodleAPI = new MoodleAPI();
    $messages = [];
    $errors = [];

    // Iniciar transacción
    $conn->begin_transaction();

    // Procesar cada curso
    $courses = ['bootcamp', 'ingles', 'englishCode', 'habilidades'];
    
    foreach ($courses as $courseType) {
        if (empty($input[$courseType]['course'])) {
            continue; // Saltar si no hay curso seleccionado
        }

        $courseId = $input[$courseType]['course'];
        $courseName = $input[$courseType]['course_name'];
        $teacher = $input[$courseType]['profesor'];
        $mentor = $input[$courseType]['mentor'];
        $monitor = $input[$courseType]['monitor'];

        try {
            // Verificar si ya existe el registro para este curso
            $checkSql = "SELECT id FROM team_assignments WHERE code = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param('i', $courseId);
            $checkStmt->execute();
            $result = $checkStmt->get_result();

            $currentUser = isset($_SESSION['username']) ? $_SESSION['username'] : 'admin';

            if ($result->num_rows > 0) {
                // Actualizar registro existente
                $updateSql = "UPDATE team_assignments SET course_name = ?, teacher = ?, mentor = ?, monitor = ?, created_by = ? WHERE code = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param('sssssi', $courseName, $teacher, $mentor, $monitor, $currentUser, $courseId);
                $updateStmt->execute();
                $messages[] = "Actualizado: {$courseName}";
            } else {
                // Insertar nuevo registro
                $insertSql = "INSERT INTO team_assignments (code, course_name, teacher, mentor, monitor, created_by) VALUES (?, ?, ?, ?, ?, ?)";
                $insertStmt = $conn->prepare($insertSql);
                $insertStmt->bind_param('isssss', $courseId, $courseName, $teacher, $mentor, $monitor, $currentUser);
                $insertStmt->execute();
                $messages[] = "Creado: {$courseName}";
            }

            // Procesar registro en Moodle para cada tipo de personal
            $staffData = [
                'teacher' => ['username' => $teacher, 'roleid' => 3],
                'mentor' => ['username' => $mentor, 'roleid' => 3],
                'monitor' => ['username' => $monitor, 'roleid' => 3]
            ];

            foreach ($staffData as $staffType => $staffInfo) {
                if (empty($staffInfo['username'])) {
                    continue;
                }

                try {
                    // Obtener datos del usuario desde la base de datos
                    $userData = getUserData($conn, $staffInfo['username']);
                    if (!$userData) {
                        $errors[] = "No se encontraron datos para {$staffType}: {$staffInfo['username']} en el curso {$courseName}";
                        continue;
                    }

                    // Verificar si el usuario ya existe en Moodle
                    $moodleUserId = getUserByUsername($moodleAPI, $staffInfo['username']);
                    
                    if (!$moodleUserId) {
                        // Crear usuario en Moodle si no existe
                        $moodleUserId = createMoodleUser($moodleAPI, $userData);
                        $messages[] = "Usuario creado en Moodle: {$userData['nombre']} ({$staffInfo['username']})";
                    }

                    // Asignar como profesor al curso
                    $assignResult = $moodleAPI->assignTeacherToCourse($moodleUserId, $courseId);
                    if (isset($assignResult['status']) && $assignResult['status'] === 'already_enrolled') {
                        $messages[] = "{$userData['nombre']} ya estaba asignado como profesor en {$courseName}";
                    } else {
                        $messages[] = "{$userData['nombre']} asignado como profesor en {$courseName}";
                    }

                } catch (Exception $e) {
                    $errors[] = "Error procesando {$staffType} {$staffInfo['username']} en {$courseName}: " . $e->getMessage();
                }
            }

        } catch (Exception $e) {
            $errors[] = "Error procesando curso {$courseName}: " . $e->getMessage();
        }
    }

    // Confirmar transacción si no hay errores críticos
    if (empty($errors) || count($messages) > count($errors)) {
        $conn->commit();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'messages' => array_merge($messages, $errors),
            'summary' => [
                'successful' => count($messages),
                'errors' => count($errors)
            ]
        ]);
    } else {
        $conn->rollback();
        
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'messages' => array_merge(['Transacción revertida por errores críticos'], $errors)
        ]);
    }

} catch (Exception $e) {
    if (isset($conn) && !$conn->connect_error) {
        $conn->rollback();
    }
    
    error_log("Error en save_team.php: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'messages' => ['Error general: ' . $e->getMessage()]
    ]);
} finally {
    if (isset($conn)) $conn->close();
}

/**
 * Obtener datos del usuario desde la base de datos
 */
function getUserData($conn, $username) {
    $sql = "SELECT username, nombre, email FROM users WHERE username = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();
    }
    
    return null;
}

/**
 * Verificar si un usuario ya existe en Moodle por username
 */
function getUserByUsername($moodleAPI, $username) {
    try {
        $params = [
            'criteria[0][key]' => 'username',
            'criteria[0][value]' => $username
        ];
        
        $response = $moodleAPI->callAPI('core_user_get_users', $params);
        
        if (isset($response['users']) && count($response['users']) > 0) {
            return $response['users'][0]['id'];
        }
        
        return null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Crear usuario en Moodle
 */
function createMoodleUser($moodleAPI, $userData) {
    // Separar nombres y apellidos
    $nombreCompleto = $userData['nombre'];
    $partesNombre = explode(' ', trim($nombreCompleto));
    
    // Tomar primeras dos partes como nombres, resto como apellidos
    $firstname = implode(' ', array_slice($partesNombre, 0, 2));
    $lastname = implode(' ', array_slice($partesNombre, 2));
    
    // Si no hay apellidos, usar un apellido por defecto
    if (empty($lastname)) {
        $lastname = 'Usuario';
    }

    $moodleUserData = [
        'username' => $userData['username'],
        'password' => 'ut@2025!',
        'firstname' => $firstname,
        'lastname' => $lastname,
        'email' => $userData['email']
    ];

    $createResponse = $moodleAPI->createUser($moodleUserData);
    
    if (!isset($createResponse[0]['id'])) {
        throw new Exception("Error creando usuario en Moodle: " . json_encode($createResponse));
    }

    return $createResponse[0]['id'];
}
?>