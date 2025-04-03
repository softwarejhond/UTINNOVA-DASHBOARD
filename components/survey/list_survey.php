<?php
// components/encuestas/listEncuestas.php

// Incluir las funciones de utilidad
include_once 'survey_utils.php';

// Obtener el estado seleccionado del query string
$estado_seleccionado = $_GET['estado'] ?? '';

// Obtener los datos de las encuestas (con filtro)
$encuestas = obtenerEncuestasFiltradas($conn, $estado_seleccionado);

?>

<!-- Sección de Filtro y Búsqueda -->
<div class="card card-radius mb-4 p-3">
    <form method="GET" action="encuestas.php" class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="estado" class="form-label subTitle">Filtrar por Situación Laboral:</label>
            <select class="form-select" id="estado" name="estado" onchange="this.form.submit()">
                <option value="">Todos las situaciones laborales</option>
                <option value="empleado" <?php if ($estado_seleccionado == 'empleado') echo 'selected'; ?>>Empleado</option>
                <option value="desempleado" <?php if ($estado_seleccionado == 'desempleado') echo 'selected'; ?>>Desempleado</option>
                <option value="otro" <?php if ($estado_seleccionado == 'otro') echo 'selected'; ?>>Otro</option>
            </select>
        </div>
         <!-- Botón de Exportación (alineado con el filtro) -->
        <div class="col-auto">
             <br/>
            <button type="button" class="btn btn-success mt-1" onclick="exportarTabla(<?php echo htmlspecialchars(json_encode($encuestas), ENT_QUOTES, 'UTF-8'); ?>)"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
        </div>
    </form>
</div>

<!-- Tabla de Encuestas -->
<?php renderizarTablaEncuestas($encuestas); ?>


<script src="https://unpkg.com/xlsx/dist/xlsx.full.min.js"></script>

<script>
function exportarTabla(encuestas) {
    // Crear un nuevo libro de Excel
    var wb = XLSX.utils.book_new();

    // Crear una nueva hoja de cálculo
    var ws_data = [
        ["ID", "Nombre Completo", "Cédula", "Situación Laboral", "Tiempo Desempleo", "Trabajo Desempeña", "Tipo de Contrato", "Rango Salarial", "Hoja de Vida", "Contacto Empleadores", "Talleres Asistidos", "Fecha Creación"] // Encabezados
    ];

    // Agregar los datos de las encuestas a la hoja de cálculo
    for (var i = 0; i < encuestas.length; i++) {
        ws_data.push([
            encuestas[i].id,
            encuestas[i].nombreCompleto,
            encuestas[i].cedula,
            encuestas[i].situacionLaboral,
            encuestas[i].tiempoDesempleo,
            encuestas[i].trabajoDesempena,
            encuestas[i].tipoContrato,
            encuestas[i].rangoSalarial,
            encuestas[i].hojaVida,
            encuestas[i].contactoEmpleadores,
            encuestas[i].talleresAsistidos,
            encuestas[i].fecha_creacion
        ]);
    }

    var ws = XLSX.utils.aoa_to_sheet(ws_data);

    // Agregar la hoja de cálculo al libro
    XLSX.utils.book_append_sheet(wb, ws, "Encuestas");

    // Escribir el libro a un archivo
    XLSX.writeFile(wb, "encuestas.xlsx");
}
</script>