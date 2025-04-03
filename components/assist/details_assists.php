
<div class="modal fade" id="verAsistenciaModal-<?php echo htmlspecialchars($id_asistencia_actual); ?>" tabindex="-1" aria-labelledby="verAsistenciaModalLabel-<?php echo htmlspecialchars($id_asistencia_actual); ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="verAsistenciaModalLabel-<?php echo htmlspecialchars($id_asistencia_actual); ?>">Detalles de Asistencia</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-start"><i class="fas fa-user"></i> <strong>Nombre Completo:</strong> <?php echo htmlspecialchars($fila['full_name']); ?></p>
                <p class="text-start"><i class="fas fa-id-card"></i> <strong>Cédula:</strong> <?php echo htmlspecialchars($fila['cedula']); ?></p>
                <p class="text-start"><i class="fas fa-calendar-alt"></i> <strong>Tipo de Actividad:</strong> <?php echo htmlspecialchars($fila['activity_type']); ?></p>
                <p class="text-start"><i class="fas fa-clock"></i> <strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($fila['created_at']); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel bg-gray-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>