<?php
// Obtener usuarios de la base de datos
$sql = "SELECT * FROM user_register ORDER BY id DESC";
$result = mysqli_query($conn, $sql);

$data = [];
if ($result && mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

// Obtener datos únicos para filtros
$departamentos = [];
$programas = [];
$modalidades = [];
$sedes = [];

foreach ($data as $row) {
    $depto = isset($row['departamento']) ? $row['departamento'] : '';
    $sede = isset($row['headquarters']) ? $row['headquarters'] : '';
    $programa = isset($row['program']) ? $row['program'] : '';
    $modalidad = isset($row['mode']) ? $row['mode'] : '';

    // Obtener departamentos únicos
    if (!in_array($depto, $departamentos) && !empty($depto)) {
        $departamentos[] = $depto;
    }

    // Obtener sedes únicas
    if (!in_array($sede, $sedes) && !empty($sede)) {
        $sedes[] = $sede;
    }

    // Obtener programas únicos
    if (!in_array($programa, $programas) && !empty($programa)) {
        $programas[] = $programa;
    }

    // Obtener modalidades únicas
    if (!in_array($modalidad, $modalidades) && !empty($modalidad)) {
        $modalidades[] = $modalidad;
    }
}
?>

<!-- 1. jQuery (requerido) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- 3. Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<!-- 4. Summernote CSS -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">

<!-- 6. Summernote JS -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<!-- 7. Summernote en Español -->
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-es-ES.min.js"></script>

<style>
    /* Estilos adicionales para Summernote */
    .note-editor.note-frame {
        width: 100% !important;
    }

    .note-editor .note-toolbar {
        text-align: center;
    }

    .note-editor .note-editing-area {
        width: 100% !important;
    }

    .note-editor .note-editing-area .note-editable {
        width: 100% !important;
        background-color: white;
    }

    /* Resto de estilos existentes... */
    .recipient-tag {
        display: inline-block;
        background-color: #f0f0f0;
        border-radius: 20px;
        padding: 5px 10px;
        margin: 3px;
        border: 1px solid #ddd;
    }

    .recipient-tag .remove-tag {
        cursor: pointer;
        margin-left: 5px;
        color: #888;
    }

    .recipient-tag .remove-tag:hover {
        color: #dc3545;
    }

    #recipientsContainer {
        min-height: 42px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px;
        background-color: white;
    }

    .table-container {
        max-height: 60vh;
        overflow: hidden;
        border: 1px solid #dee2e6;
        border-radius: 4px;
    }

    .table-wrapper {
        overflow-y: auto;
        max-height: inherit;
    }

    .table thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background-color: #f8f9fa;
    }

    .floating-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 100;
    }
</style>

<div class="container-fluid">
    <!-- Sección de redacción de email - usando divs en lugar de card -->
    <div class="mb-4">
        <div class="bg-teal-dark text-white p-3 rounded-top mt-4">
            <h5 class="text-center mb-0"><i class="bi bi-pencil-square"></i> Redactar Correo</h5>
        </div>
        <div class="p-3 border border-top-0 rounded-bottom">
            <form id="emailForm">
                <!-- Destinatarios -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Destinatarios: <span id="recipientCount" class="badge bg-teal-dark">0</span></label>

                    <div id="recipientsContainer" class="mb-3"></div>

                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <button type="button" class="btn bg-indigo-dark text-white me-2" data-bs-toggle="modal" data-bs-target="#recipientsModal">
                            <i class="bi bi-people-fill"></i> Seleccionar Destinatarios
                        </button>
                        <button type="button" class="btn bg-red-dark text-white" id="clearRecipients">
                            <i class="bi bi-trash"></i> Limpiar Todos
                        </button>
                    </div>
                </div>

                <!-- Asunto del correo -->
                <div class="mb-3">
                    <label for="emailSubject" class="form-label fw-bold">Asunto del correo:</label>
                    <input type="text" class="form-control" id="emailSubject" required>
                </div>

                <!-- Editor de texto enriquecido -->
                <div class="mb-3 w-100">
                    <label for="emailContent" class="form-label fw-bold">Contenido del correo:</label>
                    <textarea id="emailContent" name="emailContent" class="form-control"></textarea>
                </div>

                <!-- Botón de envío -->
                <div class="text-center">
                    <button type="submit" class="btn bg-indigo-dark text-white btn-lg">
                        <i class="bi bi-send-fill"></i> Enviar Correo
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para seleccionar destinatarios -->
<div class="modal fade" id="recipientsModal" tabindex="-1" aria-labelledby="recipientsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="recipientsModalLabel">
                    <i class="bi bi-people-fill"></i> Seleccionar Destinatarios
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <!-- Filtros -->
                <div class="card mb-2">
                    <div class="card-header bg-indigo-dark text-white text-center">
                        <i class="bi bi-funnel"></i> Filtros
                    </div>
                    <div class="card-body justify-content-center">
                        <!-- Filtros en la parte superior -->
                        <div class="row mb-2 justify-content-center">
                            <div class="col-md-3 mb-2">
                                <label for="filterStatus" class="form-label">Estado:</label>
                                <select id="filterStatus" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Beneficiario</option>
                                    <option value="8">Beneficiario Contrapartida</option>
                                    <option value="3">Matriculado</option>
                                    <option value="5">En Proceso</option>
                                    <option value="4">Pendiente</option>
                                    <option value="2">Rechazado</option>
                                    <option value="7">Inactivo</option>
                                    <option value="6">Certificado</option>
                                    <option value="0">Sin Estado</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="filterSede" class="form-label">Sede:</label>
                                <select id="filterSede" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    <?php foreach ($sedes as $sede): ?>
                                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="filterPrograma" class="form-label">Programa:</label>
                                <select id="filterPrograma" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <?php foreach ($programas as $programa): ?>
                                        <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="filterModalidad" class="form-label">Modalidad:</label>
                                <select id="filterModalidad" class="form-select form-select-sm">
                                    <option value="">Todas</option>
                                    <?php foreach ($modalidades as $modalidad): ?>
                                        <option value="<?= htmlspecialchars($modalidad) ?>"><?= htmlspecialchars($modalidad) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button id="aplicarFiltros" class="btn bg-indigo-dark text-white btn-sm me-1">
                            <i class="bi bi-funnel"></i> Filtrar selección
                        </button>
                        <button id="limpiarFiltros" class="btn bg-silver btn-sm">
                            <i class="bi bi-x-circle"></i> Limpiar filtros
                        </button>
                    </div>
                </div>

                <!-- Tabla de usuarios -->
                <div class="table-container">
                    <div class="table-wrapper">
                        <table id="usersTable" class="table table-hover table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="50" class="text-center">
                                        <input type="checkbox" id="selectAll" class="form-check-input">
                                    </th>
                                    <th>Tipo ID</th>
                                    <th>Número</th>
                                    <th>Nombre Completo</th>
                                    <th>Email</th>
                                    <th width="60">Estado</th>
                                    <th>Sede</th>
                                    <th>Programa</th>
                                    <th>Modalidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($data as $row):
                                    // Procesar datos del usuario
                                    $firstName   = ucwords(strtolower(trim($row['first_name'] ?? '')));
                                    $secondName  = ucwords(strtolower(trim($row['second_name'] ?? '')));
                                    $firstLast   = ucwords(strtolower(trim($row['first_last'] ?? '')));
                                    $secondLast  = ucwords(strtolower(trim($row['second_last'] ?? '')));
                                    $fullName    = $firstName . " " . $secondName . " " . $firstLast . " " . $secondLast;
                                    $email       = $row['email'] ?? '';
                                    $typeID      = $row['typeID'] ?? '';
                                    $numberID    = $row['number_id'] ?? '';
                                    $sede        = $row['headquarters'] ?? '';
                                    $programa    = $row['program'] ?? '';
                                    $modalidad   = $row['mode'] ?? '';
                                ?>
                                    <tr>
                                        <td class="text-center">
                                            <input type="checkbox" class="form-check-input user-checkbox"
                                                data-email="<?= htmlspecialchars($email) ?>"
                                                data-name="<?= htmlspecialchars($fullName) ?>"
                                                style="width: 20px; height: 20px; cursor: pointer;">
                                        </td>
                                        <td><?= htmlspecialchars($typeID) ?></td>
                                        <td><?= htmlspecialchars($numberID) ?></td>
                                        <td><?= htmlspecialchars($fullName) ?></td>
                                        <td><?= htmlspecialchars($email) ?></td>
                                        <td class="text-center" data-status="<?= htmlspecialchars($row['statusAdmin'] ?? '') ?>">
                                            <?php
                                            if ($row['statusAdmin'] == '1') {
                                                echo '<button class="btn bg-teal-dark" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="BENEFICIARIO"><i class="bi bi-check-circle"></i></button>';
                                            } elseif ($row['statusAdmin'] == '0') {
                                                echo '<button class="btn bg-indigo-dark text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="SIN ESTADO"><i class="bi bi-question-circle"></i></button>';
                                            } elseif ($row['statusAdmin'] == '2') {
                                                echo '<button class="btn bg-danger" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="RECHAZADO"><i class="bi bi-x-circle"></i></button>';
                                            } elseif ($row['statusAdmin'] == '3') {
                                                echo '<button class="btn bg-success text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="MATRICULADO"><i class="fa-solid fa-pencil"></i></button>';
                                            } elseif ($row['statusAdmin'] == '4') {
                                                echo '<button class="btn bg-secondary text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="PENDIENTE"><i class="bi bi-telephone-x"></i></button>';
                                            } elseif ($row['statusAdmin'] == '5') {
                                                echo '<button class="btn bg-warning text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="EN PROCESO"><div class="spinner-border spinner-border-sm" role="status"><span class="visually-hidden"></span></div></button>';
                                            } elseif ($row['statusAdmin'] == '6') {
                                                echo '<button class="btn bg-orange-dark text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="CERTIFICADO"><i class="bi bi-patch-check-fill"></i></button>';
                                            } elseif ($row['statusAdmin'] == '7') {
                                                echo '<button class="btn bg-silver text-white" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="INACTIVO"><i class="bi bi-person-x"></i></button>';
                                            } elseif ($row['statusAdmin'] == '8') {
                                                echo '<button class="btn bg-amber-dark text-dark" style="width:43px" tabindex="0" role="button" data-bs-toggle="popover" data-bs-trigger="hover focus" title="BENEFICIARIO CONTRAPARTIDA"><i class="bi bi-check-circle-fill"></i></button>';
                                            }
                                            ?>
                                        </td>
                                        <td><?= htmlspecialchars($sede) ?></td>
                                        <td><?= htmlspecialchars($programa) ?></td>
                                        <td><?= htmlspecialchars($modalidad) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="mt-3">
                    <span class="badge bg-magenta-dark">
                        Usuarios seleccionados: <span id="selectedUsersCount">0</span>
                    </span>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn bg-magenta-dark text-white" id="confirmRecipients" data-bs-dismiss="modal">
                    <i class="bi bi-check-circle"></i> Confirmar Selección
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante de ayuda -->
<button class="btn bg-magenta-dark text-white floating-button" type="button" data-bs-toggle="tooltip" title="Ayuda">
    <i class="bi bi-question-circle-fill"></i>
</button>

<!-- Script para funcionalidad del envío de emails -->
<script>
    $(document).ready(function() {

        Swal.fire({
            title: 'Información Importante',
            html: '<p>Este sistema tiene una capacidad límite de <strong>300 correos diarios</strong>.</p>' +
                '<p>Se recomienda usar esta función con moderación para evitar problemas con el servidor SMTP.</p>',
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#066aab'
        });

        const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        const popoverList = popoverTriggerList.map(function(popoverTriggerEl) {
            return new bootstrap.Popover(popoverTriggerEl);
        });


        // Configurar el editor Summernote
        $('#emailContent').summernote({
            lang: 'es-ES',
            height: 300,
            width: '100%', // Ancho completo
            placeholder: 'Escriba el contenido de su correo aquí...',
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'underline', 'clear', 'strikethrough']],
                ['fontname', ['fontname']],
                ['fontsize', ['fontsize']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'picture']],
                ['view', ['fullscreen', 'codeview']]
            ],
            callbacks: {
                onInit: function() {
                    $('.note-editable').css('min-height', '300px');
                    $('.note-editor').css('width', '100%');

                    // Centrar los botones de la toolbar
                    $('.note-toolbar').css({
                        'display': 'flex',
                        'flex-wrap': 'wrap',
                        'justify-content': 'center'
                    });
                }
            }
        });

        // Inicializar DataTable
        const usersTable = $('#usersTable').DataTable({
            pageLength: 50,
            lengthMenu: [
                [50, 100, -1],
                [50, 100, "Todos"]
            ],
            columnDefs: [{
                orderable: false,
                targets: 0
            }],
            responsive: true,
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            pagingType: "simple",
            dom: 'frtip',
            order: [
                [3, 'asc']
            ]
        });

        // Filtro personalizado para DataTables
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                // Asegurarse de que este filtro solo se aplique a la tabla 'usersTable'
                if (settings.nTable.id !== 'usersTable') {
                    return true;
                }

                var filterStatusVal = $('#filterStatus').val();
                var filterSedeVal = $('#filterSede').val();
                var filterProgramaVal = $('#filterPrograma').val();
                var filterModalidadVal = $('#filterModalidad').val();

                // Obtener el nodo de la fila (tr) para acceder a atributos data-*
                var rowNode = usersTable.row(dataIndex).node();
                
                // Columna Estado (índice 5) - Leer desde data-status
                // El índice 5 corresponde a la sexta columna (Estado)
                var rowStatus = $(rowNode).find('td:eq(5)').attr('data-status');
                
                // Columnas Sede (6), Programa (7), Modalidad (8) - Leer desde el contenido de texto de la celda (array 'data')
                // data[6] es el contenido de la séptima celda (Sede), y así sucesivamente.
                var rowSede = data[6]; 
                var rowPrograma = data[7];
                var rowModalidad = data[8];

                // Aplicar filtros: si un filtro está activo y la fila no coincide, se descarta (return false)
                if (filterStatusVal && filterStatusVal !== "" && (rowStatus === undefined || rowStatus != filterStatusVal)) {
                    return false;
                }
                if (filterSedeVal && filterSedeVal !== "" && rowSede != filterSedeVal) {
                    return false;
                }
                if (filterProgramaVal && filterProgramaVal !== "" && rowPrograma != filterProgramaVal) {
                    return false;
                }
                if (filterModalidadVal && filterModalidadVal !== "" && rowModalidad != filterModalidadVal) {
                    return false;
                }

                return true; // La fila pasa todos los filtros
            }
        );

        // Almacenar destinatarios seleccionados
        const selectedRecipients = new Map();

        // Función para actualizar contadores
        function updateCounters() {
            $('#recipientCount').text(selectedRecipients.size);
            $('#selectedUsersCount').text(selectedRecipients.size);
        }

        // Seleccionar/deseleccionar todos
        $('#selectAll').on('change', function() {
            const isChecked = $(this).prop('checked');
            $('.user-checkbox:visible').prop('checked', isChecked).trigger('change');
        });

        // Manejar selección individual
        $(document).on('change', '.user-checkbox', function() {
            const email = $(this).data('email');
            const name = $(this).data('name');

            if ($(this).prop('checked')) {
                selectedRecipients.set(email, {
                    email,
                    name
                });
            } else {
                selectedRecipients.delete(email);
            }

            updateCounters();
        });

        // Confirmar selección de destinatarios
        $('#confirmRecipients').on('click', function() {
            renderRecipientTags();
            updateCounters();
        });

        // Renderizar las etiquetas de destinatarios
        function renderRecipientTags() {
            const container = $('#recipientsContainer');
            container.empty();

            selectedRecipients.forEach((recipient) => {
                const tag = $(`
                    <div class="recipient-tag">
                        ${recipient.name} &lt;${recipient.email}&gt;
                        <span class="remove-tag" data-email="${recipient.email}">
                            <i class="bi bi-x-circle"></i>
                        </span>
                    </div>
                `);
                container.append(tag);
            });
        }

        // Eliminar un destinatario
        $(document).on('click', '.remove-tag', function() {
            const email = $(this).data('email');
            selectedRecipients.delete(email);
            $(this).parent().remove();

            // Desmarcar el checkbox si está visible
            $(`.user-checkbox[data-email="${email}"]`).prop('checked', false);

            updateCounters();
        });

        // Limpiar todos los destinatarios
        $('#clearRecipients').on('click', function() {
            selectedRecipients.clear();
            $('#recipientsContainer').empty();
            $('.user-checkbox').prop('checked', false);
            $('#selectAll').prop('checked', false);
            updateCounters();
        });

        // Aplicar filtros (modificado)
        $('#aplicarFiltros').on('click', function() {
            usersTable.draw(); // Simplemente redibuja la tabla, el filtro personalizado se encargará del resto
        });

        // Limpiar filtros (modificado)
        $('#limpiarFiltros').on('click', function() {
            $('#filterStatus, #filterSede, #filterPrograma, #filterModalidad').val('');
            usersTable.draw(); // Redibuja la tabla para aplicar los filtros ahora vacíos
        });

        // Actualizar el estado del checkbox "Seleccionar todos"
        function updateSelectAllCheckbox() {
            const visibleCheckboxes = $('.user-checkbox:visible');
            const checkedVisibleCheckboxes = $('.user-checkbox:visible:checked');

            if (visibleCheckboxes.length > 0) {
                $('#selectAll').prop('checked', visibleCheckboxes.length === checkedVisibleCheckboxes.length);
            } else {
                $('#selectAll').prop('checked', false);
            }
        }

        // Envío del formulario
        $('#emailForm').on('submit', function(e) {
            e.preventDefault();

            if (selectedRecipients.size === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe seleccionar al menos un destinatario',
                    icon: 'error'
                });
                return;
            }

            const subject = $('#emailSubject').val().trim();
            if (!subject) {
                Swal.fire({
                    title: 'Error',
                    text: 'El asunto del correo es obligatorio',
                    icon: 'error'
                });
                return;
            }

            const content = $('#emailContent').summernote('code');
            if (!content || content === '<p><br></p>') {
                Swal.fire({
                    title: 'Error',
                    text: 'El contenido del correo es obligatorio',
                    icon: 'error'
                });
                return;
            }

            // Confirmar envío
            Swal.fire({
                title: '¿Confirmar envío?',
                text: `Está a punto de enviar un correo a ${selectedRecipients.size} destinatarios`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendEmails(subject, content);
                }
            });
        });

        // Función para enviar correos
        function sendEmails(subject, content) {
            const recipients = Array.from(selectedRecipients.values());

            Swal.fire({
                title: 'Enviando correos',
                html: `<div id="emailProgress">Procesando: <b>0</b> de ${recipients.length}<br>Por favor espere...</div>`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let processed = 0;
            let successes = 0;
            const errors = [];

            // Procesar cada destinatario de manera secuencial para evitar sobrecargar el servidor
            const processRecipients = async () => {
                for (const recipient of recipients) {
                    try {
                        const response = await $.ajax({
                            url: 'components/multipleEmail/send_email.php',
                            method: 'POST',
                            data: {
                                email: recipient.email,
                                name: recipient.name,
                                subject: subject,
                                content: content
                            },
                            dataType: 'json'
                        });

                        processed++;

                        if (response.success) {
                            successes++;
                        } else {
                            errors.push({
                                recipient: recipient.email,
                                message: response.message || 'Error desconocido'
                            });
                        }
                    } catch (error) {
                        processed++;
                        errors.push({
                            recipient: recipient.email,
                            message: 'Error de conexión: ' + (error.message || 'Desconocido')
                        });
                    }

                    // Actualizar progreso
                    $('#emailProgress').html(`
                        Procesando: <b>${processed}</b> de ${recipients.length}<br>
                        Éxitos: <b>${successes}</b><br>
                        Errores: <b>${errors.length}</b><br>
                        Por favor espere...
                    `);
                }

                // Mostrar resultado final
                let resultMessage = `<h4>Resumen del envío</h4>`;
                resultMessage += `<p>Total procesados: <b>${processed}</b></p>`;
                resultMessage += `<p>Envíos exitosos: <b>${successes}</b></p>`;
                resultMessage += `<p>Errores: <b>${errors.length}</b></p>`;

                if (errors.length > 0) {
                    resultMessage += `<hr><h5>Detalles de errores:</h5>`;
                    resultMessage += `<div style="max-height: 200px; overflow-y: auto; text-align: left;">`;
                    errors.forEach((err, index) => {
                        resultMessage += `<p><b>${index + 1}. ${err.recipient}</b>: ${err.message}</p>`;
                    });
                    resultMessage += `</div>`;
                }

                Swal.fire({
                    title: 'Proceso completado',
                    html: resultMessage,
                    icon: errors.length === 0 ? 'success' : (successes > 0 ? 'warning' : 'error'),
                    confirmButtonText: 'Aceptar'
                });
            };

            processRecipients();
        }
    });
</script>