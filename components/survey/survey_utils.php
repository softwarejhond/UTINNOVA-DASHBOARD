<?php
// components/encuestas/encuestas_utils.php

/**
 * Obtiene las encuestas de la base de datos, aplicando un filtro opcional por estado laboral.
 *
 * @param mysqli $conn La conexión a la base de datos.
 * @param string $estado_seleccionado El estado laboral por el cual filtrar (opcional).
 * @return array Un array de encuestas.
 */
function obtenerEncuestasFiltradas(mysqli $conn, string $estado_seleccionado = ''): array
{
    $where = "1=1";
    if (!empty($estado_seleccionado)) {
        $where .= " AND situacionLaboral = '$estado_seleccionado'";
    }

    $sql = "SELECT * FROM encuestas_laborales WHERE $where ORDER BY fecha_creacion DESC";
    $result = mysqli_query($conn, $sql);

    $encuestas = [];
    while ($fila = mysqli_fetch_assoc($result)) {
        $encuestas[] = $fila;
    }

    return $encuestas;
}

/**
 * Renderiza la tabla de encuestas.
 *
 * @param array $encuestas El array de encuestas a renderizar.
 */
function renderizarTablaEncuestas(array $encuestas): void
{
    echo '<div class="table-responsive">';
    echo '    <table id="tablaEncuestas" class="table  table-bordered table-hover">';
    echo '        <thead>';
    echo '            <tr>';
    echo '                <th>Cédula</th>';
    echo '                <th>Nombre</th>';
    echo '                <th>Situación Laboral</th>';
    echo '                <th>Tipo de Contrato</th>';
    echo '                <th>Rango Salarial</th>';
    echo '                <th>Fecha Creación</th>';
    echo '                <th>Acciones</th>';
    echo '            </tr>';
    echo '        </thead>';
    echo '        <tbody>';

    foreach ($encuestas as $fila) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($fila["cedula"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["nombreCompleto"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["situacionLaboral"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["tipoContrato"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["rangoSalarial"]) . "</td>";
        echo "<td>" . htmlspecialchars($fila["fecha_creacion"]) . "</td>";
        echo "<td>
            <button type='button' class='btn bg-indigo-dark btn-sm' data-bs-toggle='modal' data-bs-target='#verEncuestaModal-" . htmlspecialchars($fila["id"]) . "' title='Ver Detalles'><i class='fas fa-eye'></i></button>
        </td>";
        echo "</tr>";
    }

    echo '        </tbody>';
    echo '    </table>';
    
    //BOTONES PARA OPERACIONES DE EDITAR Y ELIMINAR//
    // <button type='button' class='btn bg-orange-dark btn-sm' data-bs-toggle='modal' data-bs-target='#editarEncuestaModal-" . htmlspecialchars($fila["id"]) . "' title='Editar'><i class='fas fa-edit'></i></button>
    //<button type='button' class='btn bg-magenta-dark btn-sm' onclick='confirmarEliminacion(" . htmlspecialchars($fila["id"]) . ")' title='Eliminar'><i class='fas fa-trash-alt'></i></button>

    // Modales (se podría mover a otra función de utilidad si se necesita más reutilización) 
    foreach ($encuestas as $fila) {
        $id_encuesta_actual = $fila['id'];
        include 'components/modals/details_survey.php';
        include 'components/modals/edit_survey.php';
    }

    echo '    <div style="min-height: 100px;"> </div> <!-- Espacio abajo de la tabla -->';
    echo '</div>';
}
