<?php
// Consulta para obtener todos los horarios
$sql = "SELECT * FROM schedules_registrations ORDER BY created_at DESC";
$result = $conn->query($sql);
$schedules = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}
?>

<!-- Botón para agregar nuevo horario -->
<div class="container-fluid mb-4">
    <button class="btn bg-magenta-dark text-white" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
        <i class="bi bi-plus-circle"></i> Agregar Nuevo Horario
    </button>
</div>

<!-- Tabla de horarios -->
<div class="container-fluid">
    <div class="table-responsive">
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Horario</th>
                    <th>Programa</th>
                    <th>Modalidad</th>
                    <th>Sede</th>
                    <th>Departamento</th>
                    <th>Fecha Creación</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($schedules as $schedule) { ?>
                    <tr>
                        <td class="text-center"><?php echo $schedule['id']; ?></td>
                        <td><?php echo $schedule['schedule']; ?></td>
                        <td><?php echo $schedule['program']; ?></td>
                        <td><?php echo $schedule['mode']; ?></td>
                        <td><?php echo $schedule['headquarters']; ?></td>
                        <td><?php echo $schedule['department']; ?></td>
                        <td class="text-center"><?php echo date('d/m/Y H:i', strtotime($schedule['created_at'])); ?></td>
                        <td class="text-center">
                            <button class="btn bg-magenta-dark btn-sm text-white" data-bs-toggle="modal" data-bs-target="#editScheduleModal<?php echo $schedule['id']; ?>">
                                <i class="bi bi-pencil-square"></i> Editar
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para agregar nuevo horario -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-labelledby="addScheduleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="addScheduleModalLabel">Agregar Nuevo Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="addScheduleForm">
                    <div class="mb-3">
                        <label for="schedule" class="form-label">Horario</label>
                        <input type="text" class="form-control" id="schedule" name="schedule" required>
                    </div>
                    <div class="mb-3">
                        <label for="program" class="form-label">Programa</label>
                        <select class="form-control" id="program" name="program" required>
                            <option value="">Seleccionar programa</option>
                            <option value="Programación">Programación</option>
                            <option value="Análisis de Datos">Análisis de Datos</option>
                            <option value="Inteligencia Artificial">Inteligencia Artificial</option>
                            <option value="Ciberseguridad">Ciberseguridad</option>
                            <option value="Blockchain">Blockchain</option>
                            <option value="Arquitectura en la Nube">Arquitectura en la Nube</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="mode" class="form-label">Modalidad</label>
                        <select class="form-control" id="mode" name="mode" required>
                            <option value="">Seleccionar modalidad</option>
                            <option value="Virtual">Virtual</option>
                            <option value="Presencial">Presencial</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="headquarters" class="form-label">Sede</label>
                        <select class="form-control" id="headquarters" name="headquarters" required>
                            <option value="">Seleccionar sede</option>
                            <?php
                            $sql_headquarters = "SELECT * FROM headquarters_registrations ORDER BY name ASC";
                            $result_headquarters = $conn->query($sql_headquarters);
                            if ($result_headquarters->num_rows > 0) {
                                while ($row = $result_headquarters->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row['name']) . '">' . htmlspecialchars($row['name']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="department" class="form-label">Departamento</label>
                        <select class="form-control" id="department" name="department" required>
                            <option value="">Seleccionar departamento</option>
                            <option value="BOGOTÁ, D.C.">BOGOTÁ, D.C.</option>
                            <option value="No aplica">No aplica</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-magenta-dark text-white" onclick="addSchedule()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modales para editar cada horario -->
<?php foreach ($schedules as $schedule) { ?>
    <div class="modal fade" id="editScheduleModal<?php echo $schedule['id']; ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-magenta-dark text-white">
                    <h5 class="modal-title">Editar Horario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editScheduleForm<?php echo $schedule['id']; ?>">
                        <input type="hidden" name="id" value="<?php echo $schedule['id']; ?>">
                        <div class="mb-3">
                            <label for="schedule<?php echo $schedule['id']; ?>" class="form-label">Horario</label>
                            <input type="text" class="form-control" id="schedule<?php echo $schedule['id']; ?>"
                                name="schedule" value="<?php echo $schedule['schedule']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="program<?php echo $schedule['id']; ?>" class="form-label">Programa</label>
                            <select class="form-control" id="program<?php echo $schedule['id']; ?>" name="program" required>
                                <option value="Programación" <?php echo ($schedule['program'] == 'Programación') ? 'selected' : ''; ?>>Programación</option>
                                <option value="Análisis de Datos" <?php echo ($schedule['program'] == 'Análisis de Datos') ? 'selected' : ''; ?>>Análisis de Datos</option>
                                <option value="Inteligencia Artificial" <?php echo ($schedule['program'] == 'Inteligencia Artificial') ? 'selected' : ''; ?>>Inteligencia Artificial</option>
                                <option value="Ciberseguridad" <?php echo ($schedule['program'] == 'Ciberseguridad') ? 'selected' : ''; ?>>Ciberseguridad</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="mode<?php echo $schedule['id']; ?>" class="form-label">Modalidad</label>
                            <select class="form-control mode-select" id="mode<?php echo $schedule['id']; ?>" name="mode" required>
                                <option value="Virtual" <?php echo ($schedule['mode'] == 'Virtual') ? 'selected' : ''; ?>>Virtual</option>
                                <option value="Presencial" <?php echo ($schedule['mode'] == 'Presencial') ? 'selected' : ''; ?>>Presencial</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="headquarters<?php echo $schedule['id']; ?>" class="form-label">Sede</label>
                            <select class="form-control" id="headquarters<?php echo $schedule['id']; ?>" name="headquarters" required>
                                <option value="">Seleccionar sede</option>
                                <?php
                                $sql_headquarters = "SELECT * FROM headquarters ORDER BY name ASC";
                                $result_headquarters = $conn->query($sql_headquarters);
                                if ($result_headquarters->num_rows > 0) {
                                    while ($row = $result_headquarters->fetch_assoc()) {
                                        $selected = ($schedule['headquarters'] == $row['name']) ? 'selected' : '';
                                        echo '<option value="' . htmlspecialchars($row['name']) . '" ' . $selected . '>' . htmlspecialchars($row['name']) . '</option>';
                                    }
                                }
                                ?>
                                <option value="No aplica" <?php echo ($schedule['headquarters'] == 'No aplica') ? 'selected' : ''; ?>>No aplica</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="department<?php echo $schedule['id']; ?>" class="form-label">Departamento</label>
                            <select class="form-control" id="department<?php echo $schedule['id']; ?>" name="department" required>
                                <option value="BOGOTÁ, D.C." <?php echo ($schedule['department'] == 'BOGOTÁ, D.C.') ? 'selected' : ''; ?>>BOGOTÁ, D.C.</option>
                                <option value="No aplica" <?php echo ($schedule['department'] == 'No aplica') ? 'selected' : ''; ?>>No aplica</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn bg-magenta-dark text-white"
                        onclick="updateSchedule(<?php echo $schedule['id']; ?>)">Guardar Cambios</button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
    function addSchedule() {
        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const form = document.getElementById('addScheduleForm');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData);

        // Verificar campos vacíos
        if (!data.schedule || !data.program || !data.mode) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Por favor complete todos los campos obligatorios'
            });
            return;
        }

        // Asegurar valores para modalidad Virtual
        if (data.mode === 'Virtual') {
            data.headquarters = 'No aplica';
            data.department = 'No aplica';
        }

        $.ajax({
            url: 'components/listSchedules/add_scheduleRegis.php',
            type: 'POST',
            data: data,
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Horario agregado exitosamente'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            location.reload();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Error al agregar el horario'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error details:', xhr.responseText);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error en la conexión. Por favor, intente nuevamente.'
                });
            }
        });
    }

    function updateSchedule(id) {
        Swal.fire({
            title: 'Actualizando...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const form = document.getElementById(`editScheduleForm${id}`);
        const formData = new FormData(form);
        const mode = formData.get('mode');

        // Asegurar que los valores sean 'No aplica' para modalidad Virtual
        if (mode === 'Virtual') {
            formData.set('headquarters', 'No aplica');
            formData.set('department', 'No aplica');
        }

        $.ajax({
            url: 'components/listSchedules/update_scheduleRegis.php',
            type: 'POST',
            data: Object.fromEntries(formData),
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Horario actualizado exitosamente',
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
                        text: 'Error al actualizar el horario: ' + response.message
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log('Error details:', {
                    xhr,
                    status,
                    error
                });
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error en la conexión: ' + error
                });
            }
        });
    }

    $(document).ready(function() {
        // Función para manejar el cambio en los selects de modalidad
        function handleModeChange(modeSelect) {
            const form = $(modeSelect).closest('form');
            const headquartersSelect = form.find('select[name="headquarters"]');
            const departmentSelect = form.find('select[name="department"]');

            if ($(modeSelect).val() === 'Virtual') {
                // Para sede
                headquartersSelect.val('No aplica');
                headquartersSelect.prop('disabled', true);

                // Para departamento
                departmentSelect.val('No aplica');
                departmentSelect.prop('disabled', true);
            } else {
                // Para sede
                headquartersSelect.prop('disabled', false);
                if (headquartersSelect.val() === 'No aplica') {
                    headquartersSelect.val($('option:not([value="No aplica"]):first', headquartersSelect).val());
                }

                // Para departamento
                departmentSelect.prop('disabled', false);
                if (departmentSelect.val() === 'No aplica') {
                    departmentSelect.val($('option:not([value="No aplica"]):first', departmentSelect).val());
                }
            }
        }

        // Para el modal de agregar
        $('#mode').on('change', function() {
            handleModeChange(this);
        });

        // Para los modales de edición
        $('.mode-select').on('change', function() {
            handleModeChange(this);
        });

        // Verificar estado inicial
        $('#mode').each(function() {
            handleModeChange(this);
        });

        $('.mode-select').each(function() {
            handleModeChange(this);
        });

        // Al abrir cualquier modal de edición
        $('.modal').on('show.bs.modal', function() {
            const modeSelect = $(this).find('.mode-select');
            if (modeSelect.length) {
                // Pequeño retraso para asegurar que el modal está completamente cargado
                setTimeout(() => {
                    handleModeChange(modeSelect);
                }, 100);
            }
        });
    });
</script>