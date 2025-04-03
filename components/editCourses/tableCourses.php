<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Consulta para obtener todos los cursos
$sql = "SELECT * FROM courses";
$result = $conn->query($sql);
$courses = [];

// Llenar array con los resultados de la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $courses[] = $row;
    }
}

// Consulta para obtener usuarios (profesores, mentores y monitores)
$sql_users = "SELECT * FROM users";
$result_users = $conn->query($sql_users);
$users = [];

if ($result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container-fluid">
    <div class="table-responsive">
        <table id="listaCursos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Activo</th>
                    <th>Horas Reales</th>
                    <th>Horas/Semana</th> <!-- Cambio aquí -->
                    <th>Fecha Inicio</th>
                    <th>Fecha Fin</th>
                    <th>Detalles</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($courses as $course) { ?>
                    <tr>
                        <td><?php echo ($course['code']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo ($course['name']); ?>
                        </td>
                        <td class="text-center">
                            <?php if ($course['status'] == 1) { ?>
                                <span class="badge bg-success text-white">Sí</span>
                            <?php } else { ?>
                                <span class="badge bg-danger text-white">No</span>
                            <?php } ?>
                        </td>
                        <td class="text-center"><?php echo ($course['real_hours']); ?></td>
                        <td class="text-center">
                            <?php
                            $horasSemana = ($course['monday_hours'] ?? 0) +
                                ($course['tuesday_hours'] ?? 0) +
                                ($course['wednesday_hours'] ?? 0) +
                                ($course['thursday_hours'] ?? 0) +
                                ($course['friday_hours'] ?? 0) +
                                ($course['saturday_hours'] ?? 0) +
                                ($course['sunday_hours'] ?? 0);
                            echo $horasSemana;
                            ?>
                        </td>
                        <td class="text-center"><?php echo ($course['start_date']); ?></td>
                        <td class="text-center"><?php echo ($course['end_date'] ?? '-'); ?></td>
                        <td class="text-center">
                            <button class="btn bg-magenta-dark btn-sm text-white" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $course['code']; ?>">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modales para cada curso -->
<?php foreach ($courses as $course) { ?>
    <div class="modal fade" id="detailsModal<?php echo $course['code']; ?>" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel<?php echo $course['code']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-magenta-dark text-white">
                    <h5 class="modal-title" id="detailsModalLabel<?php echo $course['code']; ?>">
                        Detalles del Curso: <?php echo ($course['code'] . ' - ' . $course['name']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateCourseForm<?php echo $course['code']; ?>">
                        <input type="hidden" name="courseCode" value="<?php echo $course['code']; ?>">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="teacherSelect<?php echo $course['code']; ?>" class="form-label">Profesor</label>
                                <select class="form-select" id="teacherSelect<?php echo $course['code']; ?>" name="teacher" required>
                                    <option value="">Seleccione un profesor</option>
                                    <?php
                                    foreach ($users as $user) {
                                        if ($user['rol'] == 5) {
                                            $selected = ($user['username'] == $course['teacher']) ? 'selected' : '';
                                            echo "<option value='{$user['username']}' {$selected}>{$user['nombre']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="mentorSelect<?php echo $course['code']; ?>" class="form-label">Mentor</label>
                                <select class="form-select" id="mentorSelect<?php echo $course['code']; ?>" name="mentor" required>
                                    <option value="">Seleccione un mentor</option>
                                    <?php
                                    foreach ($users as $user) {
                                        if ($user['rol'] == 8) {
                                            $selected = ($user['username'] == $course['mentor']) ? 'selected' : '';
                                            echo "<option value='{$user['username']}' {$selected}>{$user['nombre']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="monitorSelect<?php echo $course['code']; ?>" class="form-label">Monitor</label>
                                <select class="form-select" id="monitorSelect<?php echo $course['code']; ?>" name="monitor" required>
                                    <option value="">Seleccione un monitor</option>
                                    <?php
                                    foreach ($users as $user) {
                                        if ($user['rol'] == 7) {
                                            $selected = ($user['username'] == $course['monitor']) ? 'selected' : '';
                                            echo "<option value='{$user['username']}' {$selected}>{$user['nombre']}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="statusSelect<?php echo $course['code']; ?>" class="form-label">Estado</label>
                                <select class="form-select" id="statusSelect<?php echo $course['code']; ?>" name="status" required>
                                    <option value="1" <?php echo ($course['status'] == 1) ? 'selected' : ''; ?>>Activo</option>
                                    <option value="0" <?php echo ($course['status'] == 0) ? 'selected' : ''; ?>>Inactivo</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dateStart<?php echo $course['code']; ?>" class="form-label">Fecha de Inicio</label>
                                <input type="date" class="form-control" id="dateStart<?php echo $course['code']; ?>" name="start_date" value="<?php echo $course['start_date']; ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="dateEnd<?php echo $course['code']; ?>" class="form-label">Fecha de Finalización</label>
                                <input type="date" class="form-control" id="dateEnd<?php echo $course['code']; ?>" name="end_date" value="<?php echo $course['end_date'] ?? ''; ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <?php if ($rol === 'Control maestro' || $rol==='Administrador') { ?>
                                <div class="col-12 mb-3">
                                    <div class="d-flex align-items-center">
                                        <h6 class="mb-0 me-3">Habilitar edición de horas</h6>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input custom-switch" type="checkbox"
                                                id="enableHoursEdit<?php echo $course['code']; ?>"
                                                onchange="toggleHoursEdit('<?php echo $course['code']; ?>')">
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>

                        </div>

                        <!-- Nueva sección para horas por día -->
                        <div class="row">
                            <div class="col-12">
                                <h6 class="mb-3">Horas por día de la semana</h6>
                            </div>

                            <!-- Primera columna -->
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text"><b>Horas Reales</b></span>
                                    <input type="number" class="form-control" id="realHours<?php echo $course['code']; ?>"
                                        name="real_hours" value="<?php echo $course['real_hours']; ?>"
                                        min="0" step="1" required
                                        <?php echo ($rol !== 'Control maestro') ? 'disabled' : 'disabled'; ?>>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Lunes</span>
                                    <input type="number" class="form-control hours-input"
                                        id="mondayHours<?php echo $course['code']; ?>"
                                        name="monday_hours" value="<?php echo $course['monday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador') ? 'disabled' : 'disabled'; ?>>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Martes</span>
                                    <input type="number" class="form-control hours-input"
                                        id="tuesdayHours<?php echo $course['code']; ?>"
                                        name="tuesday_hours" value="<?php echo $course['tuesday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador') ? 'disabled' : 'disabled'; ?>>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Miércoles</span>
                                    <input type="number" class="form-control hours-input"
                                        id="wednesdayHours<?php echo $course['code']; ?>"
                                        name="wednesday_hours" value="<?php echo $course['wednesday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador') ? 'disabled' : 'disabled'; ?>>
                                </div>
                            </div>

                            <!-- Segunda columna -->
                            <div class="col-md-6">
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Jueves</span>
                                    <input type="number" class="form-control hours-input"
                                        id="thursdayHours<?php echo $course['code']; ?>"
                                        name="thursday_hours" value="<?php echo $course['thursday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador') ? 'disabled' : 'disabled'; ?>>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Viernes</span>
                                    <input type="number" class="form-control hours-input"
                                        id="fridayHours<?php echo $course['code']; ?>"
                                        name="friday_hours" value="<?php echo $course['friday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador' ) ? 'disabled' : 'disabled'; ?>>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Sábado</span>
                                    <input type="number" class="form-control hours-input"
                                        id="saturdayHours<?php echo $course['code']; ?>"
                                        name="saturday_hours" value="<?php echo $course['saturday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador') ? 'disabled' : 'disabled'; ?>>
                                </div>
                                <div class="input-group mb-2">
                                    <span class="input-group-text">Domingo</span>
                                    <input type="number" class="form-control hours-input"
                                        id="sundayHours<?php echo $course['code']; ?>"
                                        name="sunday_hours" value="<?php echo $course['sunday_hours'] ?? 0; ?>"
                                        min="0" max="14" step="1" required
                                        <?php echo ($rol !== 'Control maestro' || $rol !== 'Administrador') ? 'disabled' : 'disabled'; ?>>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn bg-magenta-dark text-white" onclick="updateCourse('<?php echo $course['code']; ?>')">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        $('#listaCursos').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
            },
            "order": [
                [0, "asc"]
            ]
        });

        // Convertir selects estándar a select2 para tener búsqueda
        <?php foreach ($courses as $course) { ?>
            $(`#teacherSelect<?php echo $course['code']; ?>`).select2({
                dropdownParent: $(`#detailsModal<?php echo $course['code']; ?>`),
                placeholder: "Seleccione un profesor",
                width: '100%'
            });

            $(`#mentorSelect<?php echo $course['code']; ?>`).select2({
                dropdownParent: $(`#detailsModal<?php echo $course['code']; ?>`),
                placeholder: "Seleccione un mentor",
                width: '100%'
            });

            $(`#monitorSelect<?php echo $course['code']; ?>`).select2({
                dropdownParent: $(`#detailsModal<?php echo $course['code']; ?>`),
                placeholder: "Seleccione un monitor",
                width: '100%'
            });
        <?php } ?>
    });

    // Modificar el manejo de los modales para usar Bootstrap 5
    $(document).ready(function() {
        // Para cada botón que abre un modal
        $('[data-toggle="modal"]').on('click', function() {
            const target = $(this).data('target');
            const modalEl = document.querySelector(target);
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        });
    });

    function updateCourse(courseCode) {
        // Mostrar cargando
        Swal.fire({
            title: 'Actualizando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Obtener datos del formulario
        const formData = {
            code: courseCode,
            teacher: $(`#teacherSelect${courseCode}`).val(),
            mentor: $(`#mentorSelect${courseCode}`).val(),
            monitor: $(`#monitorSelect${courseCode}`).val(),
            status: $(`#statusSelect${courseCode}`).val(),
            start_date: $(`#dateStart${courseCode}`).val(),
            end_date: $(`#dateEnd${courseCode}`).val(),
            real_hours: $(`#realHours${courseCode}`).val(),
            monday_hours: $(`#mondayHours${courseCode}`).val(),
            tuesday_hours: $(`#tuesdayHours${courseCode}`).val(),
            wednesday_hours: $(`#wednesdayHours${courseCode}`).val(),
            thursday_hours: $(`#thursdayHours${courseCode}`).val(),
            friday_hours: $(`#fridayHours${courseCode}`).val(),
            saturday_hours: $(`#saturdayHours${courseCode}`).val(),
            sunday_hours: $(`#sundayHours${courseCode}`).val()
        };

        // Enviar datos al servidor
        $.ajax({
            url: 'components/editCourses/update_course.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Curso actualizado exitosamente',
                        showConfirmButton: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al actualizar el curso: ' + response.message
                    });
                }
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error en la conexión'
                });
            }
        });
    }

    function toggleHoursEdit(courseCode) {
        const isEnabled = document.getElementById(`enableHoursEdit${courseCode}`).checked;
        document.getElementById(`realHours${courseCode}`).disabled = !isEnabled;

        // Habilitar/deshabilitar todos los inputs de horas por día
        const diasSemana = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        diasSemana.forEach(dia => {
            document.getElementById(`${dia}Hours${courseCode}`).disabled = !isEnabled;
        });
    }
</script>

<!-- Incluir Select2 para tener búsqueda en los selectores -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<style>
    /* Estilos para Select2 */
    .select2-container--default .select2-selection--single {
        height: 38px;
        padding: 5px 12px;
        font-size: 1rem;
        line-height: 1.5;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 26px;
    }

    .select2-container--default .select2-results>.select2-results__options {
        max-height: 250px;
    }

    /* Estilos para campos deshabilitados y switch */
    input:disabled {
        background-color: #e9ecef !important;
        cursor: not-allowed;
    }

    .form-check-input:checked {
        background-color: #800080;
        border-color: #800080;
    }

    .form-switch .form-check-input {
        width: 2.5em;
        margin-left: -2.5em;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e");
        background-position: left center;
        border-radius: 2em;
        transition: background-position .15s ease-in-out;
    }

    .form-switch .form-check-input:checked {
        background-position: right center;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e");
    }

    .form-switch .custom-switch:focus {
        box-shadow: 0 0 0 0.25rem rgba(128, 0, 128, 0.25);
    }

    /* Estilos personalizados para el switch */
    .custom-switch {
        width: 3.5em !important;
        height: 1.8em !important;
        margin-left: 0 !important;
    }

    .form-check-input:checked {
        background-color: #EC008C !important;
        border-color: #EC008C !important;
    }

    .form-switch .form-check-input {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e") !important;
    }

    .form-switch .form-check-input:checked {
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e") !important;
    }

    .form-switch .custom-switch:focus {
        box-shadow: 0 0 0 0.25rem rgba(128, 0, 128, 0.25);
    }
</style>