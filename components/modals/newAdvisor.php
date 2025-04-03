<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$mensaje = '';
$tipo_mensaje = '';

if (isset($_POST['createAdvisor'])) {
    require 'conexion.php'; // Incluye tu conexión a la base de datos
    
    // Recolección y saneamiento de datos
    $identificacion = trim($_POST['identificacion']);
    $nombre = trim($_POST['name']);
    $telefono = trim($_POST['phone']);
    $correo = trim($_POST['email']);
    $rol = $_POST['role'];
    $notas = trim($_POST['notes']);

    // Verificar si el correo ya existe
    $query = "SELECT idAdvisor FROM advisors WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $correo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        $mensaje = "El correo ya está registrado.";
        $tipo_mensaje = "error";
    } else {
        // Insertar el registro
        $query = "INSERT INTO advisors (idAdvisor, name, phone, email, role, notes) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, 'ssssss', $identificacion, $nombre, $telefono, $correo, $rol, $notas);

        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "Asesor registrado correctamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al registrar el asesor: " . mysqli_error($conn);
            $tipo_mensaje = "error";
        }
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- Modal (sin cambios significativos) -->
<div class="modal fade" id="exampleModalNuevoAsesor" aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-indigo-dark">
                <h1 class="modal-title fs-5 text-white" id="exampleModalToggleLabel">
                    <i class="fas fa-plus-circle"></i> Nuevo Asesor
                </h1>
                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="" method="post">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="identificacion" placeholder="Número de identificación" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="name" placeholder="Nombre completo" required>
                    </div>
                    <div class="mb-3">
                        <input type="text" class="form-control" name="phone" placeholder="Teléfono" required>
                    </div>
                    <div class="mb-3">
                        <input type="email" class="form-control" name="email" placeholder="Correo electrónico" required>
                    </div>
                    <div class="mb-3">
                        <select class="form-select" name="role">
                            <option value="Asesor de Ventas">Asesor de Ventas</option>
                            <option value="Consultor">Consultor</option>
                            <option value="Soporte Técnico">Soporte Técnico</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <textarea class="form-control" name="notes" rows="3" placeholder="Notas adicionales (opcional)"></textarea>
                    </div>
                    <button type="submit" name="createAdvisor" class="btn bg-indigo-dark text-white">Registrar Asesor</button>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn bg-gray-light" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript para mostrar el toast -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Verifica si hay un mensaje que mostrar
        <?php if (!empty($mensaje)): ?>
            Swal.fire({
                icon: '<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>',
                title: '<?php echo $tipo_mensaje === 'success' ? 'Éxito' : 'Error'; ?>',
                text: '<?php echo $mensaje; ?>',
                confirmButtonText: 'Cerrar',
                timer: 5000, // 5 segundos
                timerProgressBar: true, // Muestra una barra de progreso del tiempo
                willClose: () => {
                    // Opcional: puedes ejecutar algo justo antes de cerrar el modal si es necesario
                }
            });
        <?php endif; ?>
    });
</script>
