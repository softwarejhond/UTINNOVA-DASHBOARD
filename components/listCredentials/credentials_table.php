<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Consultas DISTINCT para llenar los filtros
$sedes = [];
$modalidades = [];
$bootcamps = [];

// Obtener sedes únicas
$resultSedes = $conn->query("SELECT DISTINCT headquarters FROM groups ORDER BY headquarters");
while ($row = $resultSedes->fetch_assoc()) {
    $sedes[] = $row['headquarters'];
}

// Obtener modalidades únicas
$resultModalidades = $conn->query("SELECT DISTINCT mode FROM groups ORDER BY mode");
while ($row = $resultModalidades->fetch_assoc()) {
    $modalidades[] = $row['mode'];
}

// Obtener bootcamps únicos (id_bootcamp y bootcamp_name)
$resultBootcamps = $conn->query("SELECT DISTINCT id_bootcamp, bootcamp_name FROM groups WHERE id_bootcamp IS NOT NULL ORDER BY bootcamp_name");
while ($row = $resultBootcamps->fetch_assoc()) {
    $bootcamps[] = $row; // Array asociativo con 'id_bootcamp' y 'bootcamp_name'
}

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Filtros existentes -->
<div class="row p-3 mb-1">
    <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar campistas</b>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-123"></i> Buscar por Número de CC</div>
        <div class="card filter-card card-number-id" data-icon="🔢">
            <div class="card-body">
                <input 
                    type="text" 
                    id="filterNumberId" 
                    class="form-control" 
                    placeholder="Ingrese número de CC" 
                    inputmode="numeric" 
                    pattern="[0-9]*"
                    maxlength="20"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                >
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
        <div class="filter-title"><i class="bi bi-mortarboard"></i> Bootcamp</div>
        <div class="card filter-card card-bootcamp" data-icon="🎓">
            <div class="card-body">
                <select id="filterBootcamp" class="form-select">
                    <option value="">Todos los bootcamps</option>
                    <?php foreach ($bootcamps as $bootcamp): ?>
                        <option value="<?= htmlspecialchars($bootcamp['id_bootcamp']) ?>"><?= htmlspecialchars($bootcamp['bootcamp_name']) ?></option>
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
            <tbody id="tableBody">
                <!-- Datos se cargarán aquí vía AJAX -->
                <tr id="loadingRow">
                    <td colspan="10" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p>Cargando datos...</p>
                    </td>
                </tr>
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
    let dataTable = null;

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

    // Función para cargar datos vía AJAX
    function loadCredentialsData() {
        const numberId = $('#filterNumberId').val() || '';
        const headquarters = $('#filterHeadquarters').val() || '';
        const bootcamp = $('#filterBootcamp').val() || '';
        const mode = $('#filterMode').val() || '';

        // Verificar si hay al menos un filtro aplicado
        if (!numberId && !headquarters && !bootcamp && !mode) {
            $('#tableBody').html(`
                <tr>
                    <td colspan="10" class="text-center py-5">
                        <div class="d-flex flex-column align-items-center">
                            <i class="bi bi-funnel text-muted" style="font-size: 3rem;"></i>
                            <h5 class="text-muted mt-3 mb-2">Aplicar filtros para ver resultados</h5>
                            <p class="text-muted mb-0">
                                Utilice los filtros superiores para buscar campistas por:
                            </p>
                            <ul class="text-muted text-start mt-2">
                                <li>Número de documento</li>
                                <li>Sede</li>
                                <li>Bootcamp</li>
                                <li>Modalidad</li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `);
            return;
        }

        // Destruir DataTable si existe
        if (dataTable) {
            dataTable.destroy();
            dataTable = null;
        }

        // Mostrar indicador de carga
        $('#tableBody').html(`
            <tr>
                <td colspan="10" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando datos...</p>
                </td>
            </tr>
        `);

        // Llamada AJAX a getCredentialsData.php
        $.ajax({
            url: 'components/listCredentials/getCredentialsData.php',
            method: 'GET',
            data: {
                number_id: numberId,
                headquarters: headquarters,
                bootcamp: bootcamp,
                mode: mode
            },
            success: function(data) {
                let html = '';
                
                if (!Array.isArray(data)) {
                    html = '<tr><td colspan="10" class="text-center text-danger">Error: Formato de datos incorrecto</td></tr>';
                } else if (data.length === 0) {
                    html = `
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class="bi bi-search text-muted" style="font-size: 2.5rem;"></i>
                                    <h5 class="text-muted mt-3 mb-2">No se encontraron resultados</h5>
                                    <p class="text-muted mb-0">
                                        No hay campistas que coincidan con los filtros aplicados.
                                    </p>
                                    <small class="text-muted">Intente modificar los criterios de búsqueda.</small>
                                </div>
                            </td>
                        </tr>
                    `;
                } else {
                    data.forEach((row) => {
                        html += `
                            <tr>
                                <td>${row.type_id || 'N/A'}</td>
                                <td>${row.number_id || 'N/A'}</td>
                                <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${row.full_name || 'N/A'}</td>
                                <td>${row.first_phone || 'N/A'}</td>
                                <td>${row.email || 'N/A'}</td>
                                <td>${row.institutional_email || 'N/A'}</td>
                                <td>
                                    ${(row.group_department === 'CUNDINAMARCA') ? `<button class='btn bg-lime-light w-100'><b>${row.group_department}</b></button>` : 
                                      (row.group_department === 'BOYACÁ') ? `<button class='btn bg-indigo-light w-100'><b>${row.group_department}</b></button>` : 
                                      `<span>${row.group_department || 'N/A'}</span>`}
                                </td>
                                <td><b class="text-center">${row.headquarters || 'N/A'}</b></td>
                                <td>${row.mode || 'N/A'}</td>
                                <td>
                                    ${(row.carnet_id && row.file_path) ? `
                                        <div class="d-flex flex-column gap-1">
                                            <button class="btn btn-success btn-sm" onclick="viewCarnet('${row.number_id || ''}', '${row.file_name || ''}')" title="Ver Carnet">
                                                <i class="bi bi-eye"></i> Ver Carnet
                                            </button>
                                        </div>
                                    ` : `
                                        <button class="btn btn-primary btn-sm" onclick="generateCarnet('${row.number_id || ''}')" title="Generar Carnet">
                                            <i class="bi bi-card-text"></i> Generar
                                        </button>
                                    `}
                                </td>
                            </tr>
                        `;
                    });
                }
                
                $('#tableBody').html(html);

                // Reinicializar DataTable después de cargar datos
                if (data.length > 0) {
                    setTimeout(() => {
                        try {
                            dataTable = $('#listaInscritos').DataTable({
                                responsive: true,
                                language: {
                                    url: "controller/datatable_esp.json"
                                },
                                pagingType: "simple",
                                destroy: true
                            });
                        } catch (error) {
                            // Error silencioso
                        }
                    }, 100);
                }
            },
            error: function(xhr, status, error) {
                $('#tableBody').html('<tr><td colspan="10" class="text-center text-danger">Error al cargar datos: ' + error + '</td></tr>');
            }
        });
    }

    // Document ready y event listeners
    $(document).ready(function() {
        // Mostrar mensaje inicial
        $('#tableBody').html(`
            <tr>
                <td colspan="10" class="text-center py-5">
                    <div class="d-flex flex-column align-items-center">
                        <i class="bi bi-funnel text-muted" style="font-size: 3rem;"></i>
                        <h5 class="text-muted mt-3 mb-2">Aplicar filtros para ver resultados</h5>
                        <p class="text-muted mb-0">
                            Utilice los filtros superiores para buscar campistas por:
                        </p>
                        <ul class="text-muted text-start mt-2">
                            <li>Número de documento</li>
                            <li>Sede</li>
                            <li>Bootcamp</li>
                            <li>Modalidad</li>
                        </ul>
                    </div>
                </td>
            </tr>
        `);

        // Event listeners para filtros
        let searchTimeout;
        $('#filterNumberId').on('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                loadCredentialsData();
            }, 500);
        });

        $('#filterHeadquarters, #filterBootcamp, #filterMode').on('change', function() {
            loadCredentialsData();
        });
    });

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

