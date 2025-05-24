<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel"><i class="bi bi-boxes"></i> SIVP Opciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend">
                </legend>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Enviar correo masivo">
                        <a href="multipleMail.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-envelope-at-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label">Enviar <br> correos</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Historial de correos enviados">
                        <a href="mail_history.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-envelope-exclamation-fill icono"></i>
                                    </span>
                                    <span class="checkbox-label">Historial</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Listado de pre-matriculados">
                        <a href="course_assignments.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-journal-code icono"></i>
                                    </span>
                                    <span class="checkbox-label">Prematricula</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Matricular campistas con pre-asignación">
                        <a href="moodleAssignments.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-robot icono"></i>
                                    </span>
                                    <span class="checkbox-label">Matricular</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Designar equipo a cada curso en Moodle">
                        <a href="moodle_team.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="fa-solid fa-chalkboard-user icono"></i>
                                    </span>
                                    <span class="checkbox-label">Designar</span>
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
                <!-- Agrega más elementos según necesites -->
            </fieldset>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>