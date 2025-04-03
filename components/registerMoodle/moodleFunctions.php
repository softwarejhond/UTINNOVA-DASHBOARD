<?php
class MoodleAPI
{
    private $api_url;
    private $token;
    private $format;

    public function __construct()
    {
        $this->api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
        $this->token = "3f158134506350615397c83d861c2104";
        $this->format = "json";
    }

    private function callAPI($function, $params = [])
    {
        $params['wstoken'] = $this->token;
        $params['wsfunction'] = $function;
        $params['moodlewsrestformat'] = $this->format;

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->api_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params),
            CURLOPT_SSL_VERIFYPEER => false
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new Exception("Error en la llamada API: " . $error);
        }

        return json_decode($response, true);
    }

    public function createUser($userData)
    {
        $params = [
            'users[0][username]' => $userData['username'],
            'users[0][password]' => $userData['password'],
            'users[0][firstname]' => $userData['firstname'] . ' ' . ($userData['secondname'] ?? ''),
            'users[0][lastname]' => $userData['lastname'] . ' ' . ($userData['secondlastname'] ?? ''),
            'users[0][email]' => $userData['email'],
            'users[0][auth]' => 'manual',
            // Forzar cambio de contraseña en el primer login
            'users[0][preferences][0][type]' => 'auth_forcepasswordchange',
            'users[0][preferences][0][value]' => 1
        ];

        return $this->callAPI('core_user_create_users', $params);
    }

    public function enrollUserInCourses($userId, $courses)
    {
        $results = [];
        $errors = [];
        
        if (empty($userId) || !is_array($courses) || empty($courses)) {
            throw new Exception("ID de usuario o cursos inválidos");
        }
        
        foreach ($courses as $courseId) {
            if (empty($courseId)) {
                continue; // Saltamos cursos con ID vacío
            }
            
            try {
                $params = [
                    'enrolments[0][roleid]' => 5, // 5 = estudiante
                    'enrolments[0][userid]' => $userId,
                    'enrolments[0][courseid]' => $courseId
                ];

                $result = $this->callAPI('enrol_manual_enrol_users', $params);
                
                // Verificar si hay un error en la respuesta
                if (isset($result['exception'])) {
                    // Si el error es que el usuario ya está inscrito, lo consideramos exitoso
                    if (strpos($result['message'], 'already enrolled') !== false) {
                        $results[] = ['status' => 'already_enrolled', 'courseId' => $courseId];
                        continue;
                    }
                    
                    $errors[] = "Error en el curso {$courseId}: {$result['message']}";
                    continue;
                }
                
                $results[] = $result;
            } catch (Exception $e) {
                $errors[] = "Error en el curso {$courseId}: {$e->getMessage()}";
            }
        }
        
        // Si hubo errores pero también éxitos, consideramos la operación exitosa parcialmente
        if (!empty($results) && !empty($errors)) {
            error_log("Matriculación parcial: " . implode(", ", $errors));
            return $results;
        }
        
        // Si solo hubo errores, lanzamos excepción
        if (empty($results) && !empty($errors)) {
            throw new Exception("Error al matricular en cursos: " . implode(", ", $errors));
        }
        
        return $results;
    }
    
    /**
     * Actualiza el statusAdmin del usuario en la base de datos
     * 
     * @param string $numberId Número de identificación del usuario
     * @param mysqli $conn Conexión a la base de datos
     * @param string $status Estado a establecer (por defecto '3')
     * @return bool True si la actualización fue exitosa
     * @throws Exception Si hay algún error durante la actualización
     */
    public function updateUserStatus($numberId, $conn, $status = '3')
    {
        // Verificar que tengamos una conexión válida
        if (!$conn || $conn->connect_error) {
            throw new Exception("Error de conexión a la base de datos: " . 
                ($conn ? $conn->connect_error : "Conexión no disponible"));
        }
        
        // Verificar que el numberId sea válido
        if (empty($numberId)) {
            throw new Exception("El número de identificación no puede estar vacío");
        }
        
        try {
            $updateSql = "UPDATE user_register SET statusAdmin = ? WHERE number_id = ?";
            $updateStmt = $conn->prepare($updateSql);
            
            if (!$updateStmt) {
                throw new Exception("Error al preparar la actualización de estado: " . $conn->error);
            }

            $updateStmt->bind_param('ss', $status, $numberId);
            
            if (!$updateStmt->execute()) {
                throw new Exception("Error al actualizar statusAdmin: " . $updateStmt->error);
            }
            
            // Verificar que se haya actualizado al menos una fila
            if ($updateStmt->affected_rows === 0) {
                throw new Exception("No se encontró ningún usuario con el número de identificación proporcionado: {$numberId}");
            }
            
            $updateStmt->close();
            return true;
        } catch (Exception $e) {
            throw new Exception("Error al actualizar el estado del usuario {$numberId}: " . $e->getMessage());
        }
    }
}
