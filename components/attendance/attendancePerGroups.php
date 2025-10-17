<?php
// Incluir conexión y obtener datos de Moodle
require_once __DIR__ . '/../../controller/conexion.php';

// Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

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

// consulta para obtener los profesoresa 

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Filtrar Inscritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        /* Estilos para la tabla */
        #listaInscritos {
            table-layout: fixed;
        }

        #listaInscritos th,

        .email-cell {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: normal;
            word-wrap: break-word;
            font-size: 0.9em;
            line-height: 1.2;
        }

        #listaInscritos td {
            padding: 0.5rem;
            vertical-align: middle;
        }

        #listaInscritos td:nth-child(3) {
            white-space: normal !important;
            overflow: hidden;
            text-overflow: ellipsis;
        }


        .estado-asistencia {
            width: 25px;
            height: 25px;
            margin: auto;
            display: block;
        }

        #historialContainer {
            margin-top: 20px;
            padding: 15px;
            border-top: 1px solid #dee2e6;
        }

        #historialContainer table {
            font-size: 0.9em;
        }

        #historialContainer th {
            background-color: #f8f9fa;
        }

        .circular-progress {
            position: relative;
            width: 50px;
            height: 50px;
            margin: auto;
        }

        .circular-progress svg {
            transform: rotate(-90deg);
        }

        .circular-progress circle {
            fill: none;
            stroke-width: 8;
        }

        .progress-background {
            stroke: #f0f0f0;
        }

        .progress-bar {
            stroke: #ec008c;
            stroke-linecap: round;
            transition: stroke-dashoffset 0.5s ease;
        }

        .progress-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 12px;
            font-weight: bold;
        }

        .bg-magenta {
            background-color: #ec008c;
        }

        #progressInfo {
            font-size: 0.75rem;
        }

        /* Personalización para mantener el estilo consistente */
        .select2-container--default .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important;
            border: 1px solid #ced4da;
            border-radius: 0.25rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem);
            padding-left: 0.75rem;
            padding-right: 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem);
        }

        /* Asegurar que el dropdown mantenga el mismo ancho */
        .select2-dropdown {
            width: 660px !important;
        }
    </style>
</head>

<body>

    <div class="container-fluid mt-4">

        <div class="card shadow mb-3">
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="row align-items-end">

                        <!-- Seleccionar docente -->
                        <!-- Selección de Bootcamp (Clase) -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Clase</label>
                            <select id="bootcamp" class="form-select course-select">
                                <?php
                                $allowed_categories = [17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 30, 31, 32, 33, 34, 35];
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

                        <!-- Agregar después del select de bootcamp -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Tipo de Curso</label>
                            <select id="courseType" class="form-select">
                                <option value="">Seleccione tipo de curso</option>
                                <option value="bootcamp">Tecnico</option>
                                <option value="leveling_english">Inglés Nivelatorio</option>
                                <option value="english_code">English Code</option>
                                <option value="skills">Habilidas de poder</option>
                            </select>
                        </div>
                        <!-- Selección de Modalidad -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" id="modalidad" class="form-select" onchange="toggleSede()">
                                <option value="">Seleccione modalidad</option>
                                <option value="virtual">Virtual</option>
                                <option value="Presencial">Presencial</option>
                            </select>
                        </div>
                        <!-- Selección de Sede -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Sede</label>
                            <select name="sede" id="sede" class="form-select">
                                <option value="">Seleccione una sede</option>
                                <?php
                                // Consulta para obtener las sedes desde la tabla groups
                                $query = "SELECT DISTINCT headquarters FROM groups ORDER BY headquarters";
                                $result = $conn->query($query);

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $sede = $row['headquarters'];
                                        echo '<option value="' . htmlspecialchars($sede) . '">' . htmlspecialchars($sede) . '</option>';
                                    }
                                } else {
                                    echo '<option value="">No hay sedes disponibles</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <!-- Selección de Fecha -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12" style="padding-bottom: 30px;">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="class_date" id="class_date" class="form-control" required max="<?= date('Y-m-d'); ?>">
                        </div>

                        <!-- Barra de progreso grupal -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Progreso grupal</label>
                            <div class="progress" style="height: 25px;">
                                <div id="progressBar" class="progress-bar bg-magenta" role="progressbar" style="width: 0%;"
                                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                            </div>
                            <p id="progressInfo" class="mt-1 small text-muted">Sin datos</p>
                        </div>

                        <!-- Filtro por estado de asistencia -->
                        <div class="row mb-3 justify-content-center">
                            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                <label class="form-label">Filtrar por estado de asistencia</label>
                                <select id="filtroAsistencia" class="form-select" disabled>
                                    <option value="todos">Todos</option>
                                    <option value="presente">Presente</option>
                                    <option value="tarde">Tarde</option>
                                    <option value="ausente">Ausente</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Añadir después del div que contiene el filtro -->
        <div class="row mb-3 justify-content-center">
            <div class="col-lg-12 col-md-12 col-sm-12 col-12 text-end">
                <button id="exportarGrupo" class="btn btn-success" disabled>
                    <i class="fa-solid fa-file-excel me-1"></i> Exportar Grupo Completo
                </button>
            </div>
        </div>

        <!-- Tabla donde se mostrarán los datos -->
        <div class="table-responsive">
            <table id="listaInscritos" class="table table-hover table-bordered">
                <thead>
                    <tr class="text-center">
                        <th style="width: 3%">#</th>
                        <th style="width: 5%">ID</th>
                        <th style="width: 10%">Número de ID</th>
                        <th style="width: 15%">Nombre completo</th>
                        <th style="width: 20%">Correo institucional</th>
                        <th style="width: 8%">Presente</th>
                        <th style="width: 8%">Tarde</th>
                        <th style="width: 8%">Ausente</th>
                        <th style="width: 8%">Cumplimiento</th>
                        <th style="width: 8%">Horas</th>
                        <th style="width: 10%">Registro</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para registro de ausencia -->
    <div class="modal fade" id="ausenciaModal" tabindex="-1" aria-labelledby="ausenciaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ausenciaModalLabel">Registro de Ausencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="ausenciaForm">
                        <input type="hidden" id="studentId" name="studentId">
                        <input type="hidden" id="classId" name="classId">

                        <div class="row mb-3">
                            <div class="col-12">
                                <h6>Estudiante</h6>
                                <h3 class="text-magenta-dark" id="studentName"></h3>
                                <h6 class="text-muted">C.C: <span id="studentId_display"></span></h6>
                                <button type="button" class="btn btn-info btn-sm" id="verHistorial" title="Ver historial de registros">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">¿Se estableció contacto?</label>
                                <select class="form-select" id="contactEstablished" name="contactEstablished" required>
                                    <option value="">Seleccione una opción</option>
                                    <option value="1">Sí</option>
                                    <option value="0">No</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Compromiso</label>
                                <select class="form-select" id="compromiso" name="compromiso">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Asistirá a la siguiente clase">Asistirá a la siguiente clase</option>
                                    <option value="Cambio de horario">Cambio de horario</option>
                                    <option value="Cambio de programa">Cambio de programa</option>
                                    <option value="Tutoría Virtual">Tutoría Virtual</option>
                                    <option value="Tutoria Presencial">Tutoria Presencial</option>
                                    <option value="Clase grabada Virtual">Clase grabada Virtual</option>
                                    <option value="Autoreporte">Autoreporte</option>
                                    <option value="Maratón de Retos">Maratón de Retos</option>
                                    <option value="Auxilio de Transporte">Auxilio de Transporte</option>
                                    <option value="Auxilio de conectividad">Auxilio de conectividad</option>
                                    <option value="No se estableció contacto">No se establecio contacto</option>
                                    <option value="Cambio de nivel">Cambio de nivel</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Seguimiento de Compromiso</label>
                                <select class="form-select" id="seguimientoCompromiso" name="seguimientoCompromiso">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Cumplió">Cumplió</option>
                                    <option value="Requiere acompañamiento / Alerta">Requiere acompañamiento / Alerta</option>
                                    <option value="Estratégia Psicosocial">Estratégia Psicosocial</option>
                                    <option value="No se estableció contacto">No se estableció contacto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Retiro</label>
                                <select class="form-select" id="retiro" name="retiro">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Seguimiento detallado">Seguimiento detallado</option>
                                    <option value="Retiro">Retiro</option>
                                    <option value="No aplica">No aplica</option>
                                    <option value="No se estableció contacto">No se establecio contacto</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Motivo de Retiro</label>
                                <select class="form-select" id="motivoRetiro" name="motivoRetiro">
                                    <option value="">Seleccione una opción</option>
                                    <option value="Laboral">Laboral</option>
                                    <option value="Psicosocial">Psicosocial</option>
                                    <option value="Académico">Académico</option>
                                    <option value="Tiempo de destinación">Tiempo de destinación</option>
                                    <option value="No aplica">No aplica</option>
                                    <option value="Otro">Otro</option>
                                    <option value="No se estableció contacto">No se establecio contacto</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label">Observaciones</label>
                                <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>

                <div id="historialContainer" style="display: none;">
                    <hr>
                    <h4>Historial de Seguimiento</h4>
                    <button type="button" class="btn btn-success mb-3" id="exportarHistorial">
                        <i class="bi bi-file-earmark-excel"></i> Exportar Historial
                    </button>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Contacto</th>
                                    <th>Compromiso</th>
                                    <th>Seguimiento</th>
                                    <th>Retiro</th>
                                    <th>Motivo</th>
                                    <th>Observación</th>
                                    <th>Asesor</th>
                                    <th>Fecha de registro</th>
                                </tr>
                            </thead>
                            <tbody id="historialBody">
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn bg-magenta-dark text-white" id="guardarAusencia">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery para la solicitud AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function animateProgressBars() {
                document.querySelectorAll('.progress-bar').forEach(circle => {
                    const offset = circle.getAttribute('stroke-dashoffset');
                    circle.style.strokeDashoffset = circle.getAttribute('stroke-dasharray');
                    setTimeout(() => {
                        circle.style.strokeDashoffset = offset;
                    }, 100);
                });
            }

            // Llamar a la animación cuando se actualice la tabla
            const observer = new MutationObserver(animateProgressBars);
            observer.observe(document.querySelector('#listaInscritos tbody'), {
                childList: true
            });
        });

        // Función para actualizar el contador de resultados
        function actualizarContadorResultados() {
            const totalRows = $('#listaInscritos tbody tr').length;
            const visibleRows = $('#listaInscritos tbody tr:visible').length;
            const estado = $('#filtroAsistencia').val();

            let mensaje = '';
            if (estado === 'todos') {
                mensaje = `Mostrando ${totalRows} registros totales`;
            } else {
                mensaje = `Mostrando ${visibleRows} de ${totalRows} registros (filtrado por: ${estado})`;
            }

            // Agregar o actualizar el elemento que muestra el contador
            if ($('#contadorResultados').length === 0) {
                $('#listaInscritos').before('<div id="contadorResultados" class="text-muted mb-2"></div>');
            }
            $('#contadorResultados').text(mensaje);
        }

        // Función para filtrar la tabla por estado de asistencia
        function filtrarPorEstado() {
            const estado = $('#filtroAsistencia').val();
            const rows = $('#listaInscritos tbody tr');

            // Mostrar todas las filas si se selecciona "todos"
            if (estado === 'todos') {
                rows.show();
                actualizarContadorResultados(); // Asegurar que se actualice el contador
                return;
            }

            // Filtrar filas según el estado seleccionado
            rows.each(function() {
                const row = $(this);
                const radioChecked = row.find(`input[type="radio"][data-estado="${estado}"]:checked`);

                if (radioChecked.length > 0) {
                    row.show();
                } else {
                    row.hide();
                }
            });

            // Actualizar el contador después de filtrar
            actualizarContadorResultados();
        }

        $(document).ready(function() {
            $('#bootcamp').select2({
                placeholder: "Buscar y seleccionar curso...",
                allowClear: true,
                width: '100%', // Mantiene el ancho original del select
                dropdownAutoWidth: false, // Evita que cambie el ancho automáticamente
                minimumInputLength: 0, // Permite buscar desde el primer carácter
                language: {
                    noResults: function() {
                        return "No se encontraron cursos";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                }
            });

            // Función para habilitar o deshabilitar la sede según la modalidad
            const toggleSede = () => {
                const modalidad = $('#modalidad').val();
                $('#sede').prop('disabled', modalidad === 'virtual');
                if (modalidad === 'virtual') {
                    $('#sede').val('No aplica');
                }
            };

            // Hacer la función global para que el onchange del select la encuentre
            window.toggleSede = toggleSede;

            // Función para exportar el grupo completo
            $('#exportarGrupo').click(function() {
                const bootcamp = $('#bootcamp').val();
                const courseType = $('#courseType').val();
                const modalidad = $('#modalidad').val();
                const sede = $('#sede').val();
                const class_date = $('#class_date').val();
                
                // Validar que todos los campos necesarios estén completos
                if (!bootcamp || !courseType || !modalidad || !class_date) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar todos los campos requeridos para exportar'
                    });
                    return;
                }
                
                // Construir URL de exportación con parámetros
                const exportUrl = `components/attendance/exportar_grupo.php?bootcamp=${bootcamp}&courseType=${courseType}&modalidad=${modalidad}&sede=${encodeURIComponent(sede)}&class_date=${class_date}`;
                
                // Mostrar Swal con loader mientras se genera el archivo
                Swal.fire({
                    title: 'Generando archivo Excel',
                    text: 'Por favor espere mientras se genera el reporte...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                        
                        // Realizar petición AJAX para obtener el archivo
                        fetch(exportUrl)
                            .then(response => {
                                if (!response.ok) {
                                    throw new Error('Error en la generación del archivo');
                                }
                                return response.blob();
                            })
                            .then(blob => {
                                // Crear URL para el blob
                                const url = window.URL.createObjectURL(blob);
                                
                                // Crear elemento a para descargar
                                const a = document.createElement('a');
                                a.style.display = 'none';
                                a.href = url;
                                a.download = `asistencia_grupo_${bootcamp}_${class_date}.xlsx`;
                                
                                // Agregar a DOM, hacer clic y eliminar
                                document.body.appendChild(a);
                                a.click();
                                
                                // Liberar recursos
                                window.URL.revokeObjectURL(url);
                                setTimeout(() => {
                                    document.body.removeChild(a);
                                    Swal.close(); // Cerrar el Swal cuando la descarga comience
                                }, 100);
                            })
                            .catch(error => {
                                console.error('Error al descargar:', error);
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'Ocurrió un error al generar el archivo de Excel'
                                });
                            });
                    }
                });
            });

            // Modificar la función updateTable para habilitar el botón de exportación
            const updateTable = () => {
                const data = {
                    bootcamp: $('#bootcamp').val(),
                    courseType: $('#courseType').val(),
                    modalidad: $('#modalidad').val(),
                    sede: $('#sede').val(),
                    class_date: $('#class_date').val()
                };

                // Verificar que todos los campos requeridos tengan valor
                if (!data.bootcamp || !data.courseType || !data.modalidad || !data.sede || !data.class_date) {
                    console.log('Por favor, complete todos los campos');
                    return;
                }

                $.ajax({
                    url: 'components/attendance/buscar_datos_grupales.php',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: (response) => {
                        if (response && response.html) {
                            $('#listaInscritos tbody').html(response.html);

                            // Habilitar el filtro y botón de exportación si hay datos
                            $('#filtroAsistencia').prop('disabled', false);
                            $('#exportarGrupo').prop('disabled', false);  // Habilitar botón exportar

                            // Actualizar la barra de progreso
                            if (response.progressInfo) {
                                const progress = response.progressInfo.percent;
                                const avgHours = response.progressInfo.avgHours;
                                const totalHours = response.progressInfo.totalHours;

                                $('#progressBar').css('width', progress + '%').attr('aria-valuenow', progress).text(progress + '%');
                                $('#progressInfo').text(`Horas: ${avgHours} de ${totalHours} horas (${progress}%)`);

                                // Mantener el color magenta en la barra de progreso independiente del porcentaje
                                $('#progressBar').removeClass('bg-danger bg-warning bg-success').addClass('bg-magenta');
                            } else {
                                // Resetear la barra de progreso si no hay datos
                                $('#progressBar').css('width', '0%').attr('aria-valuenow', 0).text('0%');
                                $('#progressInfo').text('Sin datos');
                            }
                        } else {
                            $('#listaInscritos tbody').html('<tr><td colspan="10" class="text-center">No se encontraron registros</td></tr>');
                            // Deshabilitar el filtro y botón de exportación si no hay datos
                            $('#filtroAsistencia').prop('disabled', true).val('todos');
                            $('#exportarGrupo').prop('disabled', true);  // Deshabilitar botón exportar
                        }

                        actualizarContadorResultados();
                    },
                    error: (xhr, status, error) => {
                        console.error('Error en la solicitud:', error);
                        $('#listaInscritos tbody').html('<tr><td colspan="10" class="text-center">Error al cargar los datos</td></tr>');
                        // Deshabilitar el filtro y botón de exportación en caso de error
                        $('#filtroAsistencia').prop('disabled', true).val('todos');
                        $('#exportarGrupo').prop('disabled', true);  // Deshabilitar botón exportar
                    }
                });
            };

            // Actualizar la tabla cuando se cambie algún filtro
            $('#modalidad').change(function() {
                toggleSede();
                updateTable();
            });
            $('#bootcamp, #courseType, #sede, #class_date').change(updateTable);

            // Ejecutar toggleSede al cargar la página
            toggleSede();

            // Agregar el evento change para el filtro de asistencia
            $('#filtroAsistencia').change(function() {
                filtrarPorEstado();
            });

            // Resetear el filtro cuando se cambien los criterios de búsqueda
            $('#modalidad, #bootcamp, #courseType, #sede, #class_date').change(function() {
                $('#filtroAsistencia').val('todos');
                updateTable();
                actualizarContadorResultados(); // Asegurar que se actualice el contador
            });

            // Función para manejar la selección de contacto
            $('#contactEstablished').change(function() {
                const noContacto = $(this).val() === '0';
                const noContactoText = 'No se estableció contacto';

                if (noContacto) {
                    $('#compromiso').val(noContactoText);
                    $('#seguimientoCompromiso').val(noContactoText);
                    $('#retiro').val(noContactoText);
                    $('#motivoRetiro').val(noContactoText);

                    // Deshabilitar los demás selects
                    $('#compromiso, #seguimientoCompromiso, #retiro, #motivoRetiro').prop('disabled', true);
                } else {
                    // Limpiar y habilitar los selects
                    $('#compromiso, #seguimientoCompromiso, #retiro, #motivoRetiro').val('').prop('disabled', false);
                }
            });

            // Reemplazar el evento click del botón registrar-ausencia
            $(document).on('click', '.registrar-ausencia', function(e) {
                e.preventDefault();
                const button = $(this);
                const attendanceStatus = button.data('attendance-status');
                const studentName = button.data('student-name');
                const studentId = button.data('student-id');
                const classId = $('#bootcamp').val();
                const classDate = $('#class_date').val();

                const showModalWithData = (data = null) => {
                    // Llenar datos básicos del modal
                    $('#studentId').val(studentId);
                    $('#studentName').text(studentName);
                    $('#studentId_display').text(studentId);
                    $('#classId').val(classId);

                    // Si hay datos existentes, rellenar el formulario
                    if (data) {
                        $('#contactEstablished').val(data.contact_established).trigger('change');
                        $('#compromiso').val(data.compromiso);
                        $('#seguimientoCompromiso').val(data.seguimiento_compromiso);
                        $('#retiro').val(data.retiro);
                        $('#motivoRetiro').val(data.motivo_retiro);
                        $('#observacion').val(data.observacion);
                    }
                    
                    $('#ausenciaModal').modal('show');
                };

                const fetchAndShowModal = () => {
                    $.ajax({
                        url: 'components/attendance/get_absence_log.php',
                        type: 'POST',
                        data: { studentId, classId, classDate },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                showModalWithData(response.data);
                            } else {
                                Swal.fire('Error', 'No se pudo cargar la información previa.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Problema de conexión al buscar información previa.', 'error');
                        }
                    });
                };

                if (attendanceStatus === 'presente') {
                    Swal.fire({
                        title: '¡Atención!',
                        text: `El estudiante ${studentName} está marcado como PRESENTE. ¿Deseas continuar?`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Sí, continuar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            fetchAndShowModal();
                        }
                    });
                } else {
                    fetchAndShowModal();
                }
            });

            // Reemplaza la función existente del historial
            $('#verHistorial').click(function() {
                const studentId = $('#studentId').val();
                const studentName = $('#studentName').text();

                // Validar que exista un ID de estudiante
                if (!studentId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo identificar el estudiante seleccionado'
                    });
                    return;
                }

                $.ajax({
                    url: 'components/attendance/historial_ausencias.php',
                    type: 'POST',
                    data: {
                        studentId: studentId
                    },
                    dataType: 'json',
                    beforeSend: function() {
                        // Mostrar loading
                        Swal.fire({
                            title: 'Cargando historial',
                            text: `Consultando registros de ${studentName}`,
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                    },
                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            let html = '';
                            if (response.data && response.data.length > 0) {
                                response.data.forEach(registro => {
                                    html += `
                                        <tr>
                                            <td>${registro.class_date || 'N/A'}</td>
                                            <td>${registro.contact_established == 1 ? 'Sí' : 'No'}</td>
                                            <td>${registro.compromiso || '-'}</td>
                                            <td>${registro.seguimiento_compromiso || '-'}</td>
                                            <td>${registro.retiro || '-'}</td>
                                            <td>${registro.motivo_retiro || '-'}</td>
                                            <td>${registro.observacion || '-'}</td>
                                            <td>${registro.advisor_name || '-'}</td>
                                            <td>${registro.creation_date || '-'}</td>
                                        </tr>
                                    `;
                                });
                            } else {
                                html = '<tr><td colspan="7" class="text-center">No hay registros previos para este estudiante</td></tr>';
                            }

                            $('#historialBody').html(html);
                            $('#historialContainer').slideDown();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al cargar el historial',
                                html: `
                                    <div style="text-align: left">
                                        <p><strong>Tipo de error:</strong> ${response.error.type || 'Desconocido'}</p>
                                        <p><strong>Mensaje:</strong> ${response.error.message || 'No hay detalles disponibles'}</p>
                                    </div>
                                `
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();

                        let errorMessage = 'Error desconocido al cargar el historial';
                        let errorDetail = '';

                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || errorMessage;
                            errorDetail = response.detail || '';
                        } catch (e) {
                            errorDetail = error;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error al cargar el historial',
                            html: `
                                <div style="text-align: left">
                                    <p><strong>Estado de la solicitud:</strong> ${status}</p>
                                    <p><strong>Mensaje de error:</strong> ${errorMessage}</p>
                                    ${errorDetail ? `<p><strong>Detalle:</strong> ${errorDetail}</p>` : ''}
                                    <p><small>Si el problema persiste, contacte al administrador</small></p>
                                </div>
                            `
                        });

                        // Log para debugging
                        console.error('Error en la solicitud de historial:', {
                            status: status,
                            error: error,
                            response: xhr.responseText,
                            xhr: xhr
                        });
                    },
                    complete: function() {
                        // Asegurar que el loading se cierre
                        if (Swal.isLoading()) {
                            Swal.close();
                        }
                    }
                });
            });

            $('#exportarHistorial').click(function() {
                const studentId = $('#studentId').val();
                const studentName = $('#studentName').text();

                if (!studentId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo identificar el estudiante'
                    });
                    return;
                }

                // Crear la URL para la exportación
                const exportUrl = `components/attendance/exportar_historial.php?studentId=${encodeURIComponent(studentId)}&studentName=${encodeURIComponent(studentName)}`;

                // Abrir en una nueva ventana/pestaña
                window.open(exportUrl, '_blank');
            });

            // Agregar después del evento click del botón exportarHistorial
            $('#guardarAusencia').click(function() {
                const form = $('#ausenciaForm');
                
                // Validar campos requeridos
                const contactEstablished = $('#contactEstablished').val();
                if (!contactEstablished) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Debe seleccionar si se estableció contacto'
                    });
                    return;
                }

                // Recopilar datos del formulario
                const formData = {
                    studentId: $('#studentId').val(),
                    classId: $('#classId').val(),
                    contactEstablished: contactEstablished,
                    compromiso: $('#compromiso').val(),
                    seguimientoCompromiso: $('#seguimientoCompromiso').val(),
                    retiro: $('#retiro').val(),
                    motivoRetiro: $('#motivoRetiro').val(),
                    observacion: $('#observacion').val(),
                    classDate: $('#class_date').val()
                };

                // Mostrar loading
                Swal.fire({
                    title: 'Guardando registro',
                    text: 'Por favor espere...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Enviar datos via AJAX
                $.ajax({
                    url: 'components/attendance/guardar_ausencia.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'El registro de ausencia se ha guardado correctamente',
                                timer: 2000,
                                showConfirmButton: false
                            }).then(() => {
                                // Cerrar modal y limpiar formulario
                                $('#ausenciaModal').modal('hide');
                                $('#ausenciaForm')[0].reset();
                                $('#historialContainer').hide();
                                
                                // Actualizar la tabla para reflejar los cambios
                                updateTable();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error al guardar',
                                text: response.error || 'Ocurrió un error al guardar el registro'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close();
                        
                        let errorMessage = 'Error al conectar con el servidor';
                        try {
                            const response = JSON.parse(xhr.responseText);
                            errorMessage = response.error || errorMessage;
                        } catch (e) {
                            errorMessage = error;
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                        
                        console.error('Error en la solicitud:', {
                            status: status,
                            error: error,
                            response: xhr.responseText
                        });
                    }
                });
            });

            // Limpiar formulario cuando se cierre el modal
            $('#ausenciaModal').on('hidden.bs.modal', function() {
                $('#ausenciaForm')[0].reset();
                $('#historialContainer').hide();
                // Habilitar todos los selects que podrían estar deshabilitados
                $('#compromiso, #seguimientoCompromiso, #retiro, #motivoRetiro').prop('disabled', false);
            });
        });
    </script>
</body>

</html>