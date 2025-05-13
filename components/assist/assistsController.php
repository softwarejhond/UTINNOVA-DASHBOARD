<?php


function obtenerTodasLasAsistencias($conn)
{
    $sql = "SELECT 
                id, 
                full_name, 
                cedula, 
                email,
                phone,
                attendee_type,
                activity_type, 
                created_at 
            FROM asistencia_empleabilidad 
            ORDER BY created_at DESC";
            
    $result = mysqli_query($conn, $sql);
    $asistencias = array();
    while ($fila = mysqli_fetch_assoc($result)) {
        $asistencias[] = $fila;
    }
    return $asistencias;
}

function editarAsistencia($conn, $data)
{
    
    if (empty($data['full_name']) || empty($data['cedula']) || empty($data['activity_type'])) {
        $response = array('status' => 'error', 'message' => 'Todos los campos son obligatorios.');
        return json_encode($response);
    }

 
    $id = mysqli_real_escape_string($conn, $data['id']);
    $full_name = mysqli_real_escape_string($conn, $data['full_name']);
    $cedula = mysqli_real_escape_string($conn, $data['cedula']);
    $activity_type = mysqli_real_escape_string($conn, $data['activity_type']);

   
    $sql = "UPDATE asistencia_empleabilidad SET 
                full_name = '$full_name',
                cedula = '$cedula',
                activity_type = '$activity_type'
            WHERE id = '$id'";

    if (mysqli_query($conn, $sql)) {
        $response = array('status' => 'success', 'message' => 'Asistencia actualizada correctamente.');
    } else {
        $response = array('status' => 'error', 'message' => 'Error al actualizar la asistencia: ' . mysqli_error($conn));
    }

    return json_encode($response);
}

function crearAsistencia($conn, $data)
{
    
    if (empty($data['full_name']) || empty($data['cedula']) || empty($data['activity_type'])) {
        $response = array('status' => 'error', 'message' => 'Todos los campos son obligatorios.');
        return json_encode($response);
    }

    
    $full_name = mysqli_real_escape_string($conn, $data['full_name']);
    $cedula = mysqli_real_escape_string($conn, $data['cedula']);
    $activity_type = mysqli_real_escape_string($conn, $data['activity_type']);

    
    $sql = "INSERT INTO asistencia_empleabilidad (full_name, cedula, activity_type) VALUES ('$full_name', '$cedula', '$activity_type')";

    if (mysqli_query($conn, $sql)) {
        $response = array('status' => 'success', 'message' => 'Asistencia creada correctamente.');
    } else {
        $response = array('status' => 'error', 'message' => 'Error al crear la asistencia: ' . mysqli_error($conn));
    }

    return json_encode($response);
}


function eliminarAsistencia($conn, $id)
{
    $id = mysqli_real_escape_string($conn, $id);
    $sql = "DELETE FROM asistencia_empleabilidad WHERE id = '$id'";
    if (mysqli_query($conn, $sql)) {
        $response = array('status' => 'success', 'message' => 'Asistencia eliminada correctamente.');
    } else {
        $response = array('status' => 'error', 'message' => 'Error al eliminar la asistencia: ' . mysqli_error($conn));
    }

    return json_encode($response);
}

?>