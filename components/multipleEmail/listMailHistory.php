<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <h4><i class="bi bi-clock-history"></i> Historial de Correos Enviados</h4>
        </div>
        <div class="col text-end">
            <button class="btn bg-teal-dark text-white" onclick="exportToExcel()">
                <i class="bi bi-file-excel"></i> Exportar a Excel
            </button>
        </div>
    </div>

    <div class="table-responsive">
        <table id="emailHistoryTable" class="table table-striped table-bordered">
            <thead class="bg-indigo-dark text-white">
                <tr>
                    <th>Fecha</th>
                    <th>Asunto</th>
                    <th>Enviado por</th>
                    <th>Desde</th>
                    <th>Total</th>
                    <th>Exitosos</th>
                    <th>Fallidos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM email_history ORDER BY created_at DESC";
                $result = mysqli_query($conn, $sql);

                while ($row = mysqli_fetch_assoc($result)) {
                    $sentFrom = $row['sent_from'] === 'float' ? 'Editor Flotante' : 'Página Principal';
                    echo "<tr>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($row['created_at'])) . "</td>";
                    echo "<td>" . htmlspecialchars($row['subject']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['sent_by']) . "</td>";
                    echo "<td>" . $sentFrom . "</td>";
                    echo "<td>" . $row['recipients_count'] . "</td>";
                    echo "<td class='text-success'>" . $row['successful_count'] . "</td>";
                    echo "<td class='text-danger'>" . $row['failed_count'] . "</td>";
                    echo "<td>
                            <button class='btn btn-sm bg-indigo-dark text-white' onclick='viewDetails({$row['id']})'>
                                <i class='bi bi-eye'></i> Ver detalles
                            </button>
                          </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#emailHistoryTable').DataTable({
        pageLength: 25,
        responsive: true,
        language: {
            url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        order: [[0, 'desc']]
    });
});

function viewDetails(id) {
    $.get('components/multipleEmail/get_email_details.php', { id: id }, function(response) {
        if (response.success) {
            Swal.fire({
                title: 'Detalles del Envío',
                html: `
                    <div class="text-start">
                        <div class="mb-3">
                            <h6 class="mb-2">Información General:</h6>
                            <p class="mb-1"><strong>Fecha:</strong> ${response.date}</p>
                            <p class="mb-1"><strong>Enviado por:</strong> ${response.sent_by}</p>
                            <p class="mb-1"><strong>Desde:</strong> ${response.sent_from === 'float' ? 'Editor Flotante' : 'Página Principal'}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="mb-2">Asunto:</h6>
                            <p class="border p-2 bg-light">${response.subject}</p>
                        </div>
                        <div class="mb-3">
                            <h6 class="mb-2">Contenido:</h6>
                            <div class="border p-2 bg-light" style="max-height: 200px; overflow-y: auto;">
                                ${response.content}
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6 class="mb-2">Estadísticas:</h6>
                            <div class="row text-center">
                                <div class="col">
                                    <div class="border p-2">
                                        <h5>${response.stats.total}</h5>
                                        <small>Total</small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border p-2 text-success">
                                        <h5>${response.stats.successful}</h5>
                                        <small>Exitosos</small>
                                    </div>
                                </div>
                                <div class="col">
                                    <div class="border p-2 text-danger">
                                        <h5>${response.stats.failed}</h5>
                                        <small>Fallidos</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <h6 class="mb-2">Destinatarios:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Email</th>
                                            <th>Nombre</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${response.recipients.map(r => `
                                            <tr>
                                                <td>${r.email}</td>
                                                <td>${r.name}</td>
                                                <td>${r.status === 'success' ? 
                                                    '<span class="badge bg-success">Enviado</span>' : 
                                                    `<span class="badge bg-danger" title="${r.error_message}">Fallido</span>`}
                                                </td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                `,
                width: '800px',
                heightAuto: true,
                scrollbarPadding: false
            });
        } else {
            Swal.fire('Error', response.message, 'error');
        }
    }).fail(function() {
        Swal.fire('Error', 'No se pudo cargar los detalles del correo', 'error');
    });
}

function exportToExcel() {
    window.location.href = 'components/multipleEmail/export_history.php';
}
</script>

