<style>
    .nav-tabs .nav-link.active {
        font-weight: bold;
        color: #30336b !important;
        background-color: #f8f9fa;
    }

    .nav-tabs .nav-link {
        color: #000 !important;
        font-weight: normal;
        background-color: #fff;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    /* Layout principal */
    .main-container {
        display: flex;
        height: calc(100vh - 100px);
        gap: 20px;
    }

    /* Panel izquierdo de filtros */
    .filters-panel {
        flex: 0 0 350px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
    }

    .filters-panel h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    /* Select2 estilos mínimos */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 36px;
        padding-left: 12px;
        color: #495057;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 10px;
    }

    .select2-dropdown {
        border-radius: 0.375rem;
        border-color: #ced4da;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        padding: 8px 12px;
    }

    .select2-results__options {
        max-height: 300px;
        overflow-y: auto;
    }

    /* Panel derecho de tabla */
    .table-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-content {
        flex: 1;
        overflow: auto;
        padding: 0;
    }

    /* Estilos para la tabla */
    .lista-estudiantes {
        width: 100%;
        min-width: 1400px;
        margin: 0;
        table-layout: auto;
    }

    .lista-estudiantes th,
    .lista-estudiantes td {
        vertical-align: middle;
        white-space: nowrap;
        padding: 8px 12px;
        min-width: 100px;
    }

    /* Permitir texto largo solo en nombre y correo */
    .lista-estudiantes td:nth-child(3),
    .lista-estudiantes td:nth-child(4) {
        white-space: normal !important;
        min-width: 180px;
        max-width: 250px;
        word-wrap: break-word;
    }

    /* Thead fijo */
    .lista-estudiantes thead {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    .lista-estudiantes thead th {
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        background: #f8f9fa;
        font-weight: 600;
    }

    .badge-success {
        background-color: #66cc00;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-warning {
        background-color: #ffc107;
    }

    .percentage-bar {
        height: 20px;
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .percentage-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
        background-color: #ec008c !important;
    }

    .percentage-fill.success {
        background-color: #ec008c !important;
    }

    .percentage-fill.warning {
        background-color: #ec008c !important;
    }

    .percentage-fill.danger {
        background-color: #ec008c !important;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
            height: auto;
        }

        .filters-panel {
            flex: none;
        }

        .table-panel {
            min-height: 500px;
        }
    }
</style>

<!-- CDN de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<ul class="nav nav-tabs justify-content-center" id="demoTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="tab1-tab" data-bs-toggle="tab" data-bs-target="#tab1"
            type="button" role="tab" aria-controls="tab1" aria-selected="true">
            Estudiantes Aprobados
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="tab2-tab" data-bs-toggle="tab" data-bs-target="#tab2"
            type="button" role="tab" aria-controls="tab2" aria-selected="false">
            Constancias Generadas
        </button>
    </li>
</ul>

<div class="tab-content text-center mt-4" id="demoTabsContent">
    <!-- Tabla de aprobados -->
    <div class="tab-pane fade show active" id="tab1" role="tabpanel" aria-labelledby="tab1-tab">
        <div class="container-fluid">
            <div class="main-container">
                <!-- Panel izquierdo de filtros -->
                <div class="filters-panel">
                    <h5><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>

                    <!-- Selección de Curso -->
                    <div class="form-group">
                        <label class="form-label">Curso</label>
                        <select id="bootcampAprobados" class="form-select course-select">
                            <option value="">Seleccione un curso</option>
                            <?php 
                            // Reutilizar la lógica de cursos de list_approve.php
                            require_once __DIR__ . '/../../conexion.php';

                            // Definir las variables globales para Moodle
                            $api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
                            $token   = "3f158134506350615397c83d861c2104";
                            $format  = "json";

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

                            function getCourses()
                            {
                                return callMoodleAPI('core_course_get_courses');
                            }

                            $courses_data = getCourses();
                            
                            foreach ($courses_data as $course): ?>
                                <?php 
                                    if (
                                        in_array($course['categoryid'], [20, 22, 23, 25, 28, 34, 19, 21, 24, 26, 27, 34, 35]) &&
                                        stripos($course['fullname'], 'Copiar') === false
                                    ): 
                                ?>
                                    <option value="<?= htmlspecialchars($course['id']) ?>">
                                        <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                                    </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Información del Curso Seleccionado -->
                    <div id="courseInfoContainerAprobados" style="display: none;">
                        <div class="form-group">
                            <label class="form-label">Tipo de Curso</label>
                            <input type="text" id="courseTypeAprobados" class="form-control" readonly value="Técnico">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Modalidad</label>
                            <input type="text" id="courseModeAprobados" class="form-control" readonly placeholder="Modalidad del curso">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Sede</label>
                            <input type="text" id="courseSedeAprobados" class="form-control" readonly placeholder="Sede del curso">
                        </div>
                    </div>
                </div>

                <!-- Panel derecho de tabla -->
                <div class="table-panel">
                    <!-- Header de la tabla -->
                    <div class="table-header">
                        <h5 class="mb-0"><i class="fa fa-graduation-cap"></i> Estudiantes Aprobados</h5>
                        <div class="d-flex gap-2">
                            <button id="btnEnvioMasivo" class="btn btn-outline-primary" style="display: none;">
                                <i class="fa fa-paper-plane"></i> Envío Masivo de Constancias
                            </button>
                        </div>
                    </div>

                    <!-- Contenido de la tabla scrolleable -->
                    <div class="table-content">
                        <table id="listaEstudiantesAprobados" class="table table-hover table-bordered lista-estudiantes">
                            <thead>
                                <tr class="text-center">
                                    <th>
                                        <input type="checkbox" id="selectAllStudents" title="Seleccionar todos">
                                    </th>
                                    <th>ID</th>
                                    <th>Número de ID</th>
                                    <th>Nombre completo</th>
                                    <th>Correo institucional</th>
                                    <th>Programa</th>
                                    <th>Modalidad</th>
                                    <th>Sede</th>
                                    <th>% Asistencia</th>
                                    <th>Nota Final</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="12" class="text-center py-5">
                                        <i class="fa fa-search fa-2x text-muted mb-3"></i><br>
                                        Seleccione un curso para cargar los estudiantes aprobados
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla de constancias generadas -->
    <div class="tab-pane fade" id="tab2" role="tabpanel" aria-labelledby="tab2-tab">
        <p>Contenido para constancias generadas (próximamente).</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Inicializar Select2 para el selector de cursos aprobados
        $('#bootcampAprobados').select2({
            placeholder: "Buscar y seleccionar un curso...",
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron cursos";
                },
                searching: function() {
                    return "Buscando...";
                },
                loadingMore: function() {
                    return "Cargando más resultados...";
                }
            }
        });

        // Función para actualizar la visibilidad de los botones
        function actualizarBotonesAprobados() {
            const $btnExcel = $('#btnExportarExcelAprobados');
            const $btnMasivo = $('#btnEnvioMasivo');
            const hayEstudiantes = $('#listaEstudiantesAprobados tbody tr[data-student-id]').length > 0;
            
            if (hayEstudiantes) {
                $btnExcel.show();
                $btnMasivo.show();
            } else {
                $btnExcel.hide();
                $btnMasivo.hide();
            }
        }

        // Función para exportar a Excel estudiantes aprobados
        $('#btnExportarExcelAprobados').click(function() {
            const bootcamp = $('#bootcampAprobados').val();

            if (!bootcamp) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'Por favor, seleccione un curso antes de exportar',
                    icon: 'warning'
                });
                return;
            }

            // Mostrar loader con progreso
            Swal.fire({
                title: 'Generando archivo Excel...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mb-2">Procesando datos de estudiantes aprobados...</p>
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" id="exportProgressAprobados">
                            </div>
                        </div>
                        <small class="text-muted">Por favor espere, esto puede tomar unos segundos</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '400px'
            });

            // Simular progreso
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                }
                $('#exportProgressAprobados').css('width', progress + '%');
            }, 200);

            // Realizar la exportación usando AJAX con responseType blob
            $.ajax({
                url: 'components/certCompletion/export_excel_aprobados.php',
                type: 'POST',
                data: { bootcamp: bootcamp },
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.responseType = 'blob';
                    return xhr;
                },
                success: function(data, status, xhr) {
                    clearInterval(progressInterval);
                    $('#exportProgressAprobados').css('width', '100%');

                    const blob = new Blob([data], { 
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                    });
                    
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    
                    const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'estudiantes_aprobados_certificacion.xlsx';
                    
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    Swal.fire({
                        title: '¡Descarga completada!',
                        text: 'El archivo Excel se ha descargado correctamente',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                },
                error: function(xhr, status, error) {
                    clearInterval(progressInterval);
                    
                    console.error('Error en la exportación:', error);
                    
                    Swal.fire({
                        title: 'Error en la exportación',
                        text: 'Hubo un problema al generar el archivo Excel. Por favor, inténtelo nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // Función para cargar los estudiantes aprobados
        const cargarEstudiantesAprobados = () => {
            const bootcamp = $('#bootcampAprobados').val();

            if (!bootcamp) {
                console.log('Por favor, seleccione un curso');
                return;
            }

            // Mostrar loading
            $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Cargando estudiantes aprobados...</td></tr>');
            actualizarBotonExportacionAprobados();

            $.ajax({
                url: 'components/certCompletion/buscar_aprobados.php',
                type: 'POST',
                data: { bootcamp: bootcamp },
                dataType: 'json',
                success: (response) => {
                    if (response && response.html) {
                        $('#listaEstudiantesAprobados tbody').html(response.html);

                        // Actualizar información del curso
                        if (response.courseInfo) {
                            $('#courseModeAprobados').val(response.courseInfo.mode || 'No disponible');
                            $('#courseSedeAprobados').val(response.courseInfo.headquarters || 'No disponible');
                            $('#courseInfoContainerAprobados').show();
                        } else {
                            $('#courseInfoContainerAprobados').hide();
                        }

                        // Actualizar botón de exportación
                        actualizarBotonExportacionAprobados();
                    } else {
                        $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-exclamation-triangle fa-2x text-warning"></i><br><br>No se encontraron estudiantes aprobados para este curso</td></tr>');
                        $('#courseInfoContainerAprobados').hide();
                        actualizarBotonExportacionAprobados();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error en la solicitud:', error);
                    $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-times-circle fa-2x text-danger"></i><br><br>Error al cargar los datos</td></tr>');
                    $('#courseInfoContainerAprobados').hide();
                    actualizarBotonExportacionAprobados();
                }
            });
        };

        // Event listener para cambio de curso
        $('#bootcampAprobados').change(function() {
            const bootcamp = $(this).val();
            
            if (bootcamp) {
                cargarEstudiantesAprobados();
            } else {
                $('#listaEstudiantesAprobados tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-search fa-2x text-muted"></i><br><br>Seleccione un curso para cargar los estudiantes aprobados</td></tr>');
                $('#courseInfoContainerAprobados').hide();
                actualizarBotonExportacionAprobados();
            }
        });
        
        // Función para manejar selección de todos los estudiantes
        $('#selectAllStudents').change(function() {
            const isChecked = $(this).is(':checked');
            $('.student-checkbox').prop('checked', isChecked);
            actualizarContadorSeleccionados();
        });

        // Función para actualizar contador de seleccionados
        function actualizarContadorSeleccionados() {
            const totalSeleccionados = $('.student-checkbox:checked').length;
            const totalEstudiantes = $('.student-checkbox').length;
            
            if (totalSeleccionados > 0) {
                $('#btnEnvioMasivo').html(`<i class="fa fa-paper-plane"></i> Enviar Constancias (${totalSeleccionados})`);
            } else {
                $('#btnEnvioMasivo').html(`<i class="fa fa-paper-plane"></i> Envío Masivo de Constancias`);
            }
            
            // Actualizar estado del checkbox principal
            if (totalSeleccionados === 0) {
                $('#selectAllStudents').prop('indeterminate', false).prop('checked', false);
            } else if (totalSeleccionados === totalEstudiantes) {
                $('#selectAllStudents').prop('indeterminate', false).prop('checked', true);
            } else {
                $('#selectAllStudents').prop('indeterminate', true);
            }
        }

        // Event listener para checkboxes individuales
        $(document).on('change', '.student-checkbox', function() {
            actualizarContadorSeleccionados();
        });

        // Función para envío masivo de constancias
        $('#btnEnvioMasivo').click(function() {
            const estudiantesSeleccionados = [];
            const bootcamp = $('#bootcampAprobados').val();
            const bootcampText = $('#bootcampAprobados option:selected').text();

            if (!bootcamp) {
                Swal.fire({
                    title: 'Error',
                    text: 'Seleccione un curso antes de enviar constancias',
                    icon: 'warning'
                });
                return;
            }

            // Recopilar estudiantes seleccionados
            $('.student-checkbox:checked').each(function() {
                const $row = $(this).closest('tr');
                estudiantesSeleccionados.push({
                    id: $row.data('student-id'),
                    cedula: $row.data('student-cedula'),
                    nombre: $row.data('student-name'),
                    email: $row.data('student-email'),
                    modalidad: $row.data('student-modalidad') || 'presencial',
                    fecha_inicio: $row.data('student-start-date') || '',
                    fecha_fin: $row.data('student-end-date') || ''
                });
            });

            if (estudiantesSeleccionados.length === 0) {
                Swal.fire({
                    title: 'Sin selección',
                    text: 'Seleccione al menos un estudiante para enviar constancias',
                    icon: 'warning'
                });
                return;
            }

            // Confirmar envío masivo
            Swal.fire({
                title: '¿Confirmar envío masivo?',
                html: `
                    <div class="text-start">
                        <p><strong>Curso:</strong> ${bootcampText}</p>
                        <p><strong>Estudiantes seleccionados:</strong> ${estudiantesSeleccionados.length}</p>
                        <p class="text-muted small">Se generarán y enviarán constancias por correo electrónico a todos los estudiantes seleccionados.</p>
                    </div>
                `,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar constancias',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#066aab'
            }).then((result) => {
                if (result.isConfirmed) {
                    enviarConstanciasMasivo(estudiantesSeleccionados, bootcampText);
                }
            });
        });

        // Función para procesar envío masivo
        function enviarConstanciasMasivo(estudiantes, nombreBootcamp) {
            let procesados = 0;
            let exitosos = 0;
            let errores = 0;
            const total = estudiantes.length;
            const resultados = [];

            // Mostrar modal de progreso
            Swal.fire({
                title: 'Enviando Constancias',
                html: `
                    <div class="text-center">
                        <div class="progress mb-3">
                            <div id="progressBarMasivo" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <p id="progressTextMasivo">Iniciando envío...</p>
                        <div id="progressDetailsMasivo" class="mt-3 text-start" style="max-height: 200px; overflow-y: auto;">
                            <!-- Detalles del progreso -->
                        </div>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '600px'
            });

            // Procesar estudiantes secuencialmente
            function procesarSiguienteEstudiante(index = 0) {
                if (index >= estudiantes.length) {
                    // Completado
                    mostrarResultadoFinal();
                    return;
                }

                const estudiante = estudiantes[index];
                const progreso = ((index + 1) / total * 100).toFixed(1);
                
                // Actualizar progreso
                $('#progressBarMasivo').css('width', progreso + '%');
                $('#progressTextMasivo').text(`Procesando ${index + 1} de ${total}: ${estudiante.nombre}`);

                // Enviar solicitud AJAX
                $.ajax({
                    url: 'components/certCompletion/export_complet.php',
                    type: 'POST',
                    data: {
                        nombre_estudiante: estudiante.nombre,
                        cedula: estudiante.cedula,
                        nombre_bootcamp: nombreBootcamp,
                        fecha_inicio: estudiante.fecha_inicio,
                        fecha_fin: estudiante.fecha_fin,
                        modalidad: estudiante.modalidad,
                        schedules: 'N/A',
                        email: estudiante.email
                    },
                    dataType: 'json',
                    timeout: 30000, // 30 segundos timeout
                    success: function(response) {
                        procesados++;
                        if (response.success) {
                            exitosos++;
                            agregarDetalleProgreso(estudiante.nombre, 'success', 'Constancia enviada correctamente');
                        } else {
                            errores++;
                            agregarDetalleProgreso(estudiante.nombre, 'error', response.message || 'Error desconocido');
                        }
                        
                        // Procesar siguiente estudiante
                        setTimeout(() => procesarSiguienteEstudiante(index + 1), 500);
                    },
                    error: function(xhr, status, error) {
                        procesados++;
                        errores++;
                        agregarDetalleProgreso(estudiante.nombre, 'error', `Error de conexión: ${error}`);
                        
                        // Procesar siguiente estudiante
                        setTimeout(() => procesarSiguienteEstudiante(index + 1), 500);
                    }
                });
            }

            // Agregar detalle de progreso
            function agregarDetalleProgreso(nombre, tipo, mensaje) {
                const iconClass = tipo === 'success' ? 'fa-check-circle text-success' : 'fa-times-circle text-danger';
                const detalleHtml = `
                    <div class="d-flex align-items-center mb-1">
                        <i class="fa ${iconClass} me-2"></i>
                        <small><strong>${nombre}:</strong> ${mensaje}</small>
                    </div>
                `;
                $('#progressDetailsMasivo').append(detalleHtml);
                
                // Scroll automático al final
                const container = document.getElementById('progressDetailsMasivo');
                container.scrollTop = container.scrollHeight;
            }

            // Mostrar resultado final
            function mostrarResultadoFinal() {
                let iconType = 'success';
                let title = '¡Envío completado!';
                
                if (errores === total) {
                    iconType = 'error';
                    title = 'Envío fallido';
                } else if (errores > 0) {
                    iconType = 'warning';
                    title = 'Envío completado con advertencias';
                }

                Swal.fire({
                    title: title,
                    html: `
                        <div class="text-center">
                            <div class="mb-3">
                                <div class="row">
                                    <div class="col-4">
                                        <div class="text-success">
                                            <i class="fa fa-check-circle fa-2x"></i>
                                            <br><strong>${exitosos}</strong>
                                            <br><small>Exitosos</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-danger">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                            <br><strong>${errores}</strong>
                                            <br><small>Errores</small>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="text-info">
                                            <i class="fa fa-users fa-2x"></i>
                                            <br><strong>${total}</strong>
                                            <br><small>Total</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    icon: iconType,
                    confirmButtonText: 'Entendido',
                    width: '500px'
                });

                // Desseleccionar todos los checkboxes
                $('.student-checkbox').prop('checked', false);
                $('#selectAllStudents').prop('checked', false);
                actualizarContadorSeleccionados();
            }

            // Iniciar el procesamiento
            procesarSiguienteEstudiante();
        }

        // Actualizar función existente
        function actualizarBotonExportacionAprobados() {
            actualizarBotonesAprobados();
        }
    });
</script>