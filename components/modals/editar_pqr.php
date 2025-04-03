<?php

$respuesta = 'Sin respuesta';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $tipo = isset($_POST['tipo']) ? trim($_POST['tipo']) : null;
    $asunto = isset($_POST['asunto']) ? trim($_POST['asunto']) : null;
    $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : null;
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : null;
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : null;
    $email = isset($_POST['email']) ? trim($_POST['email']) : null;
    $telefono1 = isset($_POST['telefono1']) ? trim($_POST['telefono1']) : null;
    $telefono2 = isset($_POST['telefono2']) ? trim($_POST['telefono2']) : null;
    // Lógica para la fecha de resolución
    $estado = isset($_POST['estado']) ? intval($_POST['estado']) : null;  // Obtener el estado desde el POST
    if ($estado == 3) { 
        $fecha_resolucion_db = date('Y-m-d H:i:s');
    } else {
        $fecha_resolucion_db = null;
    }

    // Obtener respuesta del POST
    $respuesta = isset($_POST['respuesta']) ? trim($_POST['respuesta']) : 'Sin respuesta';

    $admin_id = isset($_POST['admin_id']) ? intval($_POST['admin_id']) : null;

    if ($id <= 0 || empty($tipo) || empty($asunto) || empty($nombre) || empty($cedula) || empty($email) || empty($telefono1) || empty($admin_id) || empty($estado)) {
        echo "<script>alert('Error: Todos los campos obligatorios deben completarse.'); window.history.back();</script>";
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Error: Formato de correo inválido.'); window.history.back();</script>";
        exit;
    }

    if (!preg_match('/^[0-9\+\-\(\)\s]+$/', $telefono1) || (!empty($telefono2) && !preg_match('/^[0-9\+\-\(\)\s]+$/', $telefono2))) {
        echo "<script>alert('Error: Teléfonos inválidos.'); window.history.back();</script>";
        exit;
    }

    // Verificar si el ID existe en la BD antes de actualizar
    $verificar_sql = "SELECT id FROM pqr WHERE id = ?";
    $stmt_verificar = $conn->prepare($verificar_sql);
    $stmt_verificar->bind_param("i", $id);
    $stmt_verificar->execute();
    $stmt_verificar->store_result();

    if ($stmt_verificar->num_rows == 0) {
        echo "<script>alert('Error: El registro no existe.'); window.history.back();</script>";
        exit;
    }

    $stmt_verificar->close();

    $sql = "UPDATE pqr SET 
                tipo = ?, 
                asunto = ?, 
                descripcion = ?, 
                nombre = ?, 
                cedula = ?, 
                email = ?, 
                telefono1 = ?, 
                telefono2 = ?, 
                fecha_resolucion = ?, 
                respuesta = ?, 
                admin_id = ?, 
                estado = ? 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        error_log("Error en la preparación: " . $conn->error);
        echo "<script>alert('Error en la consulta.');</script>";
        exit;
    }

    $stmt->bind_param(
        "ssssssssssiii", //¡CORREGIDO!  Todos son strings excepto admin_id, estado, id
        $tipo,
        $asunto,
        $descripcion,
        $nombre,
        $cedula,
        $email,
        $telefono1,
        $telefono2,
        $fecha_resolucion_db,
        $respuesta,
        $admin_id,
        $estado,
        $id
    );

    if ($stmt->execute()) {
        echo "<script>alert('Registro actualizado correctamente'); window.location.href='admin_pqrs.php';</script>";
    } else {
        error_log("Error al actualizar: " . $stmt->error);
        echo "<script>alert('Error al actualizar el registro.');</script>";
    }

    $stmt->close();
    // NO CERRAR LA CONEXIÓN AQUÍ. SE CIERRA EN EL ARCHIVO PRINCIPAL (admin_pqrs.php)
}
?>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Incluir otros archivos CSS y JS necesarios -->

<!-- Modal HTML -->
<div class="modal fade" id="editarPQRModal-<?php echo htmlspecialchars($id_pqr_actual); ?>" tabindex="-1"
    aria-labelledby="editarPQRModalLabel-<?php echo htmlspecialchars($id_pqr_actual); ?>" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark text-white">
                <h5 class="modal-title" id="editarPQRModalLabel-<?php echo htmlspecialchars($id_pqr_actual); ?>">Editar
                    Radicado:
                    <?php 
                      //BUSCAR LA DATA CON EL ID DE LA PQR ACTUAL
                      $sql_pqr = "SELECT * FROM pqr WHERE id = $id_pqr_actual";
                      $resultado_pqr = $conn->query($sql_pqr);
                      $fila = $resultado_pqr->fetch_assoc();
                      echo htmlspecialchars($fila["numero_radicado"]); 
                      
                    ?>
                </h5>
                <button type="button" class="btn-close bg-gray-ligth" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- IMPORTANTE: Usa $id_pqr_actual en la URL del action del formulario -->
                <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"] . "?id=" . $id_pqr_actual); ?>">
                    <!-- Campo oculto para el ID de la PQR -->
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($fila["id"]); ?>">
                    <!-- Campo oculto para el ID de la PQR (para JavaScript) -->
                    <input type="hidden" id="pqrId" name="pqrId" value="<?php echo htmlspecialchars($fila["id"]); ?>">
                    <?php
                    // Obtener los valores del ENUM tipo
                    $sql_tipo_enum = "SHOW COLUMNS FROM pqr LIKE 'tipo'";
                    $resultado_tipo_enum = $conn->query($sql_tipo_enum);
                    $fila_tipo_enum = $resultado_tipo_enum->fetch_assoc();
                    $tipo_enum = $fila_tipo_enum['Type'];

                    // Extraer los valores del ENUM
                    preg_match("/^enum\((.*)\)$/", $tipo_enum, $matches);
                    $tipos = str_getcsv($matches[1], ',', "'");
                    ?>

                    <div class="row mb-3">
                        <label for="tipo" class="col-sm-3 col-form-label text-start">Tipo:</label>
                        <select class="form-select" id="tipo" name="tipo">
                            <?php
                            foreach ($tipos as $tipo) {
                                $selected = ($fila["tipo"] == $tipo) ? "selected" : "";
                                echo "<option value='" . htmlspecialchars($tipo) . "' " . $selected . "' " . $selected . ">" . htmlspecialchars($tipo) . "</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="row mb-3">
                        <label for="asunto" class="col-sm-3 col-form-label text-start">Asunto:</label>
                        <input type="text" class="form-control" id="asunto" name="asunto"
                            value="<?php echo htmlspecialchars($fila["asunto"]); ?>">
                    </div>

                    <div class="row mb-3">
                        <label for="nombre" class="col-sm-3 col-form-label text-start">Nombre:</label>
                        <input type="text" class="form-control" id="nombre" name="nombre"
                            value="<?php echo htmlspecialchars($fila["nombre"]); ?>">
                    </div>

                    <div class="row mb-3">
                        <label for="cedula" class="col-sm-3 col-form-label text-start">Cédula:</label>
                        <input type="text" class="form-control" id="cedula" name="cedula"
                            value="<?php echo htmlspecialchars($fila["cedula"]); ?>" required>
                    </div>


                    <!-- Campo Email -->
                    <div class="row mb-3">
                        <label for="email" class="col-sm-3 col-form-label text-start">Correo:</label>
                        <input type="email" class="form-control" id="email" name="email"
                            value="<?php echo htmlspecialchars($fila["email"]); ?>">
                    </div>

                    <div class="row mb-3">
                        <label for="telefono1" class="col-sm-4 col-form-label text-start">Teléfono 1:</label>
                        <input type="tel" class="form-control" id="telefono1" name="telefono1"
                            value="<?php echo htmlspecialchars($fila["telefono1"]); ?>" required
                            data-error="El teléfono 1 es obligatorio.">
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row mb-3">
                        <label for="telefono2" class="col-sm-4 col-form-label text-start">Teléfono 2:</label>
                        <input type="tel" class="form-control" id="telefono2" name="telefono2"
                            value="<?php echo htmlspecialchars($fila["telefono2"]); ?>">
                        <div class="invalid-feedback"></div>
                    </div>

                    <!-- Campo Descripción -->
                    <div class="row mb-3">
                        <label for="descripcion" class="col-sm-3 col-form-label text-start">Descripción:</label>
                        <textarea class="form-control" id="descripcion" name="descripcion"
                            rows="3"><?php echo htmlspecialchars($fila["descripcion"]); ?></textarea>
                        <div class="invalid-feedback"></div>
                    </div>

                    <div class="row mb-3">
                        <label for="respuesta" class="col-sm-3 col-form-label text-start">Respuesta:</label>
                        <textarea class="form-control" id="respuesta" name="respuesta" rows="3"><?php echo htmlspecialchars($respuesta); ?></textarea>
                    </div>

                    <!-- Select para el estado -->
                    <div class="row mb-3">
                        <label for="estado" class="col-sm-3 col-form-label text-start">Estado:</label>
                        <select class="form-select" id="estado" name="estado">
                            <?php
                            $sql_estados = "SELECT id, nombre FROM estados";
                            $resultado_estados = $conn->query($sql_estados);
                            if ($resultado_estados->num_rows > 0) {
                                while ($fila_estado = $resultado_estados->fetch_assoc()) {
                                    $selected = ($fila["estado"] == $fila_estado["id"]) ? "selected" : "";
                                    echo "<option value='" . htmlspecialchars($fila_estado["id"]) . "' " . $selected . ">" . htmlspecialchars($fila_estado["nombre"]) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Input para el admin_id -->
                    <div class="row mb-3">
                        <label for="admin_id" class="col-sm-9 col-form-label text-start">Administrador asignado:</label>
                        <select class="form-select" id="admin_id" name="admin_id" required
                            data-error="Debe seleccionar un administrador.">
                            <option value="">-- Seleccionar Administrador --</option>
                            <?php
                            // Obtener la lista de administradores desde la base de datos
                            $sql_admins = "SELECT id, nombre FROM users WHERE rol = 1";
                            $resultado_admins = $conn->query($sql_admins);

                            if ($resultado_admins->num_rows > 0) {
                                while ($fila_admin = $resultado_admins->fetch_assoc()) {
                                    $selected = ($fila["admin_id"] == $fila_admin["id"]) ? "selected" : "";
                                    echo "<option value='" . htmlspecialchars($fila_admin["id"]) . "' " . $selected . ">" . htmlspecialchars($fila_admin["nombre"]) . "</option>";
                                }
                            }
                            ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-update bg-indigo-dark text-white">Guardar
                        Cambios</button>
                    <button type="button" class="btn btn-cancel bg-gray-light" data-bs-dismiss="modal">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        setTimeout(function() {
            // Función para obtener el ID de la PQR del ID del modal abierto (ya no es necesaria)

            // Ejecutar el código cuando un modal de edición se muestra
            $('.modal[id^="editarPQRModal-"]').on('shown.bs.modal', function(event) {
                let modalId = $(this).attr('id'); // Obtener el ID del modal directamente
                let formulario = document.querySelector(`#${modalId} form`);

                function mostrarError(campo, mensaje) {
                    campo.classList.add("is-invalid");
                    let mensajeError = campo.closest('.mb-3')?.querySelector('.invalid-feedback');
                    if (mensajeError) mensajeError.textContent = mensaje;
                }

                function limpiarError(campo) {
                    campo.classList.remove("is-invalid");
                    campo.classList.add("is-valid");
                    let mensajeError = campo.closest('.mb-3')?.querySelector('.invalid-feedback');
                    if (mensajeError) mensajeError.textContent = "";
                }

                if (formulario) { // Verificar si el formulario existe
                    formulario.addEventListener("submit", function(event) {
                        event.preventDefault();

                        let formularioValido = true;
                        let campos = formulario.querySelectorAll("input, select, textarea");

                        // Validar campos vacíos
                        campos.forEach(campo => {
                            if (campo.hasAttribute('required') && !campo.value.trim()) {
                                formularioValido = false;
                                mostrarError(campo, campo.getAttribute("data-error") || "Este campo es obligatorio.");
                            } else {
                                limpiarError(campo);
                            }
                        });

                        // Validar email dentro del modal activo
                        let emailCampo = formulario.querySelector("#email");
                        if (emailCampo) {
                            let email = emailCampo.value.trim();
                            let emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (email && !emailRegex.test(email)) {
                                formularioValido = false;
                                mostrarError(emailCampo, "Ingrese un email válido.");
                            } else {
                                limpiarError(emailCampo);
                            }
                        }

                        // Validar admin_id dentro del modal activo
                        let adminIdCampo = formulario.querySelector("#admin_id");
                        if (adminIdCampo) {
                            let adminId = adminIdCampo.value.trim();
                            if (adminId && isNaN(adminId)) {
                                formularioValido = false;
                                mostrarError(adminIdCampo, "Seleccione un administrador válido.");
                            } else {
                                limpiarError(adminIdCampo);
                            }
                        }

                        if (!formularioValido) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Error',
                                text: 'Por favor, corrige los errores en el formulario.',
                            });
                            return; // DETIENE EL ENVÍO DEL FORMULARIO
                        }

                        Swal.fire({
                            title: '¿Estás seguro de guardar los cambios?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, guardar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                let formData = new FormData(formulario);
                                fetch(formulario.action, {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => {
                                        if (!response.ok) {
                                            return response.text().then(text => {
                                                throw new Error(text);
                                            });
                                        }
                                        return response.text();
                                    })
                                    .then(data => {
                                        console.log('Respuesta del servidor:', data);
                                        Swal.fire(
                                            '¡Guardado!',
                                            'Los cambios han sido guardados correctamente.',
                                            'success'
                                        ).then(() => {
                                            let modal = document.querySelector(`#${modalId}`);
                                            $(modal).modal('hide'); // Cerrar el modal
                                            location.reload(); // Recargar la página solo si fue exitoso
                                        });
                                    })
                                    .catch(error => {
                                        console.error('Error:', error);
                                        Swal.fire(
                                            '¡Error!',
                                            'Hubo un problema al guardar los cambios. Inténtalo de nuevo.',
                                            'error'
                                        );
                                    });
                            }
                        });
                    });

                    // Validación en tiempo real
                    formulario.querySelectorAll("input, select, textarea").forEach(function(campo) {
                        campo.addEventListener("input", function() {
                            if (campo.classList.contains("is-invalid")) {
                                limpiarError(campo);
                            }
                        });
                    });
                }
            });
        }, 50); // Retraso de 50 milisegundos
    });
</script>
<!-- Fin del Modal HTML -->