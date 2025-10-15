<!-- 1. jQuery (requerido) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- 2. Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- 3. Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
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

    .floating-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 100;
    }
</style>

<div class="container-fluid">
    <!-- Sección de redacción de SMS -->
    <div class="mb-4">
        <div class="bg-teal-dark text-white p-3 rounded-top mt-4">
            <h5 class="text-center mb-0"><i class="bi bi-chat-dots"></i> Redactar SMS</h5>
        </div>
        <div class="p-3 border border-top-0 rounded-bottom">
            <!-- Input para agregar teléfonos manuales (fuera del form) -->
            <div class="mb-3">
                <label class="form-label fw-bold">Agregar Destinatarios Manuales:</label>
                <div class="input-group">
                    <input type="text" class="form-control manual-phone-input" 
                        placeholder="Ingrese número de teléfono colombiano (10 dígitos, empezando por 3, puede incluir +57) y presione Enter, o pegue múltiples separados por saltos de línea">
                    <button class="btn btn-outline-secondary add-manual-phone" type="button">
                        <i class="bi bi-plus-circle"></i> Agregar
                    </button>
                </div>
            </div>

            <form id="smsForm">
                <!-- Destinatarios (Números de teléfono) -->
                <div class="mb-3">
                    <label class="form-label fw-bold">Destinatarios: <span id="recipientCount" class="badge bg-teal-dark">0</span></label>

                    <div id="recipientsContainer" class="mb-3"></div>

                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <button type="button" class="btn bg-indigo-dark text-white me-2" data-bs-toggle="modal" data-bs-target="#recipientsModal">
                            <i class="bi bi-people-fill"></i> Buscar Destinatario
                        </button>
                        <button type="button" class="btn bg-red-dark text-white" id="clearRecipients">
                            <i class="bi bi-trash"></i> Limpiar Todos
                        </button>
                    </div>
                </div>

                <!-- Mensaje SMS -->
                <div class="mb-3">
                    <label for="smsMessage" class="form-label fw-bold">Mensaje SMS:</label>
                    <textarea class="form-control" id="smsMessage" rows="4" maxlength="160" placeholder="Escriba su mensaje aquí... (máx. 160 caracteres)"></textarea>
                    <small class="form-text text-muted">Caracteres restantes: <span id="charCount">160</span></small>
                </div>

                <!-- Botón de envío -->
                <div class="text-center">
                    <button type="submit" class="btn bg-indigo-dark text-white btn-lg">
                        <i class="bi bi-send-fill"></i> Enviar SMS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para buscar destinatarios -->
<div class="modal fade" id="recipientsModal" tabindex="-1" aria-labelledby="recipientsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="recipientsModalLabel">
                    <i class="bi bi-search"></i> Buscar destinatario por Número de Identificación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <select class="form-select" id="selectHeadquarters">
                            <option value="">Seleccionar Sede</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select class="form-select" id="selectBootcamp">
                            <option value="">Seleccionar Bootcamp</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" id="searchNumberId" class="form-control" placeholder="Ingrese el número de identificación">
                            <button class="btn bg-indigo-dark text-white" type="button" id="btnSearchUser">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
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

<!-- Script para funcionalidad del envío de SMS -->
<script>
    $(document).ready(function() {
        Swal.fire({
            title: 'Información Importante',
            html: '<p>Este sistema tiene una capacidad límite de <strong>1000 SMS diarios</strong>.</p>' +
                '<p>Se recomienda usar esta función con moderación para evitar problemas con el proveedor de SMS.</p>',
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#066aab'
        });

        // Almacenar destinatarios seleccionados (números de teléfono)
        const selectedRecipients = new Map();

        // Función para actualizar contadores
        function updateCounters() {
            $('#recipientCount').text(selectedRecipients.size);
        }

        // Función para validar y procesar número de teléfono colombiano
        function isValidColombianPhone(phone) {
            if (typeof phone !== 'string') {
                return false;
            }
            // Eliminar todos los caracteres no numéricos
            let processed = phone.replace(/\D/g, '');

            // Si empieza con '57' y tiene más de 10 dígitos, quitar el prefijo
            if (processed.startsWith('57') && processed.length > 10) {
                processed = processed.substring(2);
            }

            // Validar: exactamente 10 dígitos, empezando por 3
            const phoneRegex = /^3\d{9}$/;
            if (phoneRegex.test(processed)) {
                return processed;
            } else {
                return false;
            }
        }

        // Renderizar las etiquetas de destinatarios (sin agregar input, ya que está fijo)
        function renderRecipientTags() {
            const container = $('#recipientsContainer');
            container.empty(); // Limpia solo las etiquetas

            selectedRecipients.forEach((recipient) => {
                const tag = $(`
                    <div class="recipient-tag">
                        ${recipient.name} - ${recipient.phone}
                        <span class="remove-tag" data-phone="${recipient.phone}" title="Eliminar">
                            X
                        </span>
                    </div>
                `);
                container.append(tag);
            });
        }

        // Eventos para el input manual (fijo en HTML)
        $('.manual-phone-input').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const phone = $(this).val().trim();
                if (phone) {
                    addPhone(phone);
                    $(this).val('');
                }
            }
        });

        $('.add-manual-phone').on('click', function() {
            const phone = $('.manual-phone-input').val().trim();
            if (phone) {
                addPhone(phone);
                $('.manual-phone-input').val('');
            }
        });

        $('.manual-phone-input').on('paste', function(e) {
            const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
            const pastedData = clipboardData.getData('Text');
            if (pastedData) {
                e.preventDefault();
                processMultiplePhones(pastedData);
                $(this).val('');
            }
        });

        // Limpiar todos los destinatarios
        $('#clearRecipients').on('click', function() {
            selectedRecipients.clear();
            $('#recipientsContainer .recipient-tag').remove(); // Solo elimina las etiquetas
            updateCounters();
        });

        // Eliminar destinatario al hacer clic en la X
        $(document).on('click', '#recipientsContainer .remove-tag', function() {
            let phone = $(this).data('phone');
            phone = String(phone);
            // Si la clave no existe, busca por coincidencia
            if (!selectedRecipients.delete(phone)) {
                // Buscar por coincidencia en las claves
                for (const key of selectedRecipients.keys()) {
                    if (key == phone) {
                        selectedRecipients.delete(key);
                        break;
                    }
                }
            }
            renderRecipientTags();
            updateCounters();
        });

        // Función para agregar teléfono
        function addPhone(phone) {
            const processedPhone = isValidColombianPhone(phone);
            if (!processedPhone) {
                Swal.fire({
                    title: 'Error',
                    text: 'Por favor ingrese un número de teléfono colombiano válido (10 dígitos, empezando por 3, puede incluir +57 )',
                    icon: 'error',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            if (selectedRecipients.has(processedPhone)) {
                Swal.fire({
                    title: 'Advertencia',
                    text: 'Este número ya está en la lista',
                    icon: 'warning',
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                return;
            }

            selectedRecipients.set(processedPhone, {
                phone: processedPhone,
                name: 'Usuario Manual'
            });

            renderRecipientTags();
            updateCounters();
        }

        // Procesar múltiples teléfonos pegados
        function processMultiplePhones(input) {
            const phones = input.split(/\n+/).map(p => p.trim()).filter(p => p);
            let added = 0;
            phones.forEach(phone => {
                const processedPhone = isValidColombianPhone(phone);
                if (processedPhone && !selectedRecipients.has(processedPhone)) {
                    selectedRecipients.set(processedPhone, {
                        phone: processedPhone,
                        name: 'Usuario Manual'
                    });
                    added++;
                }
            });
            if (added > 0) {
                renderRecipientTags();
                updateCounters();
            }
        }

        // Limpiar todos los destinatarios
        /* $('#clearRecipients').on('click', function() {
            selectedRecipients.clear();
            $('#recipientsContainer .recipient-tag').remove();
            updateCounters();
        }); */ // ELIMINAR ESTE BLOQUE DUPLICADO

        // Contador de caracteres para el mensaje
        $('#smsMessage').on('input', function() {
            const maxLength = 160;
            const currentLength = $(this).val().length;
            const remaining = maxLength - currentLength;
            $('#charCount').text(remaining);
            if (remaining < 0) {
                $(this).val($(this).val().substring(0, maxLength));
                $('#charCount').text(0);
            }
        });

        // Envío del formulario
        $('#smsForm').on('submit', function(e) {
            e.preventDefault();

            if (selectedRecipients.size === 0) {
                Swal.fire({
                    title: 'Error',
                    text: 'Debe seleccionar al menos un destinatario',
                    icon: 'error'
                });
                return;
            }

            const message = $('#smsMessage').val().trim();
            if (!message) {
                Swal.fire({
                    title: 'Error',
                    text: 'El mensaje SMS es obligatorio',
                    icon: 'error'
                });
                return;
            }

            // Confirmar envío
            Swal.fire({
                title: '¿Confirmar envío?',
                text: `Está a punto de enviar un SMS a ${selectedRecipients.size} destinatarios`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, enviar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendSMS(message);
                }
            });
        });

        // Función para enviar SMS (adaptar según backend)
        function sendSMS(message) {
            const recipients = Array.from(selectedRecipients.values());

            Swal.fire({
                title: 'Enviando SMS',
                html: `<div id="smsProgress">Procesando: <b>0</b> de ${recipients.length}<br>Por favor espere...</div>`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            let processed = 0;
            let successes = 0;
            const errors = [];

            // Procesar cada destinatario de manera secuencial
            const processRecipients = async () => {
                for (const recipient of recipients) {
                    try {
                        const response = await $.ajax({
                            url: 'components/sendSMS/send_sms.php', // Asumir que existe un script similar
                            method: 'POST',
                            data: {
                                phone: recipient.phone,
                                name: recipient.name,
                                message: message
                            },
                            dataType: 'json'
                        });

                        processed++;

                        if (response.success) {
                            successes++;
                        } else {
                            errors.push({
                                recipient: recipient.phone,
                                message: response.message || 'Error desconocido'
                            });
                        }
                    } catch (error) {
                        processed++;
                        errors.push({
                            recipient: recipient.phone,
                            message: 'Error de conexión: ' + (error.message)
                        });
                    }

                    // Actualizar progreso
                    $('#smsProgress').html(`
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

        // Buscar usuario por number_id
        $('#btnSearchUser').on('click', function() {
            const numberId = $('#searchNumberId').val().trim();
            if (!numberId) {
                $('#searchResult').html('<div class="alert alert-warning">Ingrese un número de identificación.</div>');
                return;
            }
            $('#searchResult').html('<div class="text-center"><span class="spinner-border"></span> Buscando...</div>');
            $.ajax({
                url: 'components/sendSMS/get_user_by_numberid_sms.php', // Archivo separado para SMS
                method: 'GET',
                data: {
                    number_id: numberId
                },
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
                                    <span class="fw-bold me-2">Teléfono:</span>
                                    <span>${user.first_phone || 'No disponible'}</span>
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
                                        data-phone="${user.first_phone}" data-name="${user.fullName}" ${!user.first_phone ? 'disabled' : ''}>
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
            let phone = $(this).data('phone');
            const name = $(this).data('name');
            phone = String(phone); // <-- Fuerza a string
            const processedPhone = isValidColombianPhone(phone);
            if (!processedPhone) {
                Swal.fire('Error', 'El número de teléfono no es válido para Colombia', 'error');
                return;
            }
            if (!selectedRecipients.has(processedPhone)) {
                selectedRecipients.set(processedPhone, {
                    phone: processedPhone,
                    name
                });
                renderRecipientTags();
                updateCounters();
                Swal.fire('Agregado', 'El destinatario fue agregado correctamente', 'success');
            } else {
                Swal.fire('Advertencia', 'Este número ya está en la lista', 'warning');
            }
        });

        // Al abrir el modal, cargar opciones de grupos
        $('#recipientsModal').on('show.bs.modal', function() {
            loadGroupOptions();
        });

        // Función para cargar opciones de headquarters y bootcamps
        function loadGroupOptions() {
            $.ajax({
                url: 'components/sendSMS/get_group_options.php',
                method: 'GET',
                dataType: 'json'
            }).done(function(data) {
                const hqSelect = $('#selectHeadquarters');
                hqSelect.empty().append('<option value="">Seleccionar Sede</option>');
                data.headquarters.forEach(hq => {
                    hqSelect.append(`<option value="${hq}">${hq}</option>`);
                });
                const bcSelect = $('#selectBootcamp');
                bcSelect.empty().append('<option value="">Seleccionar Bootcamp</option>');
                data.bootcamps.forEach(bc => {
                    bcSelect.append(`<option value="${bc}">${bc}</option>`);
                });
            }).fail(function() {
                console.error('Error al cargar opciones de grupos');
            });
        }

        // Evento para seleccionar headquarters
        $('#selectHeadquarters').on('change', function() {
            const hq = $(this).val();
            if (hq) {
                $('#selectBootcamp').val(''); // Resetear el otro selector
                loadUsersByGroup('headquarters', hq);
            } else {
                $('#searchResult').empty();
            }
        });

        // Evento para seleccionar bootcamp
        $('#selectBootcamp').on('change', function() {
            const bc = $(this).val();
            if (bc) {
                $('#selectHeadquarters').val(''); // Resetear el otro selector
                loadUsersByGroup('bootcamp_name', bc);
            } else {
                $('#searchResult').empty();
            }
        });

        // Función para cargar usuarios por grupo
        function loadUsersByGroup(type, value) {
            $('#searchResult').html('<div class="text-center"><span class="spinner-border"></span> Cargando...</div>');
            $.ajax({
                url: 'components/sendSMS/get_users_by_group.php',
                method: 'GET',
                data: {
                    type: type,
                    value: value
                },
                dataType: 'json'
            }).done(function(response) {
                if (response.success && response.users.length > 0) {
                    let table = `
                        <button class="btn bg-indigo-dark text-white mb-3" id="addAllUsers">
                            <i class="bi bi-plus-circle-fill"></i> Agregar Todos
                        </button>
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Número ID</th>
                                    <th>Nombre Completo</th>
                                    <th>Sede</th>
                                    <th>Bootcamp</th>
                                    <th>Teléfono</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;
                    response.users.forEach(user => {
                        table += `
                            <tr>
                                <td>${user.number_id}</td>
                                <td>${user.fullName}</td>
                                <td>${user.headquarters}</td>
                                <td>${user.bootcamp_name}</td>
                                <td>${user.first_phone || 'No disponible'}</td>
                                <td>
                                    <button class="btn btn-sm bg-magenta-dark text-white add-user-btn" 
                                        data-phone="${user.first_phone}" data-name="${user.fullName}" ${!user.first_phone ? 'disabled' : ''}>
                                        <i class="bi bi-plus-circle"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    table += `</tbody></table>`;
                    $('#searchResult').html(table);
                } else {
                    $('#searchResult').html('<div class="alert alert-warning">No se encontraron usuarios.</div>');
                }
            }).fail(function() {
                $('#searchResult').html('<div class="alert alert-danger">Error al cargar usuarios.</div>');
            });
        }

        // Evento para agregar usuario desde la tabla
        $(document).on('click', '.add-user-btn', function() {
            let phone = $(this).data('phone');
            const name = $(this).data('name');
            phone = String(phone);
            const processedPhone = isValidColombianPhone(phone);
            if (!processedPhone) {
                Swal.fire('Error', 'El número de teléfono no es válido para Colombia', 'error');
                return;
            }
            if (!selectedRecipients.has(processedPhone)) {
                selectedRecipients.set(processedPhone, {
                    phone: processedPhone,
                    name
                });
                renderRecipientTags();
                updateCounters();
                Swal.fire('Agregado', 'El destinatario fue agregado correctamente', 'success');
            } else {
                Swal.fire('Advertencia', 'Este número ya está en la lista', 'warning');
            }
        });

        // Evento para agregar todos los usuarios desde la tabla
        $(document).on('click', '#addAllUsers', function() {
            let added = 0;
            $('.add-user-btn').each(function() {
                const phone = $(this).data('phone');
                const name = $(this).data('name');
                const processedPhone = isValidColombianPhone(String(phone));
                if (processedPhone && !selectedRecipients.has(processedPhone)) {
                    selectedRecipients.set(processedPhone, {
                        phone: processedPhone,
                        name
                    });
                    added++;
                }
            });
            if (added > 0) {
                renderRecipientTags();
                updateCounters();
                Swal.fire('Agregados', `${added} destinatarios agregados correctamente`, 'success');
            } else {
                Swal.fire('Advertencia', 'No se agregaron nuevos destinatarios (posiblemente ya están en la lista)', 'warning');
            }
        });
    });
</script>