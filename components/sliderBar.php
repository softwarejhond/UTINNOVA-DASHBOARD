<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
require_once __DIR__ . '/../components/modals/register_course.php';


?>
<div class="offcanvas offcanvas-start" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions" aria-labelledby="offcanvasWithBothOptionsLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasWithBothOptionsLabel"><i class="bi bi-boxes"></i> SIGP Aplicaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>

    </div>
    <div class="offcanvas-body">
        <div class="container-fluid">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend">

                </legend>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Añadir usuario">
                        <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoAdmin" data-bs-toggle="modal">

                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-person-add icono"></i>
                                </span>
                                <span class="checkbox-label">Añadir</span>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Contacto de registros">
                        <a href="registrarionsContact.php">
                            <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoReporte" data-bs-toggle="modal">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-people-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label ">Ingresar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Añadir asesor">
                        <label class="checkbox-wrapper" data-bs-target="#exampleModalNuevoAsesor" data-bs-toggle="modal">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-people-fill icono"></i>
                                </span>
                                <span class="checkbox-label">Añadir</span>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Usuarios verificados">
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
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Actualizar identificación">
                        <a href="updateDocument.php">
                            <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-person-vcard-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label ">Actualizar ID</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Asesor' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Consulta individual">
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
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Perfil">
                        <a href="contactLogs.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="fa-solid fa-address-book icono"></i>
                                    </span>
                                    <span class="checkbox-label">Registros</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Asignar a curso">
                        <label class="checkbox-wrapper" data-bs-toggle="modal" data-bs-target="#registerCourseModal">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-microsoft-teams icono"></i>
                                </span>
                                <span class="checkbox-label">Registrar Curso</span>
                            </span>
                        </label>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Emitir certificado de estudio">
                        <a href="generateCertFormat.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-file-earmark-medical-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label">Certificado</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Seguimiento de proceso de campistas">
                        <a href="registration_traking.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-person-fill-exclamation icono"></i>
                                    </span>
                                    <span class="checkbox-label">Seguimiento</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Control maestro' || $rol === 'Administrador' || $rol === 'Académico'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Administrar sedes disponibles">
                        <a href="headquarters.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-building-fill-gear icono"></i>
                                    </span>
                                    <span class="checkbox-label">Sedes</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Visítanos">
                    <a href="https://agenciaeaglesoftware.com/" target="_blank">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <img src="img/icons/eagle.png" alt="LogoEagle" width="60px">
                                </span>
                            </span>
                        </label>
                    </a>

                </div>
            </fieldset>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>