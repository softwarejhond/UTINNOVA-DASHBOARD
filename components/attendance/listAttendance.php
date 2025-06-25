<?php
// Incluir conexión
require_once __DIR__ . '/../../controller/conexion.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Función para obtener cursos desde la base de datos local
function getCoursesFromDB($conn) {
    // Verificar que el usuario esté logueado
    if (!isset($_SESSION['username'])) {
        return [];
    }
    
    $current_user = $_SESSION['username'];
    
    $sql = "SELECT c.*, u.nombre as teacher_name 
            FROM courses c 
            LEFT JOIN users u ON c.teacher = u.username 
            WHERE c.status = '1' 
            AND (c.teacher = ? OR c.mentor = ? OR c.monitor = ?)
            ORDER BY c.code ASC";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("sss", $current_user, $current_user, $current_user);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $courses[] = $row;
        }
    }
    
    $stmt->close();
    return $courses;
}

// Obtener cursos desde la BD local
$courses_data = getCoursesFromDB($conn);

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
    </style>
</head>

<body>

    <div class="container-fluid mt-4">

        <?php if (empty($courses_data)): ?>
            <div class="alert alert-warning" role="alert">
                <i class="bi bi-exclamation-triangle"></i>
                No tienes cursos asignados como docente, mentor o monitor. Contacta al administrador si crees que esto es un error.
            </div>
        <?php endif; ?>

        <div class="card shadow mb-3">
            <div class="card-body rounded-0">
                <div class="container-fluid">
                    <div class="row align-items-end">
                        <!-- Selección de Bootcamp (Clase) -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Clase</label>
                            <select id="bootcamp" class="form-select course-select" <?= empty($courses_data) ? 'disabled' : '' ?>>
                                <option value="">Seleccione un curso</option>
                                <?php if (!empty($courses_data)): ?>
                                    <?php foreach ($courses_data as $course): ?>
                                        <option value="<?= htmlspecialchars($course['code']) ?>" 
                                                data-teacher-name="<?= htmlspecialchars($course['teacher_name'] ?? 'Sin asignar') ?>">
                                            <?= htmlspecialchars($course['code'] . ' - ' . $course['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <!-- Agregar después del select de bootcamp -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Tipo de Curso</label>
                            <select id="courseType" class="form-select">
                                <option value="">Seleccione tipo de curso</option>
                                <option value="bootcamp">Técnico</option>
                                <option value="leveling_english">Inglés nivelatorio</option>
                                <option value="english_code">English Code</option>
                                <option value="skills">Habilidades de poder</option>
                            </select>
                        </div>
                        <!-- Selección de Modalidad -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                            <label class="form-label">Modalidad</label>
                            <select name="modalidad" id="modalidad" class="form-select" onchange="toggleSede()">
                                <option value="">Seleccione modalidad</option>
                                <?php
                                $modalidades = ['Virtual' => 'Virtual', 'Presencial' => 'Presencial'];
                                foreach ($modalidades as $valor => $texto): ?>
                                    <option value="<?= htmlspecialchars($valor) ?>"><?= htmlspecialchars($texto) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Selección de Sede -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Sede</label>
                            <select name="sede" id="sede" class="form-select">
                                <option value="">Seleccione una sede</option>
                                <?php
                                // Consulta para obtener las sedes
                                $query = "SELECT name FROM headquarters_attendance ORDER BY name";
                                $result = $conn->query($query);

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $sede = htmlspecialchars($row['name']);
                                        echo "<option value=\"$sede\">$sede</option>";
                                    }
                                } else {
                                    echo "<option value=\"\">No hay sedes disponibles</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Selección de Fecha -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Fecha</label>
                            <input type="date" name="class_date" id="class_date" class="form-control" required max="<?= date('Y-m-d'); ?>">
                        </div>

                        <!-- Mostrar horas por sesión (solo informativo) -->
                        <div class="col-lg-6 col-md-6 col-sm-12 col-12"><br>
                            <label class="form-label">Horas por sesión</label>
                            <input type="text" id="session_hours_display" class="form-control" readonly>
                            <!-- Campo oculto para almacenar el valor -->
                            <input type="hidden" id="session_hours" name="session_hours" value="0">
                        </div>

                        <!-- Título con nombre del usuario -->
                        <div class="col-12 mb-3"><br>
                            <h4 id="teacher-display">
                                Docente: Seleccione un curso
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-3 d-flex gap-3">
            <button id="saveAttendance" class="btn bg-magenta-dark text-white">
                <i class="fa fa-save text-white"></i> Guardar Asistencias
            </button>

            <!-- Botón para abrir el nuevo modal de Exportación -->
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exportModal" id="exportBtn" disabled>
                <i class="bi bi-file-earmark-excel"></i> Generar Informe Mensual
            </button>

            <form id="exportarListadoForm" method="POST" action="components/attendance/exportar_listado.php" style="display:inline;">
                <input type="hidden" id="export_list_course_id" name="course_id">
                <input type="hidden" id="export_list_course_type" name="course_type">
                <input type="hidden" id="export_list_course_name" name="course_name">
                <button type="button" id="exportarExcel" class="btn bg-indigo-dark text-white" disabled>	
                    <i class="bi bi-file-earmark-excel"></i> Exportar grupo
                </button>
            </form>
        </div>

        <!-- Modal para exportar informe mensual -->
        <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exportModalLabel">Exportar Informe Mensual</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <div class="card shadow mb-3 mt-4">
                            <div class="card-body">
                                <h5 class="card-title">Exportar Informe Mensual</h5>
                                <form id="exportForm" method="POST" action="components/attendance/exportar_informe.php">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="month" class="form-label">Mes</label>
                                            <select id="month" name="month" class="form-select" required>
                                                <option value="1">Enero</option>
                                                <option value="2">Febrero</option>
                                                <option value="3">Marzo</option>
                                                <option value="4">Abril</option>
                                                <option value="5">Mayo</option>
                                                <option value="6">Junio</option>
                                                <option value="7">Julio</option>
                                                <option value="8">Agosto</option>
                                                <option value="9">Septiembre</option>
                                                <option value="10">Octubre</option>
                                                <option value="11">Noviembre</option>
                                                <option value="12">Diciembre</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="year" class="form-label">Año</label>
                                            <input type="number" id="year" name="year" class="form-control" min="2020" max="<?= date('Y'); ?>" value="<?= date('Y'); ?>" required>
                                        </div>
                                        <!-- Campos ocultos para pasar valores del formulario principal -->
                                        <input type="hidden" id="export_course_id" name="course_id">
                                        <input type="hidden" id="export_course_type" name="course_type">
                                        <input type="hidden" id="export_course_name" name="course_name">
                                        <div class="col-md-12 mt-3 text-end">
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-download"></i> Exportar Excel
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <!-- Tabla donde se mostrarán los datos -->
        <div class="table-responsive">
            <table id="listaInscritos" class="table table-hover table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>ID</th>
                        <th>Número de ID</th>
                        <th style="min-width: 350px;">Nombre completo</th>
                        <th style="min-width: 350px;">Correo institucional</th>
                        <th style="width: 8%">Presente</th>
                        <th style="width: 8%">Tarde</th>
                        <th style="width: 8%">Ausente</th>
                        <th style="width: 8%">Cumplimiento</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Se llenará dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- jQuery para la solicitud AJAX -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            
            // Función para actualizar el nombre del docente cuando se selecciona un curso
            $('#bootcamp').change(function() {
                const selectedOption = $(this).find('option:selected');
                const teacherName = selectedOption.data('teacher-name');
                
                if (teacherName && teacherName !== 'Sin asignar') {
                    $('#teacher-display').text('Docente: ' + teacherName);
                } else {
                    $('#teacher-display').text('Docente: Sin asignar');
                }
                
                validateExportButton();
                updateTable();
            });

            // Función para habilitar o deshabilitar la sede según la modalidad
            const toggleSede = () => {
                const modalidad = $('#modalidad').val();
                $('#sede').prop('disabled', modalidad === 'Virtual');
                if (modalidad === 'Virtual') {
                    $('#sede').val('No aplica');
                }
            };

            // Hacer la función global para que el onchange del select la encuentre
            window.toggleSede = toggleSede;

            // Función para validar los campos y habilitar los botones de exportar
            const validateExportButton = () => {
                const bootcamp = $('#bootcamp').val();
                const courseType = $('#courseType').val();
                const modalidad = $('#modalidad').val();
                const sede = $('#sede').val();
                const fecha = $('#class_date').val();
                
                // Habilitar los botones si todos los campos están completos
                if (bootcamp && courseType && modalidad && sede && fecha) {
                    $('#exportBtn').prop('disabled', false);
                    $('#exportarExcel').prop('disabled', false);
                } else {
                    $('#exportBtn').prop('disabled', true);
                    $('#exportarExcel').prop('disabled', true);
                }
            };

            // Función para actualizar la tabla
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
                    url: 'components/attendance/buscar_datos.php',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: (response) => {
                        // Verificar si hay error por día sin horas asignadas
                        if (response.error && response.message) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No se puede tomar asistencia',
                                text: response.message
                            });
                            $('#saveAttendance').prop('disabled', true);
                            $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">' + response.message + '</td></tr>');
                            return;
                        }

                        // Verificar si ya existe un registro de asistencia
                        if (response.exists) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Asistencia ya registrada',
                                text: response.message,
                                confirmButtonText: 'Entendido'
                            });
                            $('#saveAttendance').prop('disabled', true);
                            $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">Ya existe asistencia registrada para este curso en esta fecha</td></tr>');
                            return;
                        }

                        // Resetear el estado del botón de guardar (por si antes estaba deshabilitado)
                        $('#saveAttendance').prop('disabled', false);
                        
                        // El resto del código se mantiene igual
                        if (response && response.html) {
                            $('#listaInscritos tbody').html(response.html);
                            
                            // Actualizar el nombre del profesor si está disponible
                            if (response.teacher_name) {
                                $('#teacher-display').html('Docente: ' + response.teacher_name);
                            }
                            
                            // Almacenar el ID del profesor para usarlo al guardar
                            $('#saveAttendance').data('teacher-id', response.teacher_id);
                            
                            // Actualizar y mostrar horas por sesión
                            $('#session_hours').val(response.session_hours);
                            $('#session_hours_display').val(response.session_hours);
                            
                            // Habilitar el botón de exportar cuando hay datos
                            $('#exportBtn').prop('disabled', false);
                        } else {
                            $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>');
                            // Mantener el botón habilitado si hay fecha seleccionada
                            validateExportButton();
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('Error en la solicitud:', error);
                        $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">Error al cargar los datos</td></tr>');
                    }
                });
            };

            // Actualizar la tabla cuando se cambie algún filtro
            $('#modalidad').change(function() {
                toggleSede();
                validateExportButton();
                updateTable();
            });

            // Verificar si se deben habilitar los botones cuando cambian los campos relevantes
            $('#courseType, #class_date, #sede').change(function() {
                validateExportButton();
                updateTable();
            });

            // También verificar cuando cambian los otros campos pero solo para actualizar la tabla
            $('#sede').change(updateTable);

            // Ejecutar toggleSede al cargar la página
            toggleSede();

            // Verificar estado inicial del botón de exportar
            validateExportButton();
        });

        $('#saveAttendance').click(function() {
            const attendanceData = {};
            const intensityData = {}; // Mantener para compatibilidad
            // Usar el valor de session_hours en lugar del select
            const sessionHours = parseInt($('#session_hours').val()); 

            $('input[type="radio"]:checked').each(function() {
                const studentId = $(this).attr('name').split('_')[2];
                const status = $(this).data('estado');
                const cumplimiento = $(`input[name="cumplimiento_${studentId}"]`).val(); 

                attendanceData[studentId] = status;
                intensityData[studentId] = parseInt(cumplimiento); 
            });

            const postData = {
                course_id: $('#bootcamp').val(),
                modalidad: $('#modalidad').val(),
                sede: $('#sede').val(),
                class_date: $('#class_date').val(),
                course_type: $('#courseType').val(),
                attendance: attendanceData,
                intensity_data: intensityData,
                selected_intensity: sessionHours // Usar session_hours
            };

            $.ajax({
                url: 'components/attendance/guardar_asistencia.php',
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(postData),
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: 'Asistencias guardadas correctamente'
                        }).then((result) => {
                            // Recargar la página después de cerrar el alert
                            location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.error || 'Error desconocido'
                        });
                    }
                },
                error: function(xhr) {
                    alert('Error en la solicitud: ' + xhr.responseText);
                }
            });
        });

        // Manejar cambios en los radio buttons de asistencia
        $(document).on('change', '.estado-asistencia', function() {
            const studentId = $(this).data('student-id');
            const estado = $(this).data('estado');
            const cumplimientoInput = $(`input[name="cumplimiento_${studentId}"]`);
            const maxValue = cumplimientoInput.data('max');
            const currentHours = parseInt(cumplimientoInput.data('current-hours'));
            const maxAllowed = parseInt(cumplimientoInput.data('max-allowed'));
            const remainingHours = maxAllowed - currentHours;

            // Si no quedan horas disponibles, mostrar advertencia pero permitir marcar asistencia
            if (remainingHours <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Aviso',
                    text: 'Este estudiante ya ha completado el máximo de horas permitidas. No se registrarán más horas.'
                });
                cumplimientoInput.val(0);
                return;
            }

            // Ajustar el valor de cumplimiento según el estado
            switch (estado) {
                case 'presente':
                    cumplimientoInput.val(Math.min(maxValue, remainingHours));
                    cumplimientoInput.prop('readonly', true);
                    break;
                case 'tarde':
                    const maxPosible = Math.min(maxValue, remainingHours);
                    cumplimientoInput.val(maxPosible);
                    cumplimientoInput.prop('readonly', false);
                    cumplimientoInput.attr('max', maxPosible);
                    cumplimientoInput.on('input', function() {
                        if (parseFloat($(this).val()) > maxPosible) {
                            $(this).val(maxPosible);
                        }
                    });
                    break;
                case 'ausente':
                    cumplimientoInput.val(0);
                    cumplimientoInput.prop('readonly', true);
                    break;
            }
        });

        // Pasar valores al modal de exportación
        $('#exportModal').on('show.bs.modal', function(e) {
            // Obtener el ID del curso seleccionado
            const courseId = $('#bootcamp').val();
            $('#export_course_id').val(courseId);

            // Obtener el tipo de curso seleccionado
            const courseType = $('#courseType').val();
            $('#export_course_type').val(courseType);

            // Obtener el nombre del curso seleccionado
            const courseOption = $('#bootcamp option:selected');
            const courseName = courseOption.text();
            $('#export_course_name').val(courseName.split(' - ')[1] || courseName);
        });

        $('#exportarExcel').click(function() {
            // Verificar que se haya seleccionado un curso y tipo de curso
            const courseId = $('#bootcamp').val();
            const courseType = $('#courseType').val();

            if (!courseId || !courseType) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Información incompleta',
                    text: 'Por favor, seleccione un curso y tipo de curso para exportar'
                });
                return;
            }

            // Obtener el nombre del curso seleccionado
            const courseOption = $('#bootcamp option:selected');
            const courseName = courseOption.text().split(' - ')[1] || courseOption.text();

            // Establecer los valores en el formulario
            $('#export_list_course_id').val(courseId);
            $('#export_list_course_type').val(courseType);
            $('#export_list_course_name').val(courseName);

            // Enviar el formulario
            $('#exportarListadoForm').submit();
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Agregar este script justo antes del cierre de </body> -->
    <script>
        // Mostrar alerta informativa cuando carga la página
        $(document).ready(function() {
            // Alerta informativa al cargar la página
            Swal.fire({
                icon: 'info',
                title: 'Recordatorio',
                text: 'Solo puede registrar asistencia una vez por grupo en cada fecha. Por favor, asegúrese de completar toda la información correctamente.',
                confirmButtonText: 'Entendido'
            });
            
            // Resto del código existente...

            // Modificar la función updateTable para manejar la verificación de registros existentes
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
                    url: 'components/attendance/buscar_datos.php',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: (response) => {
                        // Verificar si ya existe un registro de asistencia para este curso y fecha
                        if (response.exists) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Asistencia ya registrada',
                                text: response.message,
                                confirmButtonText: 'Entendido'
                            });
                            $('#saveAttendance').prop('disabled', true);
                            $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">Ya existe asistencia registrada para este curso en esta fecha</td></tr>');
                            return;
                        }
                        
                        // Resetear el estado del botón de guardar (por si antes estaba deshabilitado)
                        $('#saveAttendance').prop('disabled', false);
                        
                        // El resto del código se mantiene igual
                        if (response && response.html) {
                            $('#listaInscritos tbody').html(response.html);
                            
                            // Actualizar el nombre del profesor si está disponible
                            if (response.teacher_name) {
                                $('#teacher-display').html('Docente: ' + response.teacher_name);
                            }
                            
                            // Almacenar el ID del profesor para usarlo al guardar
                            $('#saveAttendance').data('teacher-id', response.teacher_id);
                            
                            // Actualizar y mostrar horas por sesión
                            $('#session_hours').val(response.session_hours);
                            $('#session_hours_display').val(response.session_hours);
                            
                            // Habilitar el botón de exportar cuando hay datos
                            $('#exportBtn').prop('disabled', false);
                        } else {
                            $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">No se encontraron registros</td></tr>');
                            // Mantener el botón habilitado si hay fecha seleccionada
                            validateExportButton();
                        }
                    },
                    error: (xhr, status, error) => {
                        console.error('Error en la solicitud:', error);
                        $('#listaInscritos tbody').html('<tr><td colspan="8" class="text-center">Error al cargar los datos</td></tr>');
                    }
                });
            };
        });
    </script>

</body>

</html>