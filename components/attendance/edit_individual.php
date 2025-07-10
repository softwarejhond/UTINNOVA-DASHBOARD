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

// Función para obtener los datos del estudiante
function getStudentData($student_id, $conn)
{
    $query = "SELECT * FROM groups WHERE number_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Filtrar Inscritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Estilos para la tabla */
        #listaInscritos {
            table-layout: fixed;
        }

        #listaInscritos th,
        #listaInscritos td {
            vertical-align: middle;
            white-space: nowrap;
            text-align: center;
        }

        #listaInscritos th:nth-child(2),
        #listaInscritos td:nth-child(2) {
            white-space: normal !important;
            text-align: left;
        }

        .estado-asistencia {
            width: 20px;
            height: 20px;
            margin: auto;
            display: block;
        }

        .form-container {
            border-right: 1px solid #dee2e6;
            padding-right: 20px;
        }



        @media (max-width: 992px) {
            .form-container {
                border-right: none;
                padding-right: 0;
                border-bottom: 1px solid #dee2e6;
                margin-bottom: 20px;
                padding-bottom: 20px;
            }
        }

        select[readonly] {
            background-color: #e9ecef !important;
            pointer-events: none;
            touch-action: none;
            opacity: 1 !important;
        }

        select.readonly option {
            display: none;
        }

        select.readonly option:checked {
            display: block;
        }
    </style>
</head>

<body>
    <div class="container-fluid mt-4">
        <div class="card shadow">
            <div class="card-header text-white bg-indigo-dark">
                <h5 class="mb-0">Gestión de Asistencia Individual</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <!-- Tabla de datos (lado derecho) -->
                    <div class="col-lg-12 col-md-8 ">
                        <!-- Estudiante -->
                        <div class="mb-3 text-center">
                            <input type="text" id="student_id" class="form-control fs-2 text-center" placeholder="Cédula del estudiante" required autofocus>
                        </div>
                        <div class="table-responsive">
                            <table id="listaInscritos" class="table table-hover table-bordered">
                                <thead>
                                    <tr class="text-center bg-light">
                                        <th width="15%">ID Estudiante</th>
                                        <th>Nombre completo</th>
                                        <th width="10%">Presente</th>
                                        <th width="10%">Tarde</th>
                                        <th width="10%">Ausente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Se llenará dinámicamente -->
                                    <tr>
                                        <td></td>
                                        <td>
                                            Ingrese un ID de estudiante, seleccione un bootcamp y una fecha para ver los registros de asistencia
                                        </td>
                                        <td class="fs-1"></td>
                                        <td class="fs-1"></td>
                                        <td class="fs-1"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- Panel de controles (lado izquierdo) -->
                    <div class="col-lg-6 col-md-6 form-container" style="overflow-y: auto;">

                        <!-- Bootcamp -->
                        <div class="mb-3">
                            <label class="form-label">Clase</label>
                            <select id="bootcamp" class="form-select course-select" disabled>
                                <option value="">Seleccione la</option>
                                <?php 
                                $allowed_categories = [19, 21, 24, 26, 27, 35, 20, 22, 23, 25, 28, 35, 18, 17, 30, 31, 32];
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

                        <!-- Modalidad -->
                        <div class="mb-3">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" id="modalidad" class="form-select" disabled onchange="toggleSede()">
                                <option value="">...</option>
                                <option value="Virtual">Virtual</option>
                                <option value="Presencial">Presencial</option>
                            </select>
                        </div>

                        <!-- Sede -->
                        <div class="mb-3">
                            <label class="form-label">Sede</label>
                            <select name="sede" id="sede" class="form-select" disabled>
                                <option value="">...</option>
                                <?php
                                $headquarters_query = "SELECT name FROM headquarters_attendance ORDER BY name";
                                $headquarters_result = $conn->query($headquarters_query);
                                if ($headquarters_result && $headquarters_result->num_rows > 0) {
                                    while ($headquarters = $headquarters_result->fetch_assoc()) {
                                        echo '<option value="' . htmlspecialchars($headquarters['name']) . '">' . htmlspecialchars($headquarters['name']) . '</option>';
                                    }
                                }
                                ?>
                            </select>
                        </div>


                    </div>
                    <div class="col-lg-6 col-md-6 form-container" style="overflow-y: auto;">


                        <!-- Fecha -->
                        <div class="mb-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="class_date" id="class_date" class="form-control" required max="<?= date('Y-m-d'); ?>">
                        </div>

                        <!-- Tipo de Curso -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tipo de Curso</label>
                            <select name="course_type" id="course_type" class="form-select border-indigo-dark" required>
                                <option value="">Seleccione tipo</option>
                                <option value="bootcamp">Tecnico</option>
                                <option value="english_code">English Code</option>
                                <option value="skills">Habilidades de Poder</option>
                                <option value="leveling_english">Ingles Nivelatorio</option>
                            </select>
                        </div>

                        <!-- Intensidad Horaria -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Intensidad Horaria</label>
                            <input type="number" name="intensity" id="intensity" class="form-control border-indigo-dark" min="1" required>
                        </div>
                        <br>
                    </div>
                    <div class="col-lg-12 col-md-12 form-container" style="overflow-y: auto;">

                        <!-- Botón guardar -->
                        <div class="d-grid">
                            <button id="saveAttendance" class="btn bg-indigo-dark text-white">
                                <i class="fa fa-save"></i> Guardar Asistencia
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    <!-- jQuery para la solicitud AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Deshabilitar los selects inicialmente
            $('#bootcamp, #modalidad, #sede').prop('disabled', true);

            $('#student_id').on('change', function() {
                const studentId = $(this).val();

                if (!studentId) {
                    $('#bootcamp').prop('disabled', true);
                    $('#modalidad, #sede').prop({
                        'disabled': true,
                        'readonly': false
                    });
                    return;
                }

                $.ajax({
                    url: 'components/attendance/get_student_data.php',
                    type: 'POST',
                    data: {
                        student_id: studentId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Habilitar solo el select de bootcamp
                            $('#bootcamp').prop('disabled', false);

                            // Configurar modalidad y sede como readonly visual
                            $('#modalidad, #sede').prop({
                                'disabled': true,
                                'readonly': true
                            }).addClass('bg-light');

                            // Manejo del select de bootcamp
                            $('#bootcamp option').hide();
                            $('#bootcamp option:first').show();
                            response.data.courses.forEach(function(course) {
                                $('#bootcamp option[value="' + course.id + '"]').show();
                            });

                            // Establecer valores de modalidad y sede
                            $('#modalidad').val(response.data.mode);
                            $('#sede').val(response.data.headquarters);

                            // Resetear el valor del bootcamp
                            $('#bootcamp').val('');

                            // Deshabilitar el cambio de modalidad
                            $('#modalidad').off('change');

                            // Aplicar estilos visuales para readonly
                            $('#modalidad, #sede').css({
                                'pointer-events': 'none',
                                'background-color': '#e9ecef',
                                'opacity': '1'
                            });
                        } else {
                            $('#bootcamp').prop('disabled', true);
                            $('#modalidad, #sede').prop({
                                'disabled': true,
                                'readonly': false
                            }).removeClass('bg-light');

                            Swal.fire({
                                icon: 'warning',
                                title: 'No se encontró información',
                                text: 'No se encontró información para el estudiante ingresado'
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        $('#bootcamp').prop('disabled', true);
                        $('#modalidad, #sede').prop({
                            'disabled': true,
                            'readonly': false
                        }).removeClass('bg-light');

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al obtener los datos del estudiante'
                        });
                    }
                });
            });
        });

        // Definir la función buscarAsistencia fuera del documento ready
        function buscarAsistencia() {
            const studentId = $('#student_id').val();
            const courseId = $('#bootcamp').val();
            const classDate = $('#class_date').val();

            if (!studentId || !courseId || !classDate) {
                return;
            }

            $.ajax({
                url: 'components/attendance/buscar_datos_individual.php',
                type: 'POST',
                data: {
                    student_id: studentId,
                    bootcamp: courseId,
                    class_date: classDate
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#listaInscritos tbody').html(response.html);
                        
                        if (response.session_hours !== undefined) {
                            $('#intensity')
                                .val(response.session_hours)
                                .attr('max', response.session_hours)
                                .prop('readonly', true)
                                .addClass('bg-light');
                        }
                        
                        // Habilitar/deshabilitar el botón de guardar
                        $('#saveAttendance').prop('disabled', response.session_hours === 0);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No disponible',
                            text: response.error || 'No es posible tomar asistencia en este día'
                        });
                        $('#intensity').val('');
                        $('#saveAttendance').prop('disabled', true);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar la solicitud'
                    });
                }
            });
        }

        $(document).ready(function() {
            // Eventos para actualizar la tabla
            $('#student_id, #bootcamp, #class_date').on('change', buscarAsistencia);
            
            // Agregar validación para el campo de intensidad
            $('#intensity').on('input', function() {
                const max = parseInt($(this).attr('max'), 10);
                const value = parseInt($(this).val(), 10);
                
                if (value > max) {
                    $(this).val(max);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Valor excedido',
                        text: `La intensidad horaria no puede ser mayor a ${max} horas`
                    });
                }
            });
        });

        function toggleSede() {
            const modalidad = document.getElementById('modalidad').value;
            const sede = document.getElementById('sede');

            if (modalidad === 'virtual') {
                sede.value = 'No aplica';
                sede.disabled = true;
            } else {
                sede.disabled = false;
                if (sede.value === 'No aplica') {
                    sede.value = '';
                }
            }
        }

        // Manejador para el botón de guardar
        $('#saveAttendance').click(function() {
            const attendance = {};
            const courseId = $('#bootcamp').val();
            const classDate = $('#class_date').val();
            const modalidad = $('#modalidad').val();
            const sede = $('#sede').val();
            const courseType = $('#course_type').val(); // Nuevo campo
            const intensity = parseInt($('#intensity').val()); // Nuevo campo

            // Validar que se hayan seleccionado todos los campos necesarios
            if (!courseId || !classDate || !modalidad || !sede || !courseType || !intensity) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos incompletos',
                    text: 'Por favor seleccione todos los campos requeridos (clase, fecha, modalidad, sede, tipo de curso e intensidad)'
                });
                return;
            }

            // Recolectar datos de los radio buttons seleccionados
            $('input[type="radio"]:checked').each(function() {
                const recordId = $(this).data('record-id');
                const estado = $(this).val();
                attendance[recordId] = estado;
            });

            // Verificar si hay datos para guardar
            if (Object.keys(attendance).length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Sin cambios',
                    text: 'No hay cambios para guardar'
                });
                return;
            }

            // Enviar datos al servidor
            $.ajax({
                url: 'components/attendance/guardar_asistencia_individual.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    attendance: attendance,
                    course_id: courseId,
                    class_date: classDate,
                    modalidad: modalidad,
                    sede: sede,
                    course_type: courseType, // Nuevo campo
                    intensity: intensity // Nuevo campo
                }),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Asistencias guardadas correctamente'
                        });
                        // Recargar los datos
                        buscarAsistencia();
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error al guardar: ' + (response.error || 'Error desconocido')
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al procesar la solicitud'
                    });
                }
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>