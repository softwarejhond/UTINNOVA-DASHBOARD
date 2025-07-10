<!-- Barra de progreso global -->
<div class="progress mt-3">
    <div id="progress-bar-global" class="progress-bar progress-bar-striped progress-bar-animated bg-indigo-dark" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="text-center">
    <small id="countdown-timer" class="text-muted">Actualización en tiempo real</small>
</div>

<style>
    .text-muted {
        color: #7987a1 !important;
    }

    .text-primary {
        color: #58b082 !important;
    }

    .text-secondary {
        color: #19456b !important;
    }

    .text-success {
        color: rgb(56 203 137) !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .text-danger {
        color: #fa5c7c !important;
    }

    .text-info {
        color: #02d7ff !important;
    }

    .bg-primary,
    .badge-primary {
        background-color: #58b082 !important;
    }

    .bg-success,
    .label-success {
        background-color: #38cb89 !important;
    }

    .bg-info {
        background-color: rgb(2 215 255) !important;
    }

    .bg-warning,
    .badge-warning {
        background-color: #ffc107 !important;
    }

    .bg-danger,
    .badge-danger {
        background-color: #fa5c7c !important;
    }

    .bg-info,
    .badge-danger {
        background-color: #02d7ff !important;
    }

    .bg-indigo {
        background: #5066e0 !important;
    }

    .bg-purple {
        background: #8a4dd2 !important;
    }

    .bg-secondary {
        background: #19456b !important;
    }

    .text-sm {
        font-size: 0.875rem;
    }

    .text-xs {
        font-size: 0.75rem;
    }

    .text-xl {
        font-size: 1.25rem;
    }

    .text-lg {
        font-size: 1.125rem;
    }

    .text-base {
        font-size: 1rem;
    }

    .text-2xl {
        font-size: 1.875rem;
    }

    .font-medium {
        font-weight: 500;
    }

    .font-semibold {
        font-weight: 600;
    }

    .opacity-50 {
        opacity: 0.6;
    }

    .opacity-25 {
        opacity: 0.25;
    }

    .opacity-75 {
        opacity: 0.75;
    }

    .tabs-container .nav-tabs>li.active {
        border-bottom: 4px solid #58b07e;
        border-left: 0;
    }

    .rounded-sm {
        border-radius: 0.125rem;
    }

    .rounded-md {
        border-radius: 0.375rem;
    }

    .rounded-lg {
        border-radius: 0.5rem;
    }

    .rounded {
        border-radius: 0.25rem;
    }

    .rounded-xl {
        border-radius: 0.75rem;
    }

    .rounded-2xl {
        border-radius: 1rem;
    }

    .rounded-3xl {
        border-radius: 1.5rem;
    }

    .rounded-full {
        border-radius: 50%;
    }

    .rounded-t-sm {
        border-top-left-radius: 0.125rem;
        border-top-right-radius: 0.125rem;
    }

    .rounded-t-md {
        border-top-left-radius: 0.375rem;
        border-top-right-radius: 0.375rem;
    }

    .rounded-t-lg {
        border-top-left-radius: 0.5rem;
        border-top-right-radius: 0.5rem;
    }

    .rounded-t-xl {
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .rounded-b-sm {
        border-bottom-left-radius: 0.125rem;
        border-bottom-right-radius: 0.125rem;
    }

    .rounded-b-md {
        border-bottom-left-radius: 0.375rem;
        border-bottom-right-radius: 0.375rem;
    }

    .rounded-b-lg {
        border-bottom-left-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
    }

    .rounded-b-xl {
        border-bottom-left-radius: 0.75rem;
        border-bottom-right-radius: 0.75rem;
    }

    .media-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        border-radius: 100%;
        background: rgb(255 255 255 / 28%);
    }

    .media-icon i {
        font-size: 0.875rem;
        line-height: 0.9;
    }

    .border {
        border: 1px solid #edeef7 !important;
    }

    .border-right {
        border-right: 1px solid #edeef7 !important;
    }

    .border-left {
        border-left: 1px solid #edeef7 !important;
    }

    .border-top {
        border-top: 1px solid #edeef7 !important;
    }

    .border-bottom {
        border-bottom: 1px solid #edeef7 !important;
    }

    .pulse {
        border-radius: 50%;
        box-shadow: 0 0 0 0 rgb(255 193 7);
        height: 22px;
        width: 22px;
        padding: 2px;
        transform: scale(1);
        animation: pulse 2s infinite;
        color: #fff;
        text-align: center;
    }

    @keyframes pulse {
        0% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(255, 177, 66, 0.7);
        }

        70% {
            transform: scale(1);
            box-shadow: 0 0 0 10px rgba(255, 177, 66, 0);
        }

        100% {
            transform: scale(0.95);
            box-shadow: 0 0 0 0 rgba(255, 177, 66, 0);
        }
    }

    .pulse i {
        color: rgb(255 193 7);
    }

    .avatar {
        position: relative;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        color: rgba(255, 255, 255, 0.85);
        background: #38cb89;
    }

    .avatar-xs,
    .media-icon-xs {
        width: 24px;
        height: 24px;
        font-size: 11px;
    }

    .avatar-sm,
    .media-icon-sm {
        width: 32px;
        height: 32px;
        font-size: 14px;
    }

    .avatar-lg,
    .media-icon-lg {
        width: 64px;
        height: 64px;
        font-size: 28px;
    }

    .avatar-xl,
    .media-icon-xl {
        width: 72px;
        height: 72px;
        font-size: 36px;
    }

    .avatar-2xl,
    .media-icon-2xl {
        width: 82px;
        height: 82px;
        font-size: 42px;
    }

    .h-5 {
        height: 5px !important;
    }

    .card-hover:hover {
        transition-timing-function: cubic-bezier(0.4, 0, 1, 1);
        transition-duration: 0.3s;
        outline: none;
        text-decoration: none;
        box-shadow: 0 5px 15px 5px rgb(236 239 245 / 80%);
        color: #58b07e;
    }

    .rotate-46 {
        transform: rotate(-46deg);
    }

    .-rotate-46 {
        transform: rotate(-46deg);
    }

    .bg-success-transparent {
        background: rgba(56, 203, 137, 0.2);
    }

    .bg-danger-transparent {
        background: rgba(250, 92, 124, 0.2);
    }

    .bg-info-transparent {
        background: rgba(2, 215, 255, 0.2);
    }

    .bg-warning-transparent {
        background: rgba(255, 193, 7, 0.2);
    }

    .btn-outline-success {
        color: #38cb89;
        border-color: #38cb89;
    }

    .btn-success {
        color: #fff;
        background-color: #38cb89;
        border-color: #38cb89;
    }

    .btn-outline-primary {
        color: #58b07e;
        background-color: transparent;
        background-image: none;
        border-color: #58b07e;
    }

    .btn-outline-primary:hover {
        color: #fff;
        background-color: #58b07e;
        border-color: #58b07e;
    }

    .btn-outline-danger {
        color: #fa5c7c !important;
        border-color: #fa5c7c;
    }

    .btn-outline-danger:hover {
        color: #fff !important;
        background-color: #fa5c7c;
        border-color: #fa5c7c;
    }

    .file-upload input[type="file"] {
        display: none;
    }

    .b-solid {
        border: solid 1px #eff0f6;
    }

    .b-solid-2 {
        border: solid 2px #eff0f6;
    }

    .b-solid-3 {
        border: solid 3px #eff0f6;
    }

    .b-solid-4 {
        border: solid 4px #eff0f6;
    }

    .b-solid-5 {
        border: solid 5px #eff0f6;
    }

    .b-dashed {
        border: dashed 1px #eff0f6;
    }

    .b-dashed-2 {
        border: dashed 2px #eff0f6;
    }

    .b-dashed-3 {
        border: dashed 3px #eff0f6;
    }

    .b-dashed-4 {
        border: dashed 4px #eff0f6;
    }

    .b-dashed-5 {
        border: dashed 5px #eff0f6;
    }

    .card {
        background-color: #fff;
        position: relative;
        margin-bottom: 1.5rem;
        border: 0;
        box-shadow: 0 10px 15px 5px rgba(0, 0, 0, 0.02);
        border-radius: 1.25rem;
        overflow: hidden;
    }

    .overflow-visible {
        overflow: visible;
    }

    .relative {
        position: relative;
    }

    .absolute {
        position: absolute;
    }

    .truncate {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .blink {
        animation: blink 1s linear infinite;
        -webkit-animation: blink 1s linear infinite;
        color: #fff;
        font-size: 18px;
    }

    @keyframes blink {
        0% {
            opacity: 0;
        }

        50% {
            opacity: 0.5;
        }

        100% {
            opacity: 1;
        }
    }

    @media (min-width: 576px) {
        .sm--mx-1 {
            margin-left: -0.25rem !important;
            margin-right: -0.25rem !important;
        }

        .sm--mx-2 {
            margin-left: -0.5rem !important;
            margin-right: -0.5rem !important;
        }

        .sm--mx-3 {
            margin-left: -0.75rem !important;
            margin-right: -0.75rem !important;
        }

        .sm--mx-4 {
            margin-left: -1rem !important;
            margin-right: -1rem !important;
        }

        .sm--mx-5 {
            margin-left: -1.25rem !important;
            margin-right: -1.25rem !important;
        }
    }

    .w-10 {
        width: 10px;
    }

    .h-10 {
        height: 10px;
    }

    .z-10 {
        z-index: 10;
    }

    .bottom-0 {
        bottom: 0;
    }

    .right-0 {
        right: 0px;
    }

    .corner {
        position: absolute;
        display: inline-block;
        width: 0;
        height: 0;
        line-height: 0;
        border-bottom: 1.2em solid transparent;
        border-right: 1.2em solid transparent;
        border-style: dashed;
        border-width: 1.2em;
        right: 0;
        bottom: 0px;
    }

    .corner-success {
        border-color: transparent #38cb89 #38cb89 transparent;
    }

    .corner-warning {
        border-color: transparent #ffc107 #ffc107 transparent;
    }

    .corner-danger {
        border-color: transparent #fa5c7c #fa5c7c transparent;
    }

    .corner-info {
        border-color: transparent #02d7ff #02d7ff transparent;
    }

    /*overhide css*/
    .dropdown-toggle::after {
        display: none;
    }

    .card-body {
        padding: 1.625rem;
    }

    .card-header {
        background: transparent;
        border-bottom: 0;
    }

    .container {
        max-width: 1250px;
    }

    .btn-link {
        color: #091c44;
    }

    .progress {
        display: flex;
        height: 1rem;
        /* Ajusta la altura */
        overflow: hidden;
        font-size: 0.75rem;
        background-color: #e9ecef;
        border-radius: 0.25rem;
    }

    .progress-bar {
        display: flex;
        flex-direction: column;
        justify-content: center;
        color: #fff;
        text-align: center;
        white-space: nowrap;
        background-color: #38cb89;
        /* Color de la barra */
        transition: width 0.6s ease;
    }

    .progress-sm {
        height: 0.5rem;
        /* Tamaño pequeño */
    }

    .h-5 {
        height: 5px !important;
    }

    .pagination .page-item.active .page-link {
        background-color: #30336b;
        /* Color índigo */
        border-color: #30336b;
        /* Borde índigo */
        color: #fff;
        /* Texto blanco */
    }

    .pagination .page-link:hover {
        background-color: #ec008c;
        /* Color índigo más oscuro al pasar el mouse */
        color: #fff;
    }
</style>
<style>
    /* Agregar escala del 80% al contenedor principal */
    .scale-container {
        transform: scale(0.9);
        transform-origin: top left;
        width: 111.11%; /* Compensar la reducción de escala para ocupar el espacio completo */
        height: 111.11%;
    }
    
    /* Mantener el contenedor principal responsivo */
    @media (max-width: 768px) {
        .scale-container {
            transform: scale(0.7); /* Escala más pequeña en móviles */
            width: 142.85%;
        }
    }
    
    @media (max-width: 480px) {
        .scale-container {
            transform: scale(0.6); /* Escala aún más pequeña en pantallas muy pequeñas */
            width: 166.67%;
        }
    }
</style>

<div class="scale-container">
    <!-- Página 1 -->
    <?php include_once 'page1.php'; ?>
    <!-- Página 2 -->
    <?php include_once 'page2.php'; ?>
    <!-- Paginador -->
    <div id="pagination-container" class="d-flex justify-content-center mb-4">
        <nav>
            <ul class="pagination pagination-lg">
                <li class="page-item disabled" id="prev-page">
                    <a class="page-link" href="#" aria-label="Anterior">
                        <span aria-hidden="true">&laquo;</span>
                    </a>
                </li>
                <li class="page-item active" id="page-btn-1">
                    <a class="page-link" href="#">1</a>
                </li>
                <li class="page-item" id="page-btn-2">
                    <a class="page-link" href="#">2</a>
                </li>
                <li class="page-item" id="next-page">
                    <a class="page-link " href="#" aria-label="Siguiente">
                        <span aria-hidden="true">&raquo;</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>
</div>
<script>
    function actualizarBarraProgreso() {
        var progreso = 0;
        var intervalo = setInterval(function() {
            progreso += 20; // Incremento para completar 100% en 5 segundos
            $('#progress-bar-global').css('width', progreso + '%').attr('aria-valuenow', progreso);
            if (progreso >= 100) {
                clearInterval(intervalo);
                $('#progress-bar-global').css('width', '0%').attr('aria-valuenow', 0); // Reiniciar la barra de progreso
            }
        }, 1000); // Actualizar cada 1 segundo
    }
</script>
<!-- Asegúrate de incluir jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        function actualizarContadores() {
            $.ajax({
                url: 'components/cardContadores/actualizarContadores.php',
                method: 'GET',
                success: function(data) {
                    console.log('Datos recibidos:', data); // Agregar esta línea para depuración
                    $('#usuers_registrados').text(data.total_registrados);
                    $('#total_usuarios').text(data.total_usuarios);
                    // Actualizar el porcentaje de usuarios aceptados
                    $('#porc_usuarios').text(data.porc_usuarios);
                    const porcentajeUsuarios = data.porc_usuarios || 0; // Asegurarse de que no sea null o undefined
                    $('#progress-bar-usuarios')
                        .css('width', porcentajeUsuarios + '%')
                        .attr('aria-valuenow', porcentajeUsuarios);
                    $('#total_boyaca').text(data.total_boyaca);
                    $('#porc_boyaca').text(data.porc_boyaca + '%');
                    // Actualizar la barra de progreso de Boyacá
                    const porcentajeBoyaca = data.porc_boyaca || 0; // Asegurarse de que no sea null o undefined
                    $('#progress-bar-boyaca')
                        .css('width', porcentajeBoyaca + '%')
                        .attr('aria-valuenow', porcentajeBoyaca);
                    $('#total_cundinamarca').text(data.total_cundinamarca);
                    $('#porc_cundinamarca').text(data.porc_cundinamarca + '%');
                    // Actualizar la barra de progreso de Cundinamarca
                    const porcentajeCundinamarca = data.porc_cundinamarca || 0; // Asegurarse de que no sea null o undefined
                    $('#progress-bar-cundinamarca')
                        .css('width', porcentajeCundinamarca + '%')
                        .attr('aria-valuenow', porcentajeCundinamarca);
                    $('#total_sinVerificar').text(data.total_sinVerificar);
                    $('#porc_sinVerificar').text(data.porc_sinVerificar + '%');
                    // Actualizar la barra de progreso de "sin verificar"
                    const porcentajeSinVerificar = data.porc_sinVerificar || 0; // Asegurarse de que no sea null o undefined
                    $('#progress-bar-sin-verificar')
                        .css('width', porcentajeSinVerificar + '%')
                        .attr('aria-valuenow', porcentajeSinVerificar);
                    $('#total_GobernacionBoyaca').text(data.total_GobernacionBoyaca);
                    $('#porc_GobernacionBoyaca').text(data.porc_GobernacionBoyaca + '%');
                    $('#total_contacto_si').text(data.total_contacto_si);
                    $('#porc_contacto_si').text(data.porc_contacto_si + '%');
                    $('#total_contacto_no').text(data.total_contacto_no);
                    $('#porc_contacto_no').text(data.porc_contacto_no + '%');
                    $('#total_contacto_si_admin').text(data.total_contacto_si_admin);
                    $('#porc_contacto_si_admin').text(data.porc_contacto_si_admin + '%');
                    $('#total_contacto_no_admin').text(data.total_contacto_no_admin);
                    $('#porc_contacto_no_admin').text(data.porc_contacto_no_admin + '%');
                    $('#total_matriculados').text(data.total_matriculados); // Actualizar total matriculados
                    $('#porc_matriculados').text(data.porc_matriculados + '%'); // Actualizar porcentaje matriculados
                    // Actualizar la barra de progreso de matriculados
                    const porcentajeMatriculados = data.porc_matriculados || 0; // Asegurarse de que no sea null o undefined
                    $('#progress-bar-matriculados')
                        .css('width', porcentajeMatriculados + '%')
                        .attr('aria-valuenow', porcentajeMatriculados);

                    // Ya no necesitamos actualizar estos campos porque ahora están en el gráfico
                    // $('#total_matriculados_card').text(data.total_matriculados || 0);
                    // $('#total_formados_card').text(data.total_formados || 0);
                    // $('#total_certificados_card').text(data.total_certificados || 0);

                    $('#total_radio').text(data.total_radio);
                    $('#total_redes_sociales').text(data.total_redes_sociales);
                    $('#total_rechazados').text(data.total_rechazados); // Agregar esta línea
                    $('#porc_rechazados').text(data.porc_rechazados); // Agregar esta línea
                    // Actualizar la barra de progreso de rechazados
                    const porcentajeRechazados = data.porc_rechazados || 0; // Asegurarse de que no sea null o undefined
                    $('#progress-bar-rechazados')
                        .css('width', porcentajeRechazados + '%')
                        .attr('aria-valuenow', porcentajeRechazados);
                    $('#porc_matriculados').text(data.porc_matriculados); // Agregar esta línea
                    $('#total_activos').text(data.total_activos); // Agregar esta línea
                    $('#total_inactivos').text(data.total_inactivos); // Agregar esta línea
                    $('#total_nuevos').text(data.total_nuevos); // Agregar esta línea
                    $('#total_premium').text(data.total_premium); // Agregar esta línea
                    $('#total_basicos').text(data.total_basicos); // Agregar esta línea
                    $('#total_suspendidos').text(data.total_suspendidos); // Agregar esta línea
                    $('#total_eliminados').text(data.total_eliminados); // Agregar esta línea
                    $('#total_invitados').text(data.total_invitados); // Agregar esta línea
                    $('#total_administradores').text(data.total_administradores); // Agregar esta línea
                    $('#total_vip').text(data.total_vip); // Agregar esta línea

                    $('#total_lote1').text(data.total_lote1);
                    $('#porc_lote1').text(data.porc_lote1 + '%');
                    // Actualizar la barra de progreso del Lote 1
                    const porcentajeLote1 = data.porc_lote1 || 0;
                    $('#progress-bar-lote1')
                        .css('width', porcentajeLote1 + '%')
                        .attr('aria-valuenow', porcentajeLote1);

                    $('#total_lote2').text(data.total_lote2);
                    $('#porc_lote2').text(data.porc_lote2 + '%');
                    // Actualizar la barra de progreso del Lote 2
                    const porcentajeLote2 = data.porc_lote2 || 0;
                    $('#progress-bar-lote2')
                        .css('width', porcentajeLote2 + '%')
                        .attr('aria-valuenow', porcentajeLote2);
                    // Actualizar select de instituciones
                    var select = $('#institucionSelect');
                    select.empty();
                    select.append('<option value="">Seleccione una institución</option>');

                    data.instituciones.forEach(function(inst) {
                        select.append(`<option value="${inst.total}">${inst.nombre}</option>`);
                    });
                },
                error: function(error) {
                    console.error('Error al obtener los datos:', error);
                }
            });
        }

        function actualizarBarraProgreso() {
            var progreso = 0;
            var intervalo = setInterval(function() {
                progreso += 20; // Incremento para completar 100% en 5 segundos
                $('#progress-bar-global').css('width', progreso + '%').attr('aria-valuenow', progreso);
                if (progreso >= 100) {
                    clearInterval(intervalo);
                    $('#progress-bar-global').css('width', '0%').attr('aria-valuenow', 0); // Reiniciar la barra de progreso
                }
            }, 1000); // Actualizar cada 1 segundo
        }

        function actualizarHoraActual() {
            var now = new Date();
            var horas = now.getHours();
            var minutos = now.getMinutes().toString().padStart(2, '0');
            var segundos = now.getSeconds().toString().padStart(2, '0');
            var ampm = horas >= 12 ? 'PM' : 'AM';
            horas = horas % 12;
            horas = horas ? horas : 12; // La hora '0' debe ser '12'
            var horaActual = '<i class="bi bi-hourglass-split"></i> Actualiza en tiempo real: ' + horas + ':' + minutos + ':' + segundos + ' ' + ampm;
            $('#current-time').html(horaActual);
        }

        // Ejecutar la función cada 5 segundos para actualizar en tiempo real
        function iniciarActualizacion() {
            actualizarContadores();
            actualizarBarraProgreso();
        }

        iniciarActualizacion();
        setInterval(iniciarActualizacion, 10000);
        setInterval(actualizarHoraActual, 1000); // Actualizar la hora cada segundo

        // Agregar el evento change para el select
        $('#institucionSelect').change(function() {
            var total = $(this).val();
            var nombre = $(this).find('option:selected').text();
            $('#total_institucion').text(total);
            $('#nombre_institucion').text(nombre !== 'Seleccione una institución' ? nombre : '');
        });
    });
</script>
<script>
    $(document).ready(function() {
        $('#date-select').on('change', function() {
            const selectedDate = $(this).val();

            if (selectedDate) {
                $.ajax({
                    url: 'components/cardContadores/actualizarContadores.php',
                    method: 'GET',
                    data: {
                        date: selectedDate
                    },
                    success: function(data) {
                        $('#usuers_registrados').text(data.total_registrados_por_fecha);
                    },
                    error: function(error) {
                        console.error('Error al obtener los datos:', error);
                    }
                });
            } else {
                $('#usuers_registrados').text(0);
            }
        });
    });
</script>
<script>
    $(document).ready(function() {
        let currentPage = 1;
        const totalPages = 2; // Cambia este valor según la cantidad de páginas que tengas

        function mostrarPagina(page) {
            // Ocultar todas las páginas
            $('.page').each(function() {
                $(this).hide(); // Asegúrate de ocultar todas las páginas
            });

            // Mostrar la página seleccionada
            $(`#page-${page}`).show();

            // Actualizar el estado del paginador
            $('.pagination .page-item').removeClass('active disabled');
            $(`#page-btn-${page}`).addClass('active');

            // Deshabilitar botones "Anterior" y "Siguiente" si es necesario
            $('#prev-page').toggleClass('disabled', page === 1);
            $('#next-page').toggleClass('disabled', page === totalPages);
        }

        // Manejar clics en el paginador
        $('.pagination .page-item').on('click', function(e) {
            e.preventDefault();

            if ($(this).hasClass('disabled')) return;

            if ($(this).attr('id') === 'prev-page') {
                currentPage = Math.max(1, currentPage - 1);
            } else if ($(this).attr('id') === 'next-page') {
                currentPage = Math.min(totalPages, currentPage + 1);
            } else {
                currentPage = parseInt($(this).attr('id').replace('page-btn-', ''));
            }

            mostrarPagina(currentPage);
        });

        // Mostrar la primera página al cargar
        mostrarPagina(currentPage);
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>