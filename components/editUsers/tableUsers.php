<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario actual

// Consulta para obtener todos los usuarios (incluir extra_rol)
$sql = "SELECT * FROM users ORDER BY id ASC";
$result = $conn->query($sql);
$users = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
}

// Función para convertir el número de rol a texto
function getRolText($rolNum) {
    $roles = [
        1 => 'Administrador',
        2 => 'Editor',
        3 => 'Asesor',
        4 => 'Visualizador',
        5 => 'Docente',
        6 => 'Académico',
        7 => 'Monitor',
        8 => 'Mentor',
        9 => 'Supervisor',
        10 => 'Empleabilidad',
        11 => 'Superacademico',
        12 => 'Control maestro',
        13 => 'Interventoria',
        14 => 'Permanencia'
    ];
    return isset($roles[$rolNum]) ? $roles[$rolNum] : 'Rol desconocido';
}
?>

<div class="container-fluid">
    <div class="table-responsive">
        <table id="listaUsuarios" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <th>Usuario</th>
                    <th>Nombre</th>
                    <th>Rol</th>
                    <th>Rol Extra</th>
                    <th>Email</th>
                    <th>Género</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Edad</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user) { ?>
                    <tr>
                        <td><?php echo !empty($user['username']) ? htmlspecialchars($user['username']) : '--'; ?></td>
                        <td><?php echo !empty($user['nombre']) ? htmlspecialchars($user['nombre']) : '--'; ?></td>
                        <td><?php echo !empty($user['rol']) ? htmlspecialchars(getRolText($user['rol'])) : '--'; ?></td>
                        <td class="text-center">
                            <?php if (isset($user['extra_rol']) && $user['extra_rol'] == 1): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Activo
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-dash-circle"></i> Inactivo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo !empty($user['email']) ? htmlspecialchars($user['email']) : '--'; ?></td>
                        <td><?php echo !empty($user['genero']) ? htmlspecialchars($user['genero']) : '--'; ?></td>
                        <td><?php echo !empty($user['telefono']) ? htmlspecialchars($user['telefono']) : '--'; ?></td>
                        <td><?php echo !empty($user['direccion']) ? htmlspecialchars($user['direccion']) : '--'; ?></td>
                        <td><?php echo !empty($user['edad']) ? htmlspecialchars($user['edad']) : '--'; ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button class="btn bg-indigo-dark btn-sm text-white" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal<?php echo $user['id']; ?>"
                                        title="Editar usuario">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#deleteModal<?php echo $user['id']; ?>"
                                        title="Eliminar usuario">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modales de Edición -->
<?php foreach ($users as $user) { ?>
    <div class="modal fade" id="editModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="editModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-indigo-dark text-white">
                    <h5 class="modal-title" id="editModalLabel<?php echo $user['id']; ?>">
                        Editar Usuario: <?php echo htmlspecialchars($user['nombre']); ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="updateForm<?php echo $user['id']; ?>">
                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre completo</label>
                            <input type="text" class="form-control" name="nombre" value="<?php echo htmlspecialchars($user['nombre']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select class="form-select" name="rol" required>
                                <option value="1" <?php echo ($user['rol'] == 1) ? 'selected' : ''; ?>>Administrador</option>
                                <option value="2" <?php echo ($user['rol'] == 2) ? 'selected' : ''; ?>>Editor</option>
                                <option value="3" <?php echo ($user['rol'] == 3) ? 'selected' : ''; ?>>Asesor</option>
                                <option value="4" <?php echo ($user['rol'] == 4) ? 'selected' : ''; ?>>Visualizador</option>
                                <option value="5" <?php echo ($user['rol'] == 5) ? 'selected' : ''; ?>>Docente</option>
                                <option value="6" <?php echo ($user['rol'] == 6) ? 'selected' : ''; ?>>Académico</option>
                                <option value="7" <?php echo ($user['rol'] == 7) ? 'selected' : ''; ?>>Monitor</option>
                                <option value="8" <?php echo ($user['rol'] == 8) ? 'selected' : ''; ?>>Mentor</option>
                                <option value="9" <?php echo ($user['rol'] == 9) ? 'selected' : ''; ?>>Supervisor</option>
                                <option value="10" <?php echo ($user['rol'] == 10) ? 'selected' : ''; ?>>Empleabilidad</option>
                                <option value="11" <?php echo ($user['rol'] == 11) ? 'selected' : ''; ?>>Superacademico</option>
                                <option value="12" <?php echo ($user['rol'] == 12) ? 'selected' : ''; ?>>Control maestro</option>
                                <option value="13" <?php echo ($user['rol'] == 13) ? 'selected' : ''; ?>>Interventoria</option>
                                <option value="14" <?php echo ($user['rol'] == 14) ? 'selected' : ''; ?>>Permanencia</option>
                            </select>
                        </div>

                        <!-- NUEVO CAMPO: Rol Extra -->
                        <div class="mb-3">
                            <label class="form-label">Rol Extra</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       role="switch" 
                                       id="extraRolSwitch<?php echo $user['id']; ?>" 
                                       name="extra_rol" 
                                       value="1"
                                       <?php echo (isset($user['extra_rol']) && $user['extra_rol'] == 1) ? 'checked' : ''; ?>
                                       style="border: 2px solid #30336B; width: 3em; height: 1.5em;">
                                <label class="form-check-label" for="extraRolSwitch<?php echo $user['id']; ?>">
                                    <span id="extraRolLabel<?php echo $user['id']; ?>">
                                        <?php echo (isset($user['extra_rol']) && $user['extra_rol'] == 1) ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                <i class="bi bi-info-circle"></i> 
                                Activa para otorgar acceso adicional a áreas especiales del sistema
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Género</label>
                            <select class="form-select" name="genero" required>
                                <option value="Masculino" <?php echo ($user['genero'] == 'Masculino') ? 'selected' : ''; ?>>Masculino</option>
                                <option value="Femenino" <?php echo ($user['genero'] == 'Femenino') ? 'selected' : ''; ?>>Femenino</option>
                                <option value="LGBTQI+" <?php echo ($user['genero'] == 'LGBTQI+') ? 'selected' : ''; ?>>LGBTQI+</option>
                                <option value="Otro" <?php echo ($user['genero'] == 'Otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" value="<?php echo htmlspecialchars($user['telefono']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" class="form-control" name="direccion" value="<?php echo htmlspecialchars($user['direccion']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Edad</label>
                            <input type="number" class="form-control" name="edad" value="<?php echo htmlspecialchars($user['edad']); ?>" required>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="changePassword<?php echo $user['id']; ?>" onchange="togglePasswordFields(<?php echo $user['id']; ?>)" style="border: 2px solid #30336B; width: 1.2em; height: 1.2em;">
                                <label class="form-check-label">
                                    Cambiar contraseña
                                </label>
                            </div>
                        </div>

                        <div id="passwordFields<?php echo $user['id']; ?>" style="display: none;">
                            <div class="mb-3">
                                <label class="form-label">Nueva contraseña</label>
                                <input type="password" class="form-control" name="password" id="password<?php echo $user['id']; ?>">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirmar contraseña</label>
                                <input type="password" class="form-control" name="confirmPassword" id="confirmPassword<?php echo $user['id']; ?>">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn bg-indigo-dark text-white" onclick="updateUser(<?php echo $user['id']; ?>)">Guardar cambios</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Eliminación (sin cambios) -->
    <div class="modal fade" id="deleteModal<?php echo $user['id']; ?>" tabindex="-1" aria-labelledby="deleteModalLabel<?php echo $user['id']; ?>" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel<?php echo $user['id']; ?>">
                        <i class="bi bi-exclamation-circle"></i> ATENCIÓN
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <h5>Usuario: <strong><?php echo htmlspecialchars($user['nombre']); ?></strong></h5>
                    <div class="card shadow-lg p-3 mb-5 bg-body-tertiary rounded">
                        <p class="mb-1">Para confirmar la eliminación, ingresa el código de seguridad:</p>
                        <div class="code-container mt-2 mb-3 p-2 bg-light">
                            <div class="input-group">
                                <input type="text" class="form-control security-code" id="securityCode<?php echo $user['id']; ?>"
                                    readonly value="" style="font-family: monospace; letter-spacing: 3px; text-align: center; font-weight: bold;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="copyBtn<?php echo $user['id']; ?>"
                                        onclick="copiarCodigo('<?php echo $user['id']; ?>')">
                                        <i class="bi bi-clipboard"></i> Copiar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted text-center d-block mt-1">Este código cambiará en <span id="codeTimer<?php echo $user['id']; ?>">15</span> segundos</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmCode<?php echo $user['id']; ?>">Ingresa el código</label>
                            <input type="text" class="form-control-lg text-center w-100" id="confirmCode<?php echo $user['id']; ?>" placeholder="Código">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-danger" id="deleteBtn<?php echo $user['id']; ?>" disabled>Eliminar</button>
                </div>
            </div>
        </div>
    </div>
<?php } ?>

<script>
$(document).ready(function() {
    // Inicializar DataTable
    $('#listaUsuarios').DataTable({
        "language": {
            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        },
        "order": [[1, "asc"]]
    });

    // Inicializar los modales de eliminación
    <?php foreach ($users as $user) { ?>
        initDeleteModal(<?php echo $user['id']; ?>);
        
        // Inicializar el switch de rol extra
        initExtraRolSwitch(<?php echo $user['id']; ?>);
    <?php } ?>
});

// Nueva función para manejar el switch de rol extra
function initExtraRolSwitch(userId) {
    const switchElement = document.getElementById('extraRolSwitch' + userId);
    const labelElement = document.getElementById('extraRolLabel' + userId);
    
    switchElement.addEventListener('change', function() {
        if (this.checked) {
            labelElement.textContent = 'Activo';
            labelElement.className = 'text-success fw-bold';
        } else {
            labelElement.textContent = 'Inactivo';
            labelElement.className = 'text-muted';
        }
    });
}

function togglePasswordFields(userId) {
    const passwordFields = document.getElementById('passwordFields' + userId);
    passwordFields.style.display = document.getElementById('changePassword' + userId).checked ? 'block' : 'none';
}

function updateUser(userId) {
    const form = document.getElementById('updateForm' + userId);
    const formData = new FormData(form);
    
    // Validar contraseñas si se va a cambiar
    if (document.getElementById('changePassword' + userId).checked) {
        const password = document.getElementById('password' + userId).value;
        const confirmPassword = document.getElementById('confirmPassword' + userId).value;
        
        if (password !== confirmPassword) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Las contraseñas no coinciden'
            });
            return;
        }
    }

    // Agregar valor del extra_rol al FormData
    const extraRolSwitch = document.getElementById('extraRolSwitch' + userId);
    if (!extraRolSwitch.checked) {
        formData.append('extra_rol', '0');
    }

    Swal.fire({
        title: 'Actualizando...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        showConfirmButton: false,
        willOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: 'components/editUsers/update_user.php',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Usuario actualizado correctamente',
                    showConfirmButton: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al actualizar el usuario: ' + response.message
                });
            }
        },
        error: function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en la conexión'
            });
        }
    });
}

// Resto de funciones sin cambios...
function initDeleteModal(userId) {
    let securityCode = '';
    let timer = 15;
    let interval;

    function generateCode() {
        const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        let result = '';
        for (let i = 0; i < 6; i++) {
            result += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        return result;
    }

    function updateTimer() {
        timer--;
        $("#codeTimer" + userId).text(timer);

        if (timer <= 0) {
            timer = 15;
            securityCode = generateCode();
            $("#securityCode" + userId).val(securityCode);
        }
    }

    $("#deleteModal" + userId).on('shown.bs.modal', function() {
        securityCode = generateCode();
        $("#securityCode" + userId).val(securityCode);
        timer = 15;
        $("#codeTimer" + userId).text(timer);
        clearInterval(interval);
        interval = setInterval(updateTimer, 1000);
    });

    $("#deleteModal" + userId).on('hidden.bs.modal', function() {
        clearInterval(interval);
        $("#confirmCode" + userId).val('');
        $("#deleteBtn" + userId).prop('disabled', true);
    });

    $("#confirmCode" + userId).on('input', function() {
        $("#deleteBtn" + userId).prop('disabled', $(this).val() !== securityCode);
    });

    $("#deleteBtn" + userId).click(function() {
        Swal.fire({
            title: '¿Estás seguro?',
            text: "Esta acción no se puede deshacer",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'components/editUsers/delete_user.php',
                    type: 'POST',
                    data: { id: userId },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: 'El usuario ha sido eliminado correctamente',
                                showConfirmButton: true
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error en la conexión'
                        });
                    }
                });
            }
        });
    });
}

function copiarCodigo(id) {
    const codigoInput = document.getElementById('securityCode' + id);
    navigator.clipboard.writeText(codigoInput.value).then(() => {
        const copyBtn = document.getElementById('copyBtn' + id);
        const originalContent = copyBtn.innerHTML;
        copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
        copyBtn.classList.add('btn-success');
        setTimeout(function() {
            copyBtn.innerHTML = originalContent;
            copyBtn.classList.remove('btn-success');
        }, 1500);
    }).catch(err => {
        console.error('Error al copiar: ', err);
    });
}
</script>