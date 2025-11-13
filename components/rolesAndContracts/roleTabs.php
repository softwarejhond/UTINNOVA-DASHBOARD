<?php
function getRolName($rol)
{
    switch ($rol) {
        case 1:
            return "Administrador";
        case 2:
            return "Editor";
        case 3:
            return "Asesor";
        case 4:
            return "Visualizador";
        case 5:
            return "Docente";
        case 6:
            return "Académico";
        case 7:
            return "Monitor";
        case 8:
            return "Mentor";
        case 9:
            return "Supervisor";
        case 10:
            return "Empleabilidad";
        case 11:
            return "Superacademico";
        case 12:
            return "Control maestro";
        case 13:
            return "Interventoría";
        case 14:
            return "Permanencia";
        case 15:
            return "Triangulo";
        default:
            return "Rol desconocido";
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

    .table td,
    .table th {
        white-space: nowrap;
        text-align: center;
        vertical-align: middle;
    }
</style>

<ul class="nav nav-tabs mb-3" id="personalTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="academico-tab" data-bs-toggle="tab" data-bs-target="#personalAcademico" type="button" role="tab" aria-controls="personalAcademico" aria-selected="true">
            Personal Académico
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="administrativo-tab" data-bs-toggle="tab" data-bs-target="#personalAdministrativo" type="button" role="tab" aria-controls="personalAdministrativo" aria-selected="false">
            Personal Administrativo
        </button>
    </li>
</ul>

<div class="tab-content" id="personalTabsContent">
    <div class="tab-pane fade show active" id="personalAcademico" role="tabpanel" aria-labelledby="academico-tab">
        <div class="container-fluid mt-4">
            <div class="card shadow mb-3">
                <div class="card-body rounded-0">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Personal Académico</h4>
                            <button type="button" class="btn bg-magenta-dark text-white" id="exportarTodos">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Descargar reporte
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="personalAcademicoTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Email</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_academico = "SELECT username, nombre, rol, orden, email, genero, telefono FROM users WHERE rol IN (5,7,8)";
                                    $result_academico = $conn->query($sql_academico);
                                    if ($result_academico && $result_academico->num_rows > 0) {
                                        while ($row = $result_academico->fetch_assoc()) {
                                            $nombre = $row['nombre'] ? strtoupper($row['nombre']) : 'Nombre no disponible';
                                            $estado = $row['orden'] == 1 ? 'Activo' : 'Inactivo';
                                            $email = $row['email'] ?: 'Email no disponible';
                                            $genero = $row['genero'] ?: 'Género no disponible';
                                            $telefono = $row['telefono'] ?: 'Teléfono no disponible';
                                            $rolNombre = getRolName($row['rol']);
                                            echo "<tr>
                                                <td>{$row['username']}</td>
                                                <td>{$nombre}</td>
                                                <td>{$rolNombre}</td>
                                                <td>{$estado}</td>
                                                <td>{$email}</td>
                                                <td>{$genero}</td>
                                                <td>{$telefono}</td>
                                                <td><button class='btn btn-sm btn-sm bg-indigo-dark text-white me-1 btn-asignar' data-username='{$row['username']}' data-nombre='{$nombre}' data-rol='{$rolNombre}' data-type='academico' data-bs-toggle='modal' data-bs-target='#asignarModal'><i class='bi bi-pencil'></i></button></td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No hay datos disponibles</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="tab-pane fade" id="personalAdministrativo" role="tabpanel" aria-labelledby="administrativo-tab">
        <div class="container-fluid mt-4">
            <div class="card shadow mb-3">
                <div class="card-body rounded-0">
                    <div class="container-fluid">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="mb-0">Personal Administrativo</h4>
                            <button class="btn bg-orange-dark text-white" id="exportarAdministrativo">
                                <i class="bi bi-file-earmark-spreadsheet"></i> Descargar reporte
                            </button>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle" id="personalAdministrativoTable">
                                <thead class="table-light">
                                    <tr>
                                        <th>Identificación</th>
                                        <th>Nombre</th>
                                        <th>Rol</th>
                                        <th>Estado</th>
                                        <th>Email</th>
                                        <th>Género</th>
                                        <th>Teléfono</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql_administrativo = "SELECT username, nombre, rol, orden, email, genero, telefono FROM users WHERE rol NOT IN (5,7,8)";
                                    $result_administrativo = $conn->query($sql_administrativo);
                                    if ($result_administrativo && $result_administrativo->num_rows > 0) {
                                        while ($row = $result_administrativo->fetch_assoc()) {
                                            $nombre = $row['nombre'] ? strtoupper($row['nombre']) : 'Nombre no disponible';
                                            $estado = $row['orden'] == 1 ? 'Activo' : 'Inactivo';
                                            $email = $row['email'] ?: 'Email no disponible';
                                            $genero = $row['genero'] ?: 'Género no disponible';
                                            $telefono = $row['telefono'] ?: 'Teléfono no disponible';
                                            $rolNombre = getRolName($row['rol']);
                                            echo "<tr>
                                                <td>{$row['username']}</td>
                                                <td>{$nombre}</td>
                                                <td>{$rolNombre}</td>
                                                <td>{$estado}</td>
                                                <td>{$email}</td>
                                                <td>{$genero}</td>
                                                <td>{$telefono}</td>
                                                <td><button class='btn btn-sm bg-indigo-dark text-white btn-asignar' data-username='{$row['username']}' data-nombre='{$nombre}' data-rol='{$rolNombre}' data-type='administrativo' data-bs-toggle='modal' data-bs-target='#asignarModal'><i class='bi bi-pencil'></i></button></td>
                                            </tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center'>No hay datos disponibles</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar información -->
<div class="modal fade" id="asignarModal" tabindex="-1" aria-labelledby="asignarModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asignarModalLabel">Asignar información para formato de radicados</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Información de la persona:</h6>
                    <div class="row">
                        <div class="col-12 mb-2">
                            <label class="form-label">Nombre:</label>
                            <input type="text" class="form-control text-center" id="modalNombre" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Cédula:</label>
                            <input type="text" class="form-control text-center" id="modalCedula" readonly>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Rol:</label>
                            <input type="text" class="form-control text-center" id="modalRol" readonly>
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <h6>Asignaciones existentes:</h6>
                    <div id="existingAssignments" class="table-responsive">
                        <!-- Aquí se poblará la tabla de asignaciones existentes -->
                    </div>
                </div>
                <form id="asignarForm">
                    <h6 id="formTitle">Agregar nueva asignación:</h6>
                    <div class="mb-3">
                        <label for="radicado" class="form-label">Radicado</label>
                        <input type="text" class="form-control" id="radicado" name="radicado" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label for="fechaRadicado" class="form-label">Fecha del Radicado</label>
                            <input type="date" class="form-control" id="fechaRadicado" name="fechaRadicado" required>
                        </div>
                        <div class="col-6">
                            <label for="lote" class="form-label">Lote</label>
                            <select class="form-select" id="lote" name="lote" required>
                                <option value="1">1</option>
                                <option value="2">2</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="rolContrato" class="form-label">Rol de Contrato</label>
                        <select class="form-select" id="rolContrato" name="rolContrato" required>
                            <!-- Opciones se poblarán dinámicamente -->
                        </select>
                    </div>
                    
                    <input type="hidden" id="assignmentId" name="assignmentId">  <!-- Nuevo: campo para ID -->
                    <input type="hidden" id="username" name="username">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-teal-dark text-white" id="guardarAsignacion">Guardar</button>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">

<!-- Modal para asignar cursos -->
<div class="modal fade" id="asignarCursosModal" tabindex="-1" aria-labelledby="asignarCursosModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="asignarCursosModalLabel">Seleccionar Cursos</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body d-flex flex-column gap-3">
                <div class="row">
                    <div class="col-12 d-flex flex-column align-items-start gap-3">
                        <label for="cursosSelect" class="form-label mb-0">Selecciona uno o varios cursos:</label>
                        <select id="cursosSelect" class="form-select" multiple="multiple" style="width: 100%;">
                            <!-- Opciones se poblarán dinámicamente -->
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-teal-dark text-white" id="exportarSeleccion">Exportar Selección</button>
                <button type="button" class="btn bg-magenta-dark text-white" id="exportarTodos">Exportar todo</button>
            </div>
        </div>
    </div>
</div>

<!-- CDN Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        $('#link-dashboard').addClass('pagina-activa');

        // Silenciar errores de DataTable
        $.fn.dataTable.ext.errMode = 'none';

        // Inicialización de DataTable para Personal Académico
        $('#personalAcademicoTable').DataTable({
            responsive: true,
            language: {
                url: "controller/datatable_esp.json"
            },
            pagingType: "simple"
        });

        // Inicialización de DataTable para Personal Administrativo
        $('#personalAdministrativoTable').DataTable({
            responsive: true,
            language: {
                url: "controller/datatable_esp.json"
            },
            pagingType: "simple"
        });

        // Inicializar popovers
        $('[data-bs-toggle="popover"]').popover();

        // Evento para abrir modal y poblar select
        $('.btn-asignar').on('click', function() {
            const username = $(this).data('username');
            const nombre = $(this).data('nombre');
            const rol = $(this).data('rol');
            const type = $(this).data('type');
            $('#username').val(username);
            $('#modalCedula').val(username);
            $('#modalNombre').val(nombre);
            $('#modalRol').val(rol);
            const select = $('#rolContrato');
            select.empty();
            if (type === 'academico') {
                select.append('<option value="Ejecutor técnico">Ejecutor técnico</option>');
                select.append('<option value="Ejecutor Inglés">Ejecutor Inglés</option>');
                select.append('<option value="Ejecutor Habilidades de poder">Ejecutor Habilidades de poder</option>');
                select.append('<option value="Mentor">Mentor</option>');
                select.append('<option value="Monitor">Monitor</option>');
            } else if (type === 'administrativo') {
                select.append('<option value="Director de Proyectos">Director de Proyectos</option>');
                select.append('<option value="Líder Administrativo y Financiero">Líder Administrativo y Financiero</option>');
                select.append('<option value="Líder Jurídico">Líder Jurídico</option>');
                select.append('<option value="Líder Operativo">Líder Operativo</option>');
                select.append('<option value="Líder de Gráfico">Líder de Gráfico</option>');
                select.append('<option value="Líder de Gestión">Líder de Gestión</option>');
                select.append('<option value="Líder de Soporte">Líder de Soporte</option>');
                select.append('<option value="Líder de Datos">Líder de Datos</option>');
                select.append('<option value="Líder de Empleabilidad">Líder de Empleabilidad</option>');
                select.append('<option value="Líder de Seguimiento">Líder de Seguimiento</option>');
                select.append('<option value="Coordinador Académico">Coordinador Académico</option>');
            }

            // Obtener datos existentes
            $.ajax({
                url: 'components/rolesAndContracts/getFilingAssignment.php',
                type: 'GET',
                data: {
                    username: username
                },
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let tableHtml = '<table class="table table-sm"><thead><tr><th>Radicado</th><th>Fecha</th><th>Rol</th><th>Lote</th><th>Acciones</th></tr></thead><tbody>';
                        response.data.forEach(assignment => {
                            tableHtml += `<tr><td>${assignment.filing_number}</td><td>${assignment.filing_date}</td><td>${assignment.contract_role}</td><td>${assignment.lote}</td><td><button class='btn btn-sm btn-warning edit-assignment' data-id='${assignment.id}' data-radicado='${assignment.filing_number}' data-fecha='${assignment.filing_date}' data-rol='${assignment.contract_role}' data-lote='${assignment.lote}'>Editar</button></td></tr>`;
                        });
                        tableHtml += '</tbody></table>';
                        $('#existingAssignments').html(tableHtml);
                    } else {
                        $('#existingAssignments').html('<p>No hay asignaciones existentes.</p>');
                    }
                    $('#asignarForm')[0].reset();
                    $('#assignmentId').val('');  // Limpiar ID
                    $('#formTitle').text('Agregar nueva asignación:');
                    $('#guardarAsignacion').text('Guardar');
                },
                error: function() {
                    $('#existingAssignments').html('<p>Error al cargar asignaciones.</p>');
                    $('#asignarForm')[0].reset();
                }
            });
        });

        // Evento para guardar via AJAX
        $('#guardarAsignacion').on('click', function() {
            const formData = new FormData(document.getElementById('asignarForm'));
            formData.append('filing_number', $('#radicado').val());
            formData.append('filing_date', $('#fechaRadicado').val());
            formData.append('contract_role', $('#rolContrato').val());
            formData.append('lote', $('#lote').val());  // Nuevo: enviar lote

            $.ajax({
                url: 'components/rolesAndContracts/saveFilingAssignment.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: 'Éxito',
                            text: response.message,
                            icon: 'success',
                            confirmButtonText: 'Aceptar'
                        });
                        $('#asignarModal').modal('hide');
                        $('#asignarForm')[0].reset();
                        // Opcional: recargar la lista de asignaciones sin cerrar el modal
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message,
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error en la solicitud AJAX',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        // Evento para abrir modal de cursos y poblar select
        $('#asignarCursosModal').on('show.bs.modal', function() {
            const cursosSelect = $('#cursosSelect');
            cursosSelect.empty();

            // Poblar cursos
            $.ajax({
                url: 'components/rolesAndContracts/getCourses.php',
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        response.cursos.forEach(curso => {
                            cursosSelect.append(`<option value="${curso.codigo}">${curso.codigo} - ${curso.nombre}</option>`);
                        });

                        // Inicializar Select2
                        cursosSelect.select2({
                            theme: 'bootstrap-5',
                            placeholder: 'Selecciona uno o varios cursos...',
                            closeOnSelect: false,
                            dropdownParent: $('#asignarCursosModal')
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: 'Error al cargar cursos',
                            icon: 'error',
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        title: 'Error',
                        text: 'Error en la solicitud AJAX',
                        icon: 'error',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });

        // Limpiar Select2 al cerrar modal
        $('#asignarCursosModal').on('hidden.bs.modal', function() {
            $('#cursosSelect').select2('destroy');
        });

        // Evento para exportar cursos seleccionados
        $('#exportarSeleccion').on('click', function() {
            const selectedCourses = $('#cursosSelect').val();
            if (selectedCourses && selectedCourses.length > 0) {
                // Mostrar loader
                Swal.fire({
                    title: 'Generando reporte...',
                    text: 'Por favor espera mientras se genera el archivo Excel',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Crear formulario para enviar datos por POST
                const form = $('<form>', {
                    'method': 'POST',
                    'action': 'components/rolesAndContracts/exportCoursesReport.php'
                });
                
                // Agregar los cursos seleccionados como campos ocultos
                selectedCourses.forEach(function(course) {
                    form.append($('<input>', {
                        'type': 'hidden',
                        'name': 'courses[]',
                        'value': course
                    }));
                });
                
                // Agregar formulario al DOM, enviarlo y removerlo
                $('body').append(form);
                form.submit();
                form.remove();
                
                // Cerrar el loader después de un tiempo y el modal
                setTimeout(function() {
                    Swal.close();
                    $('#asignarCursosModal').modal('hide');
                }, 2000);
                
            } else {
                Swal.fire({
                    title: 'Advertencia',
                    text: 'Por favor selecciona al menos un curso',
                    icon: 'warning',
                    confirmButtonText: 'Aceptar'
                });
            }
        });

        // Evento para exportar todos los académicos
        $('#exportarTodos').on('click', function() {
            // Mostrar loader
            Swal.fire({
                title: 'Generando reporte completo...',
                text: 'Por favor espera mientras se genera el archivo Excel con todos los datos académicos',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Crear formulario para exportar todos los académicos
            const form = $('<form>', {
                'method': 'GET',
                'action': 'components/rolesAndContracts/exportAllAcademicReport.php'
            });
            
            // Agregar formulario al DOM, enviarlo y removerlo
            $('body').append(form);
            form.submit();
            form.remove();
            
            // Cerrar el loader después de un tiempo y el modal
            setTimeout(function() {
                Swal.close();
                $('#asignarCursosModal').modal('hide');
            }, 2000);
        });

        // Evento para exportar personal administrativo
        $('#exportarAdministrativo').on('click', function() {
            // Mostrar loader
            Swal.fire({
                title: 'Generando reporte administrativo...',
                text: 'Por favor espera mientras se genera el archivo Excel',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Crear formulario para exportar personal administrativo
            const form = $('<form>', {
                'method': 'GET',
                'action': 'components/rolesAndContracts/exportAdministrativeReport.php'
            });
            
            // Agregar formulario al DOM, enviarlo y removerlo
            $('body').append(form);
            form.submit();
            form.remove();
            
            // Cerrar el loader después de un tiempo
            setTimeout(function() {
                Swal.close();
            }, 2000);
        });

        // Evento para editar asignación
        $(document).on('click', '.edit-assignment', function() {
            const id = $(this).data('id');
            const radicado = $(this).data('radicado');
            const fecha = $(this).data('fecha');
            const rol = $(this).data('rol');
            const lote = $(this).data('lote');
            
            $('#assignmentId').val(id);
            $('#radicado').val(radicado);
            $('#fechaRadicado').val(fecha);
            $('#rolContrato').val(rol);
            $('#lote').val(lote);
            $('#formTitle').text('Editar asignación:');
            $('#guardarAsignacion').text('Actualizar');
        });
    });
</script>