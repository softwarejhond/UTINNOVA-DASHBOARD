<?php
// Obtener bootcamps
$bootcamps = [];
$sql_bootcamp = "SELECT DISTINCT g.id_bootcamp, g.bootcamp_name 
    FROM `groups` g
    WHERE g.id_bootcamp NOT IN (
        SELECT c.bootcamp_id FROM classrooms c
    )";
$result_bootcamp = $conn->query($sql_bootcamp);
if ($result_bootcamp && $result_bootcamp->num_rows > 0) {
    while ($row = $result_bootcamp->fetch_assoc()) {
        $bootcamps[] = [
            'id' => $row['id_bootcamp'],
            'name' => $row['bootcamp_name']
        ];
    }
}
?>

<style>
    .nav-tabs .nav-link.active {
        color: #30336b !important;
        font-weight: bold !important;
        background-color: #fff !important;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    .nav-tabs .nav-link {
        color: #000 !important;
        font-weight: normal !important;
        background-color: #fff !important;
        border-color: #dee2e6 #dee2e6 #fff;
    }

    /* Estilo para los selects con select2 */
    .select2-container .select2-selection--single {
        height: 38px !important;
        display: flex !important;
        align-items: center !important;
        padding: 0 8px !important;
        box-sizing: border-box;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
        height: 38px !important;
        display: flex;
        align-items: center;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px !important;
        display: flex;
        align-items: center;
    }

    .select2-dropdown {
        /* Opcional: centra verticalmente las opciones del dropdown */
        font-size: 16px;
    }

    .select2-results__option {
        display: flex;
        align-items: center;
        min-height: 38px;
    }
</style>

<ul class="nav nav-tabs mb-3" id="classroomTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="add-tab" data-bs-toggle="tab" data-bs-target="#addClassrooms" type="button" role="tab" aria-controls="addClassrooms" aria-selected="true">
            Agregar aulas
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="registered-tab" data-bs-toggle="tab" data-bs-target="#registeredClassrooms" type="button" role="tab" aria-controls="registeredClassrooms" aria-selected="false">
            Aulas registradas
        </button>
    </li>
</ul>

<div class="tab-content" id="classroomTabsContent">
    <div class="tab-pane fade show active" id="addClassrooms" role="tabpanel" aria-labelledby="add-tab">

        <div class="container-fluid mt-4">
            <div class="card shadow mb-3">
                <div class="card-body rounded-0">
                    <div class="container-fluid">
                        <div class="row align-items-end">
                            <!-- Agrega el select de headquarters debajo del select de cursos -->
                            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                <label class="form-label">Sede</label>
                                <?php
                                $headquarters_options = [];
                                $sql = "SELECT DISTINCT g.headquarters, 
                                                   hc.classrooms_count,
                                                   (SELECT COUNT(*) FROM classrooms c WHERE c.headquarters = g.headquarters) AS aulas_ocupadas
                                        FROM groups g
                                        LEFT JOIN headquarters_classrooms hc ON g.headquarters = hc.headquarters
                                        WHERE g.mode = 'Presencial'";
                                $result = $conn->query($sql);

                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $disponibles = (int)$row['classrooms_count'] - (int)$row['aulas_ocupadas'];
                                        $headquarters_options[] = [
                                            'name' => $row['headquarters'],
                                            'classrooms_count' => $row['classrooms_count'],
                                            'aulas_ocupadas' => $row['aulas_ocupadas'],
                                            'disponibles' => $disponibles
                                        ];
                                    }
                                }
                                ?>
                                <select id="headquarters" class="form-select">
                                    <option value="">Seleccione una sede</option>
                                    <?php foreach ($headquarters_options as $hq): ?>
                                        <option
                                            value="<?= htmlspecialchars($hq['name']) ?>"
                                            data-classrooms="<?= (int)$hq['classrooms_count'] ?>"
                                            data-disponibles="<?= (int)$hq['disponibles'] ?>"
                                            <?= (int)$hq['disponibles'] < 1 ? 'class="text-muted"' : '' ?>>
                                            <?= htmlspecialchars($hq['name']) ?>
                                            <?= (int)$hq['disponibles'] > 0
                                                ? " ({$hq['disponibles']} aulas disponibles)"
                                                : " (Sin cupo disponible)" ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 col-12">
                                <label class="form-label">Agrega el número de aulas</label>
                                <input type="number" id="numberOfClassrooms" class="form-control"
                                    min="1" placeholder="Ingrese el número de aulas" disabled>
                            </div>
                        </div>
                        <!-- Botón de confirmar -->
                        <div class="row mt-3">
                            <div class="col-12 d-flex justify-content-center">
                                <button id="confirmBtn" class="btn bg-magenta-dark text-white" disabled>Confirmar</button>
                            </div>
                        </div>
                        <!-- Contenedor para los inputs generados -->
                        <div id="classroomInputs" class="mt-3"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <div class="tab-pane fade" id="registeredClassrooms" role="tabpanel" aria-labelledby="registered-tab">
        <div class="container-fluid mt-4 d-flex justify-content-center">
            <div class="card shadow mb-3 w-100">
                <div class="card-body rounded-0 d-flex flex-column align-items-center">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="listaAulas">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center text-nowrap">Sede</th>
                                    <th class="text-center text-nowrap">Aula</th>
                                    <th class="text-center text-nowrap">Curso</th>
                                    <th class="text-center text-nowrap">Creado por</th>
                                    <th class="text-center text-nowrap">Fecha de creación</th>
                                    <th class="text-center text-nowrap">Gestionar</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Consulta para obtener todas las aulas con los cruces necesarios
                                $sql = "SELECT 
                                    c.id,
                                    c.headquarters,
                                    c.classroom_name,
                                    co.name AS course_name,
                                    u.nombre AS user_name,
                                    c.created_at
                                FROM classrooms c
                                LEFT JOIN courses co ON c.bootcamp_id = co.code
                                LEFT JOIN users u ON c.created_by = u.username
                                ORDER BY c.created_at DESC";
                                $result = $conn->query($sql);
                                if ($result && $result->num_rows > 0):
                                    while ($row = $result->fetch_assoc()):
                                ?>
                                        <tr>
                                            <td class="text-center text-nowrap"><?= htmlspecialchars($row['headquarters']) ?></td>
                                            <td class="text-center text-nowrap"><?= htmlspecialchars($row['classroom_name']) ?></td>
                                            <td class="text-center text-nowrap"><?= $row['course_name'] ? htmlspecialchars($row['course_name']) : 'N/A' ?></td>
                                            <td class="text-center text-nowrap"><?= $row['user_name'] ? htmlspecialchars($row['user_name']) : 'N/A' ?></td>
                                            <td class="text-center text-nowrap"><?= htmlspecialchars($row['created_at']) ?></td>
                                            <td class="text-center text-nowrap">
                                                <button class="btn btn-sm btn-danger" title="Eliminar" data-id="<?= $row['id'] ?>">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php
                                    endwhile;
                                else:
                                    ?>
                                    <tr>
                                        <td colspan="6" class="text-center">No hay aulas registradas.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Agrega estos CDN en tu archivo antes de tus scripts -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Pasar bootcamps a JS -->
<script>
    const bootcamps = <?php echo json_encode($bootcamps); ?>;
    const headquartersSelect = document.getElementById('headquarters');
    const numberInput = document.getElementById('numberOfClassrooms');
    const confirmBtn = document.getElementById('confirmBtn');
    const classroomInputs = document.getElementById('classroomInputs');

    // Habilitar input de aulas solo si hay sede seleccionada
    headquartersSelect.addEventListener('change', function() {
        const selectedOption = headquartersSelect.options[headquartersSelect.selectedIndex];
        const aulasDisponibles = parseInt(selectedOption.getAttribute('data-disponibles'), 10);
        const classroomsCount = parseInt(selectedOption.getAttribute('data-classrooms'), 10);

        if ((isNaN(classroomsCount) || classroomsCount < 1) && headquartersSelect.value !== '') {
            Swal.fire({
                icon: 'warning',
                title: 'Límite no establecido',
                text: 'Debes establecer un número de aulas disponibles para esta sede antes de poder agregar aulas.',
            });
            numberInput.disabled = true;
            numberInput.value = '';
            numberInput.removeAttribute('max');
            checkConfirmEnabled();
            return;
        }

        if (aulasDisponibles < 1 && headquartersSelect.value !== '') {
            Swal.fire({
                icon: 'warning',
                title: 'Sede llena',
                text: 'Esta sede ya tiene todas sus aulas asignadas. No puedes agregar más.',
            });
            numberInput.disabled = true;
            numberInput.value = '';
            numberInput.removeAttribute('max');
            checkConfirmEnabled();
            return;
        }

        numberInput.disabled = this.value === '';
        numberInput.value = '';
        if (aulasDisponibles > 0) {
            numberInput.setAttribute('max', aulasDisponibles);
            numberInput.setAttribute('placeholder', `Máximo ${aulasDisponibles} aulas`);
        } else {
            numberInput.removeAttribute('max');
            numberInput.setAttribute('placeholder', 'Sin cupo disponible');
        }
        checkConfirmEnabled();
    });

    numberInput.addEventListener('input', function() {
        const max = parseInt(numberInput.getAttribute('max'), 10);
        if (max && parseInt(numberInput.value, 10) > max) {
            numberInput.value = max;
            Swal.fire({
                icon: 'warning',
                title: 'Límite de aulas',
                text: `No puedes agregar más de ${max} aulas para esta sede.`,
            });
        }
        checkConfirmEnabled();
    });

    function checkConfirmEnabled() {
        confirmBtn.disabled = headquartersSelect.value === '' || !numberInput.value || numberInput.value < 1;
    }

    // Generar selects sin duplicados
    function renderClassroomRows(count) {
        classroomInputs.innerHTML = '';
        const selectedBootcamps = [];

        for (let i = 0; i < count; i++) {
            const row = document.createElement('div');
            row.className = 'row mb-2 align-items-center';

            // Input de texto
            const colInput = document.createElement('div');
            colInput.className = 'col-6';
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'form-control classroom-name';
            input.placeholder = `Nombre aula ${i + 1}`;
            colInput.appendChild(input);

            // Selector de bootcamp con select2
            const colSelect = document.createElement('div');
            colSelect.className = 'col-6';
            const select = document.createElement('select');
            select.className = 'form-select classroom-bootcamp';
            select.dataset.index = i;

            select.innerHTML = `<option value="">Seleccione bootcamp</option>`;
            bootcamps.forEach(bc => {
                select.innerHTML += `<option value="${bc.id}">${bc.id} - ${bc.name}</option>`;
            });

            colSelect.appendChild(select);
            row.appendChild(colInput);
            row.appendChild(colSelect);
            classroomInputs.appendChild(row);
        }

        // Inicializar select2 en todos los selects
        $(classroomInputs).find('select.classroom-bootcamp').select2({
            width: '100%',
            placeholder: 'Seleccione bootcamp',
            allowClear: true
        });

        // Evento para evitar duplicados
        const selects = classroomInputs.querySelectorAll('select.classroom-bootcamp');
        selects.forEach(select => {
            $(select).on('change', function() {
                updateSelectOptions();
            });
        });

        function updateSelectOptions() {
            const selected = Array.from(selects).map(s => s.value).filter(v => v);
            selects.forEach(s => {
                const currentValue = s.value;
                // Guardar el valor actual antes de reconstruir
                $(s).empty().append('<option value="">Seleccione bootcamp</option>');
                bootcamps.forEach(bc => {
                    if (selected.includes(String(bc.id)) && String(bc.id) !== currentValue) return;
                    $(s).append(`<option value="${bc.id}"${currentValue == bc.id ? ' selected' : ''}>${bc.id} - ${bc.name}</option>`);
                });
                // Refrescar select2
                $(s).trigger('change.select2');
            });
        }

        // Agregar botón de guardar
        const saveBtnRow = document.createElement('div');
        saveBtnRow.className = 'row mt-3';
        const saveBtnCol = document.createElement('div');
        saveBtnCol.className = 'col-12 d-flex justify-content-center';
        const saveBtn = document.createElement('button');
        saveBtn.id = 'saveClassroomsBtn';
        saveBtn.className = 'btn bg-teal-dark text-white';
        saveBtn.textContent = 'Guardar sedes';
        saveBtnCol.appendChild(saveBtn);
        saveBtnRow.appendChild(saveBtnCol);
        classroomInputs.appendChild(saveBtnRow);

        // Evento guardar
        saveBtn.addEventListener('click', function() {
            const headquarters = headquartersSelect.value;
            const classroomNames = classroomInputs.querySelectorAll('.classroom-name');
            const classroomBootcamps = classroomInputs.querySelectorAll('.classroom-bootcamp');
            const classrooms = [];
            let valid = true;

            for (let i = 0; i < classroomNames.length; i++) {
                const name = classroomNames[i].value.trim();
                const bootcamp_id = classroomBootcamps[i].value;
                if (!name || !bootcamp_id) {
                    valid = false;
                    break;
                }
                classrooms.push({
                    name,
                    bootcamp_id
                });
            }

            if (!valid) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campos incompletos',
                    text: 'Completa todos los campos antes de guardar.'
                });
                return;
            }

            fetch('components/classrooms/guardar_classrooms.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        headquarters,
                        classrooms
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Aulas registradas correctamente.'
                        }).then(() => {
                            location.reload();
                        });
                        classroomInputs.innerHTML = '';
                        numberInput.value = '';
                        confirmBtn.disabled = true;
                    } else {
                        let msg = data.error ?
                            'No se puede registrar un aula con un bootcamp que ya existe en el sistema. Por favor, selecciona otro bootcamp.' :
                            'Error al registrar aulas.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: msg
                        });
                    }
                });
        });
    }

    // Al confirmar, generar los inputs y selects
    confirmBtn.addEventListener('click', function() {
        const count = parseInt(numberInput.value, 10);
        renderClassroomRows(count);
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('#listaAulas .btn-danger').forEach(btn => {
            btn.addEventListener('click', function() {
                const row = btn.closest('tr');
                const id = row ? row.querySelector('td').dataset.id : null;
                // Alternativamente, puedes poner el id en un atributo data-id en el botón
                const aulaId = btn.dataset.id || row.getAttribute('data-id') || btn.closest('tr').getAttribute('data-id');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción eliminará el aula de forma permanente.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: 'rgba(46, 51, 122, 1)',
                    cancelButtonColor: '#a1a1a1ff',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch('components/classrooms/eliminar_classroom.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify({
                                    id: aulaId
                                })
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.success) {
                                    Swal.fire('Eliminado', 'El aula ha sido eliminada.', 'success')
                                        .then(() => location.reload());
                                } else {
                                    Swal.fire('Error', 'No se pudo eliminar el aula.', 'error');
                                }
                            });
                    }
                });
            });
        });
    });
</script>