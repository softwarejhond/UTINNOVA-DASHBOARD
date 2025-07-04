<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
?>
<div class="offcanvas offcanvas-bottom text-bg-dark" tabindex="-1" id="offcanvasBottom" aria-labelledby="offcanvasBottomLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasBottomLabel"><i class="bi bi-boxes"></i> Gestión de matriculados</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body small">
        <fieldset class="checkbox-group-bottom d-flex flex-wrap justify-content-center align-items-center">

            <!-- Botones e íconos organizados horizontalmente -->
            <?php if ($rol === 'Administrador' || $rol === 'Docente' || $rol === 'Monitor' || $rol === 'Académico' || $rol === 'Control maestro' || $rol === 'Permanencia'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Tabla de asistencia">
                    <a href="attendance.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-list-check icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Asistencia</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Asesor' || $rol === 'Monitor' || $rol === 'Control maestro' || $rol === 'Permanencia'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Asistencia grupal">
                    <a href="attendanceGroup.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-ui-checks icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Asistencia G.</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Matricula múltiple">
                    <a href="multipleMoodle.php"><label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-robot icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Ingresar</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Asesor' || $rol === 'Control maestro' || $rol === 'Permanencia'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Lista de matriculados">
                    <a href="activeMoodle.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon icono text-indigo-dark">
                                    <i class="bi bi-person-fill-check icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Matriculados</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Desmatricula multiple">
                    <a href="multiple_erase.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon icono text-indigo-dark">
                                    <i class="bi bi-exclamation-diamond-fill icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Desmatricular</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Control maestro'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Listado y edición de cursos">
                    <a href="editCourses.php"><label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-journal-text icono text-indigo-dark "></i>
                                </span>
                                <span class="checkbox-label">Cursos</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
            <?php if ($rol === 'Administrador' || $rol === 'Académico' || $rol === 'Docente' || $rol === 'Monitor' || $rol === 'Control maestro' || $rol === 'Permanencia'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Actualizar asistencia individual">
                    <a href="individualAttendance.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-person-lines-fill  icono text-indigo-dark"></i>
                                </span>
                                <span class="checkbox-label">Asitencia In.</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
   
            <?php if ($rol === 'Administrador' || $rol === 'Supervisor' || $rol === 'Control maestro'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Puntajes de formularios">
                    <a href="editTest.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-list-ol icono text-indigo-dark"></i>
                                </span>
                                <span class="checkbox-label">Puntajes</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Supervisor' || $rol === 'Control maestro'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Horarios de cursos">
                    <a href="schedules.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-clock-history icono text-indigo-dark"></i>
                                </span>
                                <span class="checkbox-label">Horarios</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                <div class="checkbox me-3 text-center" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Administrar usuarios">	
                    <a href="editUsers.php">
                        <label class="checkbox-wrapper">
                            <span class="checkbox-tile">
                                <span class="checkbox-icon">
                                    <i class="bi bi-person-fill-gear icono text-indigo-dark"></i>
                                </span>
                                <span class="checkbox-label">Usuarios</span>
                            </span>
                        </label>
                    </a>
                </div>
            <?php endif; ?>
        </fieldset>

    </div>
</div>