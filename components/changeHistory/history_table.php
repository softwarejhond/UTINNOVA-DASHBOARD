<?php

// Consulta para obtener el historial con nombres completos
$sql = "SELECT 
            ch.*,
            CONCAT(ur.first_name, ' ', COALESCE(ur.second_name, ''), ' ', ur.first_last, ' ', COALESCE(ur.second_last, '')) as student_name,
            u.nombre as user_name
        FROM change_history ch
        LEFT JOIN user_register ur ON ch.student_id = ur.number_id
        LEFT JOIN users u ON ch.user_change = u.username
        ORDER BY ch.date DESC";

$result = $conn->query($sql);
$history = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
}
?>

<div class="container-fluid">
    <div class="table-responsive">
        <table id="historialCambios" class="table table-hover table-bordered">
            <thead class="thead-dark text-center">
                <tr>
                    <th>Fecha y Hora</th>
                    <th>C.C</th>
                    <th>Estudiante</th>
                    <th>Usuario que realizó el cambio</th>
                    <th>Cambio realizado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $record) { ?>
                    <tr>
                        <td><?php echo date('d/m/Y H:i:s', strtotime($record['date'])); ?></td>
                        <td><?php echo htmlspecialchars($record['student_id']); ?></td>
                        <td><?php echo !empty($record['student_name']) ? htmlspecialchars(trim($record['student_name'])) : 'No encontrado'; ?></td>
                        <td><?php echo !empty($record['user_name']) ? htmlspecialchars($record['user_name']) : 'No encontrado'; ?></td>
                        <td><?php echo htmlspecialchars($record['change_made']); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#historialCambios').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Spanish.json"
            },
            "order": [
                [0, "desc"]
            ],
            "pageLength": 25
        });
    });
</script>