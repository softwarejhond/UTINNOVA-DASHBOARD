<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol'] ?? ''; // Obtener el extra_rol del usuario

?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel"><i class="bi bi-boxes"></i> SYGNIA - Opciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid sliderbar-scale" style="padding-bottom: 50px;">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend"></legend>
                <div class="row row-cols-3 gy-1 gx-5">
                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Enviar correo masivo">
                                <a href="multipleMail.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-envelope-at-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Enviar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Historial de correos enviados">
                                <a href="mail_history.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-envelope-exclamation-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Historial</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Asesor' || $rol === 'Permanencia'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Listado de pre-matriculados">
                                <a href="course_assignments.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-journal-code icono"></i>
                                            </span>
                                            <span class="checkbox-label">Prematricula</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Asesor'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Matricular campistas con pre-asignación">
                                <a href="moodleAssignments.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-robot icono"></i>
                                            </span>
                                            <span class="checkbox-label">Matricular</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Designar equipo a cada curso en Moodle">
                                <a href="moodle_team.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-chalkboard-user icono"></i>
                                            </span>
                                            <span class="checkbox-label">Designar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Rangos de Fecha de los Bootcamps y cohortes">
                                <a href="bootcamp_period.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-calendar-range icono"></i>
                                            </span>
                                            <span class="checkbox-label">Periodos</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Monitor' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Generar carnets para estudiantes">
                                <a href="credentials.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-person-badge icono"></i>
                                            </span>
                                            <span class="checkbox-label">Carnets</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Generar carnets para docentes, mentores y monitores">
                                <a href="executor_credentials.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-id-card-clip icono"></i>
                                            </span>
                                            <span class="checkbox-label">C. personal</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($extraRol === 'Extra Administrador' || $rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Campistas en condición de culminar y aprobar">
                                <a href="studentsToApprove.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-graduation-cap icono"></i>
                                            </span>
                                            <span class="checkbox-label">Por aprobar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Control maestro'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Administrar sedes disponibles">
                                <a href="headquarters.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-building-fill-gear icono"></i>
                                            </span>
                                            <span class="checkbox-label">Sedes</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Administrar sedes activas para asistencia">
                                <a href="headquartersAttendance.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-building-check icono"></i>
                                            </span>
                                            <span class="checkbox-label">S. asistencia</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>

                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Administrar sedes con pre-registros">
                                <a href="headquartersRegistrations.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-building-fill-gear icono"></i>
                                            </span>
                                            <span class="checkbox-label">S. Registros</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>

                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Administrar horarios para pre-registros">
                                <a href="schedulesPreRegis.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-alarm icono"></i>
                                            </span>
                                            <span class="checkbox-label">H. Registros</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Permanencia' || $rol === 'Monitor'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Registro de seguimiento de asistencias por grupos">
                                <a href="attendance_tracking.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-journals icono"></i>
                                            </span>
                                            <span class="checkbox-label">Seguimiento</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Monitor'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Registro de asistencia a masterclass y generación de QR">
                                <a href="asistenciaNivelacion.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-easel2 icono"></i>
                                            </span>
                                            <span class="checkbox-label">Masterclass</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Control maestro' || $rol === 'Administrador'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Tabla dinamica para calcular proyecciones">
                                <a href="projectionTable.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-table-list icono"></i>
                                            </span>
                                            <span class="checkbox-label" style="font-size: 11px;">Proyecciones</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Monitor'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Cambiar contraseña a campista">
                                <a href="changePassword.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-key icono"></i>
                                            </span>
                                            <span class="checkbox-label" style="font-size: 11px;">Contraseña</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro' || $rol === 'Permanencia' || $rol === 'Académico'): ?>
                        <div class="col">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Gestionar reportes de campista">
                                <a href="gestionarReportes.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-flag-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label" style="font-size: 11px;">Gestionar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if ($rol === 'Control maestro'): ?>
                        <!-- <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Registro de seguimiento de asistencias por grupos">
                            <a href="directMessage.php"> <label class="checkbox-wrapper">
                                    <span class="checkbox-tile">
                                        <span class="checkbox-icon">
                                            <i class="bi bi-whatsapp icono"></i>
                                        </span>
                                        <span class="checkbox-label">Whatsapp</span>
                                    </span>
                                </label>
                            </a>
                        </div> -->
                    <?php endif; ?>
                    <!-- Agrega más elementos según necesites -->
                </div>
            </fieldset>

            <div class="text-center mt-2">
                <small class="text-muted" style="display: flex; align-items: center; justify-content: center; gap: 6px;">
                    Made by
                    <span style="height: 18px; display: inline-block; vertical-align: middle; margin-bottom: 12px;">
                        <img src="img/eagle_indigo.svg" alt="Eagle Software" style="height: 24px; vertical-align: middle;">
                    </span>
                    <a href="https://www.agenciaeaglesoftware.com/" class="eagle-link">Eagle Software</a>
                </small>
            </div>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>

<style>
    /* Espaciado uniforme entre columnas */
    .checkbox-group .row {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }

    .checkbox-group .col {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
        margin-bottom: 1rem;
    }

    /* Tamaño estándar para todos los botones */
    .checkbox-tile {
        width: 100%;
        height: 85px;
        min-height: 85px;
        max-height: 85px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        transition: all 0.2s ease;
        padding: 8px 4px;
        box-sizing: border-box;
    }

    .checkbox-tile:hover {
        background-color: #e8eafcff;
        border-color: #30336b;
    }

    /* Tamaño estándar para iconos */
    .checkbox-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        margin-bottom: 5px;
    }

    .checkbox-icon .icono {
        font-size: 35px;
        line-height: 1;
    }

    /* Tamaño estándar para etiquetas */
    .checkbox-label {
        font-size: 12px;
        font-weight: 500;
        text-align: center;
        line-height: 1.2;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        word-wrap: break-word;
    }

    /* El wrapper ocupa todo el espacio */
    .checkbox-wrapper {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }

    /* Quitar subrayado azul y color de los enlaces */
    .checkbox-group a {
        text-decoration: none !important;
        color: inherit !important;
    }

    .checkbox-wrapper:hover {
        text-decoration: none;
        color: inherit;
    }

    /* Responsive: En pantallas muy pequeñas usar 2 columnas */
    @media (max-width: 576px) {
        .checkbox-group .col {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    .popover {
        border-color: #30336b !important;

    }

    .popover .popover-arrow {
        --bs-popover-arrow-border: #30336b;
    }
</style>

<script>
    // Inicializar todos los popovers de Bootstrap en la página
    document.addEventListener('DOMContentLoaded', function() {
        var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
        popoverTriggerList.forEach(function(popoverTriggerEl) {
            new bootstrap.Popover(popoverTriggerEl, {
                container: 'body'
            });
        });
    });
</script>