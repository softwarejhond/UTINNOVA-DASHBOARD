<?php

/**
 * ============================================
 * Barra superior y navegación principal (header.php)
 * ============================================
 * Este componente muestra la barra superior fija del dashboard.
 * Incluye el logo, menú principal, accesos rápidos, perfil del usuario y botones flotantes.
 * Las opciones del menú y los accesos dependen del rol del usuario logueado.
 * 
 * - Los roles controlan el acceso a cada funcionalidad (Administrador, Control maestro, Empleabilidad, Permanencia, Académico, etc).
 * - Se integra con los componentes de barra lateral y correo flotante.
 * - Incluye menús desplegables para informes, PQRS, periodos, aulas y perfil.
 * - Permite la descarga de informes con control de tiempo y feedback visual.
 * - El diseño es responsivo y utiliza Bootstrap.
 */

$rol = $infoUsuario['rol']; // Obtener el rol del usuario
require_once __DIR__ . '/../components/modals/cohortes.php';
?>
<?php include("components/sliderBarRight.php"); ?> <!-- Barra lateral derecha de opciones -->
<?php include 'components/multipleEmail/float_email.php'; ?> <!-- Botón flotante de correo -->

<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-tertiary mr-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptionsLabel">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand" href="#"><img src="img/uttInnova.png" alt="logo" width="120px"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="main.php">Inicio</a>
                </li>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cohortModal">
                            Cohortes
                        </a>
                    </li> -->
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Empleabilidad' || $rol === 'Control maestro'): ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="encuestas.php">Empleabilidad</a>
                    </li> -->

                    <li class="nav-item">
                        <a class="nav-link" href="codigosQR.php">Generar QR</a>
                    </li>

                    <!-- <li class="nav-item">
                        <a class="nav-link" href="asistencias.php">Asistencia Talleres</a>
                    </li> -->
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <!-- Sistema PQRS -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPQRS" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            PQRS
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownPQRS">
                            <li><a class="dropdown-item" href="seguimiento_pqr.php">Seguimiento PQRS</a></li>

                        </ul>
                    </li>
                <?php endif; ?>

                <?php if ($rol === 'Control maestro'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="#" onclick="abrirSwalDocumentos(); return false;">Cambiar base</a>
                    </li>
                <?php endif; ?>

                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPQRS" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Informes
                    </a>
                    <ul class="dropdown-menu informes-scroll" aria-labelledby="navbarDropdownPQRS">
                        <a class="dropdown-item" href="#" onclick="descargarInforme('components/registrationsContact/export_to_excel.php?action=export', 'inscritos')">
                            Inscritos - general
                        </a>
                        <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                            <li><a class="dropdown-item" href="proyecciones.php"><b>Proyecciones</b></a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll.php?action=export', 'semanal_lote1')">Informe semanal Lote 1</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll_lote2.php?action=export', 'semanal_lote2')">Informe semanal Lote 2</a></li>
                            <!-- <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll_post_certificate.php?action=export', 'semanal_certificadosLote1')">Informe semanal contrapartida L1</a></li>
                                <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll_post_certificate_lote2.php?action=export', 'semanal_certificadosLote2')">Informe semanal contrapartida L2</a></li> -->
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll_non_registered.php?action=export', 'certificadosLote1')">Informe contrapartida L1</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll_non_registered_l2.php?action=export', 'certificadosLote2')">Informe contrapartida L2</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/semanal_todos.php?action=export', 'mensual')">Informe mensual (TODOS)</a></li>
                        <?php endif; ?>

                        <?php if ($rol === 'Control maestro'): ?>
                            <!-- NUEVAS EXPORTACIONES AGREGADAS -->
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/to_approve/export_excel_general_all.php', 'notas_general')">Informe de notas general</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E20.php?action=export', 'E20_lote1')">Informe E20 L1</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E20_L2.php?action=export', 'E20_lote2')">Informe E20 L2</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E_21.php?action=export', 'E21_lote1')">Informe E21 L1</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E_21_L2.php?action=export', 'E21_lote2')">Informe E21 L2</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E_19_VF.php?action=export', 'E19_VF_lote1')">Informe E19 VF L1</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E_19_VF_L2.php?action=export', 'E19_VF_lote2')">Informe E19 VF L2</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E_19_VF_contra.php?action=export', 'E19_VF_contra_lote1')">Informe E19 VF Contrapartida L1</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/export_E_19_VF_contra_l2.php?action=export', 'E19_VF_contra_lote2')">Informe E19 VF Contrapartida L2</a></li>
                        <?php endif; ?>

                        <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportHours.php?action=export', 'asistencia')">Informe de asistencia</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportHoursEL.php?action=export', 'asistencia')">Informe de asistencia LE</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAbsence.php?action=export', 'ausencias')">Registros de ausencia</a></li>
                        <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/to_approve/export_excel_general_all.php', 'notas_general')">Informe de notas general</a></li>

                    </ul>
                </li>

            </ul>


            <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                <?php include 'components/studentsReports/reportsButton.php'; ?>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Control maestro' || $rol === 'Permanencia' || $rol === 'Académico'): ?>
                <?php include 'components/pqr/pqrButton.php'; ?>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Control maestro' || $rol === 'Permanencia' || $rol === 'Académico'): ?>
                <?php include 'components/bootcampPeriods/periods_button.php'; ?>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Control maestro' || $rol === 'Académico'): ?>
                <?php include 'components/classrooms/classroom_button.php'; ?>
            <?php endif; ?>
            <!-- <button class="btn btn-warning position-relative me-4" type="button" id="previousStudentsButton" data-bs-title="Estudiantes certificados">
                    <i class="fa-solid fa-user-graduate fa-shake"></i>
                    <span id="totalCertificados" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-light text-dark">
                       <b> <?php // echo isset($totalConCertificacion) ? $totalConCertificacion : 0; 
                            ?></b>
                    </span>
                    <div id="spinnerCertificados" class="spinner-border spinner-border-sm text-light d-none" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </button> -->

            <!-- <script>
                // Función para obtener los datos del servidor
                function fetchCertificados() {
                    const spinner = document.getElementById('spinnerCertificados');
                    const totalCertificados = document.getElementById('totalCertificados');

                    // Mostrar el spinner
                    spinner.classList.remove('d-none');

                    fetch('components/registrationsContact/previous_students_button.php')
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success' && data.data) {
                                // Actualizar el contenido del contador
                                totalCertificados.textContent = data.data.total_certificados;
                            } else {
                                console.error('Error en la respuesta del servidor:', data);
                                totalCertificados.textContent = 'Error';
                            }
                        })
                        .catch(error => {
                            console.error('Error al realizar la solicitud:', error);
                            totalCertificados.textContent = 'Error';
                        })
                        .finally(() => {
                            // Ocultar el spinner
                            spinner.classList.add('d-none');
                        });
                }

                // Cargar los datos al cargar la página
                document.addEventListener('DOMContentLoaded', fetchCertificados);

                // Actualizar el contador cada 30 segundos
                setInterval(fetchCertificados, 30000);
            </script> -->

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($infoUsuario['foto']); ?>" alt="Perfil" class="rounded-circle" width="40" height="40">
                    <?php echo htmlspecialchars($infoUsuario['nombre']); ?>
                    <div class="spinner-grow spinner-grow-sm" role="status" style="color:#00976a">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <button type="button" class="btn" data-bs-toggle="popover" data-bs-placement="top" data-bs-content="<?php echo htmlspecialchars($infoUsuario['rol']); ?>" data-bs-trigger="hover">
                        <i class="bi bi-info-circle-fill colorVerde" style="color: #00976a;"></i>
                    </button>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="profile.php">Perfil</a></li>
                    <li><a class="dropdown-item" href="close.php">Cerrar sesión</a></li>
                </ul>
            </div>


        </div>

        <button type="button" class="btn bg-teal-dark text-white" id="header-email-button"
            data-bs-toggle="tooltip" data-bs-placement="bottom"
            data-bs-title="Redactar Correo">
            <i class="bi bi-envelope-at-fill"></i>
        </button>

        <button class="btn btn-tertiary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
            <i class="bi bi-list"></i>
        </button>
    </div>
</nav>

<!-- Start of HubSpot Embed Code -->
<script type="text/javascript" id="hs-script-loader" async defer src="//js-na2.hs-scripts.com/243394779.js"></script>
<!-- End of HubSpot Embed Code -->


<!-- Botón para abrir el modal -->
<!-- <button type="button" class="btn btn-success floating-button" style="bottom: 80px;" data-bs-toggle="modal" data-bs-target="#hubspotModal">
    <i class="fa-solid fa-ticket"></i>
</button> -->

<!-- Modal -->
<!-- <div class="modal fade" id="hubspotModal" tabindex="-1" aria-labelledby="hubspotModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content bg-white text-black">
            <div class="modal-header">
                <h5 class="modal-title" id="hubspotModalLabel">Formulario de Contacto</h5>
                <button type="button" class="btn-close btn-close-black" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="hs-form-frame" data-region="na2" data-form-id="f46e8418-26c6-463a-8b28-a3c236549d3a" data-portal-id="243394779"></div>
            </div>
        </div>
    </div>
</div> -->

<script>
    function descargarInforme(url, tipo) {
        let timerInterval;
        let timeLeft = 300; // 5 minutos en segundos

        // Mostrar SweetAlert con contador regresivo
        Swal.fire({
            title: 'Generando informe...',
            html: `<div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3">Preparando la descarga del informe de <strong>${tipo}</strong></p>
                <div class="alert alert-warning mt-3" style="background-color: #fff3cd; border-color: #ffeaa7;">
                    <i class="bi bi-clock-history"></i>
                    <strong>Tiempo límite por alto volumen de datos:</strong><br>
                    <span id="countdown" style="font-size: 1.4em; font-weight: bold; color: #856404;">05:00</span>
                </div>
                <div class="progress mt-3" style="height: 8px;">
                    <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                         role="progressbar" style="width: 0%" id="timeProgress"></div>
                </div>
                <small class="text-muted mt-2 d-block">El contador se cerrará automáticamente al completarse la descarga</small>
               </div>`,
            allowOutsideClick: false,
            showConfirmButton: false,
            showCancelButton: true,
            cancelButtonText: 'Cancelar',
            cancelButtonColor: '#dc3545',
            customClass: {
                popup: 'swal-wide'
            },
            didOpen: () => {
                // Función para actualizar el contador
                function updateCountdown() {
                    const minutes = Math.floor(timeLeft / 60);
                    const seconds = timeLeft % 60;
                    const formattedTime = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

                    const countdownElement = document.getElementById('countdown');
                    const progressElement = document.getElementById('timeProgress');

                    if (countdownElement) {
                        countdownElement.textContent = formattedTime;

                        // Cambiar color según el tiempo restante
                        if (timeLeft <= 60) {
                            countdownElement.style.color = '#dc3545'; // Rojo - crítico
                            countdownElement.classList.add('text-danger');
                        } else if (timeLeft <= 120) {
                            countdownElement.style.color = '#ffc107'; // Amarillo - advertencia
                        } else {
                            countdownElement.style.color = '#28a745'; // Verde - normal
                        }
                    }

                    if (progressElement) {
                        const progressPercent = ((300 - timeLeft) / 300) * 100;
                        progressElement.style.width = progressPercent + '%';

                        // Cambiar color de la barra según el progreso
                        if (progressPercent > 80) {
                            progressElement.className = 'progress-bar progress-bar-striped progress-bar-animated bg-danger';
                        } else if (progressPercent > 60) {
                            progressElement.className = 'progress-bar progress-bar-striped progress-bar-animated bg-warning';
                        }
                    }

                    timeLeft--;

                    // Si se agota el tiempo
                    if (timeLeft < 0) {
                        clearInterval(timerInterval);
                        Swal.fire({
                            icon: 'error',
                            title: 'Tiempo agotado',
                            html: `<div class="text-center">
                                <i class="bi bi-hourglass-bottom text-danger" style="font-size: 3em;"></i>
                                <p class="mt-3">El proceso ha tardado más de 5 minutos.</p>
                                <p class="text-muted">Esto puede deberse a la gran cantidad de datos a procesar.</p>
                                <strong>Por favor, intente nuevamente en unos minutos.</strong>
                            </div>`,
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'Entendido'
                        });
                        return;
                    }
                }

                // Iniciar el contador
                updateCountdown();
                timerInterval = setInterval(updateCountdown, 1000);

                // Configurar timeout para el fetch (5 minutos + 10 segundos de margen)
                const controller = new AbortController();
                const timeoutId = setTimeout(() => {
                    controller.abort();
                    clearInterval(timerInterval);
                }, 310000);

                // Iniciar la descarga
                fetch(url, {
                        signal: controller.signal,
                        headers: {
                            'Cache-Control': 'no-cache',
                            'Pragma': 'no-cache'
                        }
                    })
                    .then(response => {
                        clearTimeout(timeoutId);
                        clearInterval(timerInterval);

                        if (!response.ok) {
                            throw new Error(`Error ${response.status}: ${response.statusText}`);
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // Calcular tiempo transcurrido
                        const elapsedTime = 300 - timeLeft;
                        const elapsedMinutes = Math.floor(elapsedTime / 60);
                        const elapsedSeconds = elapsedTime % 60;
                        const elapsedFormatted = `${elapsedMinutes}:${elapsedSeconds.toString().padStart(2, '0')}`;

                        // Crear URL del blob
                        const blobUrl = window.URL.createObjectURL(blob);
                        // Crear enlace temporal
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = `informe_${tipo}_${new Date().toISOString().split('T')[0]}.xlsx`;
                        // Simular clic
                        document.body.appendChild(a);
                        a.click();
                        // Limpiar
                        window.URL.revokeObjectURL(blobUrl);
                        document.body.removeChild(a);

                        // Mostrar mensaje de éxito con tiempo transcurrido
                        Swal.fire({
                            icon: 'success',
                            title: '¡Descarga completada!',
                            html: `<div class="text-center">
                            <i class="bi bi-download text-success" style="font-size: 3em;"></i>
                            <p class="mt-3">El informe de <strong>${tipo}</strong> se ha descargado correctamente</p>
                            <div class="alert alert-success mt-3">
                                <i class="bi bi-stopwatch"></i>
                                <strong>Tiempo de procesamiento:</strong> ${elapsedFormatted}
                            </div>
                            <small class="text-muted">El archivo se ha guardado en su carpeta de descargas</small>
                        </div>`,
                            showConfirmButton: true,
                            confirmButtonColor: '#30336b',
                            confirmButtonText: 'Perfecto'
                        });
                    })
                    .catch(error => {
                        clearTimeout(timeoutId);
                        clearInterval(timerInterval);
                        console.error('Error:', error);

                        let errorMessage = 'Hubo un problema al generar el informe';
                        let errorIcon = 'error';

                        if (error.name === 'AbortError') {
                            errorMessage = 'El proceso fue cancelado por exceder el tiempo límite de 5 minutos';
                            errorIcon = 'warning';
                        }

                        Swal.fire({
                            icon: errorIcon,
                            title: 'Error en la generación',
                            html: `<div class="text-center">
                            <p>${errorMessage}</p>
                            <div class="alert alert-info mt-3">
                                <i class="bi bi-lightbulb"></i>
                                <strong>Sugerencia:</strong> Intente generar el informe en horarios de menor actividad
                            </div>
                        </div>`,
                            confirmButtonColor: '#dc3545',
                            confirmButtonText: 'Entendido'
                        });
                    });
            },
            willClose: () => {
                // Limpiar el intervalo si se cierra el modal
                if (timerInterval) {
                    clearInterval(timerInterval);
                }
            }
        }).then((result) => {
            // Si el usuario cancela
            if (result.dismiss === Swal.DismissReason.cancel) {
                clearInterval(timerInterval);
                Swal.fire({
                    icon: 'info',
                    title: 'Proceso cancelado',
                    text: 'La generación del informe ha sido cancelada por el usuario',
                    confirmButtonColor: '#6c757d',
                    confirmButtonText: 'Entendido'
                });
            }
        });
    }

    function abrirSwalDocumentos() {
        let baseValue = 1; // Valor inicial: Base Adicionales

        Swal.fire({
            title: 'Cambiar Base dirigida',
            html: `
        <div style="margin-bottom:15px;">
            <label for="baseSelector" style="font-weight:bold;">Tipo de base:</label>
            <select id="baseSelector" class="form-select" style="width:200px;display:inline-block;">
                <option value="1" selected>Base Adicionales</option>
                <option value="0">Base Normal</option>
            </select>
        </div>
        <div style="display: flex; gap: 20px; justify-content: center;">
            <div style="flex:1; display:flex; flex-direction:column;">
                <label for="docPaste" style="font-weight:bold;">Documentos</label>
                <textarea id="docPaste" rows="10" style="width:100%; resize:vertical; min-width:180px; max-height:200px; overflow:auto;" placeholder="Pega aquí los números de documento"></textarea>
            </div>
            <div style="flex:1; display:flex; flex-direction:column;">
                <label for="docResult" style="font-weight:bold;">Resultado</label>
                <textarea id="docResult" rows="10" style="width:100%; resize:vertical; min-width:180px; max-height:200px; overflow:auto;" disabled></textarea>
            </div>
        </div>
        `,
            showCancelButton: true,
            showConfirmButton: true,
            confirmButtonText: 'Aplicar',
            confirmButtonColor: '#006d68',
            cancelButtonText: 'Limpiar',
            cancelButtonColor: '#ec008c',
            width: 700,
            footer: `<button type="button" id="swalCloseBtn" class="swal2-confirm swal2-styled" style="background:#6c757d;margin-top:10px;">Cerrar</button>`,
            didOpen: () => {
                const pasteArea = document.getElementById('docPaste');
                const resultArea = document.getElementById('docResult');
                const baseSelector = document.getElementById('baseSelector');
                const confirmBtn = document.querySelector('.swal2-confirm:not(#swalCloseBtn)');

                baseSelector.addEventListener('change', function() {
                    baseValue = baseSelector.value;
                    updateButtonColor();
                });

                pasteArea.addEventListener('input', function() {
                    const lines = pasteArea.value.split('\n')
                        .map(l => l.trim())
                        .filter(l => l.length > 0);
                    resultArea.value = lines.map(l => l + ',').join('\n');
                });

                document.getElementById('swalCloseBtn').onclick = function() {
                    Swal.close();
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const pasteArea = document.getElementById('docPaste');
                const baseSelector = document.getElementById('baseSelector');
                const documentos = pasteArea.value.split('\n')
                    .map(l => l.trim())
                    .filter(l => l.length > 0);
                const baseValue = baseSelector.value;

                if (documentos.length === 0) {
                    Swal.fire('Error', 'Debes ingresar al menos un documento.', 'error');
                    return;
                }

                fetch('controller/update_directed_base.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            documentos,
                            baseValue
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('¡Listo!', 'Se actualizó la base dirigida correctamente.', 'success');
                        } else {
                            Swal.fire('Error', data.message || 'No se pudo actualizar.', 'error');
                        }
                    })
                    .catch(() => {
                        Swal.fire('Error', 'Hubo un problema con la petición.', 'error');
                    });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                abrirSwalDocumentos(); // Reinicia el modal al limpiar
            }
        });
    }
</script>

<style>
    .swal-wide {
        width: 600px !important;
    }

    #countdown {
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
        transition: color 0.3s ease;
    }

    .progress {
        background-color: #e9ecef;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 1s ease;
    }

    .informes-scroll {
        max-height: 300px;
        /* Aproximadamente 6 elementos, ajusta si lo necesitas */
        overflow-y: auto;
    }
</style>