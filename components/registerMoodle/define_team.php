<?php
// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Verificar conexión a la base de datos
require __DIR__ . '../../../controller/conexion.php';
if (!isset($conn) || $conn->connect_error) {
    die("Error de conexión a la base de datos: " .
        (isset($conn) ? $conn->connect_error : "No se pudo establecer la conexión"));
}

// Función para llamar a la API de Moodle
function callMoodleAPI($function, $params = [])
{
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
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();

// Consulta para obtener usuarios por rol
function getUsersByRole($conn, $role)
{
    $sql = "SELECT id, username, nombre, email FROM users WHERE rol = ? ORDER BY nombre ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $role);
    $stmt->execute();
    $result = $stmt->get_result();
    $users = [];

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
    }

    $stmt->close();
    return $users;
}

// Obtener usuarios por rol
$profesores = getUsersByRole($conn, 5);
$mentores = getUsersByRole($conn, 8);
$monitores = getUsersByRole($conn, 7);
?>

<?php include("controller/botonFlotanteDerecho.php"); ?>

<?php include("components/sliderBarBotton.php"); ?>

<head>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/introjs.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intro.js/7.2.0/intro.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        :root {
            --tecnico-color: #30336b;
            --ingles-color: #ec008c;
            --english-code-color: #006d68;
            --habilidades-color: #5d3fd3;
        }

        html {
            overflow-x: hidden;
        }

        .card-body {
            display: flex;
            flex-direction: column;
            padding: 15px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
            font-weight: bold;
            font-size: 1.2rem;
            color: white;
            padding: 15px;
        }

        .tecnico-card .card-header {
            background-color: var(--tecnico-color);
        }

        .ingles-card .card-header {
            background-color: var(--ingles-color);
        }

        .english-code-card .card-header {
            background-color: var(--english-code-color);
        }

        .habilidades-card .card-header {
            background-color: var(--habilidades-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
            width: calc(100% - 30px);
            /* Ancho total menos 30px (15px cada lado) */
            margin-left: auto;
            margin-right: auto;
        }

        .select2-container {
            width: 100% !important;
            min-height: 45px;
            /* Altura mínima para los selectores */
            max-width: 100% !important;
            /* Limita el ancho máximo */
        }

        /* Ajustes para el dropdown de Select2 */
        .select2-dropdown {
            min-width: auto !important;
        }

        .select2-container .select2-selection--single {
            height: 45px !important;
            padding: 8px;
            white-space: normal;
            /* Permite que el texto se ajuste en múltiples líneas */
        }

        /* Ajustar el contenedor de resultados */
        .select2-results {
            max-width: 100%;
            /* Limita el ancho de los resultados */
        }

        .select2-results__options {
            word-wrap: break-word;
            /* Permite que el texto largo se ajuste */
            white-space: normal;
            /* Permite saltos de línea */
        }

        /* Ajustar el texto dentro de las opciones */
        .select2-results__option {
            white-space: normal;
            /* Permite que el texto se ajuste en múltiples líneas */
            word-wrap: break-word;
            /* Rompe palabras largas si es necesario */
        }

        .course-icon {
            margin-right: 10px;
        }

        .btn-save {
            margin-top: 20px;
        }

        /* Colores para bordes de los selects */
        .tecnico-select {
            border-color: var(--tecnico-color);
        }

        .ingles-select {
            border-color: var(--ingles-color);
        }

        .english-code-select {
            border-color: var(--english-code-color);
        }

        .habilidades-select {
            border-color: var(--habilidades-color);
        }

        .btn-float {
            position: fixed;
            right: 20px;
            top: 100px;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: var(--tecnico-color);
            color: white;
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 1000;
        }

        .btn-float i {
            font-size: 1.5rem;
        }

        .btn-float:disabled {
            background: #b8b8b8;
            cursor: not-allowed;
            transform: none;
        }

        .customTooltip {
            background-color: white !important;
            border-radius: 10px !important;
        }

        .introjs-tooltip {
            min-width: 300px;
        }

        .introjs-helperLayer {
            background-color: transparent;
        }

        .introjs-arrow.left {
            border-right-color: var(--tecnico-color) !important;
        }

        .introjs-button {
            background-color: var(--ingles-color);
            color: white;
            border: none;
            text-shadow: none;
            padding: 8px 15px;
            border-radius: 5px;
            margin: 5px;
        }

        .introjs-button:hover {
            background-color: var(--tecnico-color);
            color: white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>

<body>
    <div class="m-2">
        <form id="teamAssignmentForm">
            <div class="row" style="transform: scale(0.9);">
                <!-- Tarjeta para Bootcamp (Técnico) -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card tecnico-card h-100">
                        <div class="card-header">
                            <i class="bi bi-laptop course-icon"></i>Bootcamp Técnico
                        </div>
                        <div class="card-body w-100">
                            <div class="form-group">
                                <label class="select-label" for="bootcampCourse">Seleccionar Curso:</label>
                                <select id="bootcampCourse" class="form-select tecnico-select" name="bootcampCourse">
                                    <option value="">-- Seleccione un curso --</option>
                                    <?php if (!empty($courses_data)): ?>
                                        <?php foreach ($courses_data as $course):
                                            $categoryAllowed = in_array($course['categoryid'], [19, 21, 24, 26, 27, 35, 20, 22, 23, 25, 28]);
                                            if ($categoryAllowed): ?>
                                                <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                    data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                    <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="bootcampProfesor">Profesor:</label>
                                <select id="bootcampProfesor" class="form-select staff-select tecnico-select" name="bootcampProfesor">
                                    <option value="">-- Seleccione un profesor --</option>
                                    <?php foreach ($profesores as $profesor): ?>
                                        <option value="<?php echo $profesor['username']; ?>" data-username="<?php echo $profesor['username']; ?>">
                                            <?php echo htmlspecialchars($profesor['nombre']) . ' (' . $profesor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="bootcampMentor">Mentor:</label>
                                <select id="bootcampMentor" class="form-select staff-select tecnico-select" name="bootcampMentor">
                                    <option value="">-- Seleccione un mentor --</option>
                                    <?php foreach ($mentores as $mentor): ?>
                                        <option value="<?php echo $mentor['username']; ?>" data-username="<?php echo $mentor['username']; ?>">
                                            <?php echo htmlspecialchars($mentor['nombre']) . ' (' . $mentor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="bootcampMonitor">Monitor:</label>
                                <select id="bootcampMonitor" class="form-select staff-select tecnico-select" name="bootcampMonitor">
                                    <option value="">-- Seleccione un monitor --</option>
                                    <?php foreach ($monitores as $monitor): ?>
                                        <option value="<?php echo $monitor['username']; ?>" data-username="<?php echo $monitor['username']; ?>">
                                            <?php echo htmlspecialchars($monitor['nombre']) . ' (' . $monitor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta para Inglés Nivelatorio -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card ingles-card h-100">
                        <div class="card-header">
                            <i class="bi bi-translate course-icon"></i>Inglés Nivelatorio
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="select-label" for="inglesCourse">Seleccionar Curso:</label>
                                <select id="inglesCourse" class="form-select ingles-select" name="inglesCourse">
                                    <option value="">-- Seleccione un curso --</option>
                                    <?php if (!empty($courses_data)): ?>
                                        <?php foreach ($courses_data as $course):
                                            if ($course['categoryid'] == 17 || $course['categoryid'] == 18): ?>
                                                <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                    data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                    <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="inglesProfesor">Profesor:</label>
                                <select id="inglesProfesor" class="form-select staff-select ingles-select" name="inglesProfesor">
                                    <option value="">-- Seleccione un profesor --</option>
                                    <?php foreach ($profesores as $profesor): ?>
                                        <option value="<?php echo $profesor['username']; ?>" data-username="<?php echo $profesor['username']; ?>">
                                            <?php echo htmlspecialchars($profesor['nombre']) . ' (' . $profesor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="inglesMentor">Mentor:</label>
                                <select id="inglesMentor" class="form-select staff-select ingles-select" name="inglesMentor">
                                    <option value="">-- Seleccione un mentor --</option>
                                    <?php foreach ($mentores as $mentor): ?>
                                        <option value="<?php echo $mentor['username']; ?>" data-username="<?php echo $mentor['username']; ?>">
                                            <?php echo htmlspecialchars($mentor['nombre']) . ' (' . $mentor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="inglesMonitor">Monitor:</label>
                                <select id="inglesMonitor" class="form-select staff-select ingles-select" name="inglesMonitor">
                                    <option value="">-- Seleccione un monitor --</option>
                                    <?php foreach ($monitores as $monitor): ?>
                                        <option value="<?php echo $monitor['username']; ?>" data-username="<?php echo $monitor['username']; ?>">
                                            <?php echo htmlspecialchars($monitor['nombre']) . ' (' . $monitor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta para English Code -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card english-code-card h-100">
                        <div class="card-header">
                            <i class="bi bi-code-slash course-icon"></i>English Code
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="select-label" for="englishCodeCourse">Seleccionar Curso:</label>
                                <select id="englishCodeCourse" class="form-select english-code-select" name="englishCodeCourse">
                                    <option value="">-- Seleccione un curso --</option>
                                    <?php if (!empty($courses_data)): ?>
                                        <?php foreach ($courses_data as $course):
                                            if ($course['categoryid'] == 30 || $course['categoryid'] == 31): ?>
                                                <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                    data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                    <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="englishCodeProfesor">Profesor:</label>
                                <select id="englishCodeProfesor" class="form-select staff-select english-code-select" name="englishCodeProfesor">
                                    <option value="">-- Seleccione un profesor --</option>
                                    <?php foreach ($profesores as $profesor): ?>
                                        <option value="<?php echo $profesor['username']; ?>" data-username="<?php echo $profesor['username']; ?>">
                                            <?php echo htmlspecialchars($profesor['nombre']) . ' (' . $profesor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="englishCodeMentor">Mentor:</label>
                                <select id="englishCodeMentor" class="form-select staff-select english-code-select" name="englishCodeMentor">
                                    <option value="">-- Seleccione un mentor --</option>
                                    <?php foreach ($mentores as $mentor): ?>
                                        <option value="<?php echo $mentor['username']; ?>" data-username="<?php echo $mentor['username']; ?>">
                                            <?php echo htmlspecialchars($mentor['nombre']) . ' (' . $mentor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="englishCodeMonitor">Monitor:</label>
                                <select id="englishCodeMonitor" class="form-select staff-select english-code-select" name="englishCodeMonitor">
                                    <option value="">-- Seleccione un monitor --</option>
                                    <?php foreach ($monitores as $monitor): ?>
                                        <option value="<?php echo $monitor['username']; ?>" data-username="<?php echo $monitor['username']; ?>">
                                            <?php echo htmlspecialchars($monitor['nombre']) . ' (' . $monitor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tarjeta para Habilidades de Poder -->
                <div class="col-lg-3 col-md-6 col-sm-12 mb-4">
                    <div class="card habilidades-card h-100">
                        <div class="card-header">
                            <i class="bi bi-lightbulb course-icon"></i>Habilidades de Poder
                        </div>
                        <div class="card-body">
                            <div class="form-group">
                                <label class="select-label" for="habilidadesCourse">Seleccionar Curso:</label>
                                <select id="habilidadesCourse" class="form-select habilidades-select" name="habilidadesCourse">
                                    <option value="">-- Seleccione un curso --</option>
                                    <?php if (!empty($courses_data)): ?>
                                        <?php foreach ($courses_data as $course):
                                            if ($course['categoryid'] == 30 || $course['categoryid'] == 32): ?>
                                                <option value="<?php echo htmlspecialchars($course['id']); ?>"
                                                    data-fullname="<?php echo htmlspecialchars($course['fullname']); ?>">
                                                    <?php echo htmlspecialchars($course['id'] . ' - ' . $course['fullname']); ?>
                                                </option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="habilidadesProfesor">Profesor:</label>
                                <select id="habilidadesProfesor" class="form-select staff-select habilidades-select" name="habilidadesProfesor">
                                    <option value="">-- Seleccione un profesor --</option>
                                    <?php foreach ($profesores as $profesor): ?>
                                        <option value="<?php echo $profesor['username']; ?>" data-username="<?php echo $profesor['username']; ?>">
                                            <?php echo htmlspecialchars($profesor['nombre']) . ' (' . $profesor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="habilidadesMentor">Mentor:</label>
                                <select id="habilidadesMentor" class="form-select staff-select habilidades-select" name="habilidadesMentor">
                                    <option value="">-- Seleccione un mentor --</option>
                                    <?php foreach ($mentores as $mentor): ?>
                                        <option value="<?php echo $mentor['username']; ?>" data-username="<?php echo $mentor['username']; ?>">
                                            <?php echo htmlspecialchars($mentor['nombre']) . ' (' . $mentor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="select-label" for="habilidadesMonitor">Monitor:</label>
                                <select id="habilidadesMonitor" class="form-select staff-select habilidades-select" name="habilidadesMonitor">
                                    <option value="">-- Seleccione un monitor --</option>
                                    <?php foreach ($monitores as $monitor): ?>
                                        <option value="<?php echo $monitor['username']; ?>" data-username="<?php echo $monitor['username']; ?>">
                                            <?php echo htmlspecialchars($monitor['nombre']) . ' (' . $monitor['username'] . ')'; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-float"
                data-bs-toggle="popover"
                data-bs-placement="left"
                data-bs-content="Guardar"
                data-bs-trigger="hover"
                data-intro="Aquí podrás guardar las asignaciones de equipos una vez completados los campos">
                <i class="bi bi-floppy"></i>
            </button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Inicializar Select2 para todos los selectores de cursos
            $('#bootcampCourse, #inglesCourse, #englishCodeCourse, #habilidadesCourse').select2({
                placeholder: "Seleccione un curso",
                allowClear: true
            });

            // Añadir evento para autoselección cuando cambie el curso técnico
            $('#bootcampCourse').on('select2:select', function(e) {
                // Obtener el texto del elemento seleccionado
                var fullname = e.params.data.text;
                console.log("Curso seleccionado:", fullname);

                // Primero, habilitar todos los selectores de cursos (por si es una nueva selección)
                $('#inglesCourse, #englishCodeCourse, #habilidadesCourse').prop('disabled', false).trigger('change');

                // Verificar si tenemos un texto válido
                if (fullname) {
                    // Buscar patrones comunes de códigos de grupo
                    var matches = fullname.match(/C\dL\d-G\d+V|C\dL\d-\d+V/);

                    // NUEVO: Si no se encuentra, buscar con /(G\d+[A-Z]?)/ como fallback
                    if (!matches || !matches[0]) {
                        matches = fullname.match(/G\d+[A-Z]?/);
                    }

                    if (matches && matches[0]) {
                        var groupCode = matches[0];
                        console.log("Código de grupo encontrado:", groupCode);

                        autoSelectRelatedCourse('#inglesCourse', groupCode);
                        autoSelectRelatedCourse('#englishCodeCourse', groupCode);
                        autoSelectRelatedCourse('#habilidadesCourse', groupCode);
                    } else {
                        console.log("No se encontró código de grupo en:", fullname);
                    }
                }
            });

            // Función mejorada para seleccionar y deshabilitar cursos relacionados
            function autoSelectRelatedCourse(selectId, groupCode) {
                console.log("Buscando cursos con código", groupCode, "en", selectId);
                var found = false;

                // Recorrer todas las opciones del selector
                $(selectId + ' option').each(function() {
                    var optionText = $(this).text();
                    console.log("Analizando opción:", optionText);

                    // Buscar el código de grupo en el texto de la opción
                    if (optionText.indexOf(groupCode) > -1) {
                        console.log("¡Coincidencia encontrada! Seleccionando y deshabilitando:", optionText);

                        // Seleccionar esta opción y activar el evento change
                        $(selectId).val($(this).val()).trigger('change');

                        // Deshabilitar el selector para evitar cambios manuales
                        $(selectId).prop('disabled', true);

                        // Agregar clase visual para indicar autoselección (opcional)
                        $(selectId).addClass('auto-selected');

                        found = true;
                        return false; // Salir del bucle al encontrar coincidencia
                    }
                });

                if (!found) {
                    console.log("No se encontró curso relacionado con código", groupCode, "en", selectId);
                }
            }

            // Añadir eventos para autoselección de mentores
            $('#bootcampMentor, #inglesMentor, #englishCodeMentor, #habilidadesMentor').on('select2:select', function(e) {
                // Obtener el ID del mentor seleccionado
                var mentorId = $(this).val();
                var sourceId = $(this).attr('id');

                console.log("Mentor seleccionado:", mentorId, "desde:", sourceId);

                // Aplicar la selección a todos los selectores de mentor (excepto al que originó el cambio)
                $('.staff-select[id$="Mentor"]').not('#' + sourceId).each(function() {
                    // Seleccionar el mismo valor y disparar el evento change
                    $(this).val(mentorId).trigger('change');

                    // Deshabilitar el selector y agregar clase visual
                    $(this).prop('disabled', true).addClass('auto-selected');

                    console.log("Autoseleccionando mentor en:", $(this).attr('id'));
                });
            });

            // Hacer lo mismo para los monitores
            $('#bootcampMonitor, #inglesMonitor, #englishCodeMonitor, #habilidadesMonitor').on('select2:select', function(e) {
                // Obtener el ID del monitor seleccionado
                var monitorId = $(this).val();
                var sourceId = $(this).attr('id');

                console.log("Monitor seleccionado:", monitorId, "desde:", sourceId);

                // Aplicar la selección a todos los selectores de monitor (excepto al que originó el cambio)
                $('.staff-select[id$="Monitor"]').not('#' + sourceId).each(function() {
                    // Seleccionar el mismo valor y disparar el evento change
                    $(this).val(monitorId).trigger('change');

                    // Deshabilitar el selector y agregar clase visual
                    $(this).prop('disabled', true).addClass('auto-selected');

                    console.log("Autoseleccionando monitor en:", $(this).attr('id'));
                });
            });

            // Inicializar Select2 para todos los selectores de personal con búsqueda
            $('.staff-select').select2({
                placeholder: "Buscar por nombre o username",
                allowClear: true,
                matcher: matchCustom
            });

            // Función personalizada para buscar por nombre o username
            function matchCustom(params, data) {
                // Si no hay término de búsqueda, devolver todos los datos
                if ($.trim(params.term) === '') {
                    return data;
                }

                // No procesar si no hay datos
                if (typeof data.text === 'undefined') {
                    return null;
                }

                // Buscar en el texto visible (nombre)
                if (data.text.toLowerCase().indexOf(params.term.toLowerCase()) > -1) {
                    return data;
                }

                // Buscar en el username (atributo data-username)
                var username = $(data.element).data('username');
                if (username && username.toString().indexOf(params.term) > -1) {
                    return data;
                }

                // No hay coincidencias
                return null;
            }

            $("style").last().html(`
                .auto-selected + .select2-container .select2-selection {
                    background-color: #f8f9fa;
                    border-style: dashed;
                    cursor: not-allowed;
                }
                
                .auto-selected + .select2-container .select2-selection::after {
                    content: "⟳";  /* Símbolo de sincronización */
                    position: absolute;
                    right: 30px;
                    top: 8px;
                    font-size: 16px;
                    color: #6c757d;
                }
                
                /* Ocultar botón de borrado en campos deshabilitados */
                .auto-selected + .select2-container .select2-selection__clear {
                    display: none !important;
                }
                
                /* Deshabilitar el botón de flecha del desplegable en campos autoseleccionados */
                .auto-selected + .select2-container .select2-selection__arrow {
                    display: none !important;
                }
            `);

            // Inicializar los popovers
            const popoverTriggerList = document.querySelectorAll('[data-bs-toggle="popover"]');
            const popoverList = [...popoverTriggerList].map(popoverTriggerEl => new bootstrap.Popover(popoverTriggerEl));

            // Modificar el código del manejador del formulario para actualizar el botón flotante
            $('#teamAssignmentForm').on('submit', function(e) {
                e.preventDefault();

                // Primero obtener el botón y guardar su estado original
                const submitButton = $(this).find('button[type="submit"]');
                const originalHtml = submitButton.html();

                // Actualizar el estado del botón
                submitButton.html('<i class="bi bi-hourglass-split"></i> Guardando...').prop('disabled', true);

                // Recoger datos del formulario
                var formData = {
                    bootcamp: {
                        course: $('#bootcampCourse').val(),
                        course_name: $('#bootcampCourse option:selected').data('fullname') || $('#bootcampCourse option:selected').text(),
                        profesor: $('#bootcampProfesor').val(),
                        mentor: $('#bootcampMentor').val(),
                        monitor: $('#bootcampMonitor').val()
                    },
                    ingles: {
                        course: $('#inglesCourse').val(),
                        course_name: $('#inglesCourse option:selected').data('fullname') || $('#inglesCourse option:selected').text(),
                        profesor: $('#inglesProfesor').val(),
                        mentor: $('#inglesMentor').val(),
                        monitor: $('#inglesMonitor').val()
                    },
                    englishCode: {
                        course: $('#englishCodeCourse').val(),
                        course_name: $('#englishCodeCourse option:selected').data('fullname') || $('#englishCodeCourse option:selected').text(),
                        profesor: $('#englishCodeProfesor').val(),
                        mentor: $('#englishCodeMentor').val(),
                        monitor: $('#englishCodeMonitor').val()
                    },
                    habilidades: {
                        course: $('#habilidadesCourse').val(),
                        course_name: $('#habilidadesCourse option:selected').data('fullname') || $('#habilidadesCourse option:selected').text(),
                        profesor: $('#habilidadesProfesor').val(),
                        mentor: $('#habilidadesMentor').val(),
                        monitor: $('#habilidadesMonitor').val()
                    }
                };

                // Realizar la petición AJAX
                $.ajax({
                    url: 'components/registerMoodle/save_team.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(formData),
                    dataType: 'json', // Especificar que esperamos JSON
                    success: function(response) {
                        let title, icon, html;

                        if (response.success) {
                            title = '¡Proceso completado!';
                            icon = 'success';
                            html = `
                                <div class="text-left">
                                    <h6>Resumen del proceso:</h6>
                                    <ul>
                                        <li><strong>Asignaciones exitosas:</strong> ${response.summary?.successful || 0}</li>
                                        <li><strong>Errores:</strong> ${response.summary?.errors || 0}</li>
                                    </ul>
                                    <hr>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        ${response.messages.map(msg => `<p>• ${msg}</p>`).join('')}
                                    </div>
                                </div>
                            `;
                        } else {
                            title = 'Error en el proceso';
                            icon = 'error';
                            html = `
                                <div class="text-left">
                                    <p>Hubo problemas durante el proceso:</p>
                                    <div style="max-height: 200px; overflow-y: auto;">
                                        ${response.messages.map(msg => `<p>• ${msg}</p>`).join('')}
                                    </div>
                                </div>
                            `;
                        }

                        Swal.fire({
                            title: title,
                            html: html,
                            icon: icon,
                            confirmButtonText: 'Entendido',
                            width: '600px'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error('Error details:', xhr.responseText);

                        let errorMessage = 'Error de comunicación con el servidor';

                        // Intentar parsear la respuesta si es JSON
                        try {
                            const response = JSON.parse(xhr.responseText);
                            if (response.messages) {
                                errorMessage = response.messages.join('<br>');
                            }
                        } catch (e) {
                            // Si no es JSON, mostrar el error HTTP
                            errorMessage = `Error ${xhr.status}: ${error}`;
                            if (xhr.responseText && xhr.responseText.length < 500) {
                                errorMessage += `<br><small>${xhr.responseText}</small>`;
                            }
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error de comunicación',
                            html: errorMessage,
                            confirmButtonText: 'Entendido'
                        });
                    },
                    complete: function() {
                        // Restaurar el botón a su estado original
                        submitButton.html(originalHtml).prop('disabled', false);
                    }
                });
            });

            // Iniciar la guía automáticamente
            setTimeout(function() {
                introJs().setOptions({
                    steps: [{
                        element: '.btn-float',
                        intro: 'Bienvenido! Una vez hayas seleccionado los cursos y personal, usa este botón para guardar las asignaciones.',
                        position: 'left'
                    }],
                    showProgress: false,
                    showBullets: false,
                    disableInteraction: false,
                    tooltipClass: 'customTooltip',
                    prevLabel: 'Anterior',
                    nextLabel: 'Siguiente',
                    doneLabel: 'Entendido'
                }).start();
            }, 1000);
        });
    </script>
</body>

</html>