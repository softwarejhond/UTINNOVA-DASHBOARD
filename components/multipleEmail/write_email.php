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

    /* Estilos sugeridos */
    .input-group.input-group-sm {
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 1;
        margin-bottom: 8px;
        padding: 4px 0;
    }

    #recipientsContainer {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .recipient-tag {
        order: 2;
    }

    .input-group {
        order: 1;
    }

    .recipient-tag.manual-entry {
        background-color: #e3f2fd;
        border-color: #90caf9;
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
                        <button type="button" class="btn bg-red-dark text-white me-2" id="clearRecipients">
                            <i class="bi bi-trash"></i> Limpiar Todos
                        </button>
                        <button type="button" class="btn bg-teal-dark text-white dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-file-text"></i> Plantillas
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="#" id="saveTemplate">
                                    <i class="bi bi-save"></i> Guardar como plantilla
                                </a></li>
                            <li><a class="dropdown-item" href="#" id="loadTemplate">
                                    <i class="bi bi-folder2-open"></i> Cargar plantilla
                                </a></li>
                        </ul>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="recipientsModalLabel">
                    <i class="bi bi-search"></i> Buscar destinatario por Número de Identificación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="searchNumberId" class="form-control" placeholder="Ingrese el número de identificación">
                    <button class="btn bg-indigo-dark text-white" type="button" id="btnSearchUser">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div id="searchResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
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
            const inputGroup = container.find('.input-group'); // Guardar referencia al input

            container.empty(); // Limpiar el contenedor
            container.append(inputGroup); // Volver a añadir el input al principio

            selectedRecipients.forEach((recipient) => {
                const tag = $(`
                    <div class="recipient-tag ${recipient.name === 'Usuario Manual' ? 'manual-entry' : ''}">
                        ${recipient.name === 'Usuario Manual' ? '' : recipient.name + ' '}&lt;${recipient.email}&gt;
                        <span class="remove-tag" data-email="${recipient.email}">
                            <i class="bi bi-x-circle"></i>
                        </span>
                    </div>
                `);
                container.append(tag);
            });
        }

        // Agregar la función de inicialización del campo de entrada después de la inicialización de DataTable
        addEmailInputField('recipientsContainer', selectedRecipients, updateCounters);

        // Agregar la función addEmailInputField si no existe
        function addEmailInputField(containerId, recipientsMap, updateCountersFn) {
            const container = $(`#${containerId}`);
            const inputGroup = $(`
                <div class="input-group input-group-sm mt-2 mb-2">
                    <input type="email" class="form-control form-control-sm manual-email-input" 
                        placeholder="Ingrese correo electrónico y presione Enter">
                    <button class="btn btn-outline-secondary add-manual-email" type="button">
                        <i class="bi bi-plus-circle"></i>
                    </button>
                </div>
            `);
            
            container.prepend(inputGroup);

            // Función para validar email
            function isValidEmail(email) {
                return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
            }

            // Función para agregar email
            function addEmail(email) {
                if (!isValidEmail(email)) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Por favor ingrese un correo electrónico válido',
                        icon: 'error',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                if (recipientsMap.has(email)) {
                    Swal.fire({
                        title: 'Advertencia',
                        text: 'Este correo ya está en la lista',
                        icon: 'warning',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    return;
                }

                recipientsMap.set(email, {
                    email: email,
                    name: 'Usuario Manual'
                });

                renderRecipientTags();
                updateCountersFn();
            }

            // Nueva función para procesar múltiples correos pegados
            function processMultipleEmails(input) {
                // Separar por salto de línea, coma o punto y coma
                const emails = input.split(/[\n,;]+/).map(e => e.trim()).filter(e => e);
                let added = 0;
                emails.forEach(email => {
                    if (isValidEmail(email) && !recipientsMap.has(email)) {
                        recipientsMap.set(email, {
                            email: email,
                            name: 'Usuario Manual'
                        });
                        added++;
                    }
                });
                if (added > 0) {
                    renderRecipientTags();
                    updateCountersFn();
                }
            }

            // Manejar entrada por Enter
            inputGroup.find('.manual-email-input').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    const email = $(this).val().trim();
                    if (email) {
                        addEmail(email);
                        $(this).val('');
                    }
                }
            });

            // Manejar clic en botón añadir
            inputGroup.find('.add-manual-email').on('click', function() {
                const email = inputGroup.find('.manual-email-input').val().trim();
                if (email) {
                    addEmail(email);
                    inputGroup.find('.manual-email-input').val('');
                }
            });

            // Manejar pegado múltiple en el input
            inputGroup.find('.manual-email-input').on('paste', function(e) {
                const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                const pastedData = clipboardData.getData('Text');
                if (pastedData) {
                    e.preventDefault();
                    processMultipleEmails(pastedData);
                    $(this).val('');
                }
            });
        }

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
        const saveHistory = async (emailData) => {
            try {
                const response = await $.ajax({
                    url: 'components/multipleEmail/save_history.php',
                    method: 'POST',
                    data: {
                        subject: emailData.subject,
                        content: emailData.content,
                        recipients_count: emailData.recipients.length,
                        successful_count: emailData.successes,
                        failed_count: emailData.errors.length,
                        sent_from: 'page', // o 'float' según corresponda
                        recipients: JSON.stringify(emailData.recipients),
                        errors: JSON.stringify(emailData.errors)
                    }
                });
                console.log('Historial guardado:', response);
            } catch (error) {
                console.error('Error al guardar historial:', error);
            }
        };

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
                            message: 'Error de conexión: ' + (error.message)
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

                // Guardar historial
                await saveHistory({
                    subject,
                    content,
                    recipients,
                    successes,
                    errors
                });

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

        // Sistema de plantillas
        $('#saveTemplate').click(function() {
            Swal.fire({
                title: 'Guardar como plantilla',
                html: `
                    <div class="form-group">
                        <label>Nombre de la plantilla</label>
                        <input type="text" id="templateName" class="form-control" required>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Guardar',
                cancelButtonText: 'Cancelar',
                preConfirm: () => {
                    const name = $('#templateName').val();
                    const subject = $('#emailSubject').val();
                    const content = $('#emailContent').summernote('code');

                    return $.ajax({
                        url: 'components/multipleEmail/email_templates.php',
                        method: 'POST',
                        data: {
                            action: 'save',
                            name: name,
                            subject: subject,
                            content: content
                        }
                    });
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire('¡Guardado!', 'La plantilla se ha guardado correctamente', 'success');
                }
            });
        });

        // Cargar plantilla
        $('#loadTemplate').click(function() {
            $.post('components/multipleEmail/email_templates.php', {
                action: 'list'
            }).done(function(response) {
                if (response.success && response.templates.length > 0) {
                    const templateList = response.templates.map(t =>
                        `<option value="${t.id}">${t.name}</option>`
                    ).join('');

                    Swal.fire({
                        title: 'Cargar plantilla',
                        html: `
                            <div class="form-group">
                                <select id="templateSelect" class="form-control">
                                    <option value="">Seleccione una plantilla...</option>
                                    ${templateList}
                                </select>
                            </div>
                        `,
                        showCancelButton: true,
                        confirmButtonText: 'Cargar',
                        cancelButtonText: 'Cancelar',
                        preConfirm: () => {
                            const templateId = $('#templateSelect').val();
                            if (!templateId) {
                                Swal.showValidationMessage('Por favor seleccione una plantilla');
                                return false;
                            }
                            return $.post('components/multipleEmail/email_templates.php', {
                                action: 'load',
                                id: templateId
                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed && result.value.success) {
                            const template = result.value.template;
                            $('#emailSubject').val(template.subject);
                            $('#emailContent').summernote('code', template.content);
                            Swal.fire('¡Cargado!', 'La plantilla se ha cargado correctamente', 'success');
                        }
                    });
                } else {
                    Swal.fire('Sin plantillas', 'No hay plantillas guardadas', 'info');
                }
            });
        });

        // Buscar usuario por number_id
        $('#btnSearchUser').on('click', function() {
            const numberId = $('#searchNumberId').val().trim();
            if (!numberId) {
                $('#searchResult').html('<div class="alert alert-warning">Ingrese un número de identificación.</div>');
                return;
            }
            $('#searchResult').html('<div class="text-center"><span class="spinner-border"></span> Buscando...</div>');
            $.ajax({
                url: 'components/multipleEmail/get_user_by_numberid.php',
                method: 'GET',
                data: { number_id: numberId },
                dataType: 'json'
            }).done(function(response) {
                if (response.success && response.user) {
                    const user = response.user;
                    $('#searchResult').html(`
                        <div class="card">
                            <div class="card-body d-flex flex-column gap-2">
                                <div class="d-flex flex-row align-items-center">
                                    <span class="fw-bold me-2">Nombre:</span>
                                    <span>${user.fullName}</span>
                                </div>
                                <div class="d-flex flex-row align-items-center">
                                    <span class="fw-bold me-2">Email:</span>
                                    <span>${user.email}</span>
                                </div>
                                <div class="d-flex flex-row align-items-center">
                                    <span class="fw-bold me-2">Programa:</span>
                                    <span>${user.program}</span>
                                </div>
                                <div class="d-flex flex-row align-items-center">
                                    <span class="fw-bold me-2">Sede:</span>
                                    <span>${user.headquarters}</span>
                                </div>
                                <div class="mt-3">
                                    <button class="btn bg-magenta-dark text-white" id="addUserRecipient"
                                        data-email="${user.email}" data-name="${user.fullName}">
                                        <i class="bi bi-plus-circle"></i> Agregar destinatario
                                    </button>
                                </div>
                            </div>
                        </div>
                    `);
                } else {
                    $('#searchResult').html('<div class="alert alert-danger">Usuario no encontrado.</div>');
                }
            }).fail(function() {
                $('#searchResult').html('<div class="alert alert-danger">Error en la búsqueda.</div>');
            });
        });

        // Agregar usuario buscado como destinatario
        $(document).on('click', '#addUserRecipient', function() {
            const email = $(this).data('email');
            const name = $(this).data('name');
            if (!selectedRecipients.has(email)) {
                selectedRecipients.set(email, { email, name });
                renderRecipientTags();
                updateCounters();
                Swal.fire('Agregado', 'El destinatario fue agregado correctamente', 'success');
            } else {
                Swal.fire('Advertencia', 'Este correo ya está en la lista', 'warning');
            }
        });

        // Eliminar destinatario al hacer clic en la X
        $(document).on('click', '.remove-tag', function() {
            const email = $(this).data('email');
            selectedRecipients.delete(email);
            renderRecipientTags();
            updateCounters();
        });
    });
</script>