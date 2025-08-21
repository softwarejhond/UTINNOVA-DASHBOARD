<div class="container-fluid px-2">
    <div class="row mb-3">
        <div class="col">
            <button type="button" class="btn bg-indigo-dark text-white" data-bs-toggle="modal" data-bs-target="#modalSede">
                <i class="bi bi-plus-circle"></i> Nueva Sede
            </button>
        </div>
    </div>
    
    <div class="table-responsive">
        <table id="tablaSedes" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Modalidad</th>
                    <th>Fecha Creación</th>
                    <th>Fotografía</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $query = "SELECT * FROM headquarters ORDER BY id DESC";
                $result = mysqli_query($conn, $query);
                
                while($row = mysqli_fetch_assoc($result)) {
                    echo "<tr>";
                    echo "<td>".$row['id']."</td>";
                    echo "<td>".$row['name']."</td>";
                    echo "<td>".(!empty($row['address']) ? $row['address'] : "<span class='text-muted'>Sin especificar</span>")."</td>";
                    echo "<td>".$row['mode']."</td>";
                    echo "<td>".$row['date_creation']."</td>";
                    echo "<td>";
                    if (!empty($row['photo'])) {
                        echo "<button class='btn btn-sm bg-teal-dark text-white' onclick=\"verFotoSede('img/sedes/".$row['photo']."')\">
                                <i class='bi bi-image'></i> Ver foto
                              </button>";
                    } else {
                        echo "<span class='text-muted'>Sin foto</span>";
                    }
                    echo "</td>";
                    echo "<td>
                            <button class='btn btn-sm bg-indigo-dark mx-2 text-white' onclick='editarSede(".$row['id'].", `".$row['name']."`, `".$row['mode']."`, `".$row['address']."`)'>
                                <i class='bi bi-pencil'></i>
                            </button>";
                    // Mostrar botón eliminar solo para Control maestro        
                    if($_SESSION['rol'] === 12) {
                        echo "<button class='btn btn-sm btn-danger' onclick='eliminarSede(".$row['id'].")'>
                                <i class='bi bi-trash'></i>
                              </button>";
                    }
                    echo "</td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal para Nueva/Editar Sede -->
<div class="modal fade" id="modalSede" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle">Nueva Sede</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formSede">
                    <input type="hidden" id="sede_id" name="sede_id">
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required>
                    </div>
                    <div class="mb-3">
                        <label for="modalidad" class="form-label">Modalidad</label>
                        <input type="text" class="form-control" id="modalidad" name="modalidad" required>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Dirección</label>
                        <input type="text" class="form-control" id="address" name="address" required>
                    </div>
                    <div class="mb-3">
                        <label for="foto" class="form-label">Fotografía de la Sede</label>
                        <input type="file" class="form-control" id="foto" name="foto" accept=".png,.jpg,.jpeg">
                        <div id="previewFoto" class="mt-2"></div>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="password" name="password">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" onclick="guardarSede()">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- Agregar en el head de tu archivo principal -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    $('#tablaSedes').DataTable({
        language: {
            url: "controller/datatable_esp.json"
        },
        responsive: true
    });
});

document.getElementById('foto').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('previewFoto');
    preview.innerHTML = '';
    if (file && ['image/png','image/jpeg','image/jpg'].includes(file.type)) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.innerHTML = `<img src="${e.target.result}" style="width:100px;height:100px;object-fit:cover;border-radius:5px;">`;
        }
        reader.readAsDataURL(file);
    } else if (file) {
        preview.innerHTML = '<span class="text-danger">Formato no permitido</span>';
        e.target.value = '';
    }
});

function guardarSede() {
    const form = document.getElementById('formSede');
    const formData = new FormData(form);
    const password = formData.get('password');
    // Validación: alfanumérica, al menos una mayúscula y un caracter especial
    const regex = /^(?=.*[A-Z])(?=.*[a-zA-Z0-9])(?=.*[\W_]).+$/;
    if (password && !regex.test(password)) {
        Swal.fire({
            icon: 'warning',
            title: 'Contraseña inválida',
            text: 'La contraseña debe ser alfanumérica, contener al menos una mayúscula y un caracter especial.'
        });
        return;
    }
    const url = formData.get('sede_id') ? 'components/headquarters/updateHeadquarter.php' : 'components/headquarters/saveHeadquarter.php';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: data.message,
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                location.reload();
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }
    });
}

function editarSede(id, nombre, modalidad, address) {
    document.getElementById('sede_id').value = id;
    document.getElementById('nombre').value = nombre;
    document.getElementById('modalidad').value = modalidad;
    document.getElementById('address').value = address;
    document.getElementById('modalTitle').textContent = 'Editar Sede';
    new bootstrap.Modal(document.getElementById('modalSede')).show();
}

function eliminarSede(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede revertir",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('components/headquarters/deleteHeadquarter.php', {
                method: 'POST',
                body: JSON.stringify({id: id}),
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if(data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: data.message,
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            });
        }
    });
}

function verFotoSede(ruta) {
    Swal.fire({
        title: 'Fotografía de la Sede',
        html: `<img src="${ruta}" style="width:100%;max-width:350px;object-fit:cover;border-radius:8px;">`,
        showCloseButton: true,
        showConfirmButton: false
    });
}
</script>