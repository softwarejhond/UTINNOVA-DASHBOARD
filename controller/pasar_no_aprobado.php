<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
require_once '../controller/conexion.php';

// Iniciar sesión para obtener el usuario
session_start();

$data = json_decode(file_get_contents('php://input'), true);

if (
    !isset($data['documentos']) || !is_array($data['documentos']) ||
    !isset($data['estado_final']) || !is_numeric($data['estado_final'])
) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$documentos = array_map('trim', $data['documentos']);
$documentos = array_filter($documentos, function($d) { return $d !== ''; });
$estadoFinal = (int)$data['estado_final'];

// Validar que el estado sea uno de los permitidos
$estadosPermitidos = [7 => 'Inactivo', 11 => 'No válido', 12 => 'No aprobado'];
if (!array_key_exists($estadoFinal, $estadosPermitidos)) {
    echo json_encode(['success' => false, 'message' => 'Estado no válido']);
    exit;
}

if (count($documentos) === 0) {
    echo json_encode(['success' => false, 'message' => 'No hay documentos']);
    exit;
}

// Iniciar transacción
$conn->autocommit(false);

try {
    $username = $_SESSION['username'] ?? 'Sistema';
    $nombreEstado = $estadosPermitidos[$estadoFinal];
    
    // Arrays para clasificar documentos
    $documentosMatriculados = [];
    $documentosNoMatriculados = [];
    $resultadosProcesamiento = [];
    
    // Verificar qué documentos están matriculados
    foreach ($documentos as $numero_id) {
        $verificarMatriculaStmt = $conn->prepare("SELECT number_id FROM groups WHERE number_id = ?");
        $verificarMatriculaStmt->bind_param("s", $numero_id);
        $verificarMatriculaStmt->execute();
        $result = $verificarMatriculaStmt->get_result();
        
        if ($result->num_rows > 0) {
            $documentosMatriculados[] = $numero_id;
        } else {
            $documentosNoMatriculados[] = $numero_id;
        }
        $verificarMatriculaStmt->close();
    }
    
    // Procesar documentos NO matriculados (cambio directo a statusAdmin = estado seleccionado)
    if (!empty($documentosNoMatriculados)) {
        $placeholders = implode(',', array_fill(0, count($documentosNoMatriculados), '?'));
        $sql = "UPDATE user_register SET statusAdmin = ? WHERE number_id IN ($placeholders)";

        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            throw new Exception('Error en la preparación de la consulta para no matriculados');
        }

        // Preparar parámetros para bind_param
        $params = array_merge([$estadoFinal], $documentosNoMatriculados);
        $types = str_repeat('s', count($documentosNoMatriculados));
        $types = 'i' . $types; // 'i' para el estado (integer) + 's' para cada documento (string)
        
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->close();

        // Registrar en historial para no matriculados
        $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
        $historialStmt = $conn->prepare($historialSql);
        
        if ($historialStmt === false) {
            throw new Exception('Error en la preparación de la consulta de historial');
        }

        $descripcionNoMatriculados = "Cambio a '$nombreEstado' - Sin matrícula (Multiple)";
        foreach ($documentosNoMatriculados as $cedula) {
            $historialStmt->bind_param('iss', $cedula, $username, $descripcionNoMatriculados);
            $historialStmt->execute();
            $resultadosProcesamiento[] = [
                'numero_id' => $cedula,
                'accion' => 'cambio_directo',
                'estado' => 'completado',
                'estado_final' => $nombreEstado
            ];
        }
        $historialStmt->close();
    }
    
    // Procesar documentos MATRICULADOS (proceso de desmatrícula + cambio de estado)
    if (!empty($documentosMatriculados)) {
        foreach ($documentosMatriculados as $numero_id) {
            try {
                // Incluir la lógica de deleteMatricula
                $resultado = procesarDesmatricula($numero_id, $conn, $username, true, $estadoFinal);
                
                if ($resultado['success']) {
                    // Después de la desmatrícula exitosa, cambiar estado al seleccionado
                    $updateStmt = $conn->prepare("UPDATE user_register SET statusAdmin = ? WHERE number_id = ?");
                    $updateStmt->bind_param("is", $estadoFinal, $numero_id);
                    $updateStmt->execute();
                    $updateStmt->close();
                    
                    // Registrar en historial el cambio adicional
                    $historialStmt = $conn->prepare("INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)");
                    $descripcionMatriculado = "Desmatrícula + Cambio a '$nombreEstado' (Multiple)";
                    $historialStmt->bind_param('iss', $numero_id, $username, $descripcionMatriculado);
                    $historialStmt->execute();
                    $historialStmt->close();
                    
                    $resultadosProcesamiento[] = [
                        'numero_id' => $numero_id,
                        'accion' => 'desmatricula_completa',
                        'estado' => 'completado',
                        'estado_final' => $nombreEstado
                    ];
                } else {
                    $resultadosProcesamiento[] = [
                        'numero_id' => $numero_id,
                        'accion' => 'desmatricula_fallida',
                        'estado' => 'error',
                        'mensaje' => $resultado['message']
                    ];
                }
            } catch (Exception $e) {
                $resultadosProcesamiento[] = [
                    'numero_id' => $numero_id,
                    'accion' => 'desmatricula_fallida',
                    'estado' => 'error',
                    'mensaje' => $e->getMessage()
                ];
            }
        }
    }

    // Confirmar la transacción
    $conn->commit();
    $conn->autocommit(true);

    echo json_encode([
        'success' => true,
        'estado_aplicado' => $nombreEstado,
        'estado_codigo' => $estadoFinal,
        'matriculados' => count($documentosMatriculados),
        'no_matriculados' => count($documentosNoMatriculados),
        'procesamiento' => $resultadosProcesamiento
    ]);

} catch (Exception $e) {
    // Revertir en caso de error
    $conn->rollback();
    $conn->autocommit(true);
    
    error_log("Error en pasar_no_aprobado.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno del servidor: ' . $e->getMessage()]);
}

/**
 * Función para procesar la desmatrícula de un usuario
 * Basada en la lógica de deleteMatricula.php
 */
function procesarDesmatricula($number_id, $conn, $username, $isMultiple = false, $estadoFinal = 1) {
    try {
        // 1. Obtener información completa del usuario desde la tabla groups
        $stmt = $conn->prepare("SELECT 
            type_id, number_id, full_name, email, institutional_email, 
            department, headquarters, program, mode,
            id_bootcamp, bootcamp_name,
            id_leveling_english, leveling_english_name,
            id_english_code, english_code_name,
            id_skills, skills_name,
            creation_date
            FROM groups WHERE number_id = ?");
        $stmt->bind_param("s", $number_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['success' => false, 'message' => 'Usuario no encontrado en groups'];
        }

        $userInfo = $result->fetch_assoc();
        $institutional_email = $userInfo['institutional_email'];
        $stmt->close();

        // 2. Obtener el usuario ID de Moodle mediante la API
        $apiUrl = "https://talento-tech.uttalento.co/webservice/rest/server.php";
        $token = "3f158134506350615397c83d861c2104";

        $params = [
            'wstoken' => $token,
            'wsfunction' => 'core_user_get_users_by_field',
            'moodlewsrestformat' => 'json',
            'field' => 'email',
            'values[0]' => $institutional_email
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);

        $response = curl_exec($ch);

        if (curl_error($ch)) {
            curl_close($ch);
            return ['success' => false, 'message' => 'Error al comunicarse con Moodle: ' . curl_error($ch)];
        }

        $userData = json_decode($response, true);

        if (empty($userData) || !isset($userData[0]['id'])) {
            curl_close($ch);
            return ['success' => false, 'message' => 'Usuario no encontrado en Moodle'];
        }

        $moodleUserId = $userData[0]['id'];

        // 3. Eliminar usuario de Moodle
        $deleteParams = [
            'wstoken' => $token,
            'wsfunction' => 'core_user_delete_users',
            'moodlewsrestformat' => 'json',
            'userids[0]' => $moodleUserId
        ];

        curl_setopt_array($ch, [
            CURLOPT_URL => $apiUrl,
            CURLOPT_POSTFIELDS => http_build_query($deleteParams)
        ]);

        $deleteResponse = curl_exec($ch);

        if (curl_error($ch)) {
            curl_close($ch);
            return ['success' => false, 'message' => 'Error al eliminar usuario de Moodle'];
        }
        curl_close($ch);

        // 4. Actualizar registros en la base de datos local
        date_default_timezone_set('America/Bogota');
        $unenrollment_date = date('Y-m-d H:i:s');

        // 4.1 Guardar el historial de matrícula
        $historyStmt = $conn->prepare("INSERT INTO enrollment_history (
            type_id, number_id, full_name, email, institutional_email,
            department, headquarters, program, mode,
            id_bootcamp, bootcamp_name,
            id_leveling_english, leveling_english_name,
            id_english_code, english_code_name,
            id_skills, skills_name,
            enrollment_date, unenrollment_date, unenrolled_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $historyStmt->bind_param("sisssssssisisissssss",
            $userInfo['type_id'],
            $userInfo['number_id'],
            $userInfo['full_name'],
            $userInfo['email'],
            $userInfo['institutional_email'],
            $userInfo['department'],
            $userInfo['headquarters'],
            $userInfo['program'],
            $userInfo['mode'],
            $userInfo['id_bootcamp'],
            $userInfo['bootcamp_name'],
            $userInfo['id_leveling_english'],
            $userInfo['leveling_english_name'],
            $userInfo['id_english_code'],
            $userInfo['english_code_name'],
            $userInfo['id_skills'],
            $userInfo['skills_name'],
            $userInfo['creation_date'],
            $unenrollment_date,
            $username
        );

        if (!$historyStmt->execute()) {
            throw new Exception("Error al guardar el historial de matrícula");
        }
        $historyStmt->close();

        // 4.2 Eliminar registros de asistencia
        if (!empty($userInfo['id_bootcamp'])) {
            $deleteAttendanceStmt = $conn->prepare("DELETE FROM attendance_records WHERE student_id = ? AND course_id = ?");
            $deleteAttendanceStmt->bind_param("ss", $number_id, $userInfo['id_bootcamp']);
            $deleteAttendanceStmt->execute();
            $deleteAttendanceStmt->close();
        }

        // 4.3 Eliminar de la tabla groups
        $deleteStmt = $conn->prepare("DELETE FROM groups WHERE number_id = ?");
        $deleteStmt->bind_param("s", $number_id);
        $deleteStmt->execute();
        $deleteStmt->close();

        // 4.4 Actualizar statusAdmin al estado seleccionado en la tabla user_register
        $updateStmt = $conn->prepare("UPDATE user_register SET statusAdmin = ? WHERE number_id = ?");
        $updateStmt->bind_param("is", $estadoFinal, $number_id);
        $updateStmt->execute();
        $updateStmt->close();

        // 4.5 Registrar en el historial de cambios la desmatrícula
        $historialSql = "INSERT INTO change_history (student_id, user_change, change_made) VALUES (?, ?, ?)";
        $stmtHistorial = $conn->prepare($historialSql);
        
        $descripcion = $isMultiple 
            ? "Se elimina matrícula de Moodle (Desmatrícula múltiple + cambio de estado)"
            : "Se elimina matrícula de Moodle";
        
        $stmtHistorial->bind_param('iss', $number_id, $username, $descripcion);
        
        if (!$stmtHistorial->execute()) {
            throw new Exception("Error al registrar el historial de desmatrícula");
        }
        $stmtHistorial->close();

        return [
            'success' => true,
            'message' => 'Desmatrícula completada exitosamente',
            'moodle_user_id' => $moodleUserId
        ];

    } catch (Exception $e) {
        return ['success' => false, 'message' => $e->getMessage()];
    }
}
?>