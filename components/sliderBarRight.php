<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
$extraRol = $infoUsuario['extra_rol']; // Obtener el extra_rol del usuario

?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel"><i class="bi bi-boxes"></i> SYGNIA - Opciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid" style="padding-bottom: 50px;">
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

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Asesor'): ?>
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

                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Administrar sedes activas para asistencia">
                        <a href="headquartersAttendance.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-building-check icono"></i>
                                    </span>
                                    <span class="checkbox-label">Sedes asistencia</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Monitor' || $rol === 'Control maestro' || $rol === 'Permanencia'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Generar carnets para estudiantes">
                        <a href="credentials.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-person-badge icono"></i>
                                    </span>
                                    <span class="checkbox-label">Carnets</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Generar carnets para docentes, mentores y monitores">
                        <a href="executor_credentials.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="fa-solid fa-id-card-clip icono"></i>
                                    </span>
                                    <span class="checkbox-label">C. personal</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Rangos de Fecha de los Bootcamps y cohortes">
                        <a href="bootcamp_period.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-calendar-range icono"></i>
                                    </span>
                                    <span class="checkbox-label">Periodos</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($extraRol === 'Extra Administrador' || $rol === 'Control maestro'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Campistas en condición de culminar">
                        <a href="studentsToApprove.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="fa-solid fa-graduation-cap icono"></i>
                                    </span>
                                    <span class="checkbox-label">Por aprobar</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Monitor' || $rol === 'Permanencia'): ?>
                    <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Registro de seguimiento de asistencias por grupos">
                        <a href="attendance_tracking.php"> <label class="checkbox-wrapper">
                                <span class="checkbox-tile">
                                    <span class="checkbox-icon">
                                        <i class="bi bi-journals icono"></i>
                                    </span>
                                    <span class="checkbox-label">Seguimiento</span>
                                </span>
                            </label>
                        </a>
                    </div>
                <?php endif; ?>
                <!-- Agrega más elementos según necesites -->
            </fieldset>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>