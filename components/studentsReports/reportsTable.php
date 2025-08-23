<?php
// Obtener nombre del usuario en sesión
$nombre_usuario_sesion = '';
if (isset($_SESSION['username'])) {
    $username_sesion = $_SESSION['username'];
    $sqlNombre = "SELECT nombre FROM users WHERE username = ?";
    $stmtNombre = $conn->prepare($sqlNombre);
    $stmtNombre->bind_param("s", $username_sesion);
    $stmtNombre->execute();
    $stmtNombre->bind_result($nombre_usuario_sesion);
    $stmtNombre->fetch();
    $stmtNombre->close();
}

// Consulta para obtener los reportes con los nombres de usuario y responsable
$sql = "
SELECT 
    sr.id,
    sr.number_id,
    CONCAT_WS(' ', ur.first_name, ur.second_name, ur.first_last, ur.second_last) AS nombre_completo,
    sr.code,
    sr.grupo,
    sr.gestion,
    sr.status,
    sr.responsable,
    u.nombre AS nombre_responsable,
    sr.fecha_registro
FROM student_reports sr
LEFT JOIN user_register ur ON sr.number_id = ur.number_id
LEFT JOIN users u ON sr.responsable = u.username
ORDER BY sr.fecha_registro DESC
";
$result = $conn->query($sql);

// Contadores de estados
$contadores = [
    'PENDIENTE' => 0,
    'EN PROCESO' => 0,
    'REALIZADO' => 0,
    'TOTAL' => 0
];
$reportes = [];
while ($row = $result->fetch_assoc()) {
    $contadores['TOTAL']++;
    if (isset($contadores[$row['status']])) {
        $contadores[$row['status']]++;
    }
    $reportes[] = $row;
}
?>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
    .estado-panel {
        min-width: 220px;
        max-width: 250px;
        background: #fff;
        border-radius: 10px;
        border: 1px solid #eee;
        padding: 28px 10px 10px 10px; /* Más padding arriba */
        margin-right: 20px;
        height: fit-content;
    }
    .estado-panel h3 {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
        margin-left: 8px;
        color: #222;
    }
    .estado-btn {
        width: 100%;
        text-align: left;
        margin-bottom: 14px;
        border: none;
        background: white;
        font-size: 1rem;
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        transition: background 0.2s;
        border-radius: 8px;
        padding: 1.1rem 1rem 1.1rem 1.3rem; /* Más alto y texto más separado del borde izquierdo */
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        border-left-width: 5px;
        border-left-style: solid;
    }
    .estado-btn.active, .estado-btn:hover {
        background: #f2f2f2;
        border-radius: 8px;
    }
    .estado-badge {
        min-width: 32px;
        font-size: 0.95rem;
        font-weight: 600;
        border-radius: 999px;
        background: #f5f5f5;
        color: #333;
        text-align: center;
        margin-left: auto;
    }
    .estado-color-pendiente { border-left-color: #dc3545; }
    .estado-color-proceso   { border-left-color: #ffc107; }
    .estado-color-realizado { border-left-color: #198754; }
    .estado-color-todos     { border-left-color: #222; }
</style>

<div class="d-flex">
    <!-- Panel lateral de estados -->
    <div class="estado-panel">
        <h3>Estados Reporte</h3>
        <button class="estado-btn estado-color-todos active" data-estado="">
            <span>Todos</span>
            <span class="estado-badge"><?= $contadores['TOTAL'] ?></span>
        </button>
        <button class="estado-btn estado-color-pendiente" data-estado="PENDIENTE">
            <span>Pendiente</span>
            <span class="estado-badge"><?= $contadores['PENDIENTE'] ?></span>
        </button>
        <button class="estado-btn estado-color-proceso" data-estado="EN PROCESO">
            <span>En Proceso</span>
            <span class="estado-badge"><?= $contadores['EN PROCESO'] ?></span>
        </button>
        <button class="estado-btn estado-color-realizado" data-estado="REALIZADO">
            <span>Realizado</span>
            <span class="estado-badge"><?= $contadores['REALIZADO'] ?></span>
        </button>
    </div>

    <!-- Tabla y exportar -->
    <div class="flex-grow-1">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div></div>
            <button id="btn-exportar-reportes" class="btn btn-success mb-2">
                <i class="bi bi-file-earmark-excel"></i> Exportar a Excel
            </button>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle" id="listaReportes">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Número ID</th>
                        <th>Nombre</th>
                        <th>Código Curso</th>
                        <th>Grupo</th>
                        <th>Gestión</th>
                        <th>Estado</th>
                        <th>Responsable</th>
                        <th>Fecha registro</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reportes as $row): ?>
                        <tr>
                            <td><?= $row['id'] ?></td>
                            <td><?= $row['number_id'] ?></td>
                            <td><?= str_replace('ñ', 'Ñ', mb_strtoupper(iconv('UTF-8', 'ASCII//TRANSLIT', $row['nombre_completo']))) ?></td>
                            <td><?= htmlspecialchars($row['code']) ?></td>
                            <td><?= htmlspecialchars($row['grupo']) ?></td>
                            <td>
                                <button
                                    class="btn btn-sm bg-teal-dark text-white btn-ver-gestion"
                                    data-gestion="<?= htmlspecialchars($row['gestion'], ENT_QUOTES) ?>"
                                    title="Ver gestión">
                                    <i class="bi bi-eye"></i> Ver
                                </button>
                            </td>
                            <td>
                                <?php
                                $status = htmlspecialchars($row['status']);
                                if ($status === 'PENDIENTE') {
                                    echo '<span class="badge bg-danger">' . $status . '</span>';
                                } elseif ($status === 'EN PROCESO') {
                                    echo '<span class="badge bg-warning text-dark">' . $status . '</span>';
                                } elseif ($status === 'REALIZADO') {
                                    echo '<span class="badge bg-teal-dark text-white">' . $status . '</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">' . $status . '</span>';
                                }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row['nombre_responsable']) ?></td>
                            <td>
                                <?php
                                $fecha = new DateTime($row['fecha_registro']);
                                echo $fecha->format('d/m/Y H:i:s');
                                ?>
                            </td>
                            <td>
                                <button
                                    class="btn btn-sm bg-indigo-dark text-white btn-gestionar-reporte"
                                    data-id="<?= $row['id'] ?>"
                                    data-gestion="<?= htmlspecialchars($row['gestion'], ENT_QUOTES) ?>"
                                    data-status="<?= htmlspecialchars($row['status'], ENT_QUOTES) ?>"
                                    title="Gestionar">
                                    <i class="bi bi-gear"></i> Gestionar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Bootstrap para gestionar reporte -->
<div class="modal fade" id="modalGestionReporte" tabindex="-1" aria-labelledby="modalGestionReporteLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form id="formGestionReporte">
                <div class="modal-header bg-indigo-dark text-white">
                    <h5 class="modal-title" id="modalGestionReporteLabel"><i class="bi bi-gear"></i> Gestionar reporte</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="reporte_id" name="reporte_id">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Responsable:</label>
                        <input type="text" class="form-control" value="<?= htmlspecialchars($nombre_usuario_sesion) ?>" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Gestión a realizar:</label>
                        <textarea class="form-control" id="gestion_a_realizar" name="gestion_a_realizar" rows="3" placeholder="Describe la gestión a realizar..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Resultado de la gestión:</label>
                        <textarea class="form-control" id="resultado_gestion" name="resultado_gestion" rows="3" placeholder="Describe el resultado de la gestión..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Estado del reporte:</label>
                        <select class="form-select" id="status_reporte" name="status_reporte">
                            <option value="PENDIENTE">PENDIENTE</option>
                            <option value="EN PROCESO">EN PROCESO</option>
                            <option value="REALIZADO">REALIZADO</option>
                        </select>
                    </div>
                    <button type="button" class="btn bg-magenta-dark text-white mb-3" id="btn-ver-gestiones-anteriores">
                        <i class="bi bi-clock-history"></i> Ver gestiones anteriores
                    </button>
                    <div id="tabla-gestiones-anteriores" style="display:none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success">Guardar gestión</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        // SweetAlert para ver gestión
        document.querySelectorAll('.btn-ver-gestion').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const gestion = this.getAttribute('data-gestion');
                Swal.fire({
                    title: 'Gestión',
                    html: `<div style="max-height:300px;overflow:auto;text-align:left;">${gestion.replace(/\n/g, '<br>')}</div>`,
                    width: 600,
                    confirmButtonText: 'Cerrar'
                });
            });
        });

        // Modal para gestionar reporte
        let modalGestion = new bootstrap.Modal(document.getElementById('modalGestionReporte'));
        document.querySelectorAll('.btn-gestionar-reporte').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const idReporte = this.getAttribute('data-id');
                document.getElementById('reporte_id').value = idReporte;
                document.getElementById('gestion_a_realizar').value = '';
                document.getElementById('resultado_gestion').value = '';
                document.getElementById('status_reporte').value = this.getAttribute('data-status');

                fetch('components/studentsReports/ultimaGestion.php?id_reporte=' + idReporte)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.gestion) {
                            document.getElementById('gestion_a_realizar').value = data.gestion.gestion_a_realizar;
                            document.getElementById('resultado_gestion').value = data.gestion.resultado_gestion;
                            document.getElementById('status_reporte').value = data.gestion.status;
                        }
                    });
                modalGestion.show();
            });
        });

        // Guardar gestión
        document.getElementById('formGestionReporte').addEventListener('submit', function(e) {
            e.preventDefault();

            let id_reporte = document.getElementById('reporte_id').value;
            let responsable = "<?= htmlspecialchars($nombre_usuario_sesion) ?>";
            let gestion_a_realizar = document.getElementById('gestion_a_realizar').value;
            let resultado_gestion = document.getElementById('resultado_gestion').value;
            let status = document.getElementById('status_reporte').value;

            if (!gestion_a_realizar.trim() || !resultado_gestion.trim()) {
                Swal.fire('Completa todos los campos', '', 'warning');
                return;
            }

            $.ajax({
                url: 'components/studentsReports/guardarGestion.php',
                type: 'POST',
                data: {
                    id_reporte: id_reporte,
                    responsable: responsable,
                    gestion_a_realizar: gestion_a_realizar,
                    resultado_gestion: resultado_gestion,
                    status: status
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success) {
                        Swal.fire({
                            title: 'Guardado',
                            text: 'La gestión ha sido guardada correctamente.',
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload();
                        });
                        $('#modalGestionReporte').modal('hide');
                    } else {
                        Swal.fire('Error', res.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo guardar la gestión.', 'error');
                }
            });
        });

        // Exportar reportes
        document.getElementById('btn-exportar-reportes').addEventListener('click', function() {
            Swal.fire({
                title: 'Generando archivo...',
                text: 'Por favor espera unos segundos.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('components/studentsReports/export_reports.php')
                .then(response => {
                    if (!response.ok) throw new Error('Error en la descarga');
                    return response.blob();
                })
                .then(blob => {
                    Swal.close();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'reportes.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                    window.URL.revokeObjectURL(url);
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo generar el archivo.', 'error');
                });
        });

        // DataTable
        const table = $('#listaReportes').DataTable({
            responsive: true,
            language: {
                url: "controller/datatable_esp.json"
            },
            pagingType: "simple"
        });

        // Filtro por estado con panel lateral
        document.querySelectorAll('.estado-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                const estado = this.getAttribute('data-estado');
                if (estado) {
                    table.column(6).search('^' + estado + '$', true, false).draw();
                } else {
                    table.column(6).search('').draw();
                }
            });
        });

        // Mostrar gestiones anteriores
        document.getElementById('btn-ver-gestiones-anteriores').addEventListener('click', function() {
            const idReporte = document.getElementById('reporte_id').value;
            const tablaDiv = document.getElementById('tabla-gestiones-anteriores');
            tablaDiv.innerHTML = '<div class="text-center my-2">Cargando...</div>';
            tablaDiv.style.display = 'block';

            fetch('components/studentsReports/todasGestiones.php?id_reporte=' + idReporte)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.gestiones.length > 0) {
                        let html = `<table class="table table-sm table-bordered mt-2">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Responsable</th>
                                    <th>Gestión</th>
                                    <th>Resultado</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>`;
                        data.gestiones.forEach(g => {
                            html += `<tr>
                                <td>${g.fecha_gestion}</td>
                                <td>${g.responsable}</td>
                                <td>${g.gestion_a_realizar.replace(/\n/g, '<br>')}</td>
                                <td>${g.resultado_gestion.replace(/\n/g, '<br>')}</td>
                                <td>${g.status}</td>
                            </tr>`;
                        });
                        html += '</tbody></table>';
                        tablaDiv.innerHTML = html;
                    } else {
                        tablaDiv.innerHTML = '<div class="alert alert-info mt-2">No hay gestiones anteriores.</div>';
                    }
                })
                .catch(() => {
                    tablaDiv.innerHTML = '<div class="alert alert-danger mt-2">Error al cargar las gestiones.</div>';
                });
        });

        // Al abrir el modal, ocultar la tabla de gestiones anteriores
        document.getElementById('modalGestionReporte').addEventListener('show.bs.modal', function () {
            document.getElementById('tabla-gestiones-anteriores').style.display = 'none';
        });
    });
</script>