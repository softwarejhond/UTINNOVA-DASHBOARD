<?php
// controller/encuestasController.php

// Función para obtener todas las encuestas
function obtenerTodasLasEncuestas($conn)
{
    $sql = "SELECT * FROM encuestas_laborales ORDER BY fecha_creacion DESC";
    $result = mysqli_query($conn, $sql);
    $encuestas = array();
    while ($fila = mysqli_fetch_assoc($result)) {
        $encuestas[] = $fila;
    }
    return $encuestas;
}

// Función para editar una encuesta
function editarEncuesta($conn, $data)
{
    // Escapar las variables para prevenir la inyección SQL
    $id = mysqli_real_escape_string($conn, $data['id']);
    $nombreCompleto = mysqli_real_escape_string($conn, $data['nombreCompleto']);
    $cedula = mysqli_real_escape_string($conn, $data['cedula']);
    $situacionLaboral = mysqli_real_escape_string($conn, $data['situacionLaboral']);
    $tiempoDesempleo = mysqli_real_escape_string($conn, $data['tiempoDesempleo']);
    $trabajoDesempena = mysqli_real_escape_string($conn, $data['trabajoDesempena']);
    $tipoContrato = mysqli_real_escape_string($conn, $data['tipoContrato']);
    $rangoSalarial = mysqli_real_escape_string($conn, $data['rangoSalarial']);
    $hojaVida = mysqli_real_escape_string($conn, $data['hojaVida']);
    $contactoEmpleadores = mysqli_real_escape_string($conn, $data['contactoEmpleadores']);
    $talleresAsistidos = mysqli_real_escape_string($conn, $data['talleresAsistidos']);

    // Construir la consulta SQL de actualización
    $sql = "UPDATE encuestas_laborales SET 
                nombreCompleto = '$nombreCompleto',
                cedula = '$cedula',
                situacionLaboral = '$situacionLaboral',
                tiempoDesempleo = '$tiempoDesempleo',
                trabajoDesempena = '$trabajoDesempena',
                tipoContrato = '$tipoContrato',
                rangoSalarial = '$rangoSalarial',
                hojaVida = '$hojaVida',
                contactoEmpleadores = '$contactoEmpleadores',
                talleresAsistidos = '$talleresAsistidos'
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        $response = array('status' => 'success', 'message' => 'Encuesta actualizada correctamente.');
        return json_encode($response); // Devuelve el JSON
    } else {
        $response = array('status' => 'error', 'message' => 'Error al actualizar la encuesta: ' . mysqli_error($conn));
        return json_encode($response); // Devuelve el JSON
    }
}

// Función para eliminar una encuesta
function eliminarEncuesta($conn, $id)
{
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "DELETE FROM encuestas_laborales WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        $response = array('status' => 'success', 'message' => 'Encuesta eliminada correctamente.');
    } else {
        $response = array('status' => 'error', 'message' => 'Error al eliminar la encuesta: ' . mysqli_error($conn));
    }

    return json_encode($response); // Devuelve el JSON
}

// Aquí puedes añadir las demás funciones para las operaciones CRUD
?>