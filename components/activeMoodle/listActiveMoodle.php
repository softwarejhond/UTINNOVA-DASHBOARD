<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

$sql = "SELECT g.*, ur.* FROM groups g 
    LEFT JOIN user_register ur ON g.number_id = ur.number_id";
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

// Obtener datos únicos para los filtros
$departamentos = ['BOYACÁ', 'CUNDINAMARCA'];
$programas = [];
$modalidades = [];
$sedes = [];

foreach ($data as $row) {
    $sede = $row['headquarters'];
    if (!in_array($sede, $sedes)) {
        $sedes[] = $sede;
    }
    if (!in_array($row['program'], $programas)) {
        $programas[] = $row['program'];
    }
    if (!in_array($row['mode'], $modalidades)) {
        $modalidades[] = $row['mode'];
    }
}

// Ordenar los arrays para mejor visualización
sort($sedes);
sort($programas);
sort($modalidades);

?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function copiarCodigo(id) {
        const codigoInput = document.getElementById('securityCode' + id);
        navigator.clipboard.writeText(codigoInput.value).then(() => {
            const copyBtn = document.getElementById('copyBtn' + id);
            const originalContent = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="bi bi-check2"></i> Copiado';
            copyBtn.classList.replace('btn-outline-secondary', 'btn-success');
            setTimeout(function() {
                copyBtn.innerHTML = originalContent;
                copyBtn.classList.replace('btn-success', 'btn-outline-secondary');
            }, 1500);
        }).catch(err => {
            console.error('Error al copiar: ', err);
        });
    }
</script>

<div class="row p-3 mb-1">
    <b class="text-left mb-1"><i class="bi bi-filter-circle"></i> Filtrar beneficiario</b>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-map"></i> Departamento</div>
        <div class="card filter-card card-department" data-icon="📍">
            <div class="card-body">
                <select id="filterDepartment" class="form-select">
                    <option value="">Todos los departamentos</option>
                    <option value="BOYACÁ">BOYACÁ</option>
                    <option value="CUNDINAMARCA">CUNDINAMARCA</option>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-building"></i> Sede</div>
        <div class="card filter-card card-headquarters" data-icon="🏫">
            <div class="card-body">
                <select id="filterHeadquarters" class="form-select">
                    <option value="">Todas las sedes</option>
                    <?php foreach ($sedes as $sede): ?>
                        <option value="<?= htmlspecialchars($sede) ?>"><?= htmlspecialchars($sede) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-mortarboard"></i> Programa</div>
        <div class="card filter-card card-program" data-icon="🎓">
            <div class="card-body">
                <select id="filterProgram" class="form-select">
                    <option value="">Todos los programas</option>
                    <?php foreach ($programas as $programa): ?>
                        <option value="<?= htmlspecialchars($programa) ?>"><?= htmlspecialchars($programa) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-sm-12 col-lg-3">
        <div class="filter-title"><i class="bi bi-laptop"></i> Modalidad</div>
        <div class="card filter-card card-mode" data-icon="💻">
            <div class="card-body">
                <select id="filterMode" class="form-select">
                    <option value="">Todas las modalidades</option>
                    <option value="Virtual">Virtual</option>
                    <option value="Presencial">Presencial</option>
                </select>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="table-responsive">
        <button id="exportarExcel" class="btn btn-success mb-3"
            onclick="window.location.href='components/registerMoodle/export_excel_enrolled.php?action=export'">
            <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
        </button>
        <table id="listaInscritos" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr class="text-center">
                    <th>Tipo ID</th>
                    <th>Numero de ID</th>
                    <th>Nombre completo</th>
                    <th>Telefono</th>
                    <th>Correo personal</th>
                    <th>Correo institucional</th>
                    <th>Departamento</th>
                    <th>Sede</th>
                    <th>Modalidad</th>
                    <th>Bootcamp</th>
                    <th>Ingles Nivelatorio</th>
                    <th>English Code</th>
                    <th>Habilidades</th>
                    <th>Desmatricular</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                    <tr data-department="<?= htmlspecialchars($row['department']) ?>"
                        data-headquarters="<?= htmlspecialchars($row['headquarters']) ?>"
                        data-program="<?= htmlspecialchars($row['program']) ?>"
                        data-mode="<?= htmlspecialchars($row['mode']) ?>">


                        <td><?php echo htmlspecialchars($row['type_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                        <td style="width: 300px; min-width: 300px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                        <td><?php echo htmlspecialchars(string: $row['first_phone']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['institutional_email']); ?></td>
                        <td>
                            <?php
                            $departamento = htmlspecialchars($row['department']);
                            if ($departamento === 'CUNDINAMARCA') {
                                echo "<button class='btn bg-lime-light w-100'><b>{$departamento}</b></button>"; // Botón verde para CUNDINAMARCA
                            } elseif ($departamento === 'BOYACÁ') {
                                echo "<button class='btn bg-indigo-light w-100'><b>{$departamento}</b></button>"; // Botón azul para BOYACÁ
                            } else {
                                echo "<span>{$departamento}</span>"; // Texto normal para otros valores
                            }
                            ?>
                        </td>
                        <td><b class="text-center"><?php echo htmlspecialchars($row['headquarters']); ?></b></td>
                        <td><?php echo htmlspecialchars($row['mode']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_bootcamp'] . ' - ' . $row['bootcamp_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_leveling_english'] . ' - ' . $row['leveling_english_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_english_code'] . ' - ' . $row['english_code_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['id_skills'] . ' - ' . $row['skills_name']); ?></td>
                        <td class="text-center">
                            <?php
                            switch($rol) {
                                case 'Academico':
                                case 'Administrador':
                                    echo '<button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal' . $row['number_id'] . '">
                                            <i class="bi bi-trash"></i>
                                          </button>';
                                    break;
                                default:
                                    echo '<button class="btn btn-danger btn-sm" 
                                            data-toggle="popover" 
                                            data-placement="top"
                                            title="Acceso Denegado" 
                                            data-content="No tienes permisos para realizar esta acción">
                                            <i class="bi bi-slash-circle"></i>
                                          </button>';
                                    echo '<script>
                                        $(document).ready(function(){
                                            $("[data-toggle=popover]").popover();
                                        });
                                    </script>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<?php
// Reiniciar el puntero de resultados para los modales
$result = $conn->query($sql);
while ($row = mysqli_fetch_assoc($result)) {
    $number_id = $row['number_id'];
    $full_name = $row['full_name'];
?>
    <div class="modal fade" id="deleteModal<?php echo $number_id; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $number_id; ?>" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-magenta-dark text-white item-align-center">
                    <h5 class="modal-title text-center" id="deleteModalLabel<?php echo $number_id; ?>">
                        <i class="bi bi-exclamation-circle"></i> ATENCIÓN
                    </h5>
                </div>
                <div class="modal-body">
                    <h5>Nombre: <strong><?php echo htmlspecialchars($full_name); ?></strong></h5>
                    <h5>Número ID: <strong><?php echo htmlspecialchars($number_id); ?></strong></h5>

                    <hr>
                    <div class="card shadow-lg p-3 mb-5 bg-body-tertiary rounded">
                        <p class="mb-1">Para confirmar la eliminación, ingresa el código de seguridad:</p>
                        <div class="code-container mt-2 mb-3 p-2 bg-light">
                            <div class="input-group">
                                <input type="text" class="form-control security-code" id="securityCode<?php echo $number_id; ?>"
                                    readonly value="" style="font-family: monospace; letter-spacing: 3px; text-align: center; font-weight: bold;">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary bg-indigo-dark" type="button" id="copyBtn<?php echo $number_id; ?>"
                                        onclick="copiarCodigo('<?php echo $number_id; ?>')">
                                        <i class="bi bi-clipboard"></i> Copiar
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted text-center d-block mt-1">Este código cambiará en <span id="codeTimer<?php echo $number_id; ?>">15</span> segundos</small>
                        </div>
                        <div class="form-group">
                            <label for="confirmCode<?php echo $number_id; ?>">Ingresa el código</label>
                            <input type="text" class="form-control-lg text-center w-100" id="confirmCode<?php echo $number_id; ?>" placeholder="Código">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn bg-magenta-dark text-white" id="deleteBtn<?php echo $number_id; ?>" disabled>Eliminar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            const modalId = <?php echo json_encode($number_id); ?>;
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
                $("#codeTimer" + modalId).text(timer);

                if (timer <= 0) {
                    timer = 15;
                    securityCode = generateCode();
                    $("#securityCode" + modalId).val(securityCode);
                }
            }

            $("#deleteModal" + modalId).on('shown.bs.modal', function() {
                securityCode = generateCode();
                $("#securityCode" + modalId).val(securityCode);
                timer = 15;
                $("#codeTimer" + modalId).text(timer);
                clearInterval(interval);
                interval = setInterval(updateTimer, 1000);
            });

            $("#deleteModal" + modalId).on('hidden.bs.modal', function() {
                clearInterval(interval);
                $("#confirmCode" + modalId).val('');
                $("#deleteBtn" + modalId).prop('disabled', true);
            });

            $("#confirmCode" + modalId).on('input', function() {
                const inputCode = $(this).val();
                $("#deleteBtn" + modalId).prop('disabled', inputCode !== securityCode);
            });

            $("#deleteBtn" + modalId).click(function() {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción eliminará permanentemente la matrícula del usuario con ID " + modalId,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Aquí deberás implementar la llamada AJAX a tu endpoint de eliminación
                        $.ajax({
                            url: 'components/activeMoodle/deleteMatricula.php',
                            type: 'POST',
                            data: {
                                number_id: modalId
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire(
                                        '¡Eliminado!',
                                        'La matrícula ha sido eliminada correctamente.',
                                        'success'
                                    ).then(() => {
                                        location.reload();
                                    });
                                } else {
                                    Swal.fire(
                                        'Error',
                                        response.message || 'Ocurrió un error al eliminar la matrícula.',
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