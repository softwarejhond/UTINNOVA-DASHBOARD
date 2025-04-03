<?php


// Recibir el ID de la PQR como variable local
if (!isset($id_pqr_actual)) {
    echo "Error: No se proporcionó el ID de la PQR.";
    exit;
}

// Validar el ID de la PQR (ya deberías tener el ID como un entero)
$id_pqr = filter_var($id_pqr_actual, FILTER_VALIDATE_INT);

if ($id_pqr === false || $id_pqr === null) {
    echo "ID de PQR inválido.";
    exit;
}

// Construir la consulta SQL para obtener los detalles de la PQR
$sql = "SELECT pqr.*,
               estados.nombre AS estado_nombre,
               COALESCE(users.nombre, 'Sin asignar') AS admin_nombre
        FROM pqr
        INNER JOIN estados ON pqr.estado = estados.id
        LEFT JOIN users ON pqr.admin_id = users.id
        WHERE pqr.id = ?";

$stmt = $conn->prepare($sql);

if ($stmt === false) {
    // Manejar el error de la preparación de la consulta
    error_log("Error al preparar la consulta: " . $conn->error);
    echo "Error interno del servidor.";
    exit;
}

$stmt->bind_param("i", $id_pqr); // "i" indica que $id_pqr es un entero

if ($stmt->execute()) {
    $resultado_detalle = $stmt->get_result();
} else {
    // Manejar el error de la ejecución de la consulta
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    echo "Error interno del servidor.";
    exit;
}

// Verificar si se encontró la PQR
if ($resultado_detalle->num_rows == 0) {
    echo "No se encontró la PQR con ID " . htmlspecialchars($id_pqr);
    exit;
}

// Obtener los datos de la PQR
$fila = $resultado_detalle->fetch_assoc();
$usaurio = htmlspecialchars($_SESSION["username"]); //No se usa
?>

<!-- Modal HTML -->
<div class="modal fade" id="detallePQRModal-<?php echo htmlspecialchars($fila["id"]); ?>" tabindex="-1"
    aria-labelledby="detallePQRModalLabel-<?php echo htmlspecialchars($fila["id"]); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="detallePQRModalLabel-<?php echo htmlspecialchars($fila["id"]); ?>">Detalles de la
                    <?php echo htmlspecialchars($fila["numero_radicado"]); ?>
                </h5>
                <button type="button" class="btn-close btn-close-white bg-gray-ligth" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-md-6">
                            <p class="subTitle text-start"><i class="fas fa-tags"></i> <strong>Tipo:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["tipo"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-heading"></i> <strong>Asunto:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["asunto"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-user"></i> <strong>Nombre:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["nombre"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-id-card"></i> <strong>Cédula:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["cedula"]); ?></span>
                            </p>

                            <p class="subTitle text-start"><i class="fas fa-envelope"></i> <strong>Correo:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["email"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-phone"></i> <strong>Teléfono 1:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["telefono1"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-phone"></i> <strong>Teléfono 2:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["telefono2"]); ?></span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p class="subTitle text-start"><i class="fas fa-exclamation-circle"></i> <strong>Estado:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["estado_nombre"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="far fa-calendar-alt"></i> <strong>Fecha de creación:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["fecha_creacion"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="far fa-calendar-check"></i> <strong>Fecha de resolución:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars(isset($fila["fecha_resolucion"]) ? $fila["fecha_resolucion"] : ''); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-user-tie"></i> <strong>Administrador asignado:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["admin_nombre"]); ?></span>
                            </p>
                            <p class="subTitle text-start"><i class="fas fa-reply-all"></i> <strong>Respuesta:</strong><br>
                            <span class="result text-start"><?php echo htmlspecialchars($fila["respuesta"] ?? ''); ?></span>
                            </p>
                        </div>
                        <div class="col-md-12">
                            <p class="subTitle text-start"><i class="fas fa-comment-dots"></i> <strong>Descripción:</strong><br>
                                <span class="result text-start"><?php echo htmlspecialchars($fila["descripcion"]); ?></span>
                            </p>
                        </div>
                        <?php if ($rol == 1): ?>
                            <div class="mb-3">
                                <label for="admin_id" class="form-label"><i class="fas fa-user-edit"></i> Asignar
                                    Usuario:</label>
                                <input type="number" class="form-control" id="admin_id" name="admin_id"
                                    placeholder="Escribe el id del usuario" required>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-cancel bg-gray-ligth" data-bs-dismiss="modal"><i
                        class="fas fa-times"></i> Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- Fin del Modal HTML -->
<?php
$stmt->close();
//$conn->close(); // No cierres la conexión aquí, ya que la cierras en seguimiento_pqr.php
?>