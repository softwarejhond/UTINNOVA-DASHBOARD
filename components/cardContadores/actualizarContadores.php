<?php
// NO SE REQUIERE IMPORTAR LA CONEXIÓN PORQUE DESDE EL MAIN YA ESTÁ CONECTADA
require '.././../controller/conexion.php';
// Agregar manejo de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Obtener total de usuarios registrados
    $sql_total_registrados = "SELECT COUNT(*) AS total_registrados FROM user_register";
    $result_total_registrados = mysqli_query($conn, $sql_total_registrados);
    $total_registrados = mysqli_fetch_assoc($result_total_registrados)['total_registrados'];

    // Obtener total de usuarios verificados
    $sql_total = "SELECT COUNT(*) AS total FROM user_register WHERE status = '1' AND statusAdmin = '1'";
    $result_total = mysqli_query($conn, $sql_total);
    $total_usuarios = mysqli_fetch_assoc($result_total)['total'];

    // Obtener total de usuarios en Lote 1 (reemplaza la consulta de Cundinamarca)
    $sql_lote1 = "SELECT COUNT(*) AS total_lote1 FROM user_register WHERE  lote = 1";
    $result_lote1 = mysqli_query($conn, $sql_lote1);
    $total_lote1 = mysqli_fetch_assoc($result_lote1)['total_lote1'];

    // Obtener total de usuarios en Lote 2 (reemplaza la consulta de Boyacá)
    $sql_lote2 = "SELECT COUNT(*) AS total_lote2 FROM user_register WHERE lote = 2";
    $result_lote2 = mysqli_query($conn, $sql_lote2);
    $total_lote2 = mysqli_fetch_assoc($result_lote2)['total_lote2'];

    // Obtener total de usuarios sin verificar
    $sql_sin_verificar = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND (statusAdmin = '0' OR statusAdmin NOT IN ('1', '3'))";
    $result_sinVerificar = mysqli_query($conn, $sql_sin_verificar);
    $total_sinVerificar = mysqli_fetch_assoc($result_sinVerificar)['total_sinVerificar'];

    // Obtener total de Gobernación de Boyacá
    $sql_GobernacionBoyaca = "SELECT COUNT(*) AS total_GobernacionBoyaca FROM user_register WHERE status = '1' AND statusAdmin = '0' AND institution = 'Gobernación de Boyacá'";
    $result_GobernacionBoyaca = mysqli_query($conn, $sql_GobernacionBoyaca);
    $total_GobernacionBoyaca = mysqli_fetch_assoc($result_GobernacionBoyaca)['total_GobernacionBoyaca'];

    // Obtener total de contactos establecidos (Sí) y su porcentaje
    $sql_contacto_si = "SELECT 
                            COUNT(DISTINCT cl.number_id) AS total_contactos,
                            (COUNT(DISTINCT cl.number_id) / (SELECT COUNT(*) FROM user_register) * 100) AS porcentaje
                        FROM contact_log cl
                        WHERE cl.contact_established = 1";
    $result_contacto_si = mysqli_query($conn, $sql_contacto_si);
    $contacto_si_data = mysqli_fetch_assoc($result_contacto_si);
    $total_contacto_si = $contacto_si_data['total_contactos'];
    $porc_contacto_si = round($contacto_si_data['porcentaje'], 2);

    // Obtener total de contactos no establecidos (No) y su porcentaje
    $sql_contacto_no = "SELECT 
                            COUNT(DISTINCT cl.number_id) AS total_contactos,
                            (COUNT(DISTINCT cl.number_id) / (SELECT COUNT(*) FROM user_register) * 100) AS porcentaje
                        FROM contact_log cl
                        WHERE cl.contact_established = 0";
    $result_contacto_no = mysqli_query($conn, $sql_contacto_no);
    $contacto_no_data = mysqli_fetch_assoc($result_contacto_no);
    $total_contacto_no = $contacto_no_data['total_contactos'];
    $porc_contacto_no = round($contacto_no_data['porcentaje'], 2);

    // Calcular el total de contactos no establecidos restando los contactos establecidos del total general
    $total_contacto_no = $total_registrados - $total_contacto_si;
    $porc_contacto_no = ($total_registrados > 0) ? round(($total_contacto_no / $total_registrados) * 100, 2) : 0;

    // Obtener total de contactos establecidos (Sí) y su porcentaje cuando su estado es 1 y statusAdmin es 1
    $sql_contacto_si_admin = "SELECT 
        COUNT(DISTINCT cl.number_id) AS total_contactos_admin,
        (COUNT(DISTINCT cl.number_id) / (SELECT COUNT(*) FROM user_register) * 100) AS porcentaje_admin
    FROM contact_log cl
    JOIN user_register ur ON cl.number_id = ur.number_id
    WHERE cl.contact_established = 1 AND ur.statusAdmin = 1";
    $result_contacto_si_admin = mysqli_query($conn, $sql_contacto_si_admin);
    $contacto_si_data_admin = mysqli_fetch_assoc($result_contacto_si_admin);
    $total_contacto_si_admin = $contacto_si_data_admin['total_contactos_admin'];
    $porc_contacto_si_admin = round($contacto_si_data_admin['porcentaje_admin'], 2);

    // Obtener total de contactos no establecidos (No) y su porcentaje cuando su estado es 1 y statusAdmin es 1
    $sql_contacto_no_admin = "SELECT 
        COUNT(DISTINCT cl.number_id) AS total_contactos_admin,
        (COUNT(DISTINCT cl.number_id) / (SELECT COUNT(*) FROM user_register) * 100) AS porcentaje_admin
    FROM contact_log cl
    JOIN user_register ur ON cl.number_id = ur.number_id
    WHERE cl.contact_established = 0 AND ur.statusAdmin = 1";
    $result_contacto_no_admin = mysqli_query($conn, $sql_contacto_no_admin);
    $contacto_no_data_admin = mysqli_fetch_assoc($result_contacto_no_admin);
    $total_contacto_no_admin = $contacto_no_data_admin['total_contactos_admin'];
    $porc_contacto_no_admin = round($contacto_no_data_admin['porcentaje_admin'], 2);

    // Obtener total de usuarios que conocieron el programa a través de Radio
    $sql_radio = "SELECT COUNT(*) AS total_radio FROM user_register WHERE knowledge_program = 'Radio'";
    $result_radio = mysqli_query($conn, $sql_radio);
    $total_radio = mysqli_fetch_assoc($result_radio)['total_radio'];

    // Obtener total de usuarios que conocieron el programa a través de Redes sociales
    $sql_redes_sociales = "SELECT COUNT(*) AS total_redes_sociales FROM user_register WHERE knowledge_program = 'Redes sociales'";
    $result_redes_sociales = mysqli_query($conn, $sql_redes_sociales);
    $total_redes_sociales = mysqli_fetch_assoc($result_redes_sociales)['total_redes_sociales'];

    // Obtener total de usuarios rechazados
    $sql_rechazados = "SELECT COUNT(*) AS total_rechazados FROM user_register WHERE statusAdmin = 2";
    $result_rechazados = mysqli_query($conn, $sql_rechazados);
    $total_rechazados = mysqli_fetch_assoc($result_rechazados)['total_rechazados'];
    $porc_rechazados = ($total_registrados > 0) ? round(($total_rechazados / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios matriculados
    $sql_matriculados = "SELECT COUNT(*) AS total_matriculados FROM user_register WHERE statusAdmin = 3";
    $result_matriculados = mysqli_query($conn, $sql_matriculados);
    $total_matriculados = mysqli_fetch_assoc($result_matriculados)['total_matriculados'];
    $porc_matriculados = ($total_registrados > 0) ? round(($total_matriculados / $total_registrados) * 100, 2) : 0;

    // Calcular porcentajes para lotes (reemplazan los porcentajes de departamentos)
    $porc_lote1 = ($total_usuarios > 0) ? round(($total_lote1 / $total_registrados) * 100, 2) : 0;
    $porc_lote2 = ($total_usuarios > 0) ? round(($total_lote2 / $total_registrados) * 100, 2) : 0;
    $porc_sinVerificar = ($total_usuarios > 0) ? round(($total_sinVerificar / $total_registrados) * 100, 2) : 0;
    $porc_GobernacionBoyaca = ($total_usuarios > 0) ? round(($total_GobernacionBoyaca / $total_registrados) * 100, 2) : 0;


    // Obtener lista de instituciones y sus totales
    $sql_instituciones = "SELECT institution, COUNT(*) as total 
                        FROM user_register 
                        WHERE institution IS NOT NULL AND institution != '' 
                        GROUP BY institution 
                        ORDER BY institution ASC";
    $result_instituciones = mysqli_query($conn, $sql_instituciones);
    $instituciones = [];
    while ($row = mysqli_fetch_assoc($result_instituciones)) {
        $instituciones[] = [
            'nombre' => $row['institution'],
            'total' => $row['total']
        ];
    }

    // Devolver los datos en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        "total_registrados" => $total_registrados,
        "total_usuarios" => $total_usuarios,
        "total_matriculados" => $total_matriculados,
        "porc_matriculados" => $porc_matriculados,
        "total_lote1" => $total_lote1,
        "porc_lote1" => $porc_lote1,
        "total_lote2" => $total_lote2,
        "porc_lote2" => $porc_lote2,
        "total_sinVerificar" => $total_sinVerificar,
        "porc_sinVerificar" => $porc_sinVerificar,
        "total_GobernacionBoyaca" => $total_GobernacionBoyaca,
        "porc_GobernacionBoyaca" => $porc_GobernacionBoyaca,
        "total_contacto_si" => $total_contacto_si,
        "porc_contacto_si" => $porc_contacto_si,
        "total_contacto_no" => $total_contacto_no,
        "porc_contacto_no" => $porc_contacto_no,
        "total_contacto_si_admin" => $total_contacto_si_admin,
        "porc_contacto_si_admin" => $porc_contacto_si_admin,
        "total_contacto_no_admin" => $total_contacto_no_admin,
        "porc_contacto_no_admin" => $porc_contacto_no_admin,
        "total_radio" => $total_radio,
        "total_redes_sociales" => $total_redes_sociales,
        "total_rechazados" => $total_rechazados,
        "porc_rechazados" => $porc_rechazados,
        "instituciones" => $instituciones
    ]);
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
