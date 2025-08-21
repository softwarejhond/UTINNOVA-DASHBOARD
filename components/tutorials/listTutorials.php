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
        background: #00796b;
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
        background: #004d40;
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

    .btn-video {
        border: 1.5px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        font-family: 'Segoe UI', Arial, sans-serif;
        font-size: 1rem;
        margin-bottom: 14px;
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
</style>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar fija y scrolleable -->
        <div class="col-12 col-md-4 col-lg-3 mb-3 mb-md-0">
            <div class="sidebar-tutorial">
                <button class="btn btn-agregar w-100 mb-3" onclick="openAddVideoModal()">
                    <i class="bi bi-plus-circle me-2"></i>Agregar video
                </button>
                <input type="text" class="input-buscar" placeholder="Buscar video" />
                <div class="video-list-scroll">
                    <button class="btn btn-video" onclick="showVideo('https://www.youtube.com/embed/dQw4w9WgXcQ', 0, this)">Video 1</button>
                    <button class="btn btn-video" onclick="showVideo('https://www.youtube.com/embed/ysz5S6PUM-U', 1, this)">Video 2</button>
                    <button class="btn btn-video" onclick="showVideo('https://www.youtube.com/embed/tgbNymZ7vqY', 2, this)">Video 3</button>
                    <button class="btn btn-video" onclick="showVideo('https://www.youtube.com/embed/ScMzIvxBSi4', 3, this)">Video 4</button>
                    <button class="btn btn-video" onclick="showVideo('https://www.youtube.com/embed/9bZkp7q19f0', 4, this)">Video 5</button>
                    <button class="btn btn-video" onclick="showVideo('https://www.youtube.com/embed/3JZ_D3ELwOQ', 5, this)">Video 6</button>
                </div>
            </div>
        </div>
        <!-- Área de video -->
        <div class="col-12 col-md-8 col-lg-9 d-flex flex-column justify-content-center align-items-center" style="min-height: 500px;">
            <iframe id="tutorialVideo" width="80%" height="500" src="https://www.youtube.com/embed/dQw4w9WgXcQ" frameborder="0" allowfullscreen></iframe>
            <!-- <button class="btn bg-magenta-dark text-white mt-3" onclick="showDescriptionSwal()">
                Ver descripción del video
            </button> -->
        </div>
    </div>
</div>

<script>
    // Descripciones con HTML y links de ejemplo
    const videoDescriptions = [
        `Descripción del Video 1. <br>Visita <a href="https://www.utinnova.edu.co" target="_blank">UTINNOVA</a> para más información.`,
        `Descripción del Video 2. <br>Revisa el <a href="https://www.google.com" target="_blank">enlace aquí</a>.`,
        `Descripción del Video 3. <br>Este video explica los conceptos básicos.`,
        `Descripción del Video 4. <br>Consulta la <a href="https://www.youtube.com" target="_blank">documentación oficial</a>.`,
        `Descripción del Video 5. <br>Incluye recursos adicionales.`,
        `Descripción del Video 6. <br>Para soporte, visita <a href="https://support.example.com" target="_blank">este enlace</a>.`
    ];

    let currentDescriptionIndex = 0;

    function showVideo(url, descriptionIndex, btn) {
        document.getElementById('tutorialVideo').src = url;
        currentDescriptionIndex = descriptionIndex;
        // Marcar botón activo
        document.querySelectorAll('.btn-video').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    function showDescriptionSwal() {
        Swal.fire({
            title: 'Descripción del Video',
            html: `<div style="max-height:300px;overflow-y:auto;text-align:left;">${videoDescriptions[currentDescriptionIndex]}</div>`,
            width: 600,
            showCloseButton: true,
            confirmButtonText: 'Cerrar',
            scrollbarPadding: false
        });
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
                        <label for="linkVideo" class="form-label">Link del video</label>
                        <input type="url" class="form-control" id="linkVideo" placeholder="https://..." required>
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
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
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
                });
                // Aquí puedes agregar el video dinámicamente a la lista si lo deseas
            }
        });
    }
</script>