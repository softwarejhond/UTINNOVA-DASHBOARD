<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .sidebar-tutorial {
        background: #f8f9fa;
        border-radius: 14px;
        border: 1.5px solid #d1d5db;
        padding: 22px 16px;
        min-height: 500px;
        height: 500px;
        position: sticky;
        top: 0;
        box-shadow: 0 2px 12px #0002;
        display: flex;
        flex-direction: column;
    }

    .btn-agregar {
        background: #ec008c;
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 1.15rem;
        font-weight: 600;
        margin-bottom: 20px;
        letter-spacing: 1px;
        transition: background 0.2s;
    }

    .btn-agregar:hover {
        background: #f5a6c2;
        color: #fff;
    }

    .input-buscar {
        border: 1.5px solid #b0bec5;
        border-radius: 8px;
        background: #e3f2fd;
        font-size: 1rem;
        margin-bottom: 20px;
        padding: 8px 12px;
        width: 100%;
        font-family: 'Segoe UI', Arial, sans-serif;
        transition: border 0.2s;
    }

    .input-buscar:focus {
        border-color: #00796b;
        outline: none;
    }

    .video-list-scroll {
        flex: 1 1 auto;
        overflow-y: auto;
        padding-right: 4px;
    }

    .video-item-container {
        display: flex;
        align-items: center;
    }

    .btn-video {
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 1rem;
        margin-bottom: 0 !important; /* Anula el margin-bottom anterior */
        width: 100%;
        text-align: left;
        padding: 12px 18px;
        color: #222;
        font-weight: 500;
        box-shadow: 0 1px 2px #0001;
        transition: background 0.2s, border 0.2s;
        cursor: pointer;
    }

    .btn-video:hover,
    .btn-video.active {
        background: #e0f2f1;
        border-color: #00796b;
        color: #00796b;
    }

    .video-actions {
        flex-shrink: 0;
        width: 40px;
    }

    .video-actions .btn {
        width: 32px;
        height: 32px;
        padding: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-thumb {
        background: #b0bec5;
        border-radius: 6px;
    }

    ::-webkit-scrollbar-track {
        background: #f8f9fa;
        border-radius: 6px;
    }

    .swal-btn-ec008c {
        background-color: #ec008c !important;
        border-color: #ec008c !important;
        color: #fff !important;
    }

    .swal-btn-ec008c:hover,
    .swal-btn-ec008c:focus {
        background-color: #b8006a !important;
        border-color: #b8006a !important;
        color: #fff;
    }

    #tutorialVideoArea {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
</style>

<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario

// Obtener los tutoriales ordenados alfabéticamente por título
$tutoriales = [];
$sql = "SELECT id, titulo, modulo, link, descripcion FROM tutoriales ORDER BY titulo ASC";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $tutoriales[] = $row;
    }
}
?>

<div class="p-4">
    <div class="row">
        <!-- Sidebar fija y scrolleable -->
        <div class="col-sm-12 col-md-4 col-lg-4 mb-3 mb-md-0">
            <div class="sidebar-tutorial">
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <button class="btn btn-agregar w-100 mb-3" onclick="openAddVideoModal()">
                        <i class="bi bi-plus-circle me-2"></i>Agregar video
                    </button>
                <?php endif; ?>

                <input type="text" class="input-buscar" placeholder="Buscar video" id="buscarVideo" onkeyup="filtrarVideos()" />
                <div class="video-list-scroll" id="videoList">
                    <?php foreach ($tutoriales as $i => $tutorial): ?>
                        <div class="video-item-container mb-2 d-flex align-items-center">
                            <button
                                class="btn btn-video flex-grow-1 me-2"
                                onclick="showVideo(`<?php echo htmlspecialchars($tutorial['link'], ENT_QUOTES); ?>`, <?php echo $i; ?>, this)"
                                data-titulo="<?php echo htmlspecialchars($tutorial['titulo']); ?>"
                                data-modulo="<?php echo htmlspecialchars($tutorial['modulo']); ?>"
                                data-id="<?php echo $tutorial['id']; ?>">
                                <?php echo htmlspecialchars($tutorial['titulo']); ?><br>
                                <small class="text-secondary"><?php echo htmlspecialchars($tutorial['modulo']); ?></small>
                            </button>
                            <?php if ($rol === 'Control maestro'): ?>
                                <div class="video-actions d-flex flex-column">
                                    <button class="btn btn-sm btn-warning mb-1" onclick="editarVideo(<?php echo $tutorial['id']; ?>, '<?php echo htmlspecialchars($tutorial['titulo'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($tutorial['modulo'], ENT_QUOTES); ?>', `<?php echo htmlspecialchars($tutorial['link'], ENT_QUOTES); ?>`, `<?php echo htmlspecialchars($tutorial['descripcion'], ENT_QUOTES); ?>`)">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="eliminarVideo(<?php echo $tutorial['id']; ?>, '<?php echo htmlspecialchars($tutorial['titulo'], ENT_QUOTES); ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- Área de video -->
        <div class="col-md-4 col-lg-4 col-sm-12 d-flex flex-column justify-content-center align-items-center pl-4">
            <div id="tutorialVideoArea" class="w-100">
                <?php
                // Muestra el título del primer video por defecto
                if (isset($tutoriales[0])) {
                    echo '<h3 id="videoTitle" class="mb-4 text-center text-teal-dark">' . htmlspecialchars($tutoriales[0]['titulo']) . '</h3>';
                    echo $tutoriales[0]['link'];
                }
                ?>
            </div>
        </div>
        <div class="col-md-4 col-lg-4 col-sm-12 d-flex flex-column justify-content-center align-items-center" >
            <div id="videoDescriptionArea" style="max-height:500px; overflow-y:auto;width:100%;margin-top:20px;text-align:left;padding:16px;background:#f8f9fa;border-radius:10px;border:1px solid #d1d5db;">
                <?php
                // Muestra la descripción del primer video por defecto
                if (isset($tutoriales[0])) {
                    echo nl2br(htmlspecialchars($tutoriales[0]['descripcion']));
                }
                ?>
            </div>
        </div>
    </div>
</div>

<script>
    // Array de descripciones generado desde PHP
    const videoDescriptions = [
        <?php foreach ($tutoriales as $tutorial): ?> `<?php echo nl2br(htmlspecialchars($tutorial['descripcion'])); ?>`,
        <?php endforeach; ?>
    ];

    let currentDescriptionIndex = 0;

    function showVideo(embedHtml, descriptionIndex, btn) {
        // Actualiza el video y el título (título encima del video)
        document.getElementById('tutorialVideoArea').innerHTML =
            `<h2 id="videoTitle" class="mb-4 text-center text-teal-dark">${btn.getAttribute('data-titulo')}</h2>` + embedHtml;
        currentDescriptionIndex = descriptionIndex;
        document.querySelectorAll('.btn-video').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        // Mostrar la descripción en el div scrolleable
        document.getElementById('videoDescriptionArea').innerHTML = videoDescriptions[descriptionIndex];
    }

    // Modal para agregar video
    function openAddVideoModal() {
        Swal.fire({
            title: 'Agregar nuevo video',
            html: `
                <form id="formAddVideo" onsubmit="return false;">
                    <div class="mb-3 text-start">
                        <label for="tituloVideo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="tituloVideo" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="moduloVideo" class="form-label">Nombre del módulo</label>
                        <input type="text" class="form-control" id="moduloVideo" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="linkVideo" class="form-label">Código embed de YouTube</label>
                        <textarea class="form-control" id="linkVideo" placeholder='<iframe src="..."></iframe>' rows="3" required></textarea>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="descripcionVideo" class="form-label">Descripción del video</label>
                        <textarea class="form-control" id="descripcionVideo" rows="4" style="resize:vertical;max-height:150px;min-height:80px;overflow-y:auto;" required></textarea>
                    </div>
                </form>
            `,
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            customClass: {
                confirmButton: 'swal-btn-ec008c'
            },
            preConfirm: () => {
                const titulo = document.getElementById('tituloVideo').value.trim();
                const modulo = document.getElementById('moduloVideo').value.trim();
                const link = document.getElementById('linkVideo').value.trim();
                const descripcion = document.getElementById('descripcionVideo').value.trim();
                if (!titulo || !modulo || !link || !descripcion) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                // AJAX para guardar
                return fetch('components/tutorials/guardarTutorial.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `titulo=${encodeURIComponent(titulo)}&modulo=${encodeURIComponent(modulo)}&link=${encodeURIComponent(link)}&descripcion=${encodeURIComponent(descripcion)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.showValidationMessage(data.error || 'Error al guardar');
                            return false;
                        }
                        return true;
                    })
                    .catch(() => {
                        Swal.showValidationMessage('Error de conexión');
                        return false;
                    });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Video agregado!',
                    text: 'El video ha sido agregado correctamente.',
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    location.reload(); // Recarga para mostrar el nuevo video
                });
            }
        });
    }

    // Función para editar video
    function editarVideo(id, titulo, modulo, link, descripcion) {
        Swal.fire({
            title: 'Editar video',
            html: `
                <form id="formEditVideo" onsubmit="return false;">
                    <div class="mb-3 text-start">
                        <label for="editTituloVideo" class="form-label">Título</label>
                        <input type="text" class="form-control" id="editTituloVideo" value="${titulo}" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="editModuloVideo" class="form-label">Nombre del módulo</label>
                        <input type="text" class="form-control" id="editModuloVideo" value="${modulo}" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="editLinkVideo" class="form-label">Código embed de YouTube</label>
                        <textarea class="form-control" id="editLinkVideo" rows="3" required>${link}</textarea>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="editDescripcionVideo" class="form-label">Descripción del video</label>
                        <textarea class="form-control" id="editDescripcionVideo" rows="4" style="resize:vertical;max-height:150px;min-height:80px;overflow-y:auto;" required>${descripcion}</textarea>
                    </div>
                </form>
            `,
            width: 600,
            showCancelButton: true,
            confirmButtonText: 'Actualizar',
            cancelButtonText: 'Cancelar',
            focusConfirm: false,
            customClass: {
                confirmButton: 'swal-btn-ec008c'
            },
            preConfirm: () => {
                const nuevoTitulo = document.getElementById('editTituloVideo').value.trim();
                const nuevoModulo = document.getElementById('editModuloVideo').value.trim();
                const nuevoLink = document.getElementById('editLinkVideo').value.trim();
                const nuevaDescripcion = document.getElementById('editDescripcionVideo').value.trim();
                if (!nuevoTitulo || !nuevoModulo || !nuevoLink || !nuevaDescripcion) {
                    Swal.showValidationMessage('Todos los campos son obligatorios');
                    return false;
                }
                // AJAX para actualizar
                return fetch('components/tutorials/editarTutorial.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}&titulo=${encodeURIComponent(nuevoTitulo)}&modulo=${encodeURIComponent(nuevoModulo)}&link=${encodeURIComponent(nuevoLink)}&descripcion=${encodeURIComponent(nuevaDescripcion)}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (!data.success) {
                            Swal.showValidationMessage(data.error || 'Error al actualizar');
                            return false;
                        }
                        return true;
                    })
                    .catch(() => {
                        Swal.showValidationMessage('Error de conexión');
                        return false;
                    });
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Video actualizado!',
                    text: 'El video ha sido actualizado correctamente.',
                    timer: 1800,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            }
        });
    }

    // Función para eliminar video
    function eliminarVideo(id, titulo) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Deseas eliminar el video "${titulo}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            customClass: {
                confirmButton: 'swal-btn-ec008c'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('components/tutorials/eliminarTutorial.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        body: `id=${id}`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: 'El video ha sido eliminado correctamente.',
                                timer: 1800,
                                showConfirmButton: false
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.error || 'No se pudo eliminar el video'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Error de conexión'
                        });
                    });
            }
        });
    }

    // Filtro de búsqueda en la lista de videos
    function filtrarVideos() {
        const input = document.getElementById('buscarVideo').value.toLowerCase();
        document.querySelectorAll('#videoList .btn-video').forEach(btn => {
            const titulo = btn.getAttribute('data-titulo').toLowerCase();
            const modulo = btn.getAttribute('data-modulo').toLowerCase();
            btn.style.display = (titulo.includes(input) || modulo.includes(input)) ? '' : 'none';
        });
    }
</script>