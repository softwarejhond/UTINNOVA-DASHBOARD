<?php
// Ejemplo: Obtener información de un curso específico por su courseId desde la API de Moodle

// Configuración de la API de Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// ID del curso que quieres consultar
$courseId = 439; // Cambia este valor por el courseId deseado

// Función para llamar a la API de Moodle
function callMoodleAPI($function, $params = []) {
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Llamar a la función para obtener el curso por ID
$params = ['options[ids][0]' => $courseId];
$courseInfo = callMoodleAPI('core_course_get_courses', $params);

// Mostrar información del curso
if (!empty($courseInfo) && isset($courseInfo[0])) {
    $curso = $courseInfo[0];
    echo "ID: " . $curso['id'] . "<br>";
    echo "Nombre: " . $curso['fullname'] . "<br>";
    echo "Categoría: " . $curso['categoryid'] . "<br>";
    echo "Shortname: " . $curso['shortname'] . "<br>";
    // Puedes mostrar más campos según lo que devuelva la API
} else {
    echo "No se encontró información para el courseId $courseId";
}