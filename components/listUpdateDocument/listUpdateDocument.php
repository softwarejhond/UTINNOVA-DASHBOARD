<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?php
// Parámetros de paginación
$limit = 30; // Número de registros por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Parámetro de búsqueda
$search = isset($_GET['search']) ? $_GET['search'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_documento'])) {
    $numberId = $_POST['numberId'];
    $frontFile = $_FILES['frontId'];
    $backFile = $_FILES['backId'];

    if ($frontFile['error'] === UPLOAD_ERR_OK && $backFile['error'] === UPLOAD_ERR_OK) {
        $frontPath = "../files/idFilesFront/" . basename($frontFile['name']);
        $backPath = "../files/idFilesBack/" . basename($backFile['name']);

        if (move_uploaded_file($frontFile['tmp_name'], $frontPath) && move_uploaded_file($backFile['tmp_name'], $backPath)) {
            $sqlUpdate = "UPDATE user_register SET file_front_id = ?, file_back_id = ? WHERE number_id = ?";
            $stmt = $conn->prepare($sqlUpdate);
            $frontFileName = basename($frontFile['name']);
            $backFileName = basename($backFile['name']);
            $stmt->bind_param("ssi", $frontFileName, $backFileName, $numberId);

            if ($stmt->execute()) {
                echo "<script>Swal.fire('¡Éxito!', 'Documentos actualizados correctamente.', 'success');</script>";
            } else {
                echo "<script>Swal.fire('Error', 'Error al actualizar los documentos.', 'error');</script>";
            }
        } else {
            echo "<script>Swal.fire('Error', 'Error al subir los archivos.', 'error');</script>";
        }
    } else {
        echo "<script>Swal.fire('Error', 'Debes seleccionar ambos archivos.', 'error');</script>";
    }
}

$sql = "SELECT user_register.*, municipios.municipio, departamentos.departamento
    FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    WHERE departamentos.id_departamento = 11
    AND user_register.status = '1' 
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)
    ORDER BY user_register.first_name ASC
    LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$searchParam = "%$search%";
$stmt->bind_param('ssii', $searchParam, $searchParam, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$data = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $birthday = new DateTime($row['birthdate']);
        $now = new DateTime();
        $row['age'] = $now->diff($birthday)->y;
        $data[] = $row;
    }
}
// Obtener el total de registros para la paginación
$totalSql = "SELECT COUNT(*) as total FROM user_register
    INNER JOIN municipios ON user_register.municipality = municipios.id_municipio
    INNER JOIN departamentos ON user_register.department = departamentos.id_departamento
    WHERE departamentos.id_departamento = 11
    AND user_register.status = '1' AND user_register.statusAdmin = ''
    AND (user_register.first_name LIKE ? OR user_register.number_id LIKE ?)";
$stmtTotal = $conn->prepare($totalSql);
$stmtTotal->bind_param('ss', $searchParam, $searchParam);
$stmtTotal->execute();
$totalResult = $stmtTotal->get_result();
$totalRows = $totalResult->fetch_assoc()['total'];
$totalPages = ceil($totalRows / $limit);
?>

<div class="container mt-3">
    <div class="row justify-content-between align-items-center">
        <!-- Formulario de búsqueda -->
        <div class="col-md-6 col-sm-12 mb-3 text-center">
            <p class="mb-2">Buscar usuarios</p>
            <form method="GET" action="" class="d-flex">
                <input type="text" class="form-control me-2 text-center" name="search" placeholder="Buscar por nombre o ID" value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-sm bg-indigo-dark text-white w-100"><i class="bi bi-search"></i> Buscar</button>
            </form>
        </div>

        <!-- Indicador y paginación -->
        <div class="col-md-6 col-sm-12 text-end">
            <p class="h6 pb-2 mb-2 text-indigo-dark"><b>Navega entre páginas para ver más registros.</b></p>
            <nav aria-label="Page navigation">
                <ul class="pagination justify-content-end">
                    <!-- Botón Anterior -->
                    <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                        <a class="page-link btn-sm" href="?page=<?php echo max(1, $page - 1); ?>&search=<?php echo htmlspecialchars($search); ?>">
                            &laquo; Anterior
                        </a>
                    </li>

                    <!-- Primera página -->
                    <?php if ($page > 2): ?>
                        <li class="page-item"><a class="page-link btn-sm" href="?page=1&search=<?php echo htmlspecialchars($search); ?>">1</a></li>
                        <?php if ($page > 3): ?>
                            <li class="page-item disabled"><span class="page-link btn-sm">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Páginas visibles -->
                    <?php for ($i = max(1, $page - 1); $i <= min($totalPages, $page + 1); $i++): ?>
                        <li class="page-item <?php if ($i == $page) echo 'active'; ?>">
                            <a class="page-link btn-sm" href="?page=<?php echo $i; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $i; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Última página -->
                    <?php if ($page < $totalPages - 1): ?>
                        <?php if ($page < $totalPages - 2): ?>
                            <li class="page-item disabled"><span class="page-link btn-sm">...</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link btn-sm" href="?page=<?php echo $totalPages; ?>&search=<?php echo htmlspecialchars($search); ?>"><?php echo $totalPages; ?></a></li>
                    <?php endif; ?>

                    <!-- Botón Siguiente -->
                    <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                        <a class="page-link btn-sm" href="?page=<?php echo min($totalPages, $page + 1); ?>&search=<?php echo htmlspecialchars($search); ?>">
                            Siguiente &raquo;
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>
</div>

<div class="table-responsive">
    <table id="listaInscritos" class="table table-hover table-bordered">
        <thead class="thead-dark text-center">
            <tr>
                <th>Tipo ID</th>
                <th>Número</th>
                <th>Nombre</th>
                <th>Edad</th>
                <th>Correo</th>
                <th>Identificación actual</th>
                <th>Actualizar identificación</th>
            </tr>
        </thead>
        <tbody class="text-center">
            <?php foreach ($data as $row): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['typeID']); ?></td>
                    <td><?php echo htmlspecialchars($row['number_id']); ?></td>
                    <td>
                        <?php
                        echo htmlspecialchars($row['first_name']) . ' ' .
                            htmlspecialchars($row['second_name']) . ' ' .
                            htmlspecialchars($row['first_last']) . ' ' .
                            htmlspecialchars($row['second_last']);
                        ?>
                    </td>
                    <td><?php echo $row['age']; ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalID_<?php echo $row['number_id']; ?>">
                            <i class="bi bi-card-image"></i>
                        </button>

                        <!-- Modal para mostrar las imágenes -->
                        <div class="modal fade" id="modalID_<?php echo $row['number_id']; ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-indigo-dark">
                                        <h5 class="modal-title">Imágenes de Identificación</h5>
                                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body position-relative" style="overflow: visible;">
                                        <div class="row">
                                            <!-- Frente del documento -->
                                            <div class="col-12 mb-4 text-center">
                                                <h6>Frente del documento</h6>
                                                <div class="position-relative overflow-visible">
                                                    <img id="idImageFront_<?php echo $row['number_id']; ?>"
                                                        src="../files/idFilesFront/<?php echo htmlspecialchars($row['file_front_id']); ?>"
                                                        class="img-fluid w-100 zoomable"
                                                        style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                        alt="Frente ID"
                                                        onclick="toggleZoom('idImageFront_<?php echo $row['number_id']; ?>')">
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageFront_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                </div>
                                            </div>

                                            <!-- Reverso del documento -->
                                            <div class="col-12 text-center">
                                                <h6>Reverso del documento</h6>
                                                <div class="position-relative overflow-visible">
                                                    <img id="idImageBack_<?php echo $row['number_id']; ?>"
                                                        src="../files/idFilesBack/<?php echo htmlspecialchars($row['file_back_id']); ?>"
                                                        class="img-fluid w-100 zoomable"
                                                        style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                        alt="Reverso ID"
                                                        onclick="toggleZoom('idImageBack_<?php echo $row['number_id']; ?>')">
                                                </div>
                                                <div class="mt-2">
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', -90)">↺ Rotar Izquierda</button>
                                                    <button class="btn btn-primary" onclick="rotateImage('idImageBack_<?php echo $row['number_id']; ?>', 90)">↻ Rotar Derecha</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <script>
                            // Verificar si la variable ya existe en el ámbito global
                            if (typeof window.imageTransforms === 'undefined') {
                                window.imageTransforms = {};
                            }

                            function rotateImage(imageId, degrees) {
                                if (!window.imageTransforms[imageId]) {
                                    window.imageTransforms[imageId] = {
                                        rotation: 0,
                                        scale: 1
                                    };
                                }
                                window.imageTransforms[imageId].rotation += degrees;
                                applyTransform(imageId);
                            }

                            function toggleZoom(imageId) {
                                if (!window.imageTransforms[imageId]) {
                                    window.imageTransforms[imageId] = {
                                        rotation: 0,
                                        scale: 1
                                    };
                                }
                                window.imageTransforms[imageId].scale = window.imageTransforms[imageId].scale === 1 ? 2 : 1;
                                applyTransform(imageId);
                            }

                            function applyTransform(imageId) {
                                let imgElement = document.getElementById(imageId);
                                if (imgElement) {
                                    let {
                                        rotation,
                                        scale
                                    } = window.imageTransforms[imageId];
                                    imgElement.style.transform = `rotate(${rotation}deg) scale(${scale})`;
                                }
                            }
                        </script>

                    </td>
                    <td>
                        <button class="btn btn-primary" onclick="mostrarModal(<?php echo $row['number_id']; ?>)"><i class="bi bi-arrow-left-right"></i></button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script>
    function mostrarModal(numberId) {
        Swal.fire({
            title: 'Actualizar Documento',
            html: `
                <form id="formActualizar" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="actualizar_documento" value="1">
                    <input type="hidden" name="numberId" value="${numberId}">
                    <div class="mb-3">
                        <label class="form-label">Frente del documento</label>
                        <input type="file" name="frontId" class="form-control" accept="image/*" required onchange="previewImage(this, 'previewFront')">
                        <img id="previewFront" src="#" alt="" style="max-width: 100px; max-height: 100px; margin: 10px auto 0; display: none;">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reverso del documento</label>
                        <input type="file" name="backId" class="form-control" accept="image/*" required onchange="previewImage(this, 'previewBack')">
                        <img id="previewBack" src="#" alt="" style="max-width: 100px; max-height: 100px; margin: 10px auto 0; display: none;">
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            preConfirm: () => {
                const form = document.getElementById('formActualizar');
                const frontFile = form.querySelector('input[name="frontId"]').files[0];
                const backFile = form.querySelector('input[name="backId"]').files[0];

                if (!frontFile || !backFile) {
                    Swal.showValidationMessage('Por favor seleccione ambos archivos');
                    return false;
                }

                const formData = new FormData(form);

                // Mostrar SweetAlert de carga con barra de progreso
                Swal.fire({
                    title: 'Subiendo imágenes...',
                    html: `
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 0%;" id="progressBar"></div>
                        </div>
                        <p id="progressText">0% completado</p>
                    `,
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();

                        const xhr = new XMLHttpRequest();
                        xhr.open('POST', window.location.href, true);
                        xhr.upload.onprogress = (event) => {
                            if (event.lengthComputable) {
                                const percentComplete = (event.loaded / event.total) * 100;
                                const progressBar = document.getElementById('progressBar');
                                const progressText = document.getElementById('progressText');
                                progressBar.style.width = percentComplete + '%';
                                progressText.textContent = Math.round(percentComplete) + '% completado';
                            }
                        };
                        xhr.onload = () => {
                            if (xhr.status === 200) {
                                Swal.fire('¡Éxito!', 'Documentos actualizados correctamente', 'success')
                                    .then(() => {
                                        window.location.reload();
                                    });
                            } else {
                                Swal.fire('Error', 'Hubo un problema al actualizar los documentos', 'error');
                            }
                        };
                        xhr.onerror = () => {
                            Swal.fire('Error', 'Hubo un problema con la solicitud', 'error');
                        };
                        xhr.send(formData);
                    }
                });

                return false; // Prevenir el cierre automático del SweetAlert
            }
        });
    }

    function previewImage(input, previewId) {
        const preview = document.getElementById(previewId);
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>