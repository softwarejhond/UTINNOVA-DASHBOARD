<?php
if (!isset($id_encuesta_actual)) {
    echo "Error: No se proporcionó el ID de la encuesta.";
    exit;
}


$id_encuesta = filter_var($id_encuesta_actual, FILTER_VALIDATE_INT);

if ($id_encuesta === false || $id_encuesta === null) {
    echo "ID de encuesta inválido.";
    exit;
}

?>

<!-- components/encuestas/modals/verEncuestaModal.php -->
<div class="modal fade" id="verEncuestaModal-<?php echo htmlspecialchars($id_encuesta_actual); ?>" tabindex="-1"
    aria-labelledby="verEncuestaModalLabel-<?php echo htmlspecialchars($id_encuesta_actual); ?>" aria-hidden="true" style="z-index: 1070 !important;">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="verEncuestaModalLabel-<?php echo htmlspecialchars($id_encuesta_actual); ?>">
                    Detalles de la Encuesta <?php echo htmlspecialchars($id_encuesta_actual); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white bg-gray-ligth" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="subTitle text-start"><i class="fas fa-user"></i> <strong>Nombre:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['nombreCompleto']); ?></span>
                            </p>
                            <br>
                            <p class="subTitle text-start"><i class="fas fa-id-card"></i> <strong>Cédula:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['cedula']); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-briefcase"></i> <strong>Situación laboral:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['situacionLaboral']); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-clock"></i> <strong>Tiempo desempleo:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['tiempoDesempleo'] ?? 'N/A'); ?></span>
                            </p>
                            <br>
                            <p class="subTitle text-start"><i class="fas fa-wrench"></i> <strong>Trabajo desempeña:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['trabajoDesempena'] ?? 'N/A'); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-calendar"></i> <strong>Fecha de creación:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['fecha_creacion']); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="subTitle text-start"><i class="fas fa-file-contract"></i> <strong>Tipo contrato:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['tipoContrato']); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-money-bill-wave"></i> <strong>Rango salarial:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['rangoSalarial']); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-file-alt"></i> <strong>Hoja de vida:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['hojaVida']); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-building"></i> <strong>Contacto empleadores:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['contactoEmpleadores']); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-users"></i> <strong>Talleres asistidos:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila['talleresAsistidos']); ?></span>
                            </p>
                        </div>
                        <?php if ($rol == 1): ?>
                            <
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel bg-gray-light" data-bs-dismiss="modal"><i
                        class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin del Modal HTML -->