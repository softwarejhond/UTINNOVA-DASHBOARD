<?php

$sql = "SELECT u.cedula, ur.first_name, ur.second_name, ur.first_last, ur.second_last, ur.program, 
               u.correo, u.id_formulario, u.nivel, ur.level, f.id AS formulario_id, f.formulario 
        FROM usuarios u
        LEFT JOIN user_register ur ON u.cedula = ur.number_id
        LEFT JOIN formularios f ON u.id_formulario = f.id
        WHERE ur.level = 'Explorador'";

$result = $conn->query($sql);
$data = [];

// Llenar $data con los resultados de la consulta
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
}

// Reiniciar el puntero del resultado para usarlo después en la tabla
$result = $conn->query($sql);


?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Define una función global para copiar (fuera de los bucles individuales)
    function copiarCodigo(id) {
        const codigoInput = document.getElementById('securityCode' + id);

        // Método alternativo de copia que funciona mejor en navegadores modernos
        navigator.clipboard.writeText(codigoInput.value).then(() => {
            // Mostrar mensaje de confirmación
            const copyBtn = document.getElementById('copyBtn' + id);
            const originalContent = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
            copyBtn.classList.replace('btn-outline-secondary', 'btn-success');

            // Restaurar el botón después de 1.5 segundos
            setTimeout(function() {
                copyBtn.innerHTML = originalContent;
                copyBtn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 1500);
        }).catch(err => {
            console.error('Error al copiar: ', err);
        });
    }
</script>

<div class="container-fluid">
    <table id="listaInscritos" class="table table-hover table-bordered">
        <thead class="thead-dark text-center">
            <tr class="text-center">
                <th>Cédula</th>
                <th>Nombre Completo</th>
                <th>Nivel de preferencia</th>
                <th>Correo</th>
                <th>Programa</th>
                <th>Puntaje</th>
                <th>Nivel</th>
                <th>Borrar Registro</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)) {
                // Concatenar nombre completo
                $nombreCompleto = trim($row['first_name'] . ' ' .
                    $row['second_name'] . ' ' .
                    $row['first_last'] . ' ' .
                    $row['second_last']);
                $cedula = $row['cedula'];
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                    <td style="width: 250px; min-width: 250px; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                        <?php echo htmlspecialchars(ucwords(strtolower($nombreCompleto))); ?>
                    </td>
                    <td><span class="badge bg-info"><?php echo htmlspecialchars($row['level']); ?></span></td>
                    <td><?php echo htmlspecialchars($row['correo']); ?></td>
                    <td><?php echo htmlspecialchars($row['program']); ?></td>
                    <td><?php echo htmlspecialchars($row['nivel'] ?? 'No asignado'); ?></td>
                    <td>
                        <?php
                        $nivel = $row['nivel'];
                        if (isset($nivel)) {
                            if ($nivel >= 1 && $nivel <= 5) {
                                echo '<span class="badge bg-magenta-dark w-100">Basico</span>';
                            } elseif ($nivel >= 6 && $nivel <= 10) {
                                echo '<span class="badge bg-orange-dark w-100">Intermedio</span>';
                            } elseif ($nivel >= 11 && $nivel <= 15) {
                                echo '<span class="badge bg-teal-dark w-100">Avanzado</span>';
                            }
                        } else {
                            echo '<span class="badge bg-silver w-100" data-toggle="popover" data-trigger="focus" data-placement="top" title="No ha presentado la prueba">
                            <i class="bi bi-ban"></i>
                            </span>';
                        }
                        ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?php echo $cedula; ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<?php
// Reiniciar el puntero de resultados para los modales
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)) {
    $nombreCompleto = trim($row['first_name'] . ' ' .
        $row['second_name'] . ' ' .
        $row['first_last'] . ' ' .
        $row['second_last']);
    $cedula = $row['cedula'];
?>
    <!-- Modal fuera del bucle de la tabla -->
    <div class="modal fade" id="deleteModal<?php echo $cedula; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $cedula; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-magenta-dark text-white item-align-center">
                    <h5 class="modal-title text-center" id="deleteModalLabel<?php echo $cedula; ?>"><i class="bi bi-exclamation-circle"></i> ATENCIÓN</h5>
                </div>
                <div class="modal-body">
                    <h5>Nombre: <strong><?php echo htmlspecialchars(ucwords(strtolower($nombreCompleto))); ?></strong></h5>
                    <h5>Cédula: <strong><?php echo htmlspecialchars($cedula); ?></strong></h5>

                    <hr>
                    <div class="card shadow-lg p-3 mb-5 bg-body-tertiary rounded">
                        <p class="mb-1">Para confirmar la eliminación, ingresa el código de seguridad:</p>
                        <div class="code-container mt-2 mb-3 p-2 bg-light">
                            <div class="input-group">
                                <input type="text" class="form-control security-code" id="securityCode<?php echo $cedula; ?>"
                                    readonly value="" style="font-family: monospace; letter-spacing: 3px; text-align: center; font-weight: bold;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary bg-indigo-dark" type="button" id="copyBtn<?php echo $cedula; ?>"
                                        onclick="copiarCodigo('<?php echo $cedula; ?>')">
                                        <i class="bi bi-clipboard"></i> Copiar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted text-center d-block mt-1">Este código cambiará en <span id="codeTimer<?php echo $cedula; ?>">15</span> segundos</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmCode<?php echo $cedula; ?>">Ingresa el código</label>
                            <input type="text" class="form-control-lg text-center w-100" id="confirmCode<?php echo $cedula; ?>" placeholder="Código">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn bg-magenta-dark text-white" id="deleteBtn<?php echo $cedula; ?>" disabled>Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const modalId = <?php echo json_encode($cedula); ?>;
            let securityCode = '';
            let timer = 15;
            let interval;

            // Función para generar código aleatorio de 6 caracteres
            function generateCode() {
                const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
                let result = '';
                for (let i = 0; i < 6; i++) {
                    result += chars.charAt(Math.floor(Math.random() * chars.length));
                }
                return result;
            }

            // Función para actualizar el temporizador
            function updateTimer() {
                timer--;
                $("#codeTimer" + modalId).text(timer);

                if (timer <= 0) {
                    timer = 15;
                    securityCode = generateCode();
                    $("#securityCode" + modalId).val(securityCode);
                }
            }

            // Inicializar el código cuando se abre el modal
            $("#deleteModal" + modalId).on('shown.bs.modal', function() {
                securityCode = generateCode();
                console.log("Código generado:", securityCode); // Para depuración
                $("#securityCode" + modalId).val(securityCode);
                timer = 15;
                $("#codeTimer" + modalId).text(timer);

                // Iniciar el temporizador
                clearInterval(interval);
                interval = setInterval(updateTimer, 1000);
            });

            // Detener el temporizador cuando se cierra el modal
            $("#deleteModal" + modalId).on('hidden.bs.modal', function() {
                clearInterval(interval);
                $("#confirmCode" + modalId).val('');
                $("#deleteBtn" + modalId).prop('disabled', true);
            });

            // Verificar el código ingresado
            $("#confirmCode" + modalId).on('input', function() {
                const inputCode = $(this).val();
                $("#deleteBtn" + modalId).prop('disabled', inputCode !== securityCode);
            });

            // Manejar eliminación
            $("#deleteBtn" + modalId).click(function() {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción eliminará permanentemente el registro con cédula " + modalId,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Realizar la eliminación mediante AJAX
                        $.ajax({
                            url: 'components/listTest/deleteUsuario.php',
                            type: 'POST',
                            data: {
                                cedula: modalId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        '¡Eliminado!',
                                        'El registro ha sido eliminado correctamente.',
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        response.message || 'Ocurrió un error al eliminar el registro.',
                                        'error'
                                    );
                                }
                            },
                            error: function() {
                                Swal.fire(
                                    'Error',
                                    'Ocurrió un error en la comunicación con el servidor.',
                                    'error'
                                );
                            }
                        });
                    }
                });
            });
        });
    </script>
<?php } ?>