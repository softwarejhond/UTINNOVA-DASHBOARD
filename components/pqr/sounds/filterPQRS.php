<?php

// Obtener el valor de 'estado' desde $_GET, o null si no existe
$estado_seleccionado = isset($_GET['estado']) ? $_GET['estado'] : null;
// Obtener el valor de 'tipo' desde $_GET, o null si no existe
$tipo_seleccionado = isset($_GET['tipo']) ? $_GET['tipo'] : null;
// Obtener el valor de 'mes' desde $_GET, o null si no existe
$mes_seleccionado = isset($_GET['mes']) ? $_GET['mes'] : null;
// Obtener el valor de 'cedula' desde $_GET, o null si no existe
$cedula_seleccionada = isset($_GET['cedula']) ? $_GET['cedula'] : null;



// -------------------- VALIDACIÓN DEL ESTADO --------------------
// Validar si el estado seleccionado es un entero válido
if ($estado_seleccionado !== null && $estado_seleccionado !== "") {
    $estado_seleccionado = filter_var($estado_seleccionado, FILTER_VALIDATE_INT);
    if ($estado_seleccionado === false) {
        // Si la validación falla, mostrar un error y salir
        echo "Error: El estado seleccionado no es válido.";
        exit;
    }
} else {
    // Si 'estado' está vacío o no existe, establecer $estado_seleccionado a null
    $estado_seleccionado = null;
}

// -------------------- VALIDACIÓN DEL TIPO --------------------
// Definir los tipos válidos para el ENUM 'tipo'
$tipos_validos = ['Petición', 'Queja', 'Reclamo', 'Sugerencia'];
// Validar si el tipo seleccionado está dentro de los tipos válidos
if ($tipo_seleccionado !== null && $tipo_seleccionado !== "") {
    if (!in_array($tipo_seleccionado, $tipos_validos)) {
        // Si el tipo no es válido, mostrar un error y salir
        echo "Error: El tipo seleccionado no es válido.";
        exit;
    }
} else {
    // Si 'tipo' está vacío o no existe, establecer $tipo_seleccionado a null
    $tipo_seleccionado = null;
}

// -------------------- VALIDACIÓN DEL MES --------------------
// Validar si el mes seleccionado es un entero válido entre 1 y 12
if ($mes_seleccionado !== null && $mes_seleccionado !== "") {
    $mes_seleccionado = filter_var($mes_seleccionado, FILTER_VALIDATE_INT, [
        'options' => [
            'min_range' => 1,
            'max_range' => 12
        ]
    ]);
    if ($mes_seleccionado === false) {
        // Si la validación falla, mostrar un error y salir
        echo "Error: El mes seleccionado no es válido: {$mes_seleccionado}";
        exit;
    }
} else {
    // Si 'mes' está vacío o no existe, establecer $mes_seleccionado a null
    $mes_seleccionado = null;
}

// -------------------- VALIDACIÓN DE LA CÉDULA --------------------
// Validar si la cédula seleccionada es un entero válido
if ($cedula_seleccionada !== null && $cedula_seleccionada !== "") {
    $cedula_seleccionada = filter_var($cedula_seleccionada, FILTER_VALIDATE_INT);
    if ($cedula_seleccionada === false) {
        // Si la validación falla, mostrar un error y salir
        echo "Error: La cédula seleccionada no es válida.";
        exit;
    }
} else {
    // Si 'cedula' está vacío o no existe, establecer $cedula_seleccionada a null
    $cedula_seleccionada = null;
}




// Definir estados y sus propiedades
$estados = [
    1 => ['nombre' => 'Pendiente', 'color' => 'red'],
    2 => ['nombre' => 'En Proceso', 'color' => 'yellow'],
    3 => ['nombre' => 'Atendido', 'color' => 'green'],
    4 => ['nombre' => 'Cerrado', 'color' => 'black']
];


$tipos_validos = ['Petición', 'Queja', 'Reclamo', 'Sugerencia'];

$meses = [
    1 => 'Enero',
    2 => 'Febrero',
    3 => 'Marzo',
    4 => 'Abril',
    5 => 'Mayo',
    6 => 'Junio',
    7 => 'Julio',
    8 => 'Agosto',
    9 => 'Septiembre',
    10 => 'Octubre',
    11 => 'Noviembre',
    12 => 'Diciembre'
];

// En el HTML, después de los botones de estado:
?>


<!-- Sección de botones de filtrado -->
<!-- Contenedor principal con grid -->
<div class="dashboard-container">
    <!-- Barra lateral con botones de filtrado -->
    <div class="sidebar-filters">
        <h3>Estados PQR</h3>
        <?php foreach ($estados as $id => $estado): ?>
            <?php
            $sql_contador = "SELECT COUNT(*) as total FROM pqr WHERE estado = $id";
            $resultado_contador = $conn->query($sql_contador);
            $total = $resultado_contador->fetch_assoc()['total'];
            ?>
            <a href="?estado=<?php echo $id; ?>"
                class="boton-estado <?php echo ($estado_seleccionado == $id) ? 'activo' : ''; ?>"
                style="border-color: <?php echo $estado['color']; ?>;">
                <span class="texto"><?php echo $estado['nombre']; ?></span>
                <span class="contador"><?php echo $total; ?></span>
            </a>

        <?php endforeach; ?>
        <div class="filtros-adicionales">
            <form method="GET" action="" class="form-filtros">
                <!-- Mantener el estado seleccionado -->
                <?php if ($estado_seleccionado): ?>
                    <input type="hidden" name="estado" value="<?php echo $estado_seleccionado; ?>">
                <?php endif; ?>

                <!-- Filtro de Tipo -->
                <div class="filtro-grupo">
                    <label for="tipo">Tipo de PQR:</label>
                    <select class="form-select" id="tipo" name="tipo" onchange="this.form.submit()">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos_validos as $tipo): ?>
                            <option value="<?php echo $tipo; ?>"
                                <?php echo ($tipo_seleccionado == $tipo) ? 'selected' : ''; ?>>
                                <?php echo $tipo; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Filtro de Mes -->
                <div class="filtro-grupo">
                    <label for="mes">Mes:</label>
                    <select class="form-select" id="mes" name="mes" onchange="this.form.submit()">
                        <option value="">Todos los meses</option>
                        <?php foreach ($meses as $num => $nombre): ?>
                            <option value="<?php echo $num; ?>"
                                <?php echo ($mes_seleccionado == $num) ? 'selected' : ''; ?>>
                                <?php echo $nombre; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <style>
            .filtros-adicionales {
                margin-top: 2rem;
                padding-top: 1.5rem;
                border-top: 1px solid #dee2e6;
            }

            .filtro-grupo {
                margin-bottom: 1.5rem;
            }

            .filtro-grupo label {
                display: block;
                margin-bottom: 0.5rem;
                color: #495057;
                font-weight: 500;
                font-size: 0.9rem;
            }

            .form-select {
                width: 100%;
                padding: 0.75rem;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                background-color: white;
                font-size: 0.9rem;
                transition: all 0.3s ease;
            }

            .form-select:focus {
                border-color: var(--btn-border);
                outline: none;
                box-shadow: 0 0 0 2px rgba(0, 123, 255, 0.25);
            }
        </style>
        <!-- ******** BOTÓN DE EXPORTACIÓN ******** -->
        <div class="col-auto">
            <button type="button" class="btn btn-success mt-4" onclick="exportData()"><i class="fas fa-file-excel"></i> Exportar a Excel</button>
        </div>

        <script>
            function exportData() {
                // Crea un formulario dinámicamente
                var form = document.createElement('form');
                form.action = 'components/pqr/exportPQRS_process.php'; // URL del script de exportación
                form.method = 'POST'; // Usa POST para enviar datos

                // Agrega los filtros al formulario (si es necesario)
                var estado = document.getElementById('estado').value;
                var tipo = document.getElementById('tipo').value;
                var mes = document.getElementById('mes').value;

                // Crea un campo oculto para el estado
                var estadoInput = document.createElement('input');
                estadoInput.type = 'hidden';
                estadoInput.name = 'estado';
                estadoInput.value = estado;
                form.appendChild(estadoInput);

                // Crea un campo oculto para el tipo
                var tipoInput = document.createElement('input');
                tipoInput.type = 'hidden';
                tipoInput.name = 'tipo';
                tipoInput.value = tipo;
                form.appendChild(tipoInput);

                // Crea un campo oculto para el mes
                var mesInput = document.createElement('input');
                mesInput.type = 'hidden';
                mesInput.name = 'mes';
                mesInput.value = mes;
                form.appendChild(mesInput);

                // Agrega el formulario al body y lo envía
                document.body.appendChild(form);
                form.submit();
                document.body.removeChild(form); // Limpia el formulario después de enviar
            }
        </script>
        <!-- ******** FIN BOTÓN DE EXPORTACIÓN ******** -->


    </div>



    <!-- Contenedor de la tabla -->
    <!-- Tabla de PQRS -->
    <div class="table-responsive">
        <table id="tablaPQRS" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Radicado</th>
                    <th>Tipo</th>
                    <th>Asunto</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Construir la consulta SQL con los filtros
                $sql_pqrs = "SELECT pqr.id, pqr.tipo, pqr.asunto,pqr.fecha_registro, pqr.cedula, pqr.nombre, pqr.fecha_creacion, pqr.numero_radicado,  estados.nombre AS estado_nombre
                         FROM pqr
                         INNER JOIN estados ON pqr.estado = estados.id
                         WHERE 1=1";

                if (!empty($estado_seleccionado)) {
                    $sql_pqrs .= " AND pqr.estado = " . intval($estado_seleccionado);
                }
                if (!empty($tipo_seleccionado)) {
                    $sql_pqrs .= " AND pqr.tipo = '" . $conn->real_escape_string($tipo_seleccionado) . "'";
                }
                if (!empty($mes_seleccionado)) {
                    $sql_pqrs .= " AND DATE_FORMAT(pqr.fecha_registro, '%m') = '" . $conn->real_escape_string(str_pad($mes_seleccionado, 2, '0', STR_PAD_LEFT)) . "'";
                }

                // Ordenar por la última fecha registrada
                $sql_pqrs .= " ORDER BY pqr.fecha_registro DESC";

                $resultado_pqrs = $conn->query($sql_pqrs);

                // Verificar si la consulta fue exitosa
                if ($resultado_pqrs === false) {
                    echo "Error en la consulta: " . $conn->error;
                    exit;
                }

                // Mostrar los registros en la tabla
                while ($fila = $resultado_pqrs->fetch_assoc()) {
                    echo "<tr>";

                    echo "<td>" . (isset($fila["fecha_registro"]) ? htmlspecialchars($fila["fecha_registro"]) : 'N/A') . "</td>";
                    echo "<td>" . (isset($fila["cedula"]) ? htmlspecialchars($fila["cedula"]) : 'N/A') . "</td>";
                    echo "<td>" . (isset($fila["nombre"]) ? htmlspecialchars($fila["nombre"]) : 'N/A') . "</td>";
                    echo "<td>" . (isset($fila["numero_radicado"]) ? htmlspecialchars($fila["numero_radicado"]) : 'N/A') . "</td>";
                    echo "<td>" . (isset($fila["tipo"]) ? htmlspecialchars($fila["tipo"]) : 'N/A') . "</td>";
                    echo "<td>" . (isset($fila["asunto"]) ? htmlspecialchars($fila["asunto"]) : 'N/A') . "</td>";
                    echo "<td class='text-center'>";
                    $estado_nombre = isset($fila["estado_nombre"]) ? htmlspecialchars($fila["estado_nombre"]) : 'N/A';
                    $clase_estado = '';

                    switch ($estado_nombre) {
                        case 'Pendiente':
                            $clase_estado = 'bg-danger text-white';
                            break;
                        case 'En Proceso':
                            $clase_estado = 'bg-warning';
                            break;
                        case 'Atendido':
                            $clase_estado = 'bg-success text-white';
                            break;
                        case 'Cerrado':
                            $clase_estado = 'bg-dark text-white';
                            break;
                        default:
                            $clase_estado = '';
                            break;
                    }
                    echo "<span class='badge " . $clase_estado . "'>" . $estado_nombre . "</span>"; // Badge con la clase de color
                    echo "</td>";
                    echo "<td>
                    <button type='button' class='btn bg-indigo-dark btn-sm' data-bs-toggle='modal' data-bs-target='#detallePQRModal-" . htmlspecialchars($fila["id"]) . "' title='Ver Detalles'><i class='fas fa-eye'></i></button>
                    <button type='button' class='btn bg-orange-dark btn-sm' data-bs-toggle='modal' data-bs-target='#editarPQRModal-" . htmlspecialchars($fila["id"]) . "' title='Editar'><i class='fas fa-edit'></i></button>
                </td>";
                    echo "</tr>";
                }
                ?>
            </tbody>
        </table>




        <div style="min-height: 100px;"> </div> <!-- Espacio abajo de la tabla -->
    </div>

    <style>
        .dashboard-container {
            display: grid;
            grid-template-columns: 250px 1fr;
            gap: 2rem;
            margin: 2rem 0;
        }

        .sidebar-filters {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .sidebar-filters h3 {
            margin-bottom: 1.5rem;
            color: #333;
            font-size: 1.2rem;
        }

        .boton-estado {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            padding: 1rem;
            margin-bottom: 0.8rem;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            background: white;
            border-left: 4px solid var(--btn-color, #ddd);
            /* Borde izquierdo coloreado */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }

        .boton-estado:hover {
            transform: translateX(5px);
            background: #f0f0f0;
        }

        .boton-estado.activo {
            color: white;
            background-color: var(--btn-color, #007bff);
        }

        .contador {
            background: rgba(0, 0, 0, 0.1);
            padding: 0.2rem 0.8rem;
            border-radius: 999px;
            font-size: 0.9em;
            font-weight: bold;
        }

        .tabla-container {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .tabla-pqr {
            width: 100%;
            border-collapse: collapse;
        }

        .tabla-pqr th,
        .tabla-pqr td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        .tabla-pqr th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .estado {
            padding: 0.4rem 0.8rem;
            border-radius: 999px;
            color: white;
            font-size: 0.9em;
        }

        .btn-ver,
        .btn-editar {
            padding: 0.4rem 0.8rem;
            border-radius: 4px;
            text-decoration: none;
            margin-right: 0.5rem;
            font-size: 0.9em;
            color: white;
        }

        .btn-ver {
            background-color: #17a2b8;
        }

        .btn-editar {
            background-color: #ffc107;
            color: #000;
        }

        /* Responsive design */
        @media (max-width: 992px) {
            .dashboard-container {
                grid-template-columns: 1fr;
            }

            .sidebar-filters {
                display: flex;
                flex-wrap: wrap;
                gap: 1rem;
            }

            .boton-estado {
                width: auto;
                margin-bottom: 0;
            }
        }
    </style>



    <?php

    // -------------------- CONSTRUCCIÓN DE LA CONSULTA SQL --------------------
    // Consulta SQL base para seleccionar datos de la tabla 'pqr' unida con la tabla 'estados'
    $sql = "SELECT pqr.id, pqr.tipo, pqr.asunto, pqr.cedula, pqr.fecha_registro, pqr.numero_radicado, estados.nombre AS estado_nombre
        FROM pqr
        INNER JOIN estados ON pqr.estado = estados.id";

    // Array para almacenar las condiciones WHERE
    $condiciones = [];
    // Array para almacenar los parámetros que se van a bindear a la consulta preparada
    $parametros = [];
    // String para almacenar los tipos de datos de los parámetros
    $tipos = "";

    // -------------------- APLICACIÓN DE FILTROS --------------------
    // Si se seleccionó un estado, agregar la condición WHERE para filtrara por estado
    if ($estado_seleccionado !== null) {
        $condiciones[] = "pqr.estado = ?";
        $parametros[] = $estado_seleccionado;
        $tipos .= "i"; // 'i' para integer (el tipo de dato de 'pqr.estado')
    }

    // Si se seleccionó un tipo, agregar la condición WHERE para filtrar por tipo
    if ($tipo_seleccionado !== null) {
        $condiciones[] = "pqr.tipo = ?";
        $parametros[] = $tipo_seleccionado;
        $tipos .= "s"; // 's' para string (el tipo de dato de 'pqr.tipo')
    }

    // Si se seleccionó un mes, agregar la condición WHERE para filtrar por mes de fecha_registro
    if ($mes_seleccionado !== null) {
        $condiciones[] = "MONTH(pqr.fecha_registro) = ?";
        $parametros[] = $mes_seleccionado;
        $tipos .= "i"; // 'i' para integer (el tipo de dato de 'MONTH(pqr.fecha_registro)')
    }

    // Si se seleccionó una cédula, agregar la condición WHERE para filtrar por cédula
    if ($cedula_seleccionada !== null) {
        $condiciones[] = "pqr.cedula = ?";
        $parametros[] = $cedula_seleccionada;
        $tipos .= "i"; // 'i' para integer (el tipo de dato de 'pqr.cedula')
    }

    // -------------------- AGREGAR CONDICIONES WHERE A LA CONSULTA --------------------
    // Si hay condiciones WHERE, agregarlas a la consulta SQL
    if (!empty($condiciones)) {
        $sql .= " WHERE " . implode(" AND ", $condiciones);
    }

    // -------------------- PREPARAR Y EJECUTAR LA CONSULTA --------------------
    // Si hay parámetros, preparar y ejecutar la consulta con bind_param
    if (!empty($parametros)) {
        // Preparar la consulta SQL
        $stmt = $conn->prepare($sql);

        // Combinar el string de tipos con el array de parámetros
        $bind_params = array_merge(array($tipos), $parametros);

        // Crear un array de referencias para pasar a bind_param
        $refs = [];
        foreach ($bind_params as $key => $value) {
            $refs[$key] = &$bind_params[$key];  // Crear referencia
        }

        // Usar Reflection para llamar a bind_param con un número variable de argumentos
        $ref = new ReflectionClass('mysqli_stmt');
        $method = $ref->getMethod("bind_param");
        $method->invokeArgs($stmt, $refs);

        // Manejar errores al preparar la consulta
        if ($stmt === false) {
            error_log("Error al preparar la consulta: " . $conn->error);
            echo "Error interno del servidor.";
            exit;
        }

        // Ejecutar la consulta preparada
        if ($stmt->execute()) {
            // Obtener el resultado de la consulta
            $resultado = $stmt->get_result();
        } else {
            // Manejar errores al ejecutar la consulta
            error_log("Error al ejecutar la consulta: " . $stmt->error);
            echo "Error interno del servidor.";
            echo $stmt->error;
            exit;
        }

        // Cerrar la declaración preparada
        $stmt->close();
    } else {
        // Si no hay parámetros, ejecutar la consulta directamente
        $resultado = $conn->query($sql);

        // Manejar errores al ejecutar la consulta
        if ($resultado === false) {
            error_log("Error al ejecutar la consulta: " . $conn->error);
            echo "Error interno del servidor.";
            exit;
        }
    }

    // -------------------- ALMACENAR LOS RESULTADOS --------------------
    // Almacenar los datos en un array
    $pqrs = array();
    // Si hay resultados, recorrerlos y almacenarlos en el array
    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $pqrs[] = $fila;
        }
    }
    ?>