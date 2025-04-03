<?php
// obtener_total_estudiantes_moodle.php

// URL de la API de Moodle y tu token
$apiUrl = 'https://tu-dominio-moodle.com/webservice/rest/server.php';
$token = '3f158134506350615397c83d861c2104';

function obtenerTotalEstudiantesMoodle($apiUrl, $token)
{
    // Parámetros de la API para obtener usuarios
    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_user_get_users',
        'moodlewsrestformat' => 'json',
        'criteria[0][key]' => 'profile_field_tipo_usuario', 
        'criteria[0][value]' => 'Estudiante'
    ];

    // Construir la URL de la solicitud
    $url = $apiUrl . '?' . http_build_query($params);

    // Realizar la solicitud
    $response = file_get_contents($url);

    // Comprobar si hubo un error en la solicitud
    if ($response === false) {
        die('Error en la solicitud a la API de Moodle');
    }

    // Decodificar la respuesta JSON
    $data = json_decode($response, true);

    // Verificar si la respuesta contiene usuarios
    if (isset($data['users']) && is_array($data['users'])) {
        $totalEstudiantes = count($data['users']);
    } else {
        $totalEstudiantes = 0;
    }

    // Retornar los datos como JSON
    echo json_encode(['total_estudiantes' => $totalEstudiantes]);
}

// Llamar a la función
obtenerTotalEstudiantesMoodle($apiUrl, $token);
?>
