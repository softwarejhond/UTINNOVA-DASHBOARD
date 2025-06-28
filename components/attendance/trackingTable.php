<?php
// Incluir conexión y obtener datos de Moodle
require_once __DIR__ . '/../../controller/conexion.php';

// Función para obtener cursos desde Moodle
function getCourses()
{
    global $api_url, $token, $format;
    $params = [
        'wstoken' => $token,
        'wsfunction' => 'core_course_get_courses',
        'moodlewsrestformat' => $format
    ];
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

// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();
?>

<style>
    .table-responsive {
        overflow-x: auto;
        max-width: 100%;
    }

    .datatable {
        width: 100%;
        table-layout: auto;
    }

    .datatable th,
    .datatable td {
        white-space: nowrap;
        min-width: 120px;
        padding: 8px 12px;
        vertical-align: middle;
        text-align: center;
    }

    /* Ajustes específicos para columnas */
    .datatable th:nth-child(1),
    .datatable td:nth-child(1) {
        min-width: 100px;
        /* Documento */
    }

    .datatable th:nth-child(2),
    .datatable td:nth-child(2) {
        min-width: 200px;
        /* Nombre */
        text-align: left;
    }

    .datatable th:nth-child(3),
    .datatable td:nth-child(3) {
        min-width: 120px;
        /* Celular */
    }

    .datatable th:nth-child(4),
    .datatable td:nth-child(4) {
        min-width: 220px;
        /* Correo Institucional */
        text-align: left;
    }

    .datatable th:nth-child(5),
    .datatable td:nth-child(5) {
        min-width: 220px;
        /* Correo Personal */
        text-align: left;
    }

    .datatable th:nth-child(6),
    .datatable td:nth-child(6) {
        min-width: 100px;
        /* Horario */
    }

    .datatable th:nth-child(7),
    .datatable td:nth-child(7) {
        min-width: 100px;
        /* Grupo */
    }

    .datatable th:nth-child(8),
    .datatable td:nth-child(8) {
        min-width: 150px;
        /* Estado Admisión */
    }

    /* Columnas de clases dinámicas */
    .datatable th:nth-child(n+9),
    .datatable td:nth-child(n+9) {
        min-width: 80px;
        /* Clases */
    }

    .observation-modal .modal-dialog {
        max-width: 600px;
    }

    .btn-outline-primary {
        font-size: 0.875rem;
        padding: 0.25rem 0.5rem;
    }

    .btn-outline-primary i {
        font-size: 0.75rem;
    }

    /* Asegurar que el contenedor de tabs no se desborde */
    .tab-content {
        overflow-x: auto;
    }

    /* Mejorar la visualización de badges */
    .badge {
        font-size: 0.75rem;
        padding: 0.35em 0.65em;
    }

    .course-code-display {
        background-color: #f8f9fa;
        font-weight: 500;
        color: #007a7a;
    }

    /* Estilo para el bg-cyan-dark */
    .bg-cyan-dark {
        background-color: #007a7a !important;
        color: white !important;
        border-color: #007a7a !important;
    }

    /* Estilos para el Select2 */
    .select2-container--default .select2-selection--single {
        height: 40px !important;
        padding: 10px 8px !important;
        border: 1px solid #ced4da;
        border-radius: 4px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 30px !important;
        font-weight: 500;
        font-size: 1.05rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }

    .select2-dropdown {
        border: 1px solid #ced4da;
    }

    .select2-container--default .select2-results__option {
        padding: 10px;
        font-size: 1.05rem;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        padding: 8px;
        height: 40px;
    }

    /* Colores personalizados para nav-tabs */
    .text-indigo-dark {
        color: #30336b !important;
        /* Color índigo oscuro */
    }

    /* Estilo para las pestañas de navegación */
    .nav-tabs .nav-link {
        color: #000000;
        /* Color negro para pestañas inactivas */
        font-weight: normal;
        transition: color 0.3s, font-weight 0.3s;
    }

    .nav-tabs .nav-link.active {
        color: #30336b !important;
        /* Color índigo para pestaña activa */
        font-weight: bold;
        border-bottom-color: #30336b;
        border-bottom-width: 2px;
    }

    /* Eliminar el border-bottom de las pestañas inactivas */
    .nav-tabs .nav-link:not(.active) {
        border-color: transparent;
    }

    /* Hover effect para las pestañas */
    .nav-tabs .nav-link:hover:not(.active) {
        border-color: transparent;
        color: #30336b;
    }
</style>

<div class="container-fluid mt-4">
    <div class="card shadow mb-3">
        <div class="card-body rounded-0">
            <div class="container-fluid">
                <div class="row align-items-end">
                    <!-- Selección de Bootcamp (Clase) -->
                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <label class="form-label">Curso</label>
                        <select id="bootcamp" class="form-select course-select" style="height: calc(2.375rem + 10px); padding: 8px 12px; padding-bottom: 10px;">
                            <option value="">Seleccione un curso</option>
                            <?php
                            $allowed_categories = [20, 22, 23, 25, 28, 35, 19, 21, 24, 26, 27, 35];
                            foreach ($courses_data as $course):
                                if (in_array($course['categoryid'], $allowed_categories)):
                            ?>
                                    <option value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                    </option>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                        <label class="form-label">Código del curso</label>
                        <input type="text" id="courseCodeDisplay" class="form-control course-code-display"
                            readonly value="No seleccionado">
                    </div>
                </div>

                <!-- <div class="row">
                    <div class="col-12">
                        <button id="loadStudents" class="btn btn-primary">Cargar Estudiantes</button>
                    </div>
                </div> -->
            </div>
        </div>
    </div>
</div>

<!-- Contenedor para centrar los Nav tabs -->
<div class="d-flex justify-content-center">
    <ul class="nav nav-tabs" id="studentsTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tecnico-tab" data-bs-toggle="tab" data-bs-target="#tecnico-tab-pane" type="button" role="tab" aria-controls="tecnico-tab-pane" aria-selected="true">
                Técnico (<span id="tecnico-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="ingles-tab" data-bs-toggle="tab" data-bs-target="#ingles-tab-pane" type="button" role="tab" aria-controls="ingles-tab-pane" aria-selected="false">
                Inglés Nivelador (<span id="ingles-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="english-code-tab" data-bs-toggle="tab" data-bs-target="#english-code-tab-pane" type="button" role="tab" aria-controls="english-code-tab-pane" aria-selected="false">
                English Code (<span id="english-code-count">0</span>)
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="habilidades-tab" data-bs-toggle="tab" data-bs-target="#habilidades-tab-pane" type="button" role="tab" aria-controls="habilidades-tab-pane" aria-selected="false">
                Habilidades (<span id="habilidades-count">0</span>)
            </button>
        </li>
    </ul>
</div>

<!-- Contenedor para las tablas de estudiantes -->
<div id="studentsContainer" class="card shadow" style="display: none;">
    <div class="card-body">

        <!-- Tab content - Con margen superior para separarse claramente de las pestañas -->
        <div class="tab-content mt-3" id="studentsTabContent">
            <!-- Técnico -->
            <div class="tab-pane fade show active" id="tecnico-tab-pane" role="tabpanel" aria-labelledby="tecnico-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable" id="tecnico-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Correo Institucional</th>
                                <th>Correo Personal</th>
                                <th>Horario</th>
                                <th>Grupo</th>
                                <th>Estado Admisión</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Inglés Nivelado -->
            <div class="tab-pane fade" id="ingles-tab-pane" role="tabpanel" aria-labelledby="ingles-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable" id="ingles-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Correo Institucional</th>
                                <th>Correo Personal</th>
                                <th>Horario</th>
                                <th>Grupo</th>
                                <th>Estado Admisión</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- English Code -->
            <div class="tab-pane fade" id="english-code-tab-pane" role="tabpanel" aria-labelledby="english-code-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable" id="english-code-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Correo Institucional</th>
                                <th>Correo Personal</th>
                                <th>Horario</th>
                                <th>Grupo</th>
                                <th>Estado Admisión</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- Habilidades -->
            <div class="tab-pane fade" id="habilidades-tab-pane" role="tabpanel" aria-labelledby="habilidades-tab" tabindex="0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover datatable" id="habilidades-table">
                        <thead>
                            <tr>
                                <th>Documento</th>
                                <th>Nombre</th>
                                <th>Celular</th>
                                <th>Correo Institucional</th>
                                <th>Correo Personal</th>
                                <th>Horario</th>
                                <th>Grupo</th>
                                <th>Estado Admisión</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap 5.3.3 CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">


<!-- Incluir SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        // Inicializar Select2 en el selector de cursos
        $('#bootcamp').select2({
            placeholder: "Buscar y seleccionar un curso",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Inicializar DataTables vacías al cargar la página
        initializeDataTables();

        // Evento de cambio en el selector de cursos con Select2
        $('#bootcamp').on('change', function() {
            const selectedCourse = $(this).val();
            const selectedText = $(this).find('option:selected').text();

            if (!selectedCourse) {
                $('#courseCodeDisplay').val('No seleccionado');
                return;
            }

            // Extraer el código del curso
            const courseCodeMatch = selectedText.match(/C\d+L\d+-G\d+[A-Z]?/);
            if (!courseCodeMatch) {
                $('#courseCodeDisplay').val('Código no disponible');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo extraer el código del curso: ' + selectedText
                });
                return;
            }

            const courseCode = courseCodeMatch[0];
            // Actualizar el campo de visualización del código
            $('#courseCodeDisplay').val('Código del curso: ' + courseCode);

            console.log("Curso seleccionado:", selectedText);
            console.log("Código extraído:", courseCode);

            // Cargar estudiantes automáticamente
            loadStudentsData(selectedCourse, courseCode);
        });
    });

    // Función para cargar los datos de estudiantes
    function loadStudentsData(courseId, courseCode) {
        // Mostrar carga con SweetAlert2
        Swal.fire({
            title: 'Cargando estudiantes',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Hacer la petición AJAX
        $.ajax({
            url: 'components/attendance/getStudents.php',
            method: 'POST',
            data: {
                courseId: courseId,
                courseCode: courseCode
            },
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta completa:", response);

                if (response.success) {
                    // Mostrar información de depuración en la consola
                    if (response.debug) {
                        console.log("Información de depuración:", response.debug);
                        console.log("Estudiantes por tipo:", response.debug.studentCounts);
                    }

                    // Primero destruir todas las DataTables existentes
                    $('.datatable').each(function() {
                        if ($.fn.DataTable.isDataTable(this)) {
                            $(this).DataTable().destroy();
                        }
                    });

                    // Luego poblar las tablas con los nuevos datos
                    populateTables(response.data, response.classes);

                    // Verificar si hay datos
                    let totalStudents = 0;
                    Object.keys(response.data).forEach(key => {
                        totalStudents += response.data[key].length;
                    });

                    // Mostrar el contenedor de estudiantes
                    $('#studentsContainer').show();

                    // Inicializar DataTables DESPUÉS de poblar las tablas
                    setTimeout(() => {
                        initializeDataTables();

                        // Cerrar SweetAlert después de que todo esté listo
                        setTimeout(() => {
                            Swal.close();

                            if (totalStudents === 0) {
                                Swal.fire({
                                    icon: 'info',
                                    title: 'Sin resultados',
                                    text: 'No se encontraron estudiantes para este curso'
                                });
                            }
                        }, 200);
                    }, 100);

                } else {
                    // Cerrar loading y mostrar error
                    Swal.close();
                    setTimeout(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al cargar estudiantes: ' + response.message
                        });
                    }, 100);
                    console.error("Detalles del error:", response.debug);
                }
            },
            error: function(xhr, status, error) {
                // Cerrar loading y mostrar error de conexión
                Swal.close();
                setTimeout(() => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor'
                    });
                }, 100);
                console.error("Error AJAX:", status, error);
                console.error("Respuesta del servidor:", xhr.responseText);
            }
        });
    }

    // Función para inicializar DataTables solo con búsqueda
    function initializeDataTables() {
        // Primero recordamos qué pestaña estaba activa
        const activeTabId = $('.nav-link.active').attr('id');

        // Para cada tabla, activamos su pestaña, inicializamos DataTable y volvemos a la pestaña original
        $('.datatable').each(function() {
            const tableId = $(this).attr('id');
            const tabId = tableId.replace('-table', '-tab');

            // Activamos temporalmente la pestaña
            $(`#${tabId}`).tab('show');

            // Destruir tabla si ya está inicializada
            if ($.fn.DataTable.isDataTable(this)) {
                $(this).DataTable().destroy();
            }

            // Inicializar DataTable with improved settings
            $(this).DataTable({
                searching: true,
                paging: false,
                info: false,
                ordering: true,
                lengthChange: false,
                dom: '<"top"f>rt<"bottom">',
                scrollX: true,
                scrollCollapse: true,
                autoWidth: false,
                columnDefs: [{
                        width: "100px",
                        targets: 0
                    }, // Documento
                    {
                        width: "200px",
                        targets: 1
                    }, // Nombre
                    {
                        width: "120px",
                        targets: 2
                    }, // Celular
                    {
                        width: "220px",
                        targets: 3
                    }, // Correo Institucional
                    {
                        width: "220px",
                        targets: 4
                    }, // Correo Personal
                    {
                        width: "100px",
                        targets: 5
                    }, // Horario
                    {
                        width: "100px",
                        targets: 6
                    }, // Grupo
                    {
                        width: "150px",
                        targets: 7
                    }, // Estado Admisión
                    {
                        width: "80px",
                        targets: "_all"
                    } // Columnas restantes (clases)
                ],
                language: {
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    emptyTable: "No hay datos disponibles en la tabla",
                    infoEmpty: "Mostrando 0 a 0 de 0 registros"
                }
            });
        });

        // Volver a la pestaña que estaba activa originalmente
        $(`#${activeTabId}`).tab('show');
    }

    // Función para cargar información de asistencia
    function loadAttendanceInfo(studentId, courseId, modalId) {
        // Mostrar spinner de carga y ocultar contenido
        $(`#loading-${modalId}`).show();
        $(`#content-${modalId}`).hide();

        // Obtener username de la sesión para el campo responsable
        const username = '<?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : "usuario"; ?>';

        // Hacer dos peticiones AJAX en paralelo usando Promise.all
        Promise.all([
                // Cargar datos de asistencia
                new Promise((resolve, reject) => {
                    $.ajax({
                        url: 'components/attendance/getAttendanceStats.php',
                        method: 'POST',
                        data: {
                            student_id: studentId,
                            course_id: courseId
                        },
                        dataType: 'json',
                        success: resolve,
                        error: reject
                    });
                }),
                // Cargar datos de gestión existentes
                new Promise((resolve, reject) => {
                    $.ajax({
                        url: 'components/attendance/getAttendanceManagement.php',
                        method: 'POST',
                        data: {
                            student_id: studentId,
                            course_id: courseId
                        },
                        dataType: 'json',
                        success: resolve,
                        error: reject
                    });
                })
            ])
            .then(([attendanceResponse, managementResponse]) => {
                // Procesar datos de asistencia
                if (attendanceResponse.success) {
                    const stats = attendanceResponse.data;
                    $(`#total-classes-${modalId}`).text(stats.totalClasses);
                    $(`#attendance-percentage-${modalId}`).text(stats.attendancePercentage + '%');
                    $(`#absence-percentage-${modalId}`).text(stats.absencePercentage + '%');

                    // Cambiar color según porcentaje de asistencia
                    if (stats.attendancePercentage < 70) {
                        $(`#attendance-percentage-${modalId}`).removeClass('text-success').addClass('text-danger');
                    } else if (stats.attendancePercentage < 85) {
                        $(`#attendance-percentage-${modalId}`).removeClass('text-success').addClass('text-warning');
                    }
                }

                // Procesar datos de gestión
                if (managementResponse.success && managementResponse.data) {
                    const data = managementResponse.data;
                    const form = $(`#management-form-${modalId}`);

                    // Rellenar el formulario con datos existentes
                    form.find('[name="requires_intervention"]').val(data.requires_intervention || '');
                    form.find('[name="intervention_observation"]').val(data.intervention_observation || '');
                    form.find('[name="is_resolved"]').val(data.is_resolved || '');
                    form.find('[name="requires_additional_strategy"]').val(data.requires_additional_strategy || '');
                    form.find('[name="strategy_observation"]').val(data.strategy_observation || '');
                    form.find('[name="strategy_fulfilled"]').val(data.strategy_fulfilled || '');
                    form.find('[name="withdrawal_reason"]').val(data.withdrawal_reason || '');

                    // Formatear fecha de retiro si existe
                    if (data.withdrawal_date) {
                        const date = new Date(data.withdrawal_date);
                        const formattedDate = date.toISOString().split('T')[0];
                        form.find('[name="withdrawal_date"]').val(formattedDate);
                    }

                    // Usar el responsable existente o el actual
                    form.find('[name="responsible_username"]').val(data.responsible_username || username);
                } else {
                    // Si no hay datos, establecer el usuario actual como responsable
                    $(`#management-form-${modalId}`).find('[name="responsible_username"]').val(username);
                }

                // Ocultar spinner y mostrar contenido
                $(`#loading-${modalId}`).hide();
                $(`#content-${modalId}`).show();
            })
            .catch(error => {
                console.error("Error cargando información:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar la información de asistencia'
                });

                // Ocultar spinner y mostrar contenido vacío
                $(`#loading-${modalId}`).hide();
                $(`#content-${modalId}`).show();
            });
    }

    // Función para guardar la gestión de asistencia
    function saveAttendanceManagement(modalId) {
        const form = $(`#management-form-${modalId}`);
        const formData = form.serialize();

        $.ajax({
            url: 'components/attendance/saveAttendanceManagement.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado exitoso',
                        text: 'La información de gestión ha sido guardada correctamente'
                    });

                    // Cerrar el modal
                    $(`#${modalId}`).modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar: ' + (response.message || 'Error desconocido')
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error guardando gestión:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
            }
        });
    }

    function populateTables(data, classes) {
        const courseTypes = ['tecnico', 'ingles_nivelado', 'english_code', 'habilidades'];

        let totalStudents = 0;

        courseTypes.forEach(type => {
            const students = data[type] || [];
            const classData = classes[type] || [];
            totalStudents += students.length;

            const tableId = type === 'ingles_nivelado' ? 'ingles' : type.replace('_', '-');
            const table = $(`#${tableId}-table`);
            const thead = table.find('thead');
            const tbody = table.find('tbody');
            const countSpan = $(`#${tableId}-count`);

            // Limpiar tabla completamente
            thead.empty();
            tbody.empty();

            // Crear encabezados base con estructura correcta
            const headerRow = $('<tr></tr>');
            headerRow.append('<th>Documento</th>');
            headerRow.append('<th>Nombre</th>');
            headerRow.append('<th>Celular</th>');
            headerRow.append('<th>Correo Institucional</th>');
            headerRow.append('<th>Correo Personal</th>');
            headerRow.append('<th>Horario</th>');
            headerRow.append('<th>Grupo</th>');
            headerRow.append('<th>Estado Admisión</th>');

            // Agregar columnas dinámicas para cada clase
            classData.forEach((classInfo, index) => {
                headerRow.append(`<th>Clase ${index + 1}</th>`);
            });

            // NUEVA COLUMNA: Información de asistencia
            headerRow.append('<th>Información de asistencia</th>');

            thead.append(headerRow);

            // Actualizar contador
            countSpan.text(students.length);

            // Llenar tabla
            if (students.length === 0) {
                const colspan = 9 + classData.length; // Actualizado para incluir la nueva columna
                tbody.append(`<tr><td colspan="${colspan}" class="text-center">No hay estudiantes registrados</td></tr>`);
            } else {
                students.forEach(student => {
                    const statusText = student.estado_admision_texto || getStatusText(student.estado_admision);
                    const row = $('<tr></tr>');

                    // Agregar celdas base
                    row.append(`<td>${student.number_id || 'N/A'}</td>`);
                    row.append(`<td style="text-align: left;">${student.full_name || 'N/A'}</td>`);
                    row.append(`<td>${student.celular || 'N/A'}</td>`);
                    row.append(`<td style="text-align: left;">${student.institutional_email || 'N/A'}</td>`);
                    row.append(`<td style="text-align: left;">${student.email || 'N/A'}</td>`);
                    row.append(`<td>${student.horario || 'N/A'}</td>`);
                    row.append(`<td>${student.group_name || 'N/A'}</td>`);
                    row.append(`<td><span class="badge ${getStatusBadge(student.estado_admision)}">${statusText}</span></td>`);

                    // Agregar celdas para cada clase
                    classData.forEach((classInfo, index) => {
                        const classNumber = index + 1;
                        const modalId = `modal-${type}-${student.number_id}-${classNumber}`;

                        // Verificar si esta clase ya tiene observación
                        const hasObservation = classInfo.has_observation || false;
                        const buttonClass = hasObservation ? 'bg-cyan-dark' : 'bg-indigo-dark text-white';

                        row.append(`<td>
                            <button class="btn btn-sm ${buttonClass}" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#${modalId}"
                                    onclick="loadObservation('${student.number_id}', '${student.course_code}', '${classInfo.class_date}', '${modalId}')">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>`);
                    });

                    // NUEVA CELDA: Información de asistencia
                    const attendanceModalId = `attendance-modal-${type}-${student.number_id}`;
                    row.append(`<td>
                        <button class="btn btn-sm btn-info" 
                                data-bs-toggle="modal" 
                                data-bs-target="#${attendanceModalId}"
                                onclick="loadAttendanceInfo('${student.number_id}', '${student.course_code}', '${attendanceModalId}')">
                            <i class="fas fa-info-circle"></i> Ver
                        </button>
                    </td>`);

                    tbody.append(row);
                });
            }

            console.log(`Tabla ${tableId}: ${students.length} estudiantes cargados con ${classData.length} clases`);
        });

        // Crear modales dinámicamente
        createObservationModals(data, classes);

        // Crear modales de información de asistencia
        createAttendanceInfoModals(data, classes);

        console.log("Total de estudiantes cargados:", totalStudents);
    }

    // Función para crear modales de observaciones
    function createObservationModals(data, classes) {
        const courseTypes = ['tecnico', 'ingles_nivelado', 'english_code', 'habilidades'];

        // Limpiar modales existentes
        $('.observation-modal').remove();

        courseTypes.forEach(type => {
            const students = data[type] || [];
            const classData = classes[type] || [];

            students.forEach(student => {
                classData.forEach((classInfo, index) => {
                    const classNumber = index + 1;
                    const modalId = `modal-${type}-${student.number_id}-${classNumber}`;

                    const modal = `
                    <div class="modal fade observation-modal" id="${modalId}" tabindex="-1">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Observación - Clase ${classNumber}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <form id="form-${modalId}">
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Estudiante:</strong> ${student.full_name}</label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label"><strong>Fecha de Clase:</strong> ${classInfo.class_date}</label>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Tipo de Observación</label>
                                            <select class="form-select" name="observation_type" required>
                                                <option value="">Seleccionar...</option>
                                                <option value="excelente">Excelente desempeño</option>
                                                <option value="bueno">Buen desempeño</option>
                                                <option value="regular">Desempeño regular</option>
                                                <option value="malo">Bajo desempeño</option>
                                                <option value="ausente">Estudiante ausente</option>
                                                <option value="tarde">Llegada tardía</option>
                                                <option value="participacion">Alta participación</option>
                                                <option value="dificultades">Dificultades de aprendizaje</option>
                                                <option value="otro">Otro</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Observaciones</label>
                                            <textarea class="form-control" name="observation_text" rows="4" 
                                                    placeholder="Escriba sus observaciones aquí..."></textarea>
                                        </div>
                                        <input type="hidden" name="student_id" value="${student.number_id}">
                                        <input type="hidden" name="course_id" value="${student.course_code}">
                                        <input type="hidden" name="class_date" value="${classInfo.class_date}">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="button" class="btn btn-primary" onclick="saveObservation('${modalId}')">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    `;

                    $('body').append(modal);
                });
            });
        });
    }

    // Función para cargar observación existente
    function loadObservation(studentId, courseId, classDate, modalId) {
        $.ajax({
            url: 'components/attendance/getObservation.php',
            method: 'POST',
            data: {
                student_id: studentId,
                course_id: courseId,
                class_date: classDate
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data) {
                    const form = $(`#form-${modalId}`);
                    form.find('[name="observation_type"]').val(response.data.observation_type);
                    form.find('[name="observation_text"]').val(response.data.observation_text);

                    // Cambiar el aspecto del botón cuando existe observación
                    const btnObservation = $(`[data-bs-target="#${modalId}"]`);
                    btnObservation.removeClass('btn-outline-primary');
                    btnObservation.addClass('bg-cyan-dark');
                }
            },
            error: function(xhr, status, error) {
                console.error("Error cargando observación:", error);
            }
        });
    }

    // Función para guardar observación
    function saveObservation(modalId) {
        const form = $(`#form-${modalId}`);
        const formData = form.serialize();

        $.ajax({
            url: 'components/attendance/saveObservation.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Observación guardada correctamente'
                    });

                    // Cambiar el aspecto del botón tras guardar con éxito
                    const btnObservation = $(`[data-bs-target="#${modalId}"]`);
                    btnObservation.removeClass('btn-outline-primary');
                    btnObservation.addClass('bg-cyan-dark');

                    $(`#${modalId}`).modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar la observación: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'No se pudo conectar con el servidor'
                });
                console.error("Error guardando observación:", error);
            }
        });
    }

    // Función actualizada para el mapeo correcto de estados
    function getStatusText(status) {
        const statusMap = {
            '0': 'Pendiente',
            '1': 'Beneficiario',
            '2': 'Rechazado',
            '3': 'Matriculado',
            '4': 'Sin contacto',
            '5': 'En proceso',
            '6': 'Culminó proceso',
            '7': 'Inactivo',
            '8': 'Beneficiario contrapartida',
            '10': 'Formado'
        };

        return statusMap[String(status)] || 'Estado desconocido';
    }

    // Función actualizada para los badges de estado
    function getStatusBadge(status) {
        const statusNum = parseInt(status);
        switch (statusNum) {
            case 0:
                return 'bg-warning text-dark'; // Pendiente
            case 1:
                return 'bg-success'; // Beneficiario
            case 2:
                return 'bg-danger'; // Rechazado
            case 3:
                return 'bg-teal-dark'; // Matriculado
            case 4:
                return 'bg-secondary'; // Sin contacto
            case 5:
                return 'bg-info'; // En proceso
            case 6:
                return 'bg-success'; // Culminó proceso
            case 7:
                return 'bg-dark'; // Inactivo
            case 8:
                return 'bg-teal-dark'; // Beneficiario contrapartida
            case 10:
                return 'bg-orange-dark'; // Formado
            default:
                return 'bg-light text-dark'; // Estado desconocido
        }
    }

    // Función para crear modales de información de asistencia
    function createAttendanceInfoModals(data, classes) {
        const courseTypes = ['tecnico', 'ingles_nivelado', 'english_code', 'habilidades'];

        // Limpiar modales existentes
        $('.attendance-info-modal').remove();

        courseTypes.forEach(type => {
            const students = data[type] || [];

            students.forEach(student => {
                const modalId = `attendance-modal-${type}-${student.number_id}`;

                const modal = `
                <div class="modal fade attendance-info-modal" id="${modalId}" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-info text-white">
                                <h5 class="modal-title">
                                    <i class="fas fa-user-clock me-2"></i> 
                                    Información de Asistencia: ${student.full_name}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <!-- Spinner de carga -->
                            <div id="loading-${modalId}" class="text-center p-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Cargando...</span>
                                </div>
                                <p class="mt-2">Cargando información de asistencia...</p>
                            </div>
                            
                            <!-- Contenido del modal -->
                            <div id="content-${modalId}" style="display: none;">
                                <!-- Estadísticas de asistencia -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Estadísticas de Asistencia</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 text-center">
                                                <div class="attendance-stat">
                                                    <h6>Total de Clases</h6>
                                                    <div class="fs-2 fw-bold" id="total-classes-${modalId}">0</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="attendance-stat text-success">
                                                    <h6>% Asistencia</h6>
                                                    <div class="fs-2 fw-bold" id="attendance-percentage-${modalId}">0%</div>
                                                </div>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <div class="attendance-stat text-danger">
                                                    <h6>% Inasistencia</h6>
                                                    <div class="fs-2 fw-bold" id="absence-percentage-${modalId}">0%</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Formulario de gestión -->
                                <form id="management-form-${modalId}">
                                    <input type="hidden" name="student_id" value="${student.number_id}">
                                    <input type="hidden" name="course_id" value="${student.course_code}">
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-tasks me-2"></i> Gestión de Subsanación</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">¿Requiere subsanación/gestión adicional?</label>
                                                    <select class="form-select" name="requires_intervention">
                                                        <option value="">Seleccione</option>
                                                        <option value="Si">Sí</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Responsable</label>
                                                    <input type="text" class="form-control" name="responsible_username" value="" readonly>
                                                </div>
                                            </div>
                                            
                                            <div class="row mb-3">
                                                <div class="col-12">
                                                    <label class="form-label">Observación Subsanación</label>
                                                    <textarea class="form-control" name="intervention_observation" rows="2"></textarea>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">¿Resuelta?</label>
                                                    <select class="form-select" name="is_resolved">
                                                        <option value="">Seleccione</option>
                                                        <option value="Si">Sí</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Estrategia Adicional</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">¿Requiere estrategia adicional?</label>
                                                    <select class="form-select" name="requires_additional_strategy">
                                                        <option value="">Seleccione</option>
                                                        <option value="Si">Sí</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">¿Cumple estrategia?</label>
                                                    <select class="form-select" name="strategy_fulfilled">
                                                        <option value="">Seleccione</option>
                                                        <option value="Si">Sí</option>
                                                        <option value="No">No</option>
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="row">
                                                <div class="col-12">
                                                    <label class="form-label">Observación Estrategia</label>
                                                    <textarea class="form-control" name="strategy_observation" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr class="my-4">
                                    
                                    <!-- Información de retiro -->
                                    <div class="card">
                                        <div class="card-header bg-light">
                                            <h6 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i> Información de Retiro</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <label class="form-label">Motivo de retiro</label>
                                                    <select class="form-select" name="withdrawal_reason">
                                                        <option value="">No aplica</option>
                                                        <option value="Motivos laborales">Motivos laborales</option>
                                                        <option value="Problemas personales">Problemas personales</option>
                                                        <option value="Cambio de programa">Cambio de programa</option>
                                                        <option value="Problemas de salud">Problemas de salud</option>
                                                        <option value="Dificultades académicas">Dificultades académicas</option>
                                                        <option value="Problemas económicos">Problemas económicos</option>
                                                        <option value="Otro">Otro</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">Fecha de retiro</label>
                                                    <input type="date" class="form-control" name="withdrawal_date">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            <button type="button" class="btn btn-primary" onclick="saveAttendanceManagement('${modalId}')">
                                <i class="fas fa-save me-2"></i> Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            `;

                $('body').append(modal);
            });
        });
    }
</script>