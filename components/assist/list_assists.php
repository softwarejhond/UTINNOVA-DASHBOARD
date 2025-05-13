<?php

include_once 'assists_utils.php';

// Obtener el valor del filtro del query string
$activity_type = $_GET['activity_type'] ?? '';

// Obtener los datos de las asistencias (con filtro)
$asistencias = obtenerAsistenciasFiltradas($conn, $activity_type);

?>

<div class="card card-radius mb-4 p-3">
    <form method="GET" action="asistencias.php" class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="activity_type" class="form-label subTitle">Filtrar por Tipo de Actividad:</label>
            <select class="form-select" id="activity_type" name="activity_type" onchange="this.form.submit()">
                <option value="">Todos los tipos de actividad</option>
                <option value="Taller" <?php if ($activity_type == 'Taller') echo 'selected'; ?>>Taller</option>
                <option value="Networking" <?php if ($activity_type == 'Networking') echo 'selected'; ?>>Networking</option>
                <option value="Feria de empleabilidad" <?php if ($activity_type == 'Feria de empleabilidad') echo 'selected'; ?>>Feria de empleabilidad</option>
            </select>
        </div>
        <div class="col-auto">
             <br/>
            <button type="button" class="btn btn-success mt-1" onclick="exportarTabla(<?php echo htmlspecialchars(json_encode($asistencias), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
        </div>
    </form>
</div>

<?php renderizarTablaAsistencias($asistencias); ?>



<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<script>
function exportarTabla(asistencias) {
    var wb = XLSX.utils.book_new();

    // Actualizar encabezados con nuevos campos
    var ws_data = [
        [
            "ID", 
            "Nombre Completo", 
            "Cédula", 
            "Correo", 
            "Celular", 
            "Tipo Asistente",
            "Tipo de Actividad", 
            "Fecha de Creación"
        ]
    ];

    // Agregar datos incluyendo nuevos campos
    for (var i = 0; i < asistencias.length; i++) {
        ws_data.push([
            asistencias[i].id,
            asistencias[i].full_name,
            asistencias[i].cedula,
            asistencias[i].email || 'No registrado',
            asistencias[i].phone || 'No registrado',
            asistencias[i].attendee_type || 'No especificado',
            asistencias[i].activity_type,
            asistencias[i].created_at
        ]);
    }

    var ws = XLSX.utils.aoa_to_sheet(ws_data);
    XLSX.utils.book_append_sheet(wb, ws, "Asistencias");
    XLSX.writeFile(wb, "asistencias.xlsx");
}
</script>