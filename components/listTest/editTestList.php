<?php
// Mantener la conexión y configuración inicial
try {
    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = $_GET['search'];

        // Modificar la consulta para búsqueda individual
        $sql = "SELECT u.cedula, ur.first_name, ur.second_name, ur.first_last, ur.second_last, 
                   ur.program, u.correo, u.id_formulario, u.nivel, ur.level
            FROM usuarios u
            LEFT JOIN user_register ur ON u.cedula = ur.number_id
            WHERE u.cedula = ? OR ur.first_name LIKE ?
            LIMIT 1";

        $stmt = $conn->prepare($sql);
        $searchLike = "%$search%";
        $stmt->bind_param('ss', $search, $searchLike);
        $stmt->execute();
        $result = $stmt->get_result();
        $userData = $result->fetch_assoc();

        // Asegurarse de que los campos no encontrados sean vacíos
        $userData = [
            'cedula' => isset($userData['cedula']) ? $userData['cedula'] : '',
            'first_name' => isset($userData['first_name']) ? $userData['first_name'] : '',
            'second_name' => isset($userData['second_name']) ? $userData['second_name'] : '',
            'first_last' => isset($userData['first_last']) ? $userData['first_last'] : '',
            'second_last' => isset($userData['second_last']) ? $userData['second_last'] : '',
            'program' => isset($userData['program']) ? $userData['program'] : '',
            'correo' => isset($userData['correo']) ? $userData['correo'] : '',
            'id_formulario' => isset($userData['id_formulario']) ? $userData['id_formulario'] : '',
            'nivel' => isset($userData['nivel']) ? $userData['nivel'] : '',
            'level' => isset($userData['level']) ? $userData['level'] : '',
        ];
    }
} catch (Exception $e) {
    error_log("Error en la consulta: " . $e->getMessage());
    echo "<div class='alert alert-danger'>Error al cargar los datos.</div>";
    exit;
}
?>



<div class="container-fluid p-4">
    <!-- Formulario de búsqueda -->
    <div class="card mb-4">
        <div class="card-header bg-indigo-dark text-white">
            <h5 class="card-title mb-0 text-center">
                <i class="bi bi-search"></i> Buscar resultado de la prueba de saberes
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="w-100">
                <div class="row justify-content-center">
                    <div class="col-12 col-md-8 col-lg-6">
                        <div class="input-group input-group-lg">
                            <input type="text"
                                class="form-control form-control-lg text-center"
                                name="search"
                                placeholder="Ingrese número de documento"
                                value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
                                required>
                            <button type="submit" class="btn bg-indigo-dark text-white px-4">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <?php if (isset($_GET['search']) && !empty($_GET['search'])): ?>
        <?php if ($userData): ?>
            <!-- Mostrar información del usuario -->
            <div class="card">
                <div class="card-header bg-indigo-dark text-white">
                    <h5 class="card-title mb-0 text-center">
                        <i class="bi bi-person-badge"></i> Información del Usuario
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">

                        <style>
                            .info-label {
                                font-weight: bold;
                                white-space: nowrap;
                            }

                            .info-value {
                                white-space: nowrap;
                            }

                            .info-row {
                                display: flex;
                                justify-content: space-between;
                                margin-bottom: 0.5rem;
                                flex-wrap: wrap;
                            }

                            .card-header {
                                background-color: #2c2e70;
                            }
                        </style>

                        <!-- Información Personal -->
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100 border-0 w-100">
                                <div class="card-header text-white text-center rounded-top">
                                    <h5 class="mb-0 py-2">Información Personal</h5>
                                </div>
                                <div class="card-body px-4 py-3">
                                    <ul class="list-group w-100">
                                        <li class="list-group-item"><b>Cédula: </b><?php echo htmlspecialchars($userData['cedula']); ?></li>
                                        <li class="list-group-item"><b>Nombre: </b><?php echo htmlspecialchars(trim($userData['first_name'] . ' ' . $userData['second_name'] . ' ' . $userData['first_last'] . ' ' . $userData['second_last'])); ?></li>
                                        <li class="list-group-item"><b>Correo electrónico: </b><?php echo htmlspecialchars($userData['correo']); ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Información Académica -->
                        <div class="col-md-6 mb-4">
                            <div class="card shadow-sm h-100 border-0">
                                <div class="card-header text-white text-center rounded-top">
                                    <h5 class="mb-0 py-2">Información Académica</h5>
                                </div>
                                <div class="card-body px-4 py-3 w-100">
                                    <ul class="list-group w-100">
                                        <li class="list-group-item"><b>Programa: </b><?php echo htmlspecialchars($userData['program']); ?></li>
                                        <li class="list-group-item"><b>Nivel de Preferencia: </b> <span class="badge bg-info text-white"><?php echo htmlspecialchars($userData['level']); ?></span></li>
                                        <li class="list-group-item"><b>Puntaje: </b><?php echo htmlspecialchars($userData['nivel']); ?></li>
                                        <li class="list-group-item"><b>Nivel Actual: </b> <?php
                                                                                            $nivel = $userData['nivel'];
                                                                                            if (isset($nivel)) {
                                                                                                if ($nivel >= 0 && $nivel <= 5) {
                                                                                                    echo '<span class="badge bg-danger">Básico</span>';
                                                                                                } elseif ($nivel >= 6 && $nivel <= 10) {
                                                                                                    echo '<span class="badge bg-warning text-dark">Intermedio</span>';
                                                                                                } elseif ($nivel >= 11 && $nivel <= 15) {
                                                                                                    echo '<span class="badge bg-success">Avanzado</span>';
                                                                                                }
                                                                                            } else {
                                                                                                echo '<span class="badge bg-secondary">No asignado</span>';
                                                                                            }
                                                                                            ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>


                        <!-- Botón de eliminación (ancho completo) -->
                        <div class="col-12 text-center mt-3">
                            <button class="btn btn-danger btn-lg"
                                data-bs-toggle="modal"
                                data-bs-target="#deleteModal<?php echo $userData['cedula']; ?>">
                                <i class="bi bi-trash"></i> Eliminar Registro
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de eliminación -->
            <div class="modal fade" id="deleteModal<?php echo $userData['cedula']; ?>" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-magenta-dark text-white">
                            <h5 class="modal-title">
                                <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <p class="lead">¿Está seguro de eliminar el registro de:</p>
                            <p class="font-weight-bold"><?php echo htmlspecialchars(trim($userData['first_name'] . ' ' . $userData['first_last'])); ?></p>

                            <div class="alert alert-warning">
                                <p class="mb-2"><strong>Código de seguridad:</strong></p>
                                <div class="input-group mb-2">
                                    <input type="text" id="securityCode<?php echo $userData['cedula']; ?>"
                                        class="form-control" readonly>
                                    <button class="btn btn-outline-secondary" type="button"
                                        onclick="copiarCodigo('securityCode<?php echo $userData['cedula']; ?>')">
                                        <i class="bi bi-clipboard"></i>
                                    </button>
                                </div>
                                <p class="mb-2">Este código cambiará en: <span id="codeTimer<?php echo $userData['cedula']; ?>">15</span> segundos</p>
                                <p class="mb-2"><strong>Ingrese el código para confirmar:</strong></p>
                                <input type="text" id="confirmCode<?php echo $userData['cedula']; ?>"
                                    class="form-control" placeholder="Ingrese el código">
                            </div>

                            <p class="text-muted">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-danger" id="deleteBtn<?php echo $userData['cedula']; ?>"
                                disabled>
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                $(document).ready(function() {
                    const modalId = <?php echo json_encode($userData['cedula']); ?>;
                    let securityCode = '';
                    let timer = 15;
                    let interval;

                    // Función para generar código aleatorio
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
                            $("#confirmCode" + modalId).val('');
                            $("#deleteBtn" + modalId).prop('disabled', true);
                        }
                    }

                    // Inicializar cuando se abre el modal
                    $("#deleteModal" + modalId).on('shown.bs.modal', function() {
                        securityCode = generateCode();
                        $("#securityCode" + modalId).val(securityCode);
                        timer = 15;
                        $("#codeTimer" + modalId).text(timer);
                        $("#confirmCode" + modalId).val('');
                        $("#deleteBtn" + modalId).prop('disabled', true);

                        clearInterval(interval);
                        interval = setInterval(updateTimer, 1000);
                    });

                    // Detener temporizador al cerrar
                    $("#deleteModal" + modalId).on('hidden.bs.modal', function() {
                        clearInterval(interval);
                        $("#confirmCode" + modalId).val('');
                        $("#deleteBtn" + modalId).prop('disabled', true);
                    });

                    // Verificar código ingresado
                    $("#confirmCode" + modalId).on('input', function() {
                        const inputCode = $(this).val();
                        $("#deleteBtn" + modalId).prop('disabled', inputCode !== securityCode);
                    });

                    // Manejar eliminación
                    $("#deleteBtn" + modalId).click(function() {
                        Swal.fire({
                            title: '¿Está seguro?',
                            text: "Esta acción eliminará permanentemente el registro",
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#d33',
                            cancelButtonColor: '#3085d6',
                            confirmButtonText: 'Sí, eliminar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $.ajax({
                                    url: 'components/listTest/deleteUsuario.php',
                                    type: 'POST',
                                    data: {
                                        cedula: modalId
                                    },
                                    dataType: 'json',
                                    success: function(response) {
                                        if (response.success) {
                                            Swal.fire({
                                                title: '¡Eliminado!',
                                                text: 'El registro ha sido eliminado correctamente.',
                                                icon: 'success'
                                            }).then(() => {
                                                window.location.href = '?page=editTestList';
                                            });
                                        } else {
                                            Swal.fire({
                                                title: 'Error',
                                                text: 'No se pudo eliminar el registro.',
                                                icon: 'error'
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            title: 'Error',
                                            text: 'Hubo un problema con la conexión.',
                                            icon: 'error'
                                        });
                                    }
                                });
                            }
                        });
                    });
                });

                // Agregar esta función para copiar el código
                function copiarCodigo(inputId) {
                    const codigoInput = document.getElementById(inputId);
                    codigoInput.select();
                    document.execCommand('copy');

                    // Mostrar tooltip de copiado
                    Swal.fire({
                        text: '¡Código copiado!',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 1500,
                        timerProgressBar: true,
                        icon: 'success'
                    });
                }
            </script>

        <?php else: ?>
            <div class="alert alert-warning text-center">
                <i class="bi bi-exclamation-triangle"></i>
                No se encontró ningún usuario con el documento especificado.
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script>
    function eliminarRegistro(cedula) {
        Swal.fire({
            title: '¿Está seguro?',
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
                    url: 'components/listTest/deleteUsuario.php',
                    type: 'POST',
                    data: {
                        cedula: cedula
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                title: '¡Eliminado!',
                                text: 'El registro ha sido eliminado.',
                                icon: 'success'
                            }).then(() => {
                                window.location.href = '?page=editTestList';
                            });
                        } else {
                            Swal.fire({
                                title: 'Error',
                                text: 'No se pudo eliminar el registro.',
                                icon: 'error'
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            title: 'Error',
                            text: 'Hubo un problema con la conexión.',
                            icon: 'error'
                        });
                    }
                });
            }
        });
    }
</script>