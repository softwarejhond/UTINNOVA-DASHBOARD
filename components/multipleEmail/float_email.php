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
                                <button type="button" class="btn bg-indigo-dark text-white me-2 btn-sm" id="openFloatRecipientsSwal">
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
        
        <!-- Botón flotante para abrir la ventana de correo -->
        <!-- <div id="email-chat-button" class="chat-closed">
            <div class="buttonWave"></div>
            <button type="button" 
                   id="email-button-body" 
                   class="chrome" 
                   tabindex="0" 
                   aria-label="Abrir editor de correo"
                   style="background: linear-gradient(135deg, #00976a, #006a4e); box-shadow: rgba(0, 151, 106, 0.5) 0px 4px 24px;">
                <i class="material-icons type1 for-closed active" style="color: rgb(255, 255, 255);">
                    <svg fill="#FFFFFF" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"></path>
                        <path d="M0 0h24v24H0z" fill="none"></path>
                    </svg>
                </i>
            </button>
        </div> -->
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
        // IMPORTANTE: NO usar jQuery.noConflict(true) para evitar problemas con Bootstrap
        // Guardamos una referencia local pero mantenemos jQuery global para Bootstrap
        var $j = jQuery;
        
        // Todo el código que usa jQuery debe estar dentro de esta función
        $j(function() {
            console.log("Inicializando chat de email con Summernote disponible");
            
            // Referencias a elementos del DOM
            const emailChatButton = document.getElementById("email-chat-button");
            const emailChatContainer = document.getElementById("emailChatContainer");
            const minimizeEmailButton = document.getElementById("minimize-email-button");
            const headerEmailButton = document.getElementById('header-email-button');
            const openRecipientsButton = document.getElementById('openFloatRecipientsSwal');
            
            // Variables para mantener el estado
            const selectedRecipients = new Map();
            let swalTable = null;
            
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
                        ['insert', ['link', 'picture']], // Mantener 'picture' para imágenes
                    ],
                    dialogsInBody: true,
                    dialogsFade: false, // Desactivar el efecto fade
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
                $j('#floatSelectedUsersCount').text(selectedRecipients.size);
            }
            
            // Modificar la función renderFloatRecipientTags
            function renderFloatRecipientTags() {
                const container = $j('#floatRecipientsContainer');
                const inputGroup = container.find('.input-group'); // Guardar referencia al input
                
                container.empty(); // Limpiar el contenedor
                container.append(inputGroup); // Volver a añadir el input al principio

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
            });
            
            // Función para crear el contenido HTML para SweetAlert
            function createSwalRecipientsContent() {
                const departamentosOptions = <?php echo json_encode($departamentos); ?>;
                const sedesOptions = <?php echo json_encode($sedes); ?>;
                const programasOptions = <?php echo json_encode($programas); ?>;
                const modalidadesOptions = <?php echo json_encode($modalidades); ?>;
                
                let departamentosHTML = '<option value="">Todos</option>';
                departamentosOptions.forEach(dept => {
                    departamentosHTML += `<option value="${dept}">${dept}</option>`;
                });
                
                let sedesHTML = '<option value="">Todas</option>';
                sedesOptions.forEach(sede => {
                    sedesHTML += `<option value="${sede}">${sede}</option>`;
                });
                
                let programasHTML = '<option value="">Todos</option>';
                programasOptions.forEach(prog => {
                    programasHTML += `<option value="${prog}">${prog}</option>`;
                });
                
                let modalidadesHTML = '<option value="">Todas</option>';
                modalidadesOptions.forEach(mod => {
                    modalidadesHTML += `<option value="${mod}">${mod}</option>`;
                });
                
                return `
                    <!-- Filtros -->
                    <div class="card mb-2">
                        <div class="card-header bg-indigo-dark text-white text-center">
                            <i class="bi bi-funnel"></i> Filtros
                        </div>
                        <div class="card-body justify-content-center">
                            <!-- Filtros en la parte superior -->
                            <div class="row mb-2 justify-content-center">
                                <div class="col-md-3 mb-2">
                                    <label for="swalFilterStatus" class="form-label">Estado:</label>
                                    <select id="swalFilterStatus" class="form-select form-select-sm">
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
                                    <label for="swalFilterSede" class="form-label">Sede:</label>
                                    <select id="swalFilterSede" class="form-select form-select-sm">
                                        ${sedesHTML}
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="swalFilterPrograma" class="form-label">Programa:</label>
                                    <select id="swalFilterPrograma" class="form-select form-select-sm">
                                        ${programasHTML}
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label for="swalFilterModalidad" class="form-label">Modalidad:</label>
                                    <select id="swalFilterModalidad" class="form-select form-select-sm">
                                        ${modalidadesHTML}
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer text-center">
                            <button id="swalAplicarFiltros" class="btn bg-indigo-dark text-white btn-sm me-1">
                                <i class="bi bi-funnel"></i> Filtrar selección
                            </button>
                            <button id="swalLimpiarFiltros" class="btn bg-silver btn-sm">
                                <i class="bi bi-x-circle"></i> Limpiar filtros
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de usuarios -->
                    <div class="table-container-swal">
                        <div class="table-wrapper-swal">
                            <table id="swalUsersTable" class="table table-hover table-bordered">
                                <thead class="thead-dark">
                                    <tr>
                                        <th width="50" class="text-center">
                                            <input type="checkbox" id="swalSelectAll" class="form-check-input">
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
                                                <input type="checkbox" class="form-check-input swal-user-checkbox"
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

                    <div class="mt-3 text-center">
                        <span class="badge bg-magenta-dark">
                            Usuarios seleccionados: <span id="swalSelectedUsersCount">0</span>
                        </span>
                    </div>
                `;
            }
            
            // Función para manejar la apertura del SweetAlert de destinatarios
            function openRecipientsSelector() {
                Swal.fire({
                    title: '<i class="bi bi-people-fill"></i> Seleccionar Destinatarios',
                    html: createSwalRecipientsContent(),
                    customClass: {
                        popup: 'recipients-swal'
                    },
                    showCancelButton: true,
                    confirmButtonText: '<i class="bi bi-check-circle"></i> Confirmar Selección',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#30336b',
                    cancelButtonColor: '#6c757d',
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    },
                    width: '90%',
                    didOpen: () => {
                        // Inicializar DataTable
                        swalTable = $j('#swalUsersTable').DataTable({
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
                        
                        // Marcar usuarios ya seleccionados
                        $j('.swal-user-checkbox').each(function() {
                            const email = $j(this).data('email');
                            if (selectedRecipients.has(email)) {
                                $j(this).prop('checked', true);
                            }
                        });
                        
                        // Actualizar contador
                        $j('#swalSelectedUsersCount').text(selectedRecipients.size);
                        
                        // Manejar selección de todos
                        $j('#swalSelectAll').on('change', function() {
                            const isChecked = $j(this).prop('checked');
                            $j('.swal-user-checkbox:visible').prop('checked', isChecked).trigger('change');
                        });
                        
                        // Manejar selección individual
                        $j(document).on('change', '.swal-user-checkbox', function() {
                            const email = $j(this).data('email');
                            const name = $j(this).data('name');

                            if ($j(this).prop('checked')) {
                                selectedRecipients.set(email, {
                                    email,
                                    name
                                });
                            } else {
                                selectedRecipients.delete(email);
                            }

                            $j('#swalSelectedUsersCount').text(selectedRecipients.size);
                        });
                        
                        // Filtro personalizado para DataTables
                        $j.fn.dataTable.ext.search.push(
                            function(settings, data, dataIndex) {
                                // Asegurarse de que este filtro solo se aplique a la tabla correcta
                                if (settings.nTable.id !== 'swalUsersTable') {
                                    return true;
                                }

                                var filterStatusVal = $j('#swalFilterStatus').val();
                                var filterSedeVal = $j('#swalFilterSede').val();
                                var filterProgramaVal = $j('#swalFilterPrograma').val();
                                var filterModalidadVal = $j('#swalFilterModalidad').val();

                                // Obtener el nodo de la fila (tr) para acceder a atributos data-*
                                var rowNode = swalTable.row(dataIndex).node();
                                
                                // Columna Estado (índice 5) - Leer desde data-status
                                var rowStatus = $j(rowNode).find('td:eq(5)').attr('data-status');
                                
                                // Columnas Sede (6), Programa (7), Modalidad (8)
                                var rowSede = data[6]; 
                                var rowPrograma = data[7];
                                var rowModalidad = data[8];

                                // Aplicar filtros
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

                                return true;
                            }
                        );
                        
                        // Aplicar filtros
                        $j('#swalAplicarFiltros').on('click', function() {
                            swalTable.draw();
                        });
                        
                        // Limpiar filtros
                        $j('#swalLimpiarFiltros').on('click', function() {
                            $j('#swalFilterStatus, #swalFilterSede, #swalFilterPrograma, #swalFilterModalidad').val('');
                            swalTable.draw();
                        });
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        renderFloatRecipientTags();
                        updateFloatCounters();
                    }
                    
                    // Eliminar el filtro personalizado para evitar que afecte a otras tablas
                    var filterIndex = $j.fn.dataTable.ext.search.indexOf(
                        $j.fn.dataTable.ext.search.find(f => 
                            f.toString().includes('swalUsersTable')
                        )
                    );
                    if (filterIndex !== -1) {
                        $j.fn.dataTable.ext.search.splice(filterIndex, 1);
                    }
                });
            }
            
            // Manejar evento de clic en el botón de seleccionar destinatarios
            $j('#openFloatRecipientsSwal').on('click', function() {
                openRecipientsSelector();
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
            
            // Manejar clic en botón flotante para abrir chat
            if (emailChatButton) {
                emailChatButton.addEventListener("click", function() {
                    if (emailChatContainer) {
                        emailChatContainer.classList.add("open");
                    }
                });
            }
            
            // Manejar clic en botón para minimizar chat
            if (minimizeEmailButton) {
                minimizeEmailButton.addEventListener("click", function() {
                    if (emailChatContainer) {
                        emailChatContainer.classList.remove("open");
                    }
                });
            }
            
            // Conectar el botón del header con la ventana flotante
            if (headerEmailButton) {
                headerEmailButton.addEventListener("click", function(e) {
                    e.preventDefault(); // Prevenir el comportamiento predeterminado del botón
                    if (emailChatContainer) {
                        emailChatContainer.classList.add("open");
                    }
                });
            } else {
                console.warn("No se encontró el botón en el header con ID 'header-email-button'");
            }

            // Agregar campo de entrada manual de correos
            addEmailInputField('floatRecipientsContainer', selectedRecipients, updateFloatCounters);
        });
    }
    
    // Función para agregar campo de entrada manual de correos
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

            const tag = $(`
                <div class="recipient-tag manual-entry">
                    &lt;${email}&gt;
                    <span class="remove-tag" data-email="${email}">
                        <i class="bi bi-x-circle"></i>
                    </span>
                </div>
            `);

            tag.insertAfter(inputGroup);
            updateCountersFn();
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
    }
    
    // Iniciar la carga de recursos necesarios
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