<?php


function obtenerAsistenciasFiltradas(mysqli $conn, string $activity_type = ''): array
{
    $where = "1=1";

    if (!empty($activity_type)) {
        $where .= " AND activity_type = ?";
    }

    $sql = "SELECT * FROM asistencia_empleabilidad WHERE $where ORDER BY created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt === false) {
        error_log("Error al preparar la consulta: " . mysqli_error($conn));
        return [];
    }

    if (!empty($activity_type)) {
        mysqli_stmt_bind_param($stmt, "s", $activity_type);
    }

    if (mysqli_stmt_execute($stmt) === false) {
        error_log("Error al ejecutar la consulta: " . mysqli_stmt_error($stmt));
        return [];
    }

    $result = mysqli_stmt_get_result($stmt);

    if ($result === false) {
        error_log("Error al obtener el resultado de la consulta: " . mysqli_error($conn));
        return [];
    }

    $asistencias = [];
    while ($fila = mysqli_fetch_assoc($result)) {
        $asistencias[] = $fila;
    }

    mysqli_stmt_close($stmt);

    return $asistencias;
}


function renderizarTablaAsistencias(array $asistencias): void
{
    echo '<div class="table-responsive">';
    echo '    <table id="tablaAsistencias" class="table  table-bordered table-hover">';
    echo '        <thead>';
    echo '            <tr>';
    echo '                <th>Cédula</th>';
    echo '                <th>Nombre</th>';
    echo '                <th>Tipo de Actividad</th>';
    echo '                <th>Fecha de Creación</th>';
    echo '                <th>Acciones</th>';
    echo '            </tr>';
    echo '        </thead>';
    echo '        <tbody>';

    foreach ($asistencias as $fila) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila["cedula"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["full_name"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["activity_type"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["created_at"]) . "</td>";
        echo "<td>
            <button type='button' class='btn bg-indigo-dark btn-sm' data-bs-toggle='modal' data-bs-target='#verAsistenciaModal-" . htmlspecialchars($fila["id"]) . "' title='Ver Detalles'><i class='fas fa-eye'></i></button>
           
        </td>";
        echo "</tr>";
//boton para eliminar en caso de ser requerido 
//<button type='button' class='btn bg-magenta-dark btn-sm' onclick='confirmarEliminacion(" . htmlspecialchars($fila["id"]) . ")' title='Eliminar'><i class='fas fa-trash-alt'></i></button>
        // Modales (se podría mover a otra función de utilidad si se necesita más reutilización) 
        $id_asistencia_actual = $fila['id'];
        include 'details_assists.php';
    }

    echo '        </tbody>';
    echo '    </table>';
    echo '    <div style="min-height: 100px;"> </div>';
    echo '</div>';
}

?>