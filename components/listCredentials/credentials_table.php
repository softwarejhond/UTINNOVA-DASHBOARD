<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Modificar la consulta para incluir información del carnet
$sql = "SELECT g.*, ur.*, g.department as group_department, 
               cr.id as carnet_id, cr.file_path, cr.file_name, cr.generated_at as carnet_date
        FROM groups g 
        LEFT JOIN user_register ur ON g.number_id = ur.number_id
        LEFT JOIN carnet_records cr ON g.number_id = cr.number_id AND cr.is_active = 1";

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
$departamentos = ['BOYACÁ', 'CUNDINAMARCA'];
$programas = [];
$modalidades = [];
$sedes = [];

foreach ($data as $row) {
    $sede = $row['headquarters'];
    if (!in_array($sede, $sedes)) {
        $sedes[] = $sede;
    }
    if (!in_array($row['program'], $programas)) {
        $programas[] = $row['program'];
    }
    if (!in_array($row['mode'], $modalidades)) {
        $modalidades[] = $row['mode'];
    }
    if (!in_array($row['group_department'], $departamentos)) {
        $departamentos[] = $row['group_department'];
    }
}

// Ordenar los arrays para mejor visualización
sort($sedes);
sort($programas);
sort($modalidades);

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Filtros existentes -->
<div class="row p-3 mb-1">
    <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar beneficiario</b>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-map"></i> Departamento</div>
        <div class="card filter-card card-department" data-icon="📍">
            <div class="card-body">
                <select id="filterDepartment" class="form-select">
                    <option value="">Todos los departamentos</option>
                    <option value="BOYACÁ">BOYACÁ</option>
                    <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
        <div class="card filter-card card-headquarters" data-icon="🏫">
            <div class="card-body">
                <select id="filterHeadquarters" class="form-select">
                    <option value="">Todas las sedes</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-mortarboard"></i> Programa</div>
        <div class="card filter-card card-program" data-icon="🎓">
            <div class="card-body">
                <select id="filterProgram" class="form-select">
                    <option value="">Todos los programas</option>
                    <?php foreach ($programas as $programa): ?>
                        <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-laptop"></i> Modalidad</div>
        <div class="card filter-card card-mode" data-icon="💻">
            <div class="card-body">
                <select id="filterMode" class="form-select">
                    <option value="">Todas las modalidades</option>
                    <option value="Virtual">Virtual</option>
                    <option value="Presencial">Presencial</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-responsive">
        <button id="exportarExcel" class="btn btn-success mb-3"
            onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
            <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
        </button>
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Numero de ID</th>
                    <th>Nombre completo</th>
                    <th>Telefono</th>
                    <th>Correo personal</th>
                    <th>Correo institucional</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Modalidad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr data-department="<?= htmlspecialchars($row['group_department']) ?>"
                        data-headquarters="<?= htmlspecialchars($row['headquarters']) ?>"
                        data-program="<?= htmlspecialchars($row['program']) ?>"
                        data-mode="<?= htmlspecialchars($row['mode']) ?>">

                        <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['first_phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['institutional_email']); ?></td>
                        <td>
                            <?php
                            $departamento = htmlspecialchars($row['group_department']);
                            if ($departamento === 'CUNDINAMARCA') {
                                echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>";
                            } elseif ($departamento === 'BOYACÁ') {
                                echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>";
                            } else {
                                echo "<span>{$departamento}</span>";
                            }
                            ?>
                        </td>
                        <td><b class="text-center"><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                        <td>
                            <?php if ($row['carnet_id'] && file_exists($row['file_path'])): ?>
                                <div class="d-flex flex-column gap-1">
                                    <button class="btn btn-success btn-sm" 
                                            onclick="viewCarnet('<?php echo $row['number_id']; ?>', '<?php echo htmlspecialchars($row['file_name']); ?>')"
                                            title="Ver Carnet">
                                        <i class="bi bi-eye"></i> Ver Carnet
                                    </button>
                                </div>
                            <?php else: ?>
                                <button class="btn btn-primary btn-sm" 
                                        onclick="generateCarnet('<?php echo $row['number_id']; ?>')"
                                        title="Generar Carnet">
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

<!-- Modal para visualizar carnet -->
<div class="modal fade" id="carnetModal" tabindex="-1" aria-labelledby="carnetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="carnetModalLabel">
                    <i class="bi bi-card-text"></i> Carnet del Estudiante
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
    // Función para generar carnet
    function generateCarnet(numberId) {
        // Mostrar loader
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

        // Llamada AJAX para generar carnet
        fetch(`components/listCredentials/generate_carnet.php?generate_carnet=1&number_id=${numberId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.exists) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Carnet ya existe',
                            text: 'Este estudiante ya tiene un carnet generado',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    } else {
                        // Carnet generado exitosamente, ahora descargarlo
                        Swal.fire({
                            icon: 'success',
                            title: '¡Carnet generado!',
                            text: 'El carnet se ha generado correctamente. Iniciando descarga...',
                            timer: 2000,
                            showConfirmButton: false
                        });

                        // Iniciar descarga automática después de un breve delay
                        setTimeout(() => {
                            downloadCarnet(numberId);
                            // Recargar la página después de iniciar la descarga
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }, 500);
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
    function viewCarnet(numberId, fileName) {
        const iframe = document.getElementById('carnetFrame');
        iframe.src = `carnets/${fileName}`;
        
        // Configurar botón de descarga
        const downloadBtn = document.getElementById('downloadCarnetBtn');
        downloadBtn.onclick = () => downloadCarnet(numberId);
        
        // Mostrar modal (compatible con Bootstrap 4 y 5)
        if (typeof bootstrap !== 'undefined') {
            // Bootstrap 5
            const modal = new bootstrap.Modal(document.getElementById('carnetModal'));
            modal.show();
        } else {
            // Bootstrap 4
            $('#carnetModal').modal('show');
        }
    }

    // Función para descargar carnet
    function downloadCarnet(numberId) {
        const downloadUrl = `components/listCredentials/generate_carnet.php?generate_carnet=1&number_id=${numberId}&download=1`;
        
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = `carnet_${numberId}_${new Date().toISOString().split('T')[0]}.pdf`;
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Manejo global de errores para requests fallidos
    window.addEventListener('error', function(e) {
        if (e.target.tagName === 'A' && e.target.href.includes('generate_carnet.php')) {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Verifique su conexión a internet.',
                confirmButtonText: 'Entendido'
            });
        }
    });
</script>

