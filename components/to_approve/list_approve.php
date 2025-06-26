<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Incluir conexión y obtener datos de la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Función para obtener cursos desde la base de datos - Definir las variables globales para Moodle
$api_url = "https://talento-tech.uttalento.co/webservice/rest/server.php";
$token   = "3f158134506350615397c83d861c2104";
$format  = "json";

function callMoodleAPI($function, $params = [])
{
    global $api_url, $token, $format;
    $params['wstoken'] = $token;
    $params['wsfunction'] = $function;
    $params['moodlewsrestformat'] = $format;
    $url = $api_url . '?' . http_build_query($params);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error en la solicitud cURL: ' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($response, true);
}

// Función para obtener cursos desde Moodle
function getCourses()
{
    return callMoodleAPI('core_course_get_courses');
}

// Obtener cursos y almacenarlos en $courses_data
$courses_data = getCourses();


?>


<style>
    /* Layout principal */
    .main-container {
        display: flex;
        height: calc(100vh - 100px);
        gap: 20px;
    }

    /* Panel izquierdo de filtros */
    .filters-panel {
        flex: 0 0 350px;
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow-y: auto;
    }

    .filters-panel h5 {
        color: #495057;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
    }

    .form-group {
        margin-bottom: 20px;
    }

    .form-label {
        font-weight: 600;
        color: #495057;
        margin-bottom: 8px;
    }

    /* Panel derecho de tabla */
    .table-panel {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        overflow: hidden;
    }

    .table-header {
        padding: 20px;
        background: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .table-content {
        flex: 1;
        overflow: auto;
        padding: 0;
    }

    /* Estilos para la tabla */
    #listaEstudiantes {
        width: 100%;
        min-width: 1400px; /* Ancho mínimo para scroll horizontal */
        margin: 0;
        table-layout: auto; /* Cambiar a auto para anchos automáticos */
    }

    #listaEstudiantes th,
    #listaEstudiantes td {
        vertical-align: middle;
        white-space: nowrap;
        padding: 8px 12px;
        min-width: 100px; /* Ancho mínimo para todas las columnas */
    }

    /* Permitir texto largo solo en nombre y correo */
    #listaEstudiantes td:nth-child(3),
    #listaEstudiantes td:nth-child(4) {
        white-space: normal !important;
        min-width: 180px;
        max-width: 250px;
        word-wrap: break-word;
    }

    /* Thead fijo */
    #listaEstudiantes thead {
        position: sticky;
        top: 0;
        background: white;
        z-index: 10;
    }

    #listaEstudiantes thead th {
        border-top: none;
        border-bottom: 2px solid #dee2e6;
        background: #f8f9fa;
        font-weight: 600;
    }

    .badge-success {
        background-color: #66cc00;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    .badge-warning {
        background-color: #ffc107;
    }

    .percentage-bar {
        height: 20px;
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .percentage-fill {
        height: 100%;
        border-radius: 10px;
        transition: width 0.3s ease;
        background-color: #ec008c !important;
    }

    .percentage-fill.success {
        background-color: #ec008c !important;
    }

    .percentage-fill.warning {
        background-color: #ec008c !important;
    }

    .percentage-fill.danger {
        background-color: #ec008c !important;
    }

    /* Estilos para el icono de birrete */
    .graduation-icon {
        font-size: 24px;
        color: #66cc00;
        cursor: pointer;
        transition: color 0.3s ease, transform 0.2s ease;
    }

    .graduation-icon:hover {
        color: #66cc00;
        transform: scale(1.1);
    }

    /* Estilo personalizado para el popover */
    .popover {
        max-width: 200px;
    }

    .popover-header {
        background-color: #28a745;
        color: white;
        border-bottom: 1px solid #66cc00;
    }

    /* Botón de aprobar todos */
    .btn-aprobar-todos {
        position: fixed;
        color: white !important; 
        top: 120px; /* 100px + 20px extra para no quedar encima del header */
        right: 30px;
        z-index: 1000;
        border: none;
        font-weight: 600;
        padding: 15px 25px;
        border-radius: 50px;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        font-size: 16px;
        min-width: 100px;
        text-align: center;
    }

    /* Estado habilitado - indigo oscuro */
    .btn-aprobar-todos:not(:disabled) {
        background-color: #30336b; /* indigo-dark personalizado */
        cursor: pointer;
    }

    .btn-aprobar-todos:not(:disabled):hover {
        background-color: #252856; /* indigo más oscuro */
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 6px 20px rgba(48, 51, 107, 0.4);
    }

    /* Estado deshabilitado */
    .btn-aprobar-todos:disabled {
        background-color: #6b7280; /* gris */
        cursor: not-allowed;
        opacity: 0.6;
    }

    .btn-aprobar-todos:disabled:hover {
        transform: none;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }

    /* Animación de entrada */
    .btn-aprobar-todos.show {
        animation: slideInRight 0.5s ease-out;
    }

    @keyframes slideInRight {
        from {
            transform: translateX(100px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }

    /* Responsive para móvil */
    @media (max-width: 768px) {
        .btn-aprobar-todos {
            top: 100px;
            right: 15px;
            padding: 12px 20px;
            font-size: 14px;
            min-width: 180px;
        }
    }

    @media (max-width: 480px) {
        .btn-aprobar-todos {
            top: 90px;
            right: 10px;
            padding: 10px 15px;
            font-size: 12px;
            min-width: 150px;
        }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .main-container {
            flex-direction: column;
            height: auto;
        }

        .filters-panel {
            flex: none;
        }

        .table-panel {
            min-height: 500px;
        }
    }
</style>



<div class="container-fluid mt-4">
    <div class="main-container">
        <!-- Panel izquierdo de filtros -->
        <div class="filters-panel">
            <h5><i class="fa fa-filter"></i> Filtros de Búsqueda</h5>

            <!-- Selección de Curso -->
            <div class="form-group">
                <label class="form-label">Curso</label>
                <select id="bootcamp" class="form-select course-select">
                    <option value="">Seleccione un curso</option>
                    <?php foreach ($courses_data as $course): ?>
                        <?php if (in_array($course['categoryid'], [19, 21, 24, 26, 27, 35, 20, 22, 23, 25, 28, 36])): ?>
                            <option value="<?= htmlspecialchars($course['id']) ?>">
                                <?= htmlspecialchars($course['id'] . ' - ' . $course['fullname']) ?>
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Información del Curso Seleccionado -->
            <div id="courseInfoContainer" style="display: none;">
                <div class="form-group">
                    <label class="form-label">Tipo de Curso</label>
                    <input type="text" id="courseType" class="form-control" readonly value="Técnico">
                </div>

                <div class="form-group">
                    <label class="form-label">Modalidad</label>
                    <input type="text" id="courseMode" class="form-control" readonly placeholder="Modalidad del curso">
                </div>

                <div class="form-group">
                    <label class="form-label">Sede</label>
                    <input type="text" id="courseSede" class="form-control" readonly placeholder="Sede del curso">
                </div>
            </div>
        </div>

        <!-- Panel derecho de tabla -->
        <div class="table-panel">
            <!-- Header de la tabla -->
            <div class="table-header">
                <h5 class="mb-0"><i class="fa fa-users"></i> Estudiantes para Aprobación</h5>
                <button id="btnExportarExcel" class="btn btn-outline-success" style="display: none;">
                    <i class="fa fa-file-excel"></i> Exportar Excel
                </button>
            </div>

            <!-- Contenido de la tabla scrolleable -->
            <div class="table-content">
                <table id="listaEstudiantes" class="table table-hover table-bordered">
                    <thead>
                        <tr class="text-center">
                            <th>ID</th>
                            <th>Número de ID</th>
                            <th>Nombre completo</th>
                            <th>Correo institucional</th>
                            <th>Programa</th>
                            <th>Modalidad</th>
                            <th>Sede</th>
                            <th>% Asistencia</th>
                            <th>Nota Final</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="11" class="text-center py-5">
                                <i class="fa fa-search fa-2x text-muted mb-3"></i><br>
                                Seleccione un curso para cargar los estudiantes
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Botón flotante para aprobar todos -->
<button id="btnAprobarTodos" class="btn btn-aprobar-todos" disabled>
    <i class="fa fa-graduation-cap text-white"></i> No hay estudiantes aptos
</button>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        // Variable para almacenar estudiantes aptos
        let estudiantesAptos = [];

        // Inicializar popovers de Bootstrap
        function initializePopovers() {
            $('[data-bs-toggle="popover"]').popover();
        }

        // Función para aprobar estudiante individual
        window.aprobarEstudiante = function(studentId) {
            console.log('aprobarEstudiante llamada con ID:', studentId);
            
            // Verificar que tenemos todos los datos necesarios
            const courseCode = $('#bootcamp').val();
            console.log('Curso actual:', courseCode);
            
            if (!courseCode) {
                Swal.fire({
                    title: 'Error',
                    text: 'No se ha seleccionado un curso',
                    icon: 'error'
                });
                return;
            }
            
            // Ocultar cualquier popover activo
            $('[data-bs-toggle="popover"]').popover('hide');

            Swal.fire({
                title: '¿Está seguro?',
                text: '¿Desea aprobar a este estudiante?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, aprobar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    console.log('Usuario confirmó la aprobación');
                    aprobarEstudianteIndividual(studentId);
                } else {
                    console.log('Usuario canceló la aprobación');
                }
            });
        };

        // Función para actualizar el botón flotante
        function actualizarBotonFlotante() {
            const $btn = $('#btnAprobarTodos');
            
            if (estudiantesAptos.length > 0) {
                $btn.prop('disabled', false);
                $btn.html(`<i class="fa fa-graduation-cap text-white"></i> Aprobar (${estudiantesAptos.length})`);
                if (!$btn.hasClass('show')) {
                    $btn.addClass('show');
                }
            } else {
                $btn.prop('disabled', true);
                $btn.html('<i class="fa fa-graduation-cap text-white"></i>');
                $btn.removeClass('show');
            }
        }

        // Función para actualizar la visibilidad del botón de exportación
        function actualizarBotonExportacion() {
            const $btn = $('#btnExportarExcel');
            const hayEstudiantes = $('#listaEstudiantes tbody tr[data-student-id]').length > 0;
            
            if (hayEstudiantes) {
                $btn.show();
            } else {
                $btn.hide();
            }
        }

        // Función para exportar a Excel (simplificada)
        $('#btnExportarExcel').click(function() {
            const bootcamp = $('#bootcamp').val();

            // Verificar que el bootcamp esté seleccionado
            if (!bootcamp) {
                Swal.fire({
                    title: 'Campo requerido',
                    text: 'Por favor, seleccione un curso antes de exportar',
                    icon: 'warning'
                });
                return;
            }

            // Mostrar loader con progreso
            Swal.fire({
                title: 'Generando archivo Excel...',
                html: `
                    <div class="text-center">
                        <div class="spinner-border text-primary mb-3" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                        <p class="mb-2">Procesando datos de estudiantes...</p>
                        <div class="progress mb-2">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%" id="exportProgress">
                            </div>
                        </div>
                        <small class="text-muted">Por favor espere, esto puede tomar unos segundos</small>
                    </div>
                `,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                width: '400px'
            });

            // Simular progreso
            let progress = 0;
            const progressInterval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 90) {
                    progress = 90;
                }
                $('#exportProgress').css('width', progress + '%');
            }, 200);

            // Realizar la exportación usando AJAX con responseType blob
            $.ajax({
                url: 'components/to_approve/export_excel_aprovados.php',
                type: 'POST',
                data: { bootcamp: bootcamp },
                xhr: function() {
                    var xhr = new XMLHttpRequest();
                    xhr.responseType = 'blob';
                    return xhr;
                },
                success: function(data, status, xhr) {
                    // Limpiar intervalo de progreso
                    clearInterval(progressInterval);
                    $('#exportProgress').css('width', '100%');

                    // Crear URL del blob y descargar
                    const blob = new Blob([data], { 
                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' 
                    });
                    
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    
                    // Obtener nombre del archivo del header Content-Disposition
                    const contentDisposition = xhr.getResponseHeader('Content-Disposition');
                    let filename = 'estudiantes_aprobados.xlsx';
                    
                    if (contentDisposition) {
                        const filenameMatch = contentDisposition.match(/filename="(.+)"/);
                        if (filenameMatch) {
                            filename = filenameMatch[1];
                        }
                    }
                    
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    window.URL.revokeObjectURL(url);

                    // Mostrar mensaje de éxito
                    Swal.fire({
                        title: '¡Descarga completada!',
                        text: 'El archivo Excel se ha descargado correctamente',
                        icon: 'success',
                        timer: 3000,
                        showConfirmButton: false,
                        timerProgressBar: true
                    });
                },
                error: function(xhr, status, error) {
                    // Limpiar intervalo de progreso
                    clearInterval(progressInterval);
                    
                    console.error('Error en la exportación:', error);
                    
                    Swal.fire({
                        title: 'Error en la exportación',
                        text: 'Hubo un problema al generar el archivo Excel. Por favor, inténtelo nuevamente.',
                        icon: 'error',
                        confirmButtonText: 'Entendido'
                    });
                }
            });
        });

        // Función para manejar clics en iconos de eliminación
        $(document).on('click', '.delete-approval-icon', function() {
            const studentId = $(this).data('student-id');
            const studentName = $(this).data('student-name');
            const courseCode = $(this).data('course-code');
            
            mostrarModalEliminacion(studentId, studentName, courseCode);
        });

        // Función para mostrar modal de eliminación con código de seguridad
        function mostrarModalEliminacion(studentId, studentName, courseCode) {
            let securityCode = '';
            let timer = 15;
            let interval;
            let timerElement; // Referencia específica al elemento del temporizador

            // Función para generar código aleatorio
            function generateCode() {
                const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                let result = '';
                for (let i = 0; i < 6; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            }

            // Función para actualizar el temporizador
            function updateTimer() {
                timer--;
                if (timerElement) {
                    timerElement.textContent = timer;
                }

                if (timer <= 0) {
                    timer = 15;
                    securityCode = generateCode();
                    const securityInput = document.getElementById('securityCodeInput');
                    const confirmInput = document.getElementById('confirmCodeInput');
                    
                    if (securityInput) securityInput.value = securityCode;
                    if (confirmInput) confirmInput.value = '';
                    
                    // Deshabilitar botón
                    const confirmButton = Swal.getConfirmButton();
                    if (confirmButton) {
                        confirmButton.disabled = true;
                        confirmButton.style.opacity = '0.6';
                    }
                }
            }

            // Función para copiar código (evitar que cierre el modal)
            function copiarCodigo(event) {
                // Prevenir comportamientos por defecto
                if (event) {
                    event.preventDefault();
                    event.stopPropagation();
                }

                const codigoInput = document.getElementById('securityCodeInput');
                if (codigoInput) {
                    codigoInput.select();
                    codigoInput.setSelectionRange(0, 99999); // Para móviles
                    
                    try {
                        // Usar el API moderno si está disponible
                        if (navigator.clipboard && window.isSecureContext) {
                            navigator.clipboard.writeText(codigoInput.value).then(() => {
                                mostrarMensajeCopia();
                            }).catch(() => {
                                // Fallback al método antiguo
                                document.execCommand('copy');
                                mostrarMensajeCopia();
                            });
                        } else {
                            // Fallback al método antiguo
                            document.execCommand('copy');
                            mostrarMensajeCopia();
                        }
                    } catch (err) {
                        console.error('Error al copiar:', err);
                    }
                }
            }

            function mostrarMensajeCopia() {
                // Crear un toast temporal sin usar SweetAlert para evitar conflictos
                const toast = document.createElement('div');
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: #28a745;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 5px;
                    z-index: 10000;
                    font-size: 14px;
                    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                `;
                toast.textContent = '¡Código copiado!';
                document.body.appendChild(toast);

                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 1500);
            }

            // Crear y mostrar el modal con SweetAlert2
            Swal.fire({
                title: '<i class="fas fa-exclamation-triangle text-danger"></i> Eliminar Aprobación',
                html: `
                    <div class="text-start">
                        <p class="lead">¿Está seguro de eliminar la aprobación de:</p>
                        <p class="fw-bold">${studentName}</p>
                        <p class="text-muted">Estudiante ID: ${studentId}</p>

                        <div class="alert alert-warning">
                            <p class="mb-2"><strong>Código de seguridad:</strong></p>
                            <div class="input-group mb-2">
                                <input type="text" id="securityCodeInput" class="form-control" readonly>
                                <button class="btn btn-outline-secondary" type="button" id="copyCodeBtn">
                                    <i class="fas fa-clipboard"></i>
                                </button>
                            </div>
                            <p class="mb-2">Este código cambiará en: <span id="uniqueCodeTimer">15</span> segundos</p>
                            <p class="mb-2"><strong>Ingrese el código para confirmar:</strong></p>
                            <input type="text" id="confirmCodeInput" class="form-control" placeholder="Ingrese el código">
                        </div>

                        <p class="text-muted">Esta acción eliminará la aprobación del estudiante y podrá ser aprobado nuevamente.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="fas fa-trash-alt"></i> Eliminar Aprobación',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                width: '500px',
                allowOutsideClick: false, // Evitar cierre accidental
                didOpen: () => {
                    // Obtener referencia específica al temporizador
                    timerElement = document.getElementById('uniqueCodeTimer');
                    
                    // Inicializar código y temporizador
                    securityCode = generateCode();
                    timer = 15;
                    
                    const securityInput = document.getElementById('securityCodeInput');
                    const confirmInput = document.getElementById('confirmCodeInput');
                    const copyBtn = document.getElementById('copyCodeBtn');
                    
                    if (securityInput) securityInput.value = securityCode;
                    if (confirmInput) confirmInput.value = '';
                    if (timerElement) timerElement.textContent = timer;
                    
                    // Deshabilitar botón de confirmación inicialmente
                    const confirmButton = Swal.getConfirmButton();
                    if (confirmButton) {
                        confirmButton.disabled = true;
                        confirmButton.style.opacity = '0.6';
                    }
                    
                    // Iniciar temporizador
                    interval = setInterval(updateTimer, 1000);
                    
                    // Evento para copiar código
                    if (copyBtn) {
                        copyBtn.addEventListener('click', copiarCodigo);
                    }
                    
                    // Verificar código ingresado
                    if (confirmInput) {
                        confirmInput.addEventListener('input', function() {
                            const inputCode = this.value;
                            const isValid = inputCode === securityCode;
                            
                            const confirmButton = Swal.getConfirmButton();
                            if (confirmButton) {
                                confirmButton.disabled = !isValid;
                                confirmButton.style.opacity = isValid ? '1' : '0.6';
                            }
                        });
                    }
                },
                willClose: () => {
                    // Limpiar temporizador al cerrar
                    if (interval) {
                        clearInterval(interval);
                    }
                    // Limpiar referencias
                    timerElement = null;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    eliminarAprobacion(studentId, courseCode);
                }
            });
        }

        // Función para eliminar aprobación
        function eliminarAprobacion(studentId, courseCode) {
            Swal.fire({
                title: 'Eliminando aprobación...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/to_approve/eliminar_aprobacion.php',
                type: 'POST',
                data: {
                    studentId: studentId,
                    courseCode: courseCode
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Actualizar la fila del estudiante
                        const $row = $('#student-' + studentId);
                        
                        // Cambiar el badge de estado de "Aprobado" a "Apto"
                        $row.find('td:nth-child(10)').html('<span class="badge badge-success"><i class="fa fa-check"></i> Apto</span>');
                        
                        // Cambiar el icono de eliminación por el icono de birrete
                        const $actionCell = $row.find('td:nth-child(11)');
                        $actionCell.html(`
                            <i class="fas fa-graduation-cap graduation-icon" 
                               data-bs-toggle="popover" 
                               data-bs-placement="top" 
                               data-bs-trigger="hover" 
                               data-bs-title="Aprobar Estudiante" 
                               data-bs-content="Haz clic para aprobar a este estudiante" 
                               onclick="aprobarEstudiante('${studentId}')" 
                               title="Aprobar">
                            </i>
                        `);
                        
                        // Agregar a la lista de estudiantes aptos
                        if (!estudiantesAptos.includes(studentId)) {
                            estudiantesAptos.push(studentId);
                        }
                        
                        // Actualizar botón flotante
                        actualizarBotonFlotante();
                        
                        // Reinicializar popovers
                        initializePopovers();
                        
                        Swal.fire({
                            title: '¡Eliminado!',
                            text: 'La aprobación ha sido eliminada correctamente.',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'No se pudo eliminar la aprobación.',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    Swal.fire({
                        title: 'Error',
                        text: 'Hubo un problema con la conexión.',
                        icon: 'error'
                    });
                    console.error('Error al eliminar aprobación:', error);
                }
            });
        }

        // Función para aprobar estudiante individual (sin confirmación)
        function aprobarEstudianteIndividual(studentId) {
            console.log('Iniciando aprobación para estudiante:', studentId);
            console.log('Curso seleccionado:', $('#bootcamp').val());
            
            // Mostrar loader inmediatamente
            Swal.fire({
                title: 'Aprobando estudiante...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: 'components/to_approve/aprobar_estudiante.php',
                type: 'POST',
                data: {
                    studentId: studentId,
                    courseCode: $('#bootcamp').val()
                },
                dataType: 'json',
                beforeSend: function() {
                    console.log('Enviando solicitud de aprobación...');
                },
                success: function(response) {
                    console.log('Respuesta recibida:', response);
                    
                    if (response.success) {
                        // Actualizar el estado del estudiante en la tabla
                        const $row = $('#student-' + studentId);
                        console.log('Fila encontrada:', $row.length);

                        // Cambiar el badge de estado (columna 10)
                        $row.find('td:nth-child(10)').html('<span class="badge text-black" style="background-color: #ffd700; color: #000;"><i class="fa fa-medal"></i> Aprobado</span>');

                        // Cambiar el icono de birrete por icono de eliminación
                        const $actionCell = $row.find('td:nth-child(11)');
                        const studentName = $row.find('td:nth-child(3)').text().trim();
                        $actionCell.html(`
                            <i class="fas fa-trash-alt delete-approval-icon" 
                               style="color: #dc3545; cursor: pointer; font-size: 20px;" 
                               data-student-id="${studentId}" 
                               data-student-name="${studentName}" 
                               data-course-code="${$('#bootcamp').val()}" 
                               title="Eliminar Aprobación">
                            </i>
                        `);

                        // Remover de la lista de aptos
                        estudiantesAptos = estudiantesAptos.filter(id => id !== studentId);

                        // Actualizar botón flotante
                        actualizarBotonFlotante();
                        
                        // Mostrar mensaje de éxito
                        Swal.fire({
                            title: '¡Aprobado!',
                            text: 'El estudiante ha sido aprobado exitosamente',
                            icon: 'success',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.message || 'No se pudo aprobar al estudiante',
                            icon: 'error'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en la solicitud AJAX:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    
                    Swal.fire({
                        title: 'Error de conexión',
                        text: 'No se pudo conectar con el servidor. Verifique su conexión.',
                        icon: 'error'
                    });
                }
            });
        }

        // Función para aprobar todos los estudiantes aptos
        $('#btnAprobarTodos').click(function() {
            if ($(this).prop('disabled') || estudiantesAptos.length === 0) {
                return;
            }

            Swal.fire({
                title: '¿Está seguro?',
                text: `¿Desea aprobar a todos los ${estudiantesAptos.length} estudiantes aptos?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#30336b',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, aprobar todos',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    aprobarTodosLosEstudiantes();
                }
            });
        });

        // Función para aprobar todos los estudiantes
        function aprobarTodosLosEstudiantes() {
            // Mostrar progreso
            let aprobados = 0;
            const total = estudiantesAptos.length;
            const estudiantesACopiar = [...estudiantesAptos]; // Copia para iterar

            Swal.fire({
                title: 'Aprobando estudiantes...',
                text: `Progreso: 0/${total}`,
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                willOpen: () => {
                    Swal.showLoading();
                }
            });

            // Procesar estudiantes uno por uno
            function procesarSiguiente(index) {
                if (index >= estudiantesACopiar.length) {
                    // Terminado
                    Swal.fire(
                        'Completado!',
                        `Se aprobaron ${aprobados} de ${total} estudiantes`,
                        'success'
                    );
                    return;
                }

                const studentId = estudiantesACopiar[index];

                $.ajax({
                    url: 'components/to_approve/aprobar_estudiante.php',
                    type: 'POST',
                    data: {
                        studentId: studentId,
                        courseCode: $('#bootcamp').val()
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            aprobados++;
                            // Actualizar visualmente el estudiante
                            const $row = $('#student-' + studentId);
                            $row.find('td:nth-child(10)').html('<span class="badge text-black" style="background-color: #ffd700; color: #000;"><i class="fa fa-medal"></i> Aprobado</span>');

                            // Cambiar a icono de eliminación
                            const $actionCell = $row.find('td:nth-child(11)');
                            $actionCell.html(`
                                <i class="fas fa-trash-alt delete-approval-icon" 
                                   style="color: #dc3545; cursor: pointer; font-size: 20px;" 
                                   data-student-id="${studentId}" 
                                   data-student-name="${$row.find('td:nth-child(3)').text()}" 
                                   data-course-code="${$('#bootcamp').val()}" 
                                   title="Eliminar Aprobación">
                                </i>
                            `);
                        }

                        // Actualizar progreso
                        Swal.update({
                            text: `Progreso: ${index + 1}/${total}`
                        });

                        // Procesar siguiente
                        setTimeout(() => procesarSiguiente(index + 1), 100);
                    },
                    error: function() {
                        // Continuar con el siguiente aunque falle
                        Swal.update({
                            text: `Progreso: ${index + 1}/${total}`
                        });
                        setTimeout(() => procesarSiguiente(index + 1), 100);
                    }
                });
            }

            // Iniciar procesamiento
            procesarSiguiente(0);

            // Limpiar lista de aptos y actualizar botón
            estudiantesAptos = [];
            actualizarBotonFlotante();
        }

        // Función para cargar los estudiantes
        const cargarEstudiantes = () => {
            const bootcamp = $('#bootcamp').val();

            // Verificar que el bootcamp esté seleccionado
            if (!bootcamp) {
                console.log('Por favor, seleccione un curso');
                return;
            }

            // Mostrar loading y resetear botones
            $('#listaEstudiantes tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-spinner fa-spin fa-2x"></i><br><br>Cargando estudiantes...</td></tr>');
            estudiantesAptos = [];
            actualizarBotonFlotante();
            actualizarBotonExportacion();

            $.ajax({
                url: 'components/to_approve/buscar_aprovados.php',
                type: 'POST',
                data: { bootcamp: bootcamp },
                dataType: 'json',
                success: (response) => {
                    if (response && response.html) {
                        $('#listaEstudiantes tbody').html(response.html);

                        // Actualizar información del curso
                        if (response.courseInfo) {
                            $('#courseMode').val(response.courseInfo.mode || 'No disponible');
                            $('#courseSede').val(response.courseInfo.headquarters || 'No disponible');
                            $('#courseInfoContainer').show();
                        } else {
                            $('#courseInfoContainer').hide();
                        }

                        // Actualizar lista de estudiantes aptos
                        estudiantesAptos = [];
                        $('#listaEstudiantes tbody tr').each(function() {
                            const $row = $(this);
                            const studentId = $row.data('student-id');
                            const estado = $row.find('td:nth-child(10)').text().trim(); // Columna 10 es Estado

                            if (studentId && estado.includes('Apto')) {
                                estudiantesAptos.push(studentId);
                            }
                        });

                        // Actualizar botones
                        actualizarBotonFlotante();
                        actualizarBotonExportacion();

                        // Reinicializar popovers después de cargar el contenido
                        initializePopovers();
                    } else {
                        $('#listaEstudiantes tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-exclamation-triangle fa-2x text-warning"></i><br><br>No se encontraron estudiantes que cumplan los criterios de aprobación</td></tr>');
                        $('#courseInfoContainer').hide();
                        estudiantesAptos = [];
                        actualizarBotonFlotante();
                        actualizarBotonExportacion();
                    }
                },
                error: (xhr, status, error) => {
                    console.error('Error en la solicitud:', error);
                    $('#listaEstudiantes tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-times-circle fa-2x text-danger"></i><br><br>Error al cargar los datos</td></tr>');
                    $('#courseInfoContainer').hide();
                    estudiantesAptos = [];
                    actualizarBotonFlotante();
                    actualizarBotonExportacion();
                }
            });
        };

        // Event listeners simplificados
        $('#bootcamp').change(function() {
            const bootcamp = $(this).val();
            
            if (bootcamp) {
                cargarEstudiantes();
            } else {
                $('#listaEstudiantes tbody').html('<tr><td colspan="11" class="text-center py-5"><i class="fa fa-search fa-2x text-muted"></i><br><br>Seleccione un curso para cargar los estudiantes</td></tr>');
                $('#courseInfoContainer').hide(); // Ocultar información del curso
                estudiantesAptos = [];
                actualizarBotonFlotante();
                actualizarBotonExportacion();
            }
        });
        
        actualizarBotonFlotante(); // Inicializar el estado del botón
        actualizarBotonExportacion(); // Inicializar el estado del botón de exportación
    });
</script>