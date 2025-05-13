<?php
// Incluir la conexión a la base de datos
require_once __DIR__ . '/../../controller/conexion.php';

// Función para obtener todos los códigos QR
function getQRCodes($conn)
{
    $sql = "SELECT * FROM qr_codes ORDER BY created_at DESC";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Obtener los códigos QR
$qrCodes = getQRCodes($conn);
?>

<!-- Botón para agregar nuevo código QR -->
<div class="mb-4">
    <button type="button" class="btn bg-magenta-dark" data-bs-toggle="modal" data-bs-target="#addQRModal">
        <i class="bi bi-plus-circle"></i> Nuevo Código QR
    </button>
</div>

<!-- Grid de cards para códigos QR -->
<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4">
    <?php foreach ($qrCodes as $qr): ?>
        <div class="col">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-teal-dark text-white">
                    <h5 class="card-title mb-0 text-center">
                        <?php echo htmlspecialchars($qr['title']); ?>
                    </h5>
                </div>
                <div class="card-body d-flex flex-column align-items-center">
                    <div class="qr-image mb-3">
                        <img src="img/qrcodes/<?php echo htmlspecialchars($qr['image_filename']); ?>"
                            class="img-fluid"
                            alt="Código QR"
                            style="max-width: 200px;">
                    </div>
                    <div class="separator w-100 border-bottom my-2"></div>
                    <div class="qr-info w-100">
                        <p class="card-text small text-muted mb-2">
                            <i class="bi bi-link-45deg"></i>
                            <?php echo htmlspecialchars($qr['url']); ?>
                        </p>
                        <div class="separator w-100 border-bottom my-2"></div>
                        <p class="card-text small mb-0">
                            <i class="bi bi-calendar3"></i>
                            <?php echo date('d/m/Y H:i', strtotime($qr['created_at'])); ?>
                        </p>
                    </div>
                </div>
                <div class="card-footer bg-transparent">
                    <div class="d-flex justify-content-between gap-2">
                        <button class="btn bg-teal-dark text-white btn-sm flex-grow-1 download-qr" 
                                data-filename="<?php echo htmlspecialchars($qr['image_filename']); ?>">
                            <i class="bi bi-download"></i> Descargar QR
                        </button>
                        <button class="btn btn-danger btn-sm delete-qr" 
                                data-id="<?php echo $qr['id']; ?>"
                                data-title="<?php echo htmlspecialchars($qr['title']); ?>"
                                data-filename="<?php echo htmlspecialchars($qr['image_filename']); ?>">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Agregar estilos CSS -->
<style>
    .card {
        transition: transform 0.2s;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .card-header {
        border-bottom: none;
    }

    .card-footer {
        border-top: none;
    }

    .qr-info {
        text-align: center;
    }

    .separator {
        opacity: 0.2;
    }

    .card-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .btn {
        transition: all 0.3s;
    }

    .btn:hover {
        transform: scale(1.05);
    }
</style>

<!-- Modal para agregar nuevo código QR -->
<div class="modal fade" id="addQRModal" tabindex="-1" aria-labelledby="addQRModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addQRModalLabel">Nuevo Código QR</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addQRForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="title" class="form-label">Título</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="url" class="form-label">URL</label>
                        <input type="url" class="form-control" id="url" name="url" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver código QR -->
<div class="modal fade" id="viewQRModal" tabindex="-1" aria-labelledby="viewQRModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewQRModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img id="qrImage" src="" alt="Código QR" class="img-fluid mb-3">
                <p id="qrUrl" class="mb-3"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <a id="downloadQR" href="#" class="btn bg-indigo-dark">
                    <i class="bi bi-download"></i> Descargar QR
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        // Manejar el envío del formulario de nuevo QR
        $('#addQRForm').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'components/qrcodes/save_qr.php',
                method: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            title: '¡Éxito!',
                            text: 'El código QR ha sido generado correctamente',
                            icon: 'success',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#3085d6'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('#addQRModal').modal('hide');
                                location.reload();
                            }
                        });
                    } else {
                        Swal.fire({
                            title: 'Error',
                            text: response.error || 'Error desconocido al guardar el código QR',
                            icon: 'error',
                            confirmButtonText: 'Aceptar',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error AJAX:', status, error);
                    Swal.fire({
                        title: 'Error',
                        text: 'Error en la solicitud: ' + error,
                        icon: 'error',
                        confirmButtonText: 'Aceptar',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        });

        $('.download-qr').on('click', function() {
            const filename = $(this).data('filename');
            const qrUrl = `img/qrcodes/${filename}`;

            // Crear elemento de descarga
            const link = document.createElement('a');
            link.href = qrUrl;
            link.download = filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        // Manejar eliminación de QR
        $('.delete-qr').on('click', function() {
            const id = $(this).data('id');
            const title = $(this).data('title');
            const filename = $(this).data('filename');

            Swal.fire({
                title: '¿Estás seguro?',
                text: `¿Deseas eliminar el código QR "${title}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Segunda confirmación
                    Swal.fire({
                        title: 'Confirmar eliminación',
                        text: 'Esta acción no se puede deshacer',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, eliminar definitivamente',
                        cancelButtonText: 'Cancelar'
                    }).then((secondResult) => {
                        if (secondResult.isConfirmed) {
                            // Proceder con la eliminación
                            $.ajax({
                                url: 'components/qrcodes/delete_qr.php',
                                method: 'POST',
                                data: {
                                    id: id,
                                    filename: filename
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            title: '¡Eliminado!',
                                            text: 'El código QR ha sido eliminado correctamente',
                                            icon: 'success',
                                            confirmButtonColor: '#3085d6'
                                        }).then(() => {
                                            location.reload();
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            text: response.error || 'Error al eliminar el código QR',
                                            icon: 'error',
                                            confirmButtonColor: '#d33'
                                        });
                                    }
                                }
                            });
                        }
                    });
                }
            });
        });
    });
</script>