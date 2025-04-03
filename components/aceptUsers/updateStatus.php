<?php
// Habilitar reporte de errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Asegurarse de que la conexión a la base de datos esté configurada
if (!isset($conn) || !$conn) {
    die('Error: La conexión a la base de datos no está configurada.');
}

// Inicializar la variable de mensaje
$mensajeToast = '';

// Actualizar estado de la propiedad si se envió el formulario
if (isset($_POST['btnActualizarEstado'])) {
    $codigo = $_POST['codigo']; // Obtener el código de la propiedad desde el formulario
    $nuevoEstado = $_POST['nuevoEstado']; // Obtener el nuevo estado
    
    // Consulta SQL para actualizar el estado
    $updateSql = "UPDATE user_register SET status = ? WHERE number_id = ?";
    $stmt = $conn->prepare($updateSql);

    // Usar bind_param correctamente con tipos: 's' para string y 'i' para entero
    $stmt->bind_param('si', $nuevoEstado, $codigo); // Preparar la consulta para ejecutar
    
    if ($stmt->execute()) {
        $mensajeToast = 'Estado actualizado correctamente.';
    } else {
        $mensajeToast = 'Error al actualizar el estado.';
    }
}

// Consulta SQL para obtener los datos
$sql = "SELECT user_register.number_id, user_register.first_name, user_register.second_name, user_register.first_last,
               user_register.second_last, user_register.first_phone, user_register.second_phone,user_register.password, user_register.email,
               municipios.municipio, user_register.address, user_register.status, program
        FROM user_register
        INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
        WHERE user_register.status = '1' 
        ORDER BY user_register.first_name ASC";

$result = $conn->query($sql);

// Si la consulta tiene resultados, generar los datos
$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Agregar acciones a cada fila
        $row['acciones'] = '
        <td><button class="btn bg-lime-dark btn-sm"><i class="bi bi-eye-fill"></i></button></td>
        <td>
            <form method="POST" class="d-inline" onsubmit="return confirmarActualizacion();">
                <input type="hidden" name="codigo" value="' . htmlspecialchars($row["number_id"]) . '">
                <div class="input-group">
                    <select class="form-control" name="nuevoEstado" required>
                        <option value="1" ' . ($row["status"] == 'NUEVO' ? 'selected' : '') . '>NUEVO</option>
                        <option value="2" ' . ($row["status"] == 'ACEPTADO' ? 'selected' : '') . '>ACEPTADO</option>
                        <option value="3" ' . ($row["status"] == 'DENEGADO' ? 'selected' : '') . '>DENEGADO</option>
                    </select>
                    <div class="input-group-append">
                        <button type="submit" name="btnActualizarEstado" class="btn bg-indigo-dark text-white btn-sm">
                            <i class="bi bi-pencil-fill"></i>
                        </button>
                    </div>
                </div>
            </form>
        </td>';

        // Concatenar el nombre y el apellido
        $fullName = $row['first_name'] . ' ' . $row['second_name'] . ' ' . $row['first_last'] . ' ' . $row['second_last'];

        // Llamar a la función para crear el usuario en Moodle con los datos de la base de datos
        $token = '3f158134506350615397c83d861c2104'; // Token de autenticación
        $username = $row['number_id'];
        $password = $row['password']; // Contraseña por defecto (esto debería cambiarse en un sistema real)
        $name = ucwords($row['first_name'].' '.$row['second_name']);
        $last = ucwords($row['first_last'].' '.$row['second_last']);        
        $email = $row['email'];

        // Crear el usuario en Moodle
        $response = crearUsuarioMoodle($token, $username, $password, $name, $last, $email);
        // Puede agregar lógica para manejar la respuesta de la API aquí si es necesario

        // Guardar fila de datos
        $data[] = $row;
    }
} else {
    // Si no hay datos, mostrar mensaje
    echo '<div class="alert alert-info">No hay datos disponibles.</div>';
}

// Función para enviar solicitud a Moodle
function crearUsuarioMoodle($token, $username, $password, $firstname, $lastname, $email) {
    // Configuración de la URL y parámetros de la API
    $apiUrl = 'https://talento-tech.uttalento.co/webservice/rest/server.php';
    $function = 'core_user_create_users';
    $format = 'json';

    // Crear el cuerpo de la solicitud con los datos dinámicos
    $postData = http_build_query([
        'wstoken' => $token,
        'wsfunction' => $function,
        'moodlewsrestformat' => $format,
        'users[0][username]' => $username,
        'users[0][password]' => $password,
        'users[0][firstname]' => $firstname,
        'users[0][lastname]' => $lastname,
        'users[0][email]' => $email,
        'users[0][auth]' => 'manual' // Tipo de autenticación
    ]);

    // Configuración de cURL
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $apiUrl,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/x-www-form-urlencoded'
        ]
    ]);

    // Ejecutar la solicitud cURL y obtener la respuesta
    $response = curl_exec($curl);
    curl_close($curl);

    // Devolver la respuesta
    return $response;
}
?>

<!-- Tabla -->
<table id="listaInscritos" class="table table-hover table-bordered">
    <thead class="thead-dark">
        <tr>
            <th>Identificación</th>
            <th>Nombre Completo</th>
            <th>Teléfonos</th>
            <th>Correo</th>
            <th>Programa</th>
            <th>Municipio</th>
            <th>Dirección</th>
            <th>Estado</th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($data as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                <td><?php echo htmlspecialchars($row['first_name']) . ' ' . htmlspecialchars($row['second_name']) . ' ' . htmlspecialchars($row['first_last']) . ' ' . htmlspecialchars($row['second_last']); ?></td>
                <td><?php echo htmlspecialchars($row['first_phone']) . ' / ' . htmlspecialchars($row['second_phone']); ?></td>
                <td><?php echo htmlspecialchars($row['email']); ?></td>
                <td><?php echo htmlspecialchars($row['program']); ?></td>
                <td><?php echo htmlspecialchars($row['municipio']); ?></td>
                <td><?php echo htmlspecialchars($row['address']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <?php echo $row['acciones']; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Toastr CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
$(document).ready(function() {
    // Inicialización de la tabla
    $('#propiedadesVenta').DataTable({
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        }
    });

    // Mostrar mensaje de toast si existe
    <?php if ($mensajeToast): ?>
        toastr.success("<?php echo $mensajeToast; ?>");
    <?php endif; ?>
});

// Función de confirmación de actualización
function confirmarActualizacion() {
    return confirm("¿Está seguro de que desea actualizar el estado de este usuario?");
}
</script>
