<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Modificar la consulta para incluir información de ejecutores y sus sedes
$sql = "SELECT u.*, 
               eh.headquarter, eh.creation_date as headquarter_assigned_date,
               cr.id as carnet_id, cr.file_path, cr.file_name, cr.generated_at as carnet_date,
               CASE 
                   WHEN u.rol = 5 THEN 'Docente'
                   WHEN u.rol = 7 THEN 'Monitor'
                   WHEN u.rol = 8 THEN 'Mentor'
                   ELSE 'Ejecutor'
               END as rol_nombre
        FROM users u 
        LEFT JOIN executor_headquarters eh ON u.username = eh.username
        LEFT JOIN carnet_records cr ON u.username = cr.number_id AND cr.is_active = 1
        WHERE u.rol IN (5, 7, 8)
        ORDER BY u.nombre ASC";

$result = $conn->query($sql);
$data = [];

// Llenar $data con los resultados de la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Reiniciar el puntero del resultado para usarlo después en la tabla
$result = $conn->query($sql);

// Obtener datos únicos para los filtros
$roles = [];
$sedes = [];

foreach ($data as $row) {
    if (!in_array($row['rol_nombre'], $roles)) {
        $roles[] = $row['rol_nombre'];
    }
    if ($row['headquarter'] && !in_array($row['headquarter'], $sedes)) {
        $sedes[] = $row['headquarter'];
    }
}

// Ordenar los arrays para mejor visualización
sort($roles);
sort($sedes);

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Filtros -->
<div class="row p-3 mb-3">
    <b class="text-left mb-3"><i class="bi bi-filter-circle"></i> Filtrar ejecutores</b>
    
    <div class="col-md-6 col-sm-12">
        <div class="filter-title"><i class="bi bi-person-badge"></i> Rol</div>
        <div class="card filter-card card-role" data-icon="👤">
            <div class="card-body">
                <select id="filterRole" class="form-select">
                    <option value="">Todos los roles</option>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars($role) ?>"><?= htmlspecialchars($role) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12">
        <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
        <div class="card filter-card card-headquarters" data-icon="🏫">
            <div class="card-body">
                <select id="filterHeadquarters" class="form-select">
                    <option value="">Todas las sedes</option>
                    <option value="Sin asignar">Sin asignar</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-responsive">
        <table id="listaEjecutores" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Documento</th>
                    <th>Nombre completo</th>
                    <th>Rol</th>
                    <th>Email</th>
                    <th>Teléfono</th>
                    <th>Género</th>
                    <th>Sede</th>
                    <th>Asignar Sede</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr id="executor-row-<?php echo $row['username']; ?>" 
                        data-role="<?= htmlspecialchars($row['rol_nombre']) ?>"
                        data-headquarters="<?= htmlspecialchars($row['headquarter'] ?? 'Sin asignar') ?>">

                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($row['nombre']); ?>
                        </td>
                        <td>
                            <?php
                            $rolClass = '';
                            switch ($row['rol']) {
                                case 5:
                                    $rolClass = 'bg-indigo-dark';
                                    break;
                                case 7:
                                    $rolClass = 'bg-teal-dark';
                                    break;
                                case 8:
                                    $rolClass = 'bg-magenta-dark';
                                    break;
                                default:
                                    $rolClass = 'btn-secondary';
                            }
                            ?>
                            <button class="btn <?php echo $rolClass; ?> btn-sm w-100">
                                <b><?php echo htmlspecialchars($row['rol_nombre']); ?></b>
                            </button>
                        </td>
                        <td><?php echo htmlspecialchars(!empty($row['email']) ? $row['email'] : 'No registrado'); ?></td>
                        <td><?php echo htmlspecialchars(!empty($row['telefono']) ? $row['telefono'] : 'No registrado'); ?></td>
                        <td><?php echo htmlspecialchars(!empty($row['genero']) ? $row['genero'] : 'No registrado'); ?></td>
                        <td id="sede-<?php echo $row['username']; ?>">
                            <b class="text-center">
                                <?php echo htmlspecialchars($row['headquarter'] ?? 'Sin asignar'); ?>
                            </b>
                        </td>
                        <td id="assign-btn-<?php echo $row['username']; ?>">
                            <button class="btn bg-indigo-dark text-white btn-sm"
                                onclick="openHeadquarterModal('<?php echo $row['username']; ?>', '<?php echo htmlspecialchars($row['nombre']); ?>', '<?php echo htmlspecialchars($row['headquarter'] ?? ''); ?>')"
                                title="Asignar/Cambiar Sede">
                                <i class="bi bi-geo-alt"></i> <?php echo ($row['headquarter'] ? 'Cambiar' : 'Asignar'); ?>
                            </button>
                        </td>
                        <td id="actions-<?php echo $row['username']; ?>">
                            <?php if ($row['carnet_id'] && file_exists($row['file_path'])): ?>
                                <div class="d-flex flex-column gap-1">
                                    <button class="btn btn-success btn-sm"
                                        onclick="viewCarnet('<?php echo $row['username']; ?>', '<?php echo htmlspecialchars($row['file_name']); ?>')"
                                        title="Ver Carnet">
                                        <i class="bi bi-eye"></i> Ver Carnet
                                    </button>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-primary btn-sm <?php echo empty($row['headquarter']) ? 'disabled' : ''; ?>"
                                    onclick="<?php echo empty($row['headquarter']) ? 'return false;' : "generateCarnet('{$row['username']}')"; ?>"
                                    title="<?php echo empty($row['headquarter']) ? 'Debe asignar una sede primero' : 'Generar Carnet'; ?>"
                                    <?php echo empty($row['headquarter']) ? 'disabled' : ''; ?>>
                                    <i class="bi bi-card-text"></i> Generar
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para asignar sede -->
<div class="modal fade" id="headquarterModal" tabindex="-1" aria-labelledby="headquarterModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="headquarterModalLabel">
                    <i class="bi bi-geo-alt"></i> Asignar Sede
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="headquarterForm">
                <div class="modal-body">
                    <input type="hidden" id="executorUsername" name="username">
                    <div class="mb-3">
                        <label for="executorName" class="form-label">Ejecutor:</label>
                        <input type="text" class="form-control" id="executorName" readonly>
                    </div>
                    <div class="mb-3">
                        <label for="headquarterSelect" class="form-label">Sede:</label>
                        <select class="form-control" id="headquarterSelect" name="headquarter" required>
                            <option value="">Seleccione una sede</option>
                            <?php
                            // Consulta para obtener las sedes
                            $query = "SELECT name FROM headquarters_attendance ORDER BY name";
                            $result_hq = $conn->query($query);

                            if ($result_hq && $result_hq->num_rows > 0) {
                                while ($row_hq = $result_hq->fetch_assoc()) {
                                    $sede = htmlspecialchars($row_hq['name']);
                                    echo "<option value=\"$sede\">$sede</option>";
                                }
                            } else {
                                echo "<option value=\"\">No hay sedes disponibles</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para visualizar carnet -->
<div class="modal fade" id="carnetModal" tabindex="-1" aria-labelledby="carnetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carnetModalLabel">
                    <i class="bi bi-card-text"></i> Carnet del Ejecutor
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <iframe id="carnetFrame" src="" width="100%" height="600" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="downloadCarnetBtn">
                    <i class="bi bi-download"></i> Descargar PDF
                </button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Solo filtros de rol y sede
    document.getElementById('filterRole').addEventListener('change', filterTable);
    document.getElementById('filterHeadquarters').addEventListener('change', filterTable);

    function filterTable() {
        const roleFilter = document.getElementById('filterRole').value.toLowerCase();
        const headquartersFilter = document.getElementById('filterHeadquarters').value.toLowerCase();
        
        const rows = document.querySelectorAll('#listaEjecutores tbody tr');
        
        rows.forEach(row => {
            const role = row.getAttribute('data-role').toLowerCase();
            const headquarters = row.getAttribute('data-headquarters').toLowerCase();
            
            const matchRole = !roleFilter || role.includes(roleFilter);
            const matchHeadquarters = !headquartersFilter || headquarters.includes(headquartersFilter);
            
            if (matchRole && matchHeadquarters) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Función para abrir modal de asignación de sede
    function openHeadquarterModal(username, nombre, currentHeadquarter) {
        document.getElementById('executorUsername').value = username;
        document.getElementById('executorName').value = nombre;
        document.getElementById('headquarterSelect').value = currentHeadquarter || '';

        // Mostrar modal (compatible con Bootstrap 4 y 5)
        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('headquarterModal'));
            modal.show();
        } else {
            $('#headquarterModal').modal('show');
        }
    }

    // Función para actualizar la fila dinámicamente
    function updateExecutorRow(username, headquarter) {
        const row = document.getElementById(`executor-row-${username}`);
        const sedeCell = document.getElementById(`sede-${username}`);
        const assignBtn = document.getElementById(`assign-btn-${username}`);
        const actionsCell = document.getElementById(`actions-${username}`);
        
        if (row && sedeCell && assignBtn && actionsCell) {
            // Actualizar atributo data-headquarters para filtros
            row.setAttribute('data-headquarters', headquarter);
            
            // Actualizar celda de sede
            sedeCell.innerHTML = `<b class="text-center">${headquarter}</b>`;
            
            // Actualizar botón de asignar
            assignBtn.innerHTML = `
                <button class="btn btn-outline-primary btn-sm"
                    onclick="openHeadquarterModal('${username}', '${document.getElementById('executorName').value}', '${headquarter}')"
                    title="Asignar/Cambiar Sede">
                    <i class="bi bi-geo-alt"></i> Cambiar
                </button>
            `;
            
            // Actualizar botón de acciones (habilitar generar carnet)
            actionsCell.innerHTML = `
                <button class="btn btn-primary btn-sm"
                    onclick="generateCarnet('${username}')"
                    title="Generar Carnet">
                    <i class="bi bi-card-text"></i> Generar
                </button>
            `;
        }
    }

    // Manejar envío del formulario de sede (SIN RELOAD)
    document.getElementById('headquarterForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const username = formData.get('username');
        const headquarter = formData.get('headquarter');

        // Mostrar loading en el botón
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="spinner-border spinner-border-sm me-2"></i>Guardando...';
        submitBtn.disabled = true;

        fetch('components/listCredentials/assign_headquarter.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Actualizar la fila dinámicamente
                    updateExecutorRow(username, headquarter);
                    
                    // Cerrar modal
                    if (typeof bootstrap !== 'undefined') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('headquarterModal'));
                        modal.hide();
                    } else {
                        $('#headquarterModal').modal('hide');
                    }
                    
                    // Mostrar mensaje de éxito
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: 'Sede asignada correctamente',
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo asignar la sede'
                });
            })
            .finally(() => {
                // Restaurar botón
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
    });

    // Función para generar carnet
    function generateCarnet(username) {
        Swal.fire({
            title: 'Generando Carnet',
            html: `
                <div class="d-flex flex-column align-items-center">
                    <div class="spinner-border text-primary mb-3" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Generando PDF del carnet...</p>
                    <small class="text-muted">Este proceso puede tomar unos segundos</small>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false
        });

        fetch(`components/listCredentials/executor_carnet.php?generate_carnet=1&number_id=${username}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.exists) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Carnet ya existe',
                            text: 'Este ejecutor ya tiene un carnet generado',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Carnet generado!',
                            text: 'El carnet se ha generado correctamente. Iniciando descarga...',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        setTimeout(() => {
                            downloadCarnet(username);
                        }, 500);
                    }
                    
                    // Actualizar la celda de acciones dinámicamente
                    const actionsCell = document.getElementById(`actions-${username}`);
                    if (actionsCell) {
                        actionsCell.innerHTML = `
                            <div class="d-flex flex-column gap-1">
                                <button class="btn btn-success btn-sm"
                                    onclick="viewCarnet('${username}', '${data.file_name}')"
                                    title="Ver Carnet">
                                    <i class="bi bi-eye"></i> Ver Carnet
                                </button>
                            </div>
                        `;
                    }
                } else {
                    throw new Error(data.message || 'Error desconocido');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message || 'No se pudo generar el carnet'
                });
            });
    }

    // Función para ver carnet en modal
    function viewCarnet(username, fileName) {
        const iframe = document.getElementById('carnetFrame');
        iframe.src = `carnets/${fileName}`;

        const downloadBtn = document.getElementById('downloadCarnetBtn');
        downloadBtn.onclick = () => downloadCarnet(username);

        if (typeof bootstrap !== 'undefined') {
            const modal = new bootstrap.Modal(document.getElementById('carnetModal'));
            modal.show();
        } else {
            $('#carnetModal').modal('show');
        }
    }

    // Función para descargar carnet
    function downloadCarnet(username) {
        const downloadUrl = `components/listCredentials/executor_carnet.php?generate_carnet=1&number_id=${username}&download=1`;

        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `carnet_${username}_${new Date().toISOString().split('T')[0]}.pdf`;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>