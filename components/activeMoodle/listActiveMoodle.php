<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Solo obtener datos para los filtros iniciales
$sql = "SELECT DISTINCT g.headquarters, g.bootcamp_name FROM groups g ORDER BY g.headquarters, g.bootcamp_name";
$result = $conn->query($sql);

$sedes = [];
$bootcamps = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        if (!in_array($row['headquarters'], $sedes)) {
            $sedes[] = $row['headquarters'];
        }
        if (!in_array($row['bootcamp_name'], $bootcamps)) {
            $bootcamps[] = $row['bootcamp_name'];
        }
    }
}

sort($sedes);
sort($bootcamps);
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="row p-3 mb-1">
    <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar beneficiario</b>

    <div class="col-md-6 col-sm-12 col-lg-4">
        <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
        <div class="card filter-card card-headquarters" data-icon="🏫">
            <div class="card-body">
                <select id="filterHeadquarters" class="form-select">
                    <option value="">Seleccionar la sede</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-4">
        <div class="filter-title"><i class="bi bi-mortarboard"></i> Bootcamp</div>
        <div class="card filter-card card-bootcamp" data-icon="🎓">
            <div class="card-body">
                <select id="filterBootcamp" class="form-select">
                    <option value="">Selecciona bootcamp</option>
                    <?php foreach ($bootcamps as $bootcamp): ?>
                        <option value="<?= htmlspecialchars($bootcamp) ?>"><?= htmlspecialchars($bootcamp) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-4">
        <div class="filter-title"><i class="bi bi-search"></i> Buscar por CC</div>
        <div class="card filter-card card-search-id" data-icon="🔎">
            <div class="card-body d-flex align-items-center">
                <input type="text" id="searchNumberId" class="form-control mr-2" placeholder="Número de CC"
                    maxlength="15"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                <button type="button" class="btn bg-indigo-dark" id="btnSearchId">
                    <i class="bi bi-search"></i>
                </button>
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
        
        <div id="loadingIndicator" class="text-center" style="display: none;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p>Cargando datos...</p>
        </div>
        
        <div id="resultCount" class="mb-2"></div>
        
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center" style="white-space:nowrap;">
                    <th style="white-space:nowrap;">Tipo ID</th>
                    <th style="white-space:nowrap;">Numero de ID</th>
                    <th style="white-space:nowrap;">Nombre completo</th>
                    <th style="white-space:nowrap;">Telefono</th>
                    <th style="white-space:nowrap;">Correo personal</th>
                    <th style="white-space:nowrap;">Correo institucional</th>
                    <th style="white-space:nowrap;">Horario</th>
                    <th style="white-space:nowrap;">Departamento</th>
                    <th style="white-space:nowrap;">Sede</th>
                    <th style="white-space:nowrap;">Aula</th>
                    <th style="white-space:nowrap;">Modalidad</th>
                    <th style="white-space:nowrap;">Bootcamp</th>
                    <th style="white-space:nowrap;">Ingles Nivelatorio</th>
                    <th style="white-space:nowrap;">English Code</th>
                    <th style="white-space:nowrap;">Habilidades</th>
                    <th style="white-space:nowrap;">Fecha de matricula</th>
                    <th style="white-space:nowrap;">Cohorte</th>
                    <th style="white-space:nowrap;">Nivel elegido</th>
                    <th style="white-space:nowrap;">Puntaje de prueba</th>
                    <th style="white-space:nowrap;">Nivel obtenido</th>
                    <th style="white-space:nowrap;">Desmatricular</th>
                </tr>
            </thead>
            <tbody id="tableBody">
                <tr>
                    <td class="text-center" colspan="15">
                        <i class="bi bi-info-circle"></i> Selecciona un filtro para cargar los datos
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para confirmación de eliminación -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white item-align-center">
                <h5 class="modal-title text-center" id="deleteModalLabel">
                    <i class="bi bi-exclamation-circle"></i> ATENCIÓN
                </h5>
            </div>
            <div class="modal-body">
                <h5>Nombre: <strong id="deleteUserName"></strong></h5>
                <h5>Número ID: <strong id="deleteUserId"></strong></h5>

                <hr>
                <div class="card shadow-lg p-3 mb-5 bg-body-tertiary rounded">
                    <p class="mb-1">Para confirmar la eliminación, ingresa el código de seguridad:</p>
                    <div class="code-container mt-2 mb-3 p-2 bg-light">
                        <div class="input-group">
                            <input type="text" class="form-control security-code" id="securityCode"
                                readonly value="" style="font-family: monospace; letter-spacing: 3px; text-align: center; font-weight: bold;">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary bg-indigo-dark" type="button" id="copyBtn"
                                    onclick="copiarCodigo()">
                                    <i class="bi bi-clipboard"></i> Copiar
                                </button>
                            </div>
                        </div>
                        <small class="text-muted text-center d-block mt-1">Este código cambiará en <span id="codeTimer">15</span> segundos</small>
                    </div>
                    <div class="form-group">
                        <label for="confirmCode">Ingresa el código</label>
                        <input type="text" class="form-control-lg text-center w-100" id="confirmCode" placeholder="Código">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-magenta-dark text-white" id="deleteBtn" disabled>Eliminar</button>
            </div>
        </div>
    </div>
</div>

<script>
let currentUserId = '';
let securityCode = '';
let timer = 15;
let interval;

function copiarCodigo() {
    const codigoInput = document.getElementById('securityCode');
    navigator.clipboard.writeText(codigoInput.value).then(() => {
        const copyBtn = document.getElementById('copyBtn');
        const originalContent = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
        copyBtn.classList.replace('btn-outline-secondary', 'btn-success');
        setTimeout(function() {
            copyBtn.innerHTML = originalContent;
            copyBtn.classList.replace('btn-success', 'btn-outline-secondary');
        }, 1500);
    }).catch(err => {
        console.error('Error al copiar: ', err);
    });
}

function generateCode() {
    const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
    let result = '';
    for (let i = 0; i < 6; i++) {
        result += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    return result;
}

function updateTimer() {
    timer--;
    $("#codeTimer").text(timer);

    if (timer <= 0) {
        timer = 15;
        securityCode = generateCode();
        $("#securityCode").val(securityCode);
    }
}

function loadData() {
    const headquarters = $('#filterHeadquarters').val();
    const bootcamp = $('#filterBootcamp').val();
    const numberId = $('#searchNumberId').val();

    if (!headquarters && !bootcamp && !numberId) {
        $('#tableBody').html('<tr><td colspan="15" class="text-center"><i class="bi bi-info-circle"></i> Selecciona un filtro para cargar los datos</td></tr>');
        $('#resultCount').html('');
        return;
    }

    Swal.fire({
        title: 'Cargando datos...',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'components/activeMoodle/filterActiveMoodle.php',
        type: 'POST',
        data: {
            headquarters: headquarters,
            bootcamp: bootcamp,
            number_id: numberId
        },
        dataType: 'json',
        success: function(response) {
            Swal.close();
            if (response.success) {
                $('#tableBody').html(response.html);
                $('[data-toggle="popover"]').popover();

                // Destruye el DataTable si ya existe
                if ($.fn.DataTable.isDataTable('#listaInscritos')) {
                    $('#listaInscritos').DataTable().destroy();
                }
                // Inicializa de nuevo DataTable
                $('#listaInscritos').DataTable({
                    responsive: true,
                    language: {
                        url: "controller/datatable_esp.json"
                    },
                    paging: false,
                    info: false,
                    drawCallback: function(settings) {
                        $.fn.dataTable.ext.errMode = 'none';
                    }
                });
            } else {
                $('#tableBody').html('<tr><td colspan="15" class="text-center text-danger">' + (response.error ? response.error : 'Error al cargar los datos') + '</td></tr>');
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            Swal.close();
            $('#tableBody').html('<tr><td colspan="15" class="text-center text-danger">Error de conexión: ' + textStatus + '</td></tr>');
        }
    });
}

$(document).ready(function() {
    // Cuando cambia la sede, reinicia bootcamp y CC
    $('#filterHeadquarters').change(function() {
        $('#filterBootcamp').val('');
        $('#searchNumberId').val('');
        loadData();
    });

    // Cuando cambia el bootcamp, reinicia sede y CC
    $('#filterBootcamp').change(function() {
        $('#filterHeadquarters').val('');
        $('#searchNumberId').val('');
        loadData();
    });

    // Cuando busca por CC, reinicia sede y bootcamp
    $('#btnSearchId').click(function() {
        $('#filterHeadquarters').val('');
        $('#filterBootcamp').val('');
        loadData();
    });

    $('#searchNumberId').keypress(function(e) {
        if (e.which == 13) {
            $('#filterHeadquarters').val('');
            $('#filterBootcamp').val('');
            loadData();
        }
    });
    
    // Event listener para botones de eliminar (delegado)
    $(document).on('click', '.btn-delete', function() {
        currentUserId = $(this).data('id');
        const userName = $(this).data('name');
        
        $('#deleteUserId').text(currentUserId);
        $('#deleteUserName').text(userName);
        $('#deleteModal').modal('show');
    });
    
    // Modal events
    $('#deleteModal').on('shown.bs.modal', function() {
        securityCode = generateCode();
        $("#securityCode").val(securityCode);
        timer = 15;
        $("#codeTimer").text(timer);
        clearInterval(interval);
        interval = setInterval(updateTimer, 1000);
    });

    $('#deleteModal').on('hidden.bs.modal', function() {
        clearInterval(interval);
        $("#confirmCode").val('');
        $("#deleteBtn").prop('disabled', true);
    });

    $("#confirmCode").on('input', function() {
        const inputCode = $(this).val();
        $("#deleteBtn").prop('disabled', inputCode !== securityCode);
    });

    $("#deleteBtn").click(function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción eliminará permanentemente la matrícula del usuario con ID " + currentUserId,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Procesando desmatriculación',
                    text: 'Por favor espere...',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading()
                    }
                });

                $.ajax({
                    url: 'components/activeMoodle/deleteMatricula.php',
                    type: 'POST',
                    data: {
                        number_id: currentUserId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: 'La matrícula ha sido eliminada correctamente.',
                                allowOutsideClick: false
                            }).then(() => {
                                $('#deleteModal').modal('hide');
                                loadData(); // Recargar datos
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message || 'Ocurrió un error al eliminar la matrícula.'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error en la comunicación con el servidor.'
                        });
                    }
                });
            }
        });
    });
});
</script>