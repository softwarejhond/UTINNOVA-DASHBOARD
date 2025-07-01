<?php
// Incluir conexión y obtener dados de Moodle
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

                <!-- Botón de exportación -->
                <div class="row mt-3">
                    <div class="col-12 text-center">
                        <button id="exportBtn" class="btn btn-success" disabled>
                            <i class="fas fa-file-excel me-2"></i>
                            Exportar a Excel
                        </button>
                    </div>
                </div>
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

            // Deshabilitar botón de exportación
            $('#exportBtn').prop('disabled', true);
            currentCourseData = null;
            currentCourseCode = null;

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

    // Variable global para almacenar los datos cargados
    let currentCourseData = null;
    let currentCourseCode = null;

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
                    // Guardar datos para exportación
                    currentCourseData = response;
                    currentCourseCode = courseCode;

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

                    // Habilitar botón de exportación si hay datos
                    if (totalStudents > 0) {
                        $('#exportBtn').prop('disabled', false);
                    } else {
                        $('#exportBtn').prop('disabled', true);
                    }

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

    // Función para manejar la exportación
    $('#exportBtn').on('click', function() {
        if (!currentCourseData || !currentCourseCode) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No hay datos para exportar'
            });
            return;
        }

        // Mostrar indicador de carga
        Swal.fire({
            title: 'Generando archivo Excel',
            text: 'Por favor espere...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Hacer petición AJAX para exportar
        $.ajax({
            url: 'components/attendance/exportAttendance.php',
            method: 'POST',
            data: {
                courseCode: currentCourseCode,
                data: JSON.stringify(currentCourseData.data),
                classes: JSON.stringify(currentCourseData.classes)
            },
            xhrFields: {
                responseType: 'blob'
            },
            success: function(data, status, xhr) {
                // Crear enlace de descarga
                const blob = new Blob([data], {
                    type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                });
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `Seguimiento_Asistencia_${currentCourseCode}_${new Date().toISOString().split('T')[0]}.xlsx`;
                document.body.appendChild(a);
                a.click();
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                // Cerrar loading
                Swal.close();

                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Exportación exitosa',
                    text: 'El archivo se ha descargado correctamente',
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function(xhr, status, error) {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error de exportación',
                    text: 'No se pudo generar el archivo Excel'
                });
                console.error("Error exportando:", error);
            }
        });
    });

    // Función para inicializar DataTables solo with búsqueda
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

    // Función mejorada para guardar observación
    function saveObservation(modalId) {
        const form = $(`#form-${modalId}`);
        const formData = form.serialize();
        const saveButton = $(`#${modalId}`).find('button[onclick*="saveObservation"]');

        // Mostrar indicador de carga en el botón
        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveObservation.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000, // 10 segundos timeout
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Observación guardada correctamente',
                        timer: 2000,
                        showConfirmButton: false
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
                console.error("Error guardando observación:", {
                    xhr,
                    status,
                    error
                });
                let errorMessage = 'No se pudo conectar con el servidor';

                if (status === 'timeout') {
                    errorMessage = 'La operación tardó demasiado tiempo';
                } else if (xhr.responseText) {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        console.error("Error parsing response:", xhr.responseText);
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: errorMessage
                });
            },
            complete: function() {
                // Restaurar el botón
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Función mejorada para guardar gestión de asistencia
    function saveAttendanceManagement(modalId) {
        const form = $(`#management-form-${modalId}`);
        const formData = form.serialize();
        const saveButton = $(`#${modalId}`).find('button[onclick*="saveAttendanceManagement"]');

        // Mostrar indicador de carga
        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveAttendanceManagement.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 15000, // 15 segundos timeout
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado exitoso',
                        text: 'La información de gestión ha sido guardada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Cerrar el modal después de un pequeño delay
                    setTimeout(() => {
                        $(`#${modalId}`).modal('hide');
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar: ' + (response.message || 'Error desconocido')
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error guardando gestión:", {
                    xhr,
                    status,
                    error
                });
                let errorMessage = 'No se pudo conectar con el servidor';

                if (status === 'timeout') {
                    errorMessage = 'La operación tardó demasiado tiempo';
                } else if (xhr.responseText) {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        console.error("Error parsing response:", xhr.responseText);
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: errorMessage
                });
            },
            complete: function() {
                // Restaurar el botón
                saveButton.prop('disabled', false).html(originalText);
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

            // Columna de información de asistencia
            headerRow.append('<th>Información de asistencia</th>');

            thead.append(headerRow);

            // Actualizar contador
            countSpan.text(students.length);

            // Llenar tabla
            if (students.length === 0) {
                const colspan = 9 + classData.length;
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

                    // Agregar celdas para cada clase usando el modal genérico
                    classData.forEach((classInfo, index) => {
                        const classNumber = index + 1;
                        const classDate = classInfo.class_date; // Obtener la fecha de la clase

                        // Verificar si esta clase ya tiene observación
                        const hasObservation = classInfo.has_observation || false;
                        const buttonClass = hasObservation ? 'bg-cyan-dark' : 'bg-indigo-dark text-white';

                        row.append(`<td>
                            <button class="btn btn-sm ${buttonClass} observation-btn" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#genericObservationModal"
                                    data-student-id="${student.number_id}"
                                    data-student-name="${student.full_name}"
                                    data-course-id="${student.course_code}"
                                    data-class-date="${classDate}"
                                    data-class-number="${classNumber}">
                                <i class="fas fa-eye"></i>
                            </button>
                        </td>`);
                    });

                    // Celda de información de asistencia usando el modal genérico
                    row.append(`<td>
                        <button class="btn btn-sm bg-teal-dark text-white attendance-info-btn" 
                                data-bs-toggle="modal"
                                data-bs-target="#genericAttendanceModal"
                                data-student-id="${student.number_id}"
                                data-student-name="${student.full_name}"
                                data-course-id="${student.course_code}">
                            <i class="fas fa-info-circle"></i> Ver
                        </button>
                    </td>`);

                    tbody.append(row);
                });
            }

            console.log(`Tabla ${tableId}: ${students.length} estudiantes cargados con ${classData.length} clases`);
        });

        // Crear solo los modales genéricos
        createGenericModals();

        console.log("Total de estudiantes cargados:", totalStudents);
    }

    // Función para crear solo los modales genéricos
    function createGenericModals() {
        // Limpiar modales existentes
        $('.observation-modal, .attendance-info-modal').remove();

        // Modal genérico para observaciones
        const observationModal = `
        <div class="modal fade observation-modal" id="genericObservationModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="observationModalTitle">Observación</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <form id="genericObservationForm">
                            <div class="mb-3">
                                <label class="form-label" id="observationStudentLabel"><strong>Estudiante:</strong></label>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" id="observationClassLabel"><strong>Fecha de Clase:</strong></label>
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
                            <input type="hidden" name="student_id">
                            <input type="hidden" name="course_id">
                            <input type="hidden" name="class_date">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" id="saveObservationBtn">Guardar</button>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Modal genérico para información de asistencia
        const attendanceModal = `
       <div class="modal fade attendance-info-modal" id="genericAttendanceModal" tabindex="-1">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark text-white">
                        <h5 class="modal-title" id="attendanceModalTitle">
                            <i class="fas fa-user-clock me-2"></i> 
                            Información de Asistencia
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <!-- Spinner de carga -->
                        <div id="attendanceLoading" class="text-center p-4">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2">Cargando información de asistencia...</p>
                        </div>
                        
                        <!-- Contenido del modal -->
                        <div id="attendanceContent" style="display: none;">
                            <!-- Estadísticas de asistencia -->
                            <div class="card mb-4 border-0 shadow-sm">
                                <div class="card-header bg-gradient bg-indigo-dark text-white">
                                    <h6 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Estadísticas de Asistencia</h6>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="row g-3 w-100">
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded border-start border-5 border-primary">
                                                <h6 class="text-muted mb-1">Total de Clases</h6>
                                                <div class="fs-2 fw-bold text-primary" id="totalClasses">0</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded border-start border-5 border-success">
                                                <h6 class="text-muted mb-1">% Asistencia</h6>
                                                <div class="fs-2 fw-bold text-success" id="attendancePercentage">0%</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="text-center p-3 bg-white rounded border-start border-5 border-danger">
                                                <h6 class="text-muted mb-1">% Inasistencia</h6>
                                                <div class="fs-2 fw-bold text-danger" id="absencePercentage">0%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Formulario de gestión -->
                            <form id="genericAttendanceForm">
                                <input type="hidden" name="student_id">
                                <input type="hidden" name="course_id">
                                
                                <!-- Gestión de Subsanación -->
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-gradient bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-tasks me-2"></i> Gestión de Subsanación</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-question-circle me-1"></i>
                                                    ¿Requiere subsanación/gestión adicional?
                                                </label>
                                                <select class="form-select form-select-lg" name="requires_intervention">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-user me-1"></i>
                                                    Responsable
                                                </label>
                                                <input type="text" class="form-control form-control-lg" name="responsible_username" readonly>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-comment-dots me-1"></i>
                                                    Observación Subsanación
                                                </label>
                                                <textarea class="form-control" name="intervention_observation" rows="3" 
                                                         placeholder="Describe las acciones de subsanación realizadas o planificadas..."></textarea>
                                            </div>
                                            <div class="col-lg-6 mx-auto">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-check-circle me-1"></i>
                                                    ¿Resuelta?
                                                </label>
                                                <select class="form-select form-select-lg" name="is_resolved">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Estrategia Adicional -->
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-gradient bg-teal-dark text-white">
                                        <h6 class="mb-0"><i class="fas fa-lightbulb me-2"></i> Estrategia Adicional</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 w-100">
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-cog me-1"></i>
                                                    ¿Requiere estrategia adicional?
                                                </label>
                                                <select class="form-select form-select-lg" name="requires_additional_strategy">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-6">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-chart-line me-1"></i>
                                                    ¿Cumple estrategia?
                                                </label>
                                                <select class="form-select form-select-lg" name="strategy_fulfilled">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="Si">Sí</option>
                                                    <option value="No">No</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-sticky-note me-1"></i>
                                                    Observación Estrategia
                                                </label>
                                                <textarea class="form-control" name="strategy_observation" rows="3"
                                                         placeholder="Describe la estrategia implementada y su efectividad..."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Información de retiro -->
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-gradient bg-secondary text-white">
                                        <h6 class="mb-0"><i class="fas fa-sign-out-alt me-2"></i> Información de Retiro</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-3 w-100">
                                            <div class="col-lg-8">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                                    Motivo de retiro
                                                </label>
                                                <select class="form-select form-select-lg" name="withdrawal_reason">
                                                    <option value="">No aplica / Estudiante activo</option>
                                                    <option value="Motivos laborales">Motivos laborales</option>
                                                    <option value="Problemas personales">Problemas personales</option>
                                                    <option value="Cambio de programa">Cambio de programa</option>
                                                    <option value="Problemas de salud">Problemas de salud</option>
                                                    <option value="Dificultades académicas">Dificultades académicas</option>
                                                    <option value="Problemas económicos">Problemas económicos</option>
                                                    <option value="Otro">Otro</option>
                                                </select>
                                            </div>
                                            <div class="col-lg-4">
                                                <label class="form-label fw-semibold">
                                                    <i class="fas fa-calendar-alt me-1"></i>
                                                    Fecha de retiro
                                                </label>
                                                <input type="date" class="form-control form-control-lg" name="withdrawal_date">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-top">
                        <button type="button" class="btn btn-secondary btn-lg" data-bs-dismiss="modal">
                            <i class="fas fa-times me-2"></i>Cerrar
                        </button>
                        <button type="button" class="btn bg-indigo-dark text-white btn-lg" id="saveAttendanceBtn">
                            <i class="fas fa-save me-2"></i> Guardar Información
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Agregar modales al DOM
        $('body').append(observationModal);
        $('body').append(attendanceModal);

        // Configurar event listeners para los modales genéricos
        setupModalEventListeners();
    }

    // Función para configurar los event listeners de los modales
    function setupModalEventListeners() {
        // Event listener para botones de observación
        $(document).off('click', '.observation-btn').on('click', '.observation-btn', function(e) {
            e.preventDefault();

            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const courseId = $(this).data('course-id');
            const classDate = $(this).data('class-date');
            const classNumber = $(this).data('class-number');

            // Configurar el modal con los datos
            $('#observationModalTitle').text(`Observación - Clase ${classNumber}`);
            $('#observationStudentLabel').html(`<strong>Estudiante:</strong> ${studentName}`);
            $('#observationClassLabel').html(`<strong>Fecha de Clase:</strong> ${classDate}`);

            // Limpiar formulario y establecer datos
            const form = $('#genericObservationForm');
            form[0].reset();
            form.find('[name="student_id"]').val(studentId);
            form.find('[name="course_id"]').val(courseId);
            form.find('[name="class_date"]').val(classDate);

            // Guardar referencia del botón actual para actualizar su estado después
            $('#genericObservationModal').data('current-button', this);

            // Cargar observación existente
            loadObservationGeneric(studentId, courseId, classDate);

            // Mostrar modal
            $('#genericObservationModal').modal('show');
        });

        // Event listener para botones de información de asistencia
        $(document).off('click', '.attendance-info-btn').on('click', '.attendance-info-btn', function(e) {
            e.preventDefault();
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const courseId = $(this).data('course-id');
            loadAttendanceInfoGeneric(studentId, courseId, studentName);
        });

        // Event listener para guardar observación
        $('#saveObservationBtn').off('click').on('click', function() {
            saveObservationGeneric();
        });

        // Event listener para guardar información de asistencia
        $('#saveAttendanceBtn').off('click').on('click', function() {
            saveAttendanceManagementGeneric();
        });
    }

    // Función para cargar observación existente en modal genérico
    function loadObservationGeneric(studentId, courseId, classDate) {
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
                    const form = $('#genericObservationForm');
                    form.find('[name="observation_type"]').val(response.data.observation_type);
                    form.find('[name="observation_text"]').val(response.data.observation_text);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error cargando observación:", error);
            }
        });
    }

    // Función para guardar observación desde modal genérico
    function saveObservationGeneric() {
        const form = $('#genericObservationForm');
        const formData = form.serialize();
        const saveButton = $('#saveObservationBtn');
        const currentButton = $('#genericObservationModal').data('current-button');

        // Mostrar indicador de carga en el botón
        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveObservation.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 10000,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: 'Observación guardada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Cambiar el aspecto del botón tras guardar con éxito
                    if (currentButton) {
                        $(currentButton).removeClass('btn-outline-primary').addClass('bg-cyan-dark');
                    }

                    $('#genericObservationModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar la observación: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error guardando observación:", {
                    xhr,
                    status,
                    error
                });
                let errorMessage = 'No se pudo conectar con el servidor';

                if (status === 'timeout') {
                    errorMessage = 'La operación tardó demasiado tiempo';
                } else if (xhr.responseText) {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        console.error("Error parsing response:", xhr.responseText);
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: errorMessage
                });
            },
            complete: function() {
                // Restaurar el botón
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Función para cargar información de asistencia en modal genérico
    function loadAttendanceInfoGeneric(studentId, courseId, studentName) {
        const modal = $('#genericAttendanceModal');

        // Configurar título y datos básicos
        modal.find('#attendanceModalTitle').html(`<i class="fas fa-user-clock me-2"></i> Asistencia: ${studentName}`);
        const form = modal.find('#genericAttendanceForm');
        form[0].reset();
        form.find('[name="student_id"]').val(studentId);
        form.find('[name="course_id"]').val(courseId);

        // Mostrar spinner y ocultar contenido
        modal.find('#attendanceLoading').show();
        modal.find('#attendanceContent').hide();

        // Obtener username de la sesión
        const username = '<?php echo isset($_SESSION["username"]) ? $_SESSION["username"] : "usuario"; ?>';

        // Peticiones AJAX
        Promise.all([
            // Cargar estadísticas
            $.ajax({
                url: 'components/attendance/getAttendanceStats.php',
                method: 'POST',
                data: {
                    student_id: studentId,
                    course_id: courseId
                },
                dataType: 'json'
            }),
            // Cargar gestión existente
            $.ajax({
                url: 'components/attendance/getAttendanceManagement.php',
                method: 'POST',
                data: {
                    student_id: studentId,
                    course_id: courseId
                },
                dataType: 'json'
            })
        ]).then(([statsResponse, managementResponse]) => {
            // Procesar estadísticas
            if (statsResponse && statsResponse.success) {
                const stats = statsResponse.data;
                modal.find('#totalClasses').text(stats.totalClasses || 0);
                modal.find('#attendancePercentage').text(`${stats.attendancePercentage || 0}%`);
                modal.find('#absencePercentage').text(`${stats.absencePercentage || 0}%`);

                const attendanceElement = modal.find('#attendancePercentage');
                attendanceElement.removeClass('text-success text-warning text-danger');
                if (stats.attendancePercentage < 70) {
                    attendanceElement.addClass('text-danger');
                } else if (stats.attendancePercentage < 85) {
                    attendanceElement.addClass('text-warning');
                } else {
                    attendanceElement.addClass('text-success');
                }
            }

            // Procesar gestión
            form.find('[name="responsible_username"]').val(username); // Por defecto el usuario actual
            if (managementResponse && managementResponse.success && managementResponse.data) {
                const data = managementResponse.data;
                form.find('[name="requires_intervention"]').val(data.requires_intervention || '');
                form.find('[name="intervention_observation"]').val(data.intervention_observation || '');
                form.find('[name="is_resolved"]').val(data.is_resolved || '');
                form.find('[name="requires_additional_strategy"]').val(data.requires_additional_strategy || '');
                form.find('[name="strategy_observation"]').val(data.strategy_observation || '');
                form.find('[name="strategy_fulfilled"]').val(data.strategy_fulfilled || '');
                form.find('[name="withdrawal_reason"]').val(data.withdrawal_reason || '');
                form.find('[name="withdrawal_date"]').val(data.withdrawal_date ? data.withdrawal_date.split(' ')[0] : '');
                if (data.responsible_username) {
                    form.find('[name="responsible_username"]').val(data.responsible_username);
                }
            }

            // Mostrar contenido
            modal.find('#attendanceLoading').hide();
            modal.find('#attendanceContent').show();

        }).catch(error => {
            console.error("Error cargando información de asistencia:", error);
            Swal.fire('Error', 'No se pudo cargar la información de asistencia.', 'error');
            modal.find('#attendanceLoading').hide();
        });
    }

    // Función para guardar gestión de asistencia desde modal genérico
    function saveAttendanceManagementGeneric() {
        const form = $('#genericAttendanceForm');
        const formData = form.serialize();
        const saveButton = $('#saveAttendanceBtn');

        // Mostrar indicador de carga
        const originalText = saveButton.html();
        saveButton.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        $.ajax({
            url: 'components/attendance/saveAttendanceManagement.php',
            method: 'POST',
            data: formData,
            dataType: 'json',
            timeout: 15000,
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Guardado exitoso',
                        text: 'La información de gestión ha sido guardada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    // Cerrar el modal después de un pequeño delay
                    setTimeout(() => {
                        $('#genericAttendanceModal').modal('hide');
                    }, 2000);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al guardar: ' + (response.message || 'Error desconocido')
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error("Error guardando gestión:", {
                    xhr,
                    status,
                    error
                });
                let errorMessage = 'No se pudo conectar con el servidor';

                if (status === 'timeout') {
                    errorMessage = 'La operación tardó demasiado tiempo';
                } else if (xhr.responseText) {
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        errorMessage = errorResponse.message || errorMessage;
                    } catch (e) {
                        console.error("Error parsing response:", xhr.responseText);
                    }
                }

                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: errorMessage
                });
            },
            complete: function() {
                // Restaurar el botón
                saveButton.prop('disabled', false).html(originalText);
            }
        });
    }

    // Eliminar las funciones antiguas que ya no se necesitan
    // - createObservationModals()
    // - createAttendanceInfoModals() 
    // - loadObservation()
    // - saveObservation()
    // - loadAttendanceInfo()
    // - saveAttendanceManagement()

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
</script>