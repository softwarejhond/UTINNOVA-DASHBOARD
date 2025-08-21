<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol']; // Obtener el extra_rol del usuario
require_once __DIR__ . '/../components/modals/register_course.php';
?>

<div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><i class="bi bi-boxes"></i> SYGNIA - Aplicaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid sliderbar-scale" style="padding-bottom: 50px;">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend"></legend>
                <!-- Cambié row-cols-3 por col específicos y ajusté el gap -->
                <div class="row gx-2 gy-1">
                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Añadir usuario">
                                <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoAdmin" data-bs-toggle="modal">
                                    <span class="checkbox-tile">
                                        <span class="checkbox-icon">
                                            <i class="bi bi-person-add icono"></i>
                                        </span>
                                        <span class="checkbox-label">Añadir</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro' || $rol === 'Permanencia'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Contacto de registros">
                                <a href="registrarionsContact.php">
                                    <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoReporte" data-bs-toggle="modal">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-people-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Ingresar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Añadir asesor">
                                <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoAsesor" data-bs-toggle="modal">
                                    <span class="checkbox-tile">
                                        <span class="checkbox-icon">
                                            <i class="bi bi-people-fill icono"></i>
                                        </span>
                                        <span class="checkbox-label">Añadir</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Usuarios verificados">
                                <a href="verifiedUsers.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-mortarboard-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Ingresar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Actualizar identificación">
                                <a href="updateDocument.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-person-vcard-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Actualizar ID</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Administrativos inscritos a Bootcamp">
                                <a href="adminRegistrations.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-user-tie icono"></i>
                                            </span>
                                            <span class="checkbox-label">Ingresar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro' || $rol === 'Permanencia' || $rol === 'Triangulo'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Consulta individual">
                                <a href="individualSearch.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-person-bounding-box icono"></i>
                                            </span>
                                            <span class="checkbox-label">Ingresar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Registros">
                                <a href="contactLogs.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="fa-solid fa-address-book icono"></i>
                                            </span>
                                            <span class="checkbox-label">Registros</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Registrar y asignar personal e info">
                                <label class="checkbox-wrapper" data-bs-toggle="modal" data-bs-target="#registerCourseModal">
                                    <span class="checkbox-tile">
                                        <span class="checkbox-icon">
                                            <i class="bi bi-microsoft-teams icono"></i>
                                        </span>
                                        <span class="checkbox-label">Reg. Cursos</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Monitor' || $rol === 'Permanencia'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Emitir certificado de estudio">
                                <a href="generateCertFormat.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-file-earmark-medical-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Certificado</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Interventoría'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Seguimiento de proceso de campistas">
                                <a href="registration_traking.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-person-fill-exclamation icono"></i>
                                            </span>
                                            <span class="checkbox-label">Seguimiento</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Control maestro' || $rol === 'Administrador' || $rol === 'Empleabilidad'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Encuestas de empleabilidad (Ingreso y cierre)">
                                <a href="entryAndClosing.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-list-stars icono"></i>
                                            </span>
                                            <span class="checkbox-label">Encuestas</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>


                    <?php if ($rol === 'Control maestro'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Notas por bootcamp campistas">
                                <a href="studentsNotes.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-journal-bookmark-fill icono"></i>
                                            </span>
                                            <span class="checkbox-label">Notas</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro' || $rol === 'Permanencia' || $rol === 'Triangulo'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Listado de pre-registros">
                                <a href="preRegistrations.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-clipboard-data icono"></i>
                                            </span>
                                            <span class="checkbox-label">Ingresar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($rol === 'Administrador' || $rol === 'Monitor'): ?>
                        <div class="col-4">
                            <div class="checkbox"
                                data-bs-toggle="popover"
                                data-bs-trigger="hover focus"
                                data-bs-placement="bottom"
                                data-bs-content="Incidencias y novedades con campistas">
                                <a href="studentReport.php">
                                    <label class="checkbox-wrapper">
                                        <span class="checkbox-tile">
                                            <span class="checkbox-icon">
                                                <i class="bi bi-flag icono"></i>
                                            </span>
                                            <span class="checkbox-label">Reportar</span>
                                        </span>
                                    </label>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="col-4">
                        <div class="checkbox"
                            data-bs-toggle="popover"
                            data-bs-trigger="hover focus"
                            data-bs-placement="bottom"
                            data-bs-content="Tutoriales de uso de la plataforma">
                            <a href="tutoriales.php">
                                <label class="checkbox-wrapper">
                                    <span class="checkbox-tile">
                                        <span class="checkbox-icon">
                                            <i class="bi bi-youtube icono"></i>
                                        </span>
                                        <span class="checkbox-label">Tutoriales</span>
                                    </span>
                                </label>
                            </a>
                        </div>
                    </div>
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
    /* Asegurar espaciado uniforme entre columnas */
    .checkbox-group .row {
        margin-left: -0.5rem;
        margin-right: -0.5rem;
    }

    .checkbox-group .col-4 {
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

    /* Asegurar que el wrapper del checkbox ocupe todo el espacio */
    .checkbox-wrapper {
        display: block;
        width: 100%;
        height: 100%;
        text-decoration: none;
    }


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
        .checkbox-group .col-4 {
            flex: 0 0 50%;
            max-width: 50%;
        }
    }

    @font-face {
        font-family: 'Sparose';
        src: url('css/fonts/fonnts.com-Sparose.ttf') format('truetype');
        font-weight: normal;
        font-style: normal;
        font-display: swap;
        /* Añade esto para mejor rendimiento */

    }

    .eagle-link {
        font-family: 'Sparose', sans-serif;
        font-size: 1em;
        color: #30336B !important;
        text-decoration: none !important;
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