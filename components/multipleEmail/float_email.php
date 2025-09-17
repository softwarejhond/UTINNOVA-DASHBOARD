<?php
// Obtener usuarios de la base de datos - mismo código que en write_email.php
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

<!-- Ventana de chat flotante para editar correos -->
<div class="chat-email-board">
    <div class="frame-content">
        <!-- Contenedor de la ventana flotante -->
        <div class="widget-position-right sidebar-position-right onlyBubble" id="emailChatContainer">
            <div class="email-chat no-clip-path chrome moveFromRight-enter-done">
                <!-- Encabezado de la ventana flotante -->
                <div class="email-chat-header" style="background: linear-gradient(135deg, #00976a 0%, #006a4e 100%);">
                    <h2 class="oneline"><span>Redactar Correo</span></h2>
                    <button class="material-icons exit-chat ripple" id="minimize-email-button" type="button" aria-label="Minimizar">
                        <i class="bi bi-arrow-left-short" style="color: #ffffff; font-size: 24px;"></i>
                    </button>
                </div>
                
                <!-- Contenido del correo -->
                <div id="email-content-area">
                    <form id="emailFloatingForm">
                        <!-- Destinatarios -->
                        <div class="mb-2 mt-2 px-2">
                            <label class="form-label fw-bold">Destinatarios: <span id="floatRecipientCount" class="badge bg-teal-dark">0</span></label>
                            <div id="floatRecipientsContainer" class="mb-2"></div>
                            <div class="d-flex align-items-center justify-content-center mb-2">
                                <button type="button" class="btn bg-indigo-dark text-white me-2 btn-sm" data-bs-toggle="modal" data-bs-target="#floatRecipientsModal">
                                    <i class="bi bi-people-fill"></i> Seleccionar
                                </button>
                                <button type="button" class="btn bg-red-dark text-white me-2 btn-sm" id="floatClearRecipients">
                                    <i class="bi bi-trash"></i> Limpiar
                                </button>
                                <!-- Nuevo botón de plantillas -->
                                <div class="btn-group">
                                    <button type="button" class="btn bg-teal-dark text-white btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="bi bi-file-text"></i> Plantillas
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#" id="floatSaveTemplate">
                                            <i class="bi bi-save"></i> Guardar plantilla
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" id="floatLoadTemplate">
                                            <i class="bi bi-folder2-open"></i> Cargar plantilla
                                        </a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Asunto del correo -->
                        <div class="mb-2 px-2">
                            <label for="floatEmailSubject" class="form-label fw-bold">Asunto:</label>
                            <input type="text" class="form-control form-control-sm" id="floatEmailSubject" required>
                        </div>
                        
                        <!-- Editor de texto enriquecido -->
                        <div class="mb-2 px-2">
                            <label for="floatEmailContent" class="form-label fw-bold">Contenido:</label>
                            <textarea id="floatEmailContent" name="floatEmailContent" class="form-control"></textarea>
                        </div>
                        
                        <!-- Botón de envío -->
                        <div class="text-center mb-2">
                            <button type="submit" class="btn bg-indigo-dark text-white">
                                <i class="bi bi-send-fill"></i> Enviar Correo
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para seleccionar destinatarios en ventana flotante -->
<div class="modal fade" id="floatRecipientsModal" tabindex="-1" aria-labelledby="floatRecipientsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-magenta-dark text-white">
                <h5 class="modal-title" id="floatRecipientsModalLabel">
                    <i class="bi bi-search"></i> Buscar destinatario por Número de Identificación
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="input-group mb-3">
                    <input type="text" id="floatSearchNumberId" class="form-control" placeholder="Ingrese el número de identificación">
                    <button class="btn bg-indigo-dark text-white" type="button" id="btnFloatSearchUser">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div id="floatSearchResult"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Estilos CSS para la ventana flotante -->
<style>
    /* Estilos básicos para la ventana flotante */
    .chat-email-board {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    
    .widget-position-right {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 1000;
    }
    
    /* Estilo del contenedor principal */
    .email-chat {
        display: none;
        flex-direction: column;
        width: 480px; /* Aumentado de 350px a 450px para mayor ancho */
        max-width: 95vw;
        height: 550px; /* Aumentado de 500px a 550px para más espacio vertical */
        max-height: 85vh;
        background-color: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 40px rgba(0, 0, 0, 0.16);
        transition: transform 0.3s ease;
    }
    
    /* Cuando está abierto */
    #emailChatContainer.open .email-chat {
        display: flex;
        transform: translateY(0);
    }
    
    /* Encabezado */
    .email-chat-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        color: white;
    }
    
    .email-chat-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 600;
    }
    
    /* Botón para cerrar/minimizar */
    .material-icons.exit-chat {
        background: transparent;
        border: none;
        cursor: pointer;
        padding: 5px;
        display: flex;
        align-items: center;
    }
    
    /* Área de contenido */
    #email-content-area {
        flex-grow: 1;
        overflow-y: auto;
        padding: 10px 0;
    }
    
    /* Botón de envío - añadir nuevo estilo */
    #emailFloatingForm .text-center.mb-2 {
        margin-bottom: 20px !important; /* Aumentar el margen inferior */
        padding-bottom: 8px; /* Añadir relleno inferior */
    }
    
    /* Mejorar el estilo del botón enviar */
    #emailFloatingForm button[type="submit"] {
        padding: 8px 30px; /* Más relleno para un botón más grande */
        transition: background-color 0.3s ease;
    }
    
    #emailFloatingForm button[type="submit"]:hover {
        background-color: #5e35b1 !important; /* Color más oscuro al pasar el ratón */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Botón flotante para abrir */
    #email-chat-button {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 999;
    }
    
    #email-button-body {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }
    
    /* Estilos para el contenedor de recipients */
    #floatRecipientsContainer {
        min-height: 42px;
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 5px;
        background-color: white;
        max-height: 100px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    
    /* Estilo para las etiquetas de destinatarios */
    .recipient-tag {
        display: inline-block;
        background-color: #f0f0f0;
        border-radius: 20px;
        padding: 3px 8px;
        margin: 2px;
        border: 1px solid #ddd;
        font-size: 12px;
        order: 2;
    }
    
    .recipient-tag .remove-tag {
        cursor: pointer;
        margin-left: 3px;
        color: #888;
    }
    
    /* Animación del botón flotante */
    .buttonWave {
        position: absolute;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: rgba(0, 151, 106, 0.3);
        opacity: 0;
        z-index: -1;
        animation: pulse 1.8s ease-out infinite;
    }
    
    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 0;
        }
        50% {
            opacity: 0.5;
        }
        100% {
            transform: scale(1.8);
            opacity: 0;
        }
    }
    
    /* Ajustes para el editor Summernote en modo flotante */
    .email-chat .note-editor.note-frame {
        border: 1px solid #ddd !important;
    }

    /* Ajustes para los modales de Summernote */
    .note-modal {
        z-index: 9999 !important; /* Aseguramos que el modal esté por encima */
    }

    .note-modal-backdrop {
        display: none !important;
    }

    /* Ajustar el modal para que funcione sin backdrop */
    .note-modal.note-modal-show {
        position: absolute !important;
        z-index: 99999 !important;
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        max-width: 90vw !important;
        margin: 0 !important;
        box-shadow: 0 0 15px rgba(0,0,0,0.2) !important;
    }

    /* Asegurar que el modal esté por encima del contenido */
    .note-modal-content {
        position: relative !important;
        z-index: 100000 !important;
        background: white !important;
    }

    .note-popover {
        z-index: 9999 !important; /* Para los popovers de Summernote */
    }

    /* Asegurar que el contenedor del chat no bloquee los modales */
    .email-chat {
        z-index: 1000;
    }

    .widget-position-right {
        z-index: 1000;
    }

    /* Estilos para el modal de imagen */
    .note-modal.note-modal-show {
        position: fixed !important;
        z-index: 99999 !important; /* Valor muy alto para asegurar que esté por encima de todo */
        top: 50% !important;
        left: 50% !important;
        transform: translate(-50%, -50%) !important;
        max-width: 90vw !important;
        margin: 0 !important;
    }

    .note-modal-backdrop {
        position: fixed !important;
        z-index: 99998 !important; /* Justo debajo del modal */
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        height: 100% !important;
        background: rgba(0, 0, 0, 0.5) !important;
    }

    /* Reset para otros elementos que puedan interferir */
    .note-editor {
        position: relative !important;
        z-index: 1 !important;
    }

    .chat-email-board {
        position: relative !important;
        z-index: 1000 !important;
    }

    /* Asegurar que los elementos del modal estén por encima */
    .note-modal-content {
        position: relative !important;
        z-index: 100000 !important;
    }

    /* Corregir posible desbordamiento */
    .note-modal-body {
        max-height: 80vh !important;
        overflow-y: auto !important;
    }

    /* Evitar que el contenido del chat interfiera */
    .email-chat {
        transform: none !important;
    }

    .widget-position-right {
        transform: none !important;
    }
    
    /* Ajustes para el editor Summernote en modo flotante */
    .email-chat .note-editor.note-frame {
        border: 1px solid #ddd !important;
    }
    
    .email-chat .note-toolbar {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        padding: 3px !important;
    }
    
    .email-chat .note-editing-area {
        min-height: 150px;
    }
    
    .email-chat .note-editable {
        font-size: 14px;
    }
    
    /* Estilos responsive */
    @media (max-width: 576px) {
        .email-chat {
            width: 300px;
        }
    }
    
    /* Estilos para el SweetAlert personalizado */
    .swal2-popup.swal2-modal.recipients-swal {
        width: 90% !important;
        max-width: 1200px !important;
        padding: 0 !important;
    }
    
    .swal2-popup.recipients-swal .swal2-title {
        margin: 0;
        padding: 0.8em;
        color: white;
        background: #30336b;
        font-size: 1.2em;
    }
    
    .swal2-popup.recipients-swal .swal2-html-container {
        margin: 0;
        padding: 0.5em;
        overflow: hidden;
    }
    
    .swal2-popup.recipients-swal .swal2-actions {
        margin-top: 0;
    }
    
    /* Tabla dentro del SweetAlert */
    #swalUsersTable_wrapper {
        margin-top: 10px;
    }
    
    #swalUsersTable {
        width: 100% !important;
    }
    
    .table-container-swal {
        max-height: 50vh;
        overflow: hidden;
    }
    
    .table-wrapper-swal {
        overflow-y: auto;
        max-height: inherit;
    }

    /* Estilos adicionales */
    .input-group.input-group-sm {
        position: sticky;
        top: 0;
        background-color: white;
        z-index: 1;
        margin-bottom: 8px;
        padding: 4px 0;
    }

    .input-group {
        order: 1;
    }

    /* Estilos para el modal de búsqueda individual */
    #floatSearchResult .card {
        border: 1px solid #dee2e6;
        border-radius: 8px;
    }
    
    #floatSearchResult .card-body {
        padding: 1rem;
    }
    
    #floatSearchResult .fw-bold {
        color: #495057;
        min-width: 80px;
    }
    
    .spinner-border {
        width: 1.5rem;
        height: 1.5rem;
    }
</style>

<!-- Crear un archivo JavaScript separado para el editor de correo -->
<script>
// IIFE (Immediately Invoked Function Expression) para encapsular todo el código
(function() {
    // Función para cargar scripts dinámicamente
    function loadScript(url, callback) {
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.async = true;
        
        if (script.readyState) {  // IE
            script.onreadystatechange = function() {
                if (script.readyState === "loaded" || script.readyState === "complete") {
                    script.onreadystatechange = null;
                    callback();
                }
            };
        } else {  // Otros navegadores
            script.onload = callback;
        }
        
        script.src = url;
        document.head.appendChild(script);
    }
    
    // Función para cargar CSS dinámicamente
    function loadCSS(url) {
        var link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = url;
        document.head.appendChild(link);
    }
    
    // Función para verificar si jQuery ya está cargado
    function jQueryLoaded() {
        return (typeof jQuery !== 'undefined');
    }
    
    // Función para verificar si Summernote ya está cargado
    function summernoteLoaded() {
        return (typeof jQuery !== 'undefined' && typeof jQuery.fn.summernote !== 'undefined');
    }
    
    // Función para verificar si SweetAlert2 ya está cargado
    function sweetalert2Loaded() {
        return (typeof Swal !== 'undefined');
    }
    
    // Función para inicializar el editor cuando todo esté listo
    function initializeEmailChat() {
        var $j = jQuery;
        
        $j(function() {
            console.log("Inicializando chat de email con Summernote disponible");
            
            // Referencias a elementos del DOM
            const emailChatButton = document.getElementById("email-chat-button");
            const emailChatContainer = document.getElementById("emailChatContainer");
            const minimizeEmailButton = document.getElementById("minimize-email-button");
            const headerEmailButton = document.getElementById('header-email-button');
            
            // Variables para mantener el estado
            const selectedRecipients = new Map();
            
            // Configurar el editor Summernote
            try {
                $j('#floatEmailContent').summernote({
                    lang: 'es-ES',
                    height: 200,
                    placeholder: 'Escriba el contenido de su correo aquí...',
                    toolbar: [
                        ['style', ['bold', 'italic', 'underline']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol']],
                        ['insert', ['link', 'picture']],
                    ],
                    dialogsInBody: true,
                    dialogsFade: false,
                    callbacks: {
                        onInit: function() {
                            $j('.note-editable').css('min-height', '180px');
                        },
                        onImageUpload: function(files) {
                            for(let i = 0; i < files.length; i++) {
                                const file = files[i];
                                const reader = new FileReader();
                                
                                reader.onloadend = function() {
                                    const image = $j('<img>').attr('src', reader.result);
                                    $j('#floatEmailContent').summernote('insertNode', image[0]);
                                }
                                
                                reader.readAsDataURL(file);
                            }
                        }
                    },
                    popover: {
                        image: [
                            ['imagesize', ['imageSize100', 'imageSize50', 'imageSize25']],
                            ['float', ['floatLeft', 'floatRight', 'floatNone']],
                            ['remove', ['removeMedia']]
                        ]
                    }
                });
                console.log("Summernote inicializado correctamente");
            } catch(e) {
                console.error("Error al inicializar Summernote:", e);
            }
            
            // Sistema de plantillas para el editor flotante
            $j('#floatSaveTemplate').click(function() {
                Swal.fire({
                    title: 'Guardar como plantilla',
                    html: `
                        <div class="form-group">
                            <label>Nombre de la plantilla</label>
                            <input type="text" id="floatTemplateName" class="form-control" required>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    preConfirm: () => {
                        const name = $j('#floatTemplateName').val();
                        const subject = $j('#floatEmailSubject').val();
                        const content = $j('#floatEmailContent').summernote('code');

                        return $j.ajax({
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

            // Cargar plantilla en el editor flotante
            $j('#floatLoadTemplate').click(function() {
                $j.post('components/multipleEmail/email_templates.php', {
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
                                    <select id="floatTemplateSelect" class="form-control">
                                        <option value="">Seleccione una plantilla...</option>
                                        ${templateList}
                                    </select>
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'Cargar',
                            cancelButtonText: 'Cancelar',
                            preConfirm: () => {
                                const templateId = $j('#floatTemplateSelect').val();
                                if (!templateId) {
                                    Swal.showValidationMessage('Por favor seleccione una plantilla');
                                    return false;
                                }
                                return $j.post('components/multipleEmail/email_templates.php', {
                                    action: 'load',
                                    id: templateId
                                });
                            }
                        }).then((result) => {
                            if (result.isConfirmed && result.value.success) {
                                const template = result.value.template;
                                $j('#floatEmailSubject').val(template.subject);
                                $j('#floatEmailContent').summernote('code', template.content);
                                Swal.fire('¡Cargado!', 'La plantilla se ha cargado correctamente', 'success');
                            }
                        });
                    } else {
                        Swal.fire('Sin plantillas', 'No hay plantillas guardadas', 'info');
                    }
                });
            });
            
            // Función para actualizar contadores de destinatarios
            function updateFloatCounters() {
                $j('#floatRecipientCount').text(selectedRecipients.size);
            }
            
            // Función para renderizar las etiquetas de destinatarios
            function renderFloatRecipientTags() {
                const container = $j('#floatRecipientsContainer');
                const inputGroup = container.find('.input-group');
                
                container.empty();
                container.append(inputGroup);

                selectedRecipients.forEach((recipient) => {
                    const tag = $j(`
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
            
            // Eliminar un destinatario
            $j(document).on('click', '#floatRecipientsContainer .remove-tag', function() {
                const email = $j(this).data('email');
                selectedRecipients.delete(email);
                $j(this).parent().remove();
                updateFloatCounters();
            });
            
            // Limpiar todos los destinatarios
            $j('#floatClearRecipients').on('click', function() {
                selectedRecipients.clear();
                $j('#floatRecipientsContainer').empty();
                updateFloatCounters();
                
                // Volver a agregar el campo de entrada manual
                addEmailInputField('floatRecipientsContainer', selectedRecipients, updateFloatCounters);
            });
            
            // Buscar usuario por number_id (nueva funcionalidad)
            $j('#btnFloatSearchUser').on('click', function() {
                const numberId = $j('#floatSearchNumberId').val().trim();
                if (!numberId) {
                    $j('#floatSearchResult').html('<div class="alert alert-warning">Ingrese un número de identificación.</div>');
                    return;
                }
                
                $j('#floatSearchResult').html('<div class="text-center"><span class="spinner-border"></span> Buscando...</div>');
                
                $j.ajax({
                    url: 'components/multipleEmail/get_user_by_numberid.php',
                    method: 'GET',
                    data: { number_id: numberId },
                    dataType: 'json'
                }).done(function(response) {
                    if (response.success && response.user) {
                        const user = response.user;
                        $j('#floatSearchResult').html(`
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
                                        <button class="btn bg-magenta-dark text-white" id="addFloatUserRecipient"
                                            data-email="${user.email}" data-name="${user.fullName}">
                                            <i class="bi bi-plus-circle"></i> Agregar destinatario
                                        </button>
                                    </div>
                                </div>
                            </div>
                        `);
                    } else {
                        $j('#floatSearchResult').html('<div class="alert alert-danger">Usuario no encontrado.</div>');
                    }
                }).fail(function() {
                    $j('#floatSearchResult').html('<div class="alert alert-danger">Error en la búsqueda.</div>');
                });
            });

            // Agregar usuario buscado como destinatario
            $j(document).on('click', '#addFloatUserRecipient', function() {
                const email = $j(this).data('email');
                const name = $j(this).data('name');
                
                if (!selectedRecipients.has(email)) {
                    selectedRecipients.set(email, { email, name });
                    renderFloatRecipientTags();
                    updateFloatCounters();
                    
                    // Cerrar el modal
                    $j('#floatRecipientsModal').modal('hide');
                    
                    // Limpiar el resultado de búsqueda
                    $j('#floatSearchNumberId').val('');
                    $j('#floatSearchResult').html('');
                    
                    Swal.fire('Agregado', 'El destinatario fue agregado correctamente', 'success');
                } else {
                    Swal.fire('Advertencia', 'Este correo ya está en la lista', 'warning');
                }
            });

            // Permitir búsqueda con Enter
            $j('#floatSearchNumberId').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $j('#btnFloatSearchUser').click();
                }
            });
            
            // Envío del formulario
            $j('#emailFloatingForm').on('submit', function(e) {
                e.preventDefault();

                if (selectedRecipients.size === 0) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Debe seleccionar al menos un destinatario',
                        icon: 'error'
                    });
                    return;
                }

                const subject = $j('#floatEmailSubject').val().trim();
                if (!subject) {
                    Swal.fire({
                        title: 'Error',
                        text: 'El asunto del correo es obligatorio',
                        icon: 'error'
                    });
                    return;
                }

                const content = $j('#floatEmailContent').summernote('code');
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
                        sendFloatEmails(subject, content);
                    }
                });
            });
            
            // Función para enviar correos
            async function sendFloatEmails(subject, content) {
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

                // Procesar cada destinatario de manera secuencial
                const processRecipients = async () => {
                    for (const recipient of recipients) {
                        try {
                            const response = await $j.ajax({
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
                            console.error(`Error al enviar a ${recipient.email}:`, error);
                            const detail = error.responseText
                                || error.statusText
                                || error.errorThrown
                                || error.message
                                || JSON.stringify(error);
                            errors.push({
                                recipient: recipient.email,
                                message: `Error de conexión: ${detail}`
                            });
                        }

                        // Actualizar progreso
                        $j('#emailProgress').html(`
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

            // Función para guardar historial
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
                            sent_from: 'float',
                            recipients: JSON.stringify(emailData.recipients),
                            errors: JSON.stringify(emailData.errors)
                        }
                    });
                    console.log('Historial guardado:', response);
                } catch (error) {
                    console.error('Error al guardar historial:', error);
                }
            };
            
            // Manejar eventos de abrir/cerrar ventana flotante
            if (emailChatButton) {
                emailChatButton.addEventListener("click", function() {
                    if (emailChatContainer) {
                        emailChatContainer.classList.add("open");
                    }
                });
            }
            
            if (minimizeEmailButton) {
                minimizeEmailButton.addEventListener("click", function() {
                    if (emailChatContainer) {
                        emailChatContainer.classList.remove("open");
                    }
                });
            }
            
            if (headerEmailButton) {
                headerEmailButton.addEventListener("click", function(e) {
                    e.preventDefault();
                    if (emailChatContainer) {
                        emailChatContainer.classList.add("open");
                    }
                });
            }

            // Función para agregar campo de entrada manual de correos electrónicos
            function addEmailInputField(containerId, recipientsMap, updateCountersFn) {
                const $j = jQuery;
                const container = $j(`#${containerId}`);
                const inputGroup = $j(`
                    <div class="input-group input-group-sm mt-2 mb-2">
                        <input type="email" class="form-control form-control-sm manual-email-input" 
                            placeholder="Ingrese correo electrónico y presione Enter">
                        <button class="btn btn-outline-secondary add-manual-email" type="button">
                            <i class="bi bi-plus-circle"></i>
                        </button>
                    </div>
                `);

                container.prepend(inputGroup);

                // Validar email
                function isValidEmail(email) {
                    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
                }

                // Agregar email
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

                    renderFloatRecipientTags();
                    updateCountersFn();
                }

                // Procesar múltiples correos pegados
                function processMultipleEmails(input) {
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
                        renderFloatRecipientTags();
                        updateCountersFn();
                    }
                }

                // Enter para agregar
                inputGroup.find('.manual-email-input').on('keypress', function(e) {
                    if (e.which === 13) {
                        e.preventDefault();
                        const email = $j(this).val().trim();
                        if (email) {
                            addEmail(email);
                            $j(this).val('');
                        }
                    }
                });

                // Botón para agregar
                inputGroup.find('.add-manual-email').on('click', function() {
                    const email = inputGroup.find('.manual-email-input').val().trim();
                    if (email) {
                        addEmail(email);
                        inputGroup.find('.manual-email-input').val('');
                    }
                });

                // Pegado múltiple
                inputGroup.find('.manual-email-input').on('paste', function(e) {
                    const clipboardData = e.originalEvent.clipboardData || window.clipboardData;
                    const pastedData = clipboardData.getData('Text');
                    if (pastedData) {
                        e.preventDefault();
                        processMultipleEmails(pastedData);
                        $j(this).val('');
                    }
                });
            }

            // Agregar campo de entrada manual de correos
            addEmailInputField('floatRecipientsContainer', selectedRecipients, updateFloatCounters);
        });
    }
    
    // Función para cargar recursos dinámicamente
    function startLoading() {
        const dependencies = [];
        
        // Cargar jQuery si es necesario
        if (!jQueryLoaded()) {
            dependencies.push(new Promise(resolve => {
                loadScript("https://code.jquery.com/jquery-3.6.0.min.js", resolve);
            }));
        }
        
        // Cargar SweetAlert2 si es necesario
        if (!sweetalert2Loaded()) {
            dependencies.push(new Promise(resolve => {
                loadCSS("https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.min.css");
                loadScript("https://cdn.jsdelivr.net/npm/sweetalert2@11.7.0/dist/sweetalert2.all.min.js", resolve);
            }));
        }
        
        // Cargar Animate.css para animaciones de SweetAlert
        loadCSS("https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css");
        
        // Cuando jQuery esté listo, cargar Summernote si es necesario
        Promise.all(dependencies).then(() => {
            if (!summernoteLoaded()) {
                loadCSS("https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css");
                loadScript("https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js", function() {
                    loadScript("https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/lang/summernote-es-ES.min.js", function() {
                        initializeEmailChat();
                    });
                });
            } else {
                initializeEmailChat();
            }
        });
    }
    
    // Esperar a que el DOM esté completamente cargado
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", startLoading);
    } else {
        startLoading();
    }
})();
</script>