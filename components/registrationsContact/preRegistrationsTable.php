<?php
// Consulta para obtener todos los pre-registros
$query = "SELECT * FROM pre_registrations";
$result = mysqli_query($conn, $query);
?>

<style>
    /* Evita saltos de línea y ajusta el ancho automáticamente */
    .table-nowrap th, .table-nowrap td {
        white-space: nowrap;
        vertical-align: middle;
    }
</style>

<!-- Botón de exportación -->
<div class="mb-3 text-start">
    <button id="exportPreRegBtn" class="btn btn-success">
        <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
    </button>
</div>

<div class="table-responsive">
    <table id="listaPreRegistros" class="table table-striped table-bordered table-nowrap align-middle">
        <thead>
            <tr>
                <th class="text-center">Tipo ID</th>
                <th class="text-center">Número ID</th>
                <th class="text-center">Nombre completo</th>
                <th class="text-center">Email</th>
                <th class="text-center">Email alterno</th>
                <th class="text-center">Teléfono 1</th>
                <th class="text-center">Teléfono 2</th>
                <th class="text-center">Sede</th>
                <th class="text-center">Programa</th>
                <th class="text-center">Horario</th>
                <th class="text-center">Requisitos</th>
                <th class="text-center">Políticas</th>
                <th class="text-center">Fecha de registro</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td class="text-center"><?= htmlspecialchars($row['type_id']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['number_id']) ?></td>
                    <td class="text-center">
                        <?= htmlspecialchars(
                            trim($row['first_name'] . ' ' . 
                            ($row['second_name'] ?? '') . ' ' . 
                            $row['first_last'] . ' ' . 
                            $row['second_last'])
                        ) ?>
                    </td>
                    <td class="text-center"><?= htmlspecialchars($row['email']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['email2']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['phone1']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['phone2']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['sede_name']) ?></td>
                    <td class="text-center"><?= htmlspecialchars($row['programa']) ?></td>
                    <td class="text-center">
                        <button 
                            type="button" 
                            class="btn btn-sm" 
                            style="background-color: #008080; color: white;"
                            data-bs-toggle="popover" 
                            data-bs-trigger="hover focus" 
                            data-bs-content="<?= htmlspecialchars($row['horario'] ?? 'No definido') ?>"
                        >
                            <i class="bi bi-clock"></i>
                        </button>
                    </td>
                    <td class="text-center"><?= $row['accept_requirements'] ? 'Sí' : 'No' ?></td>
                    <td class="text-center"><?= $row['accept_data_policies'] ? 'Sí' : 'No' ?></td>
                    <td class="text-center"><?= date('d/m/Y', strtotime($row['created_at'])) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>



<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener("DOMContentLoaded", function() {
    // Inicializar popovers de Bootstrap
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    // Exportar a Excel con loader
    document.getElementById('exportPreRegBtn').addEventListener('click', function() {
        Swal.fire({
            title: 'Generando archivo...',
            html: 'Por favor espera unos segundos.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
                window.location.href = 'components/registrationsContact/export_pre_registrations.php';
                setTimeout(() => Swal.close(), 3000); // Cierra el loader después de 3 segundos
            }
        });
    });
});
</script>