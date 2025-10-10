<style>
    .badge-total-metas {
        background-color: #30336b !important;
        color: #fff !important;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 0.5rem;
        padding: 0.4em 1em;
    }

    /* Segundo badge: Total Beneficiarios */
    .row.mb-4 .col-md-4:nth-child(2) .badge-total-metas {
        background-color: #ec008c !important;
    }

    /* Tercer badge: Total Matriculados */
    .row.mb-4 .col-md-4:nth-child(3) .badge-total-metas {
        background-color: #006d68 !important;
    }

    /* Cuarto badge: Total Formados */
    .row.w-100 .col-md-4:nth-child(1) .badge-total-metas {
        background-color: #e67300 !important;
    }

    /* Quinto badge: Total No Aprobados */
    .row.w-100 .col-md-4:nth-child(2) .badge-total-metas {
        background-color: #c52424ff !important;
        /* Nuevo color para No Aprobados */
    }



    .card-meta {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    /* Hover solo para tarjetas que NO sean de filtros */
    .card-meta:not(.card-filtros):hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 20px rgba(0, 0, 0, 0.15);
    }

    /* Tarjeta de filtros sin hover */
    .card-filtros {
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        /* Sin transition ni hover */
    }

    .progreso-meta {
        height: 12px;
        background: #e0e0e0;
        border-radius: 6px;
        overflow: hidden;
        margin-top: 8px;
    }

    .progreso-bar {
        height: 100%;
        background: #30336b;
        transition: width 0.5s;
    }

    /* Solución completa para dropdowns */
    .dropdown {
        position: relative;
        z-index: 1000;
    }

    .dropdown.show {
        z-index: 10000 !important;
    }

    .dropdown-menu {
        position: absolute !important;
        z-index: 10001 !important;
        top: 100% !important;
        left: 0 !important;
        width: 100% !important;
        transform: none !important;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        border: 1px solid rgba(0, 0, 0, 0.15) !important;
    }

    /* Contenedores padre deben permitir overflow visible */
    .card-body {
        position: relative;
        overflow: visible !important;
    }

    .card {
        overflow: visible !important;
    }

    .row {
        overflow: visible !important;
    }

    .col-md-4 {
        overflow: visible !important;
    }

    /* Asegurar que los dropdowns estén por encima de todo */
    .dropdown-menu.show {
        z-index: 10050 !important;
    }

    #listaSenaTICS td,
    #listaSenaTICS th {
        white-space: nowrap !important;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: middle;
    }
</style>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total Inscritos</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeInscritos" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total beneficiarios</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeBeneficiarios" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card card-meta h-100 bg-white text-dark">
            <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                <h4 class="card-title mb-3">Total matriculados</h4>
                <span class="badge badge-total-metas mt-auto mb-2" id="badgeMatriculados" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
            </div>
        </div>
    </div>
    <div class="col-12 mt-3 d-flex justify-content-center">
        <div class="row w-100 justify-content-center">
            <div class="col-md-4">
                <div class="card card-meta h-100 bg-white text-dark">
                    <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                        <h4 class="card-title mb-3">Total formados</h4>
                        <span class="badge badge-total-metas mt-auto mb-2" id="badgeFormados" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-meta h-100 bg-white text-dark">
                    <div class="card-body d-flex flex-column align-items-center justify-content-between text-center" style="height: 100%;">
                        <h4 class="card-title mb-3">Total No aprobados</h4>
                        <span class="badge badge-total-metas mt-auto mb-2" id="badgeNoAprobados" style="font-size:1.5rem; padding:0.6em 1.5em;"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="col-12 mb-4">
    <div class="card card-filtros bg-white text-dark">
        <div class="card-header bg-indigo-dark text-white">
            <h5 class="card-title mb-0">Filtrado de listado</h5>
        </div>
        <div class="card-body w-100">
            <form class="d-flex flex-wrap gap-3 align-items-end w-100" id="formFiltroListado" autocomplete="off">
                <div class="flex-fill">
                    <label for="selectPrograma" class="form-label">Programa</label>
                    <select class="form-select" id="selectPrograma" name="programa">
                        <option value="">Seleccione un programa</option>
                        <?php
                        // Assuming $conn is your database connection
                        $query = "SELECT DISTINCT program FROM user_register WHERE institution = 'SenaTICS'";
                        $result = mysqli_query($conn, $query);
                        while ($row = mysqli_fetch_assoc($result)) {
                            echo '<option value="' . htmlspecialchars($row['program']) . '">' . htmlspecialchars($row['program']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="flex-fill">
                    <label for="inputBusqueda" class="form-label">Buscar por número de cedula</label>
                    <input type="text" class="form-control" id="inputBusqueda" name="busqueda" maxlength="15" pattern="\d*" inputmode="numeric" placeholder="Ingrese número" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                </div>
                <div class="flex-shrink-0">
                    <button type="button" class="btn bg-indigo-dark text-white" id="btnSearch">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>
            </form>         
        </div>
    </div>
</div>

<div class="row mt-3 mb-3">
    <div class="col-12 d-flex justify-content-center">
        <button type="button" class="btn bg-teal-dark text-white" id="btnDownloadExcel">
            <i class="fas fa-file-excel"></i> Descargar Excel
        </button>
    </div>
</div>

<div class="col-12 mb-4">
    <div class="card bg-white text-dark">
        <div class="card-header bg-indigo-dark text-white">
            <h5 class="card-title mb-0">Lista de Inscritos SenaTICS</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="listaSenaTICS">
                    <thead>
                        <tr>
                            <th>Número ID</th>
                            <th>Nombre Completo</th>
                            <th>Fecha Nacimiento</th>
                            <th>Correo</th>
                            <th>Teléfono</th>
                            <th>Programa</th>
                            <th>Modalidad</th>
                            <th>Estado Adm.</th>
                            <th>Nombre Bootcamp</th>
                            <th>Nombre Inglés Nivelador</th>
                            <th>Nombre English Code</th>
                            <th>Nombre Habilidades</th>
                            <th>Fecha Matrícula</th>
                            <th>Nivel</th>
                            <th>Fecha Prueba</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center" colspan="15">
                                <i class="bi bi-info-circle"></i> Selecciona un filtro para cargar los datos
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function updateCounts() {
        fetch('components/goalsAndPayments/getSenaTICSCounts.php')
            .then(response => response.json())
            .then(data => {
                document.getElementById('badgeInscritos').textContent = data.inscritos;
                document.getElementById('badgeBeneficiarios').textContent = data.beneficiarios;
                document.getElementById('badgeMatriculados').textContent = data.matriculados;
                document.getElementById('badgeFormados').textContent = data.formados;
                document.getElementById('badgeNoAprobados').textContent = data.noAprobados;
            })
            .catch(error => console.error('Error al actualizar conteos:', error));
    }

    // Actualizar inmediatamente al cargar la página
    updateCounts();

    // Polling cada 30 segundos
    setInterval(updateCounts, 30000);
</script>

<script>
    let table = null;

    function loadTable(programa = '', busqueda = '') {
        if (!programa && !busqueda) {
            if (!table) {
                table = $('#listaSenaTICS').DataTable({
                    responsive: true,
                    language: {
                        url: "controller/datatable_esp.json"
                    },
                    pagingType: "simple"
                });
            }
            table.clear();
            table.row.add(['<i class="bi bi-info-circle"></i> Selecciona un filtro para cargar los datos', '', '', '', '', '', '', '', '', '', '', '', '', '', '']).draw();
            return;
        }

        // Mostrar loader con SweetAlert
        Swal.fire({
            title: 'Cargando datos...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        const url = `components/goalsAndPayments/getSenaTICSList.php?programa=${encodeURIComponent(programa)}&busqueda=${encodeURIComponent(busqueda)}`;
        fetch(url)
            .then(response => response.json())
            .then(data => {
                if (!table) {
                    table = $('#listaSenaTICS').DataTable({
                        responsive: true,
                        language: {
                            url: "controller/datatable_esp.json"
                        },
                        pagingType: "simple"
                    });
                }
                table.clear();
                if (data.length > 0) {
                    table.rows.add(data.map(row => [
                        row.number_id,
                        row.nombre_completo,
                        row.birth_date,
                        row.email,
                        row.phone,
                        row.program,
                        row.modalidad,
                        row.statusAdmin,
                        row.bootcamp_name,
                        row.leveling_english_name,
                        row.english_code_name,
                        row.skills_name,
                        row.fecha_matricula,
                        row.nivel,
                        row.fecha_prueba
                    ])).draw();
                } else {
                    table.row.add(['No se encontraron resultados', '', '', '', '', '', '', '', '', '', '', '', '', '', '']).draw();
                }

                // Cerrar loader después de procesar
                Swal.close();
            })
            .catch(error => {
                console.error('Error al cargar la tabla:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los datos. Inténtalo de nuevo.'
                });
            });
    }

    // Prevenir submit del form
    $('#formFiltroListado').submit(function(e) {
        e.preventDefault();
    });

    // Eventos para cargar datos bajo demanda
    $('#selectPrograma').change(function() {
        $('#inputBusqueda').val(''); // Limpiar el input de búsqueda
        const programa = $(this).val();
        const busqueda = '';
        loadTable(programa, busqueda);
    });

    $('#btnSearch').click(function() {
        const programa = $('#selectPrograma').val();
        const busqueda = $('#inputBusqueda').val();
        loadTable(programa, busqueda);
    });

    $('#inputBusqueda').keypress(function(e) {
        if (e.which == 13) {
            e.preventDefault(); // Prevenir submit
            const programa = $('#selectPrograma').val();
            const busqueda = $(this).val();
            loadTable(programa, busqueda);
        }
    });

    $('#btnDownloadExcel').click(function() {
        // Mostrar loader con SweetAlert
        Swal.fire({
            title: 'Generando y descargando Excel...',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        // Iniciar descarga
        window.location.href = 'components/goalsAndPayments/export_excel_senatics.php?action=export';

        // Cerrar loader después de un tiempo estimado (ajustar según necesidad)
        setTimeout(() => {
            Swal.close();
        }, 8000); // 5 segundos, ajustar si es necesario
    });
</script>