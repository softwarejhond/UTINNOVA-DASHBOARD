<?php
// NO SE REQUIERE IMPORTAR LA CONEXIÓN PORQUE DESDE EL MAIN YA ESTÁ CONECTADA
require '.././../controller/conexion.php';
// Agregar manejo de errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $fecha = isset($_GET['date']) ? $_GET['date'] : null;
    $lote = isset($_GET['lote']) ? $_GET['lote'] : null; // Agregar parámetro lote

    // Consulta para obtener registros hasta la fecha seleccionada
    if ($fecha) {
        if ($lote) {
            // Filtrar por fecha Y lote específico
            $sql_registros_por_fecha = "SELECT COUNT(*) AS total_registrados FROM user_register WHERE DATE(creationDate) <= ? AND lote = ?";
            $stmt = $conn->prepare($sql_registros_por_fecha);
            $stmt->bind_param("si", $fecha, $lote);
        } else {
            // Solo filtrar por fecha (comportamiento anterior)
            $sql_registros_por_fecha = "SELECT COUNT(*) AS total_registrados FROM user_register WHERE DATE(creationDate) <= ?";
            $stmt = $conn->prepare($sql_registros_por_fecha);
            $stmt->bind_param("s", $fecha);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $total_registrados_por_fecha = $result->fetch_assoc()['total_registrados'];
    } else {
        $total_registrados_por_fecha = 0;
    }

    // Obtener total de usuarios registrados
    $sql_total_registrados = "SELECT COUNT(*) AS total_registrados FROM user_register";
    $result_total_registrados = mysqli_query($conn, $sql_total_registrados);
    $total_registrados = mysqli_fetch_assoc($result_total_registrados)['total_registrados'];

    // Obtener total de usuarios verificados
    $sql_total = "SELECT COUNT(*) AS total FROM user_register WHERE status = '1' AND statusAdmin = '1'";
    $result_total = mysqli_query($conn, $sql_total);
    $total_usuarios = mysqli_fetch_assoc($result_total)['total'];

    // Obtener total de usuarios en Boyacá
    $sql_boyaca = "SELECT COUNT(*) AS total_boyaca FROM user_register WHERE status = '1' AND statusAdmin = '1' AND department = 15";
    $result_boyaca = mysqli_query($conn, $sql_boyaca);
    $total_boyaca = mysqli_fetch_assoc($result_boyaca)['total_boyaca'];

    // Obtener total de usuarios en Cundinamarca
    $sql_cundinamarca = "SELECT COUNT(*) AS total_cundinamarca FROM user_register WHERE status = '1' AND statusAdmin = '1' AND department = 25";
    $result_cundinamarca = mysqli_query($conn, $sql_cundinamarca);
    $total_cundinamarca = mysqli_fetch_assoc($result_cundinamarca)['total_cundinamarca'];

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

    // Obtener total de usuarios formados
    $sql_formados = "SELECT COUNT(*) AS total_formados FROM user_register WHERE statusAdmin = 10";
    $result_formados = mysqli_query($conn, $sql_formados);
    $total_formados = mysqli_fetch_assoc($result_formados)['total_formados'];
    $porc_formados = ($total_registrados > 0) ? round(($total_formados / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios certificados
    $sql_certificados = "SELECT COUNT(*) AS total_certificados FROM user_register WHERE statusAdmin = 6";
    $result_certificados = mysqli_query($conn, $sql_certificados);
    $total_certificados = mysqli_fetch_assoc($result_certificados)['total_certificados'];
    $porc_certificados = ($total_registrados > 0) ? round(($total_certificados / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios matriculados LOTE 1
    $sql_matriculados_1 = "SELECT COUNT(*) AS total_matriculados FROM user_register WHERE statusAdmin = 3 AND lote = 1";
    $result_matriculados_1 = mysqli_query($conn, $sql_matriculados_1);
    $total_matriculados_1 = mysqli_fetch_assoc($result_matriculados_1)['total_matriculados'];
    $porc_matriculados_1 = ($total_registrados > 0) ? round(($total_matriculados_1 / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios matriculados LOTE 2
    $sql_matriculados_2 = "SELECT COUNT(*) AS total_matriculados FROM user_register WHERE statusAdmin = 3 AND lote = 2";
    $result_matriculados_2 = mysqli_query($conn, $sql_matriculados_2);
    $total_matriculados_2 = mysqli_fetch_assoc($result_matriculados_2)['total_matriculados'];
    $porc_matriculados_2 = ($total_registrados > 0) ? round(($total_matriculados_2 / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios formados LOTE 1
    $sql_formados_1 = "SELECT COUNT(*) AS total_formados FROM user_register WHERE statusAdmin = 10 AND lote = 1";
    $result_formados_1 = mysqli_query($conn, $sql_formados_1);
    $total_formados_1 = mysqli_fetch_assoc($result_formados_1)['total_formados'];
    $porc_formados_1 = ($total_registrados > 0) ? round(($total_formados_1 / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios formados LOTE 2
    $sql_formados_2 = "SELECT COUNT(*) AS total_formados FROM user_register WHERE statusAdmin = 10 AND lote = 2";
    $result_formados_2 = mysqli_query($conn, $sql_formados_2);
    $total_formados_2 = mysqli_fetch_assoc($result_formados_2)['total_formados'];
    $porc_formados_2 = ($total_registrados > 0) ? round(($total_formados_2 / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios certificados LOTE 1
    $sql_certificados_1 = "SELECT COUNT(*) AS total_certificados FROM user_register WHERE statusAdmin = 6 AND lote = 1";
    $result_certificados_1 = mysqli_query($conn, $sql_certificados_1);
    $total_certificados_1 = mysqli_fetch_assoc($result_certificados_1)['total_certificados'];
    $porc_certificados_1 = ($total_registrados > 0) ? round(($total_certificados_1 / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios certificados LOTE 2
    $sql_certificados_2 = "SELECT COUNT(*) AS total_certificados FROM user_register WHERE statusAdmin = 6 AND lote = 2";
    $result_certificados_2 = mysqli_query($conn, $sql_certificados_2);
    $total_certificados_2 = mysqli_fetch_assoc($result_certificados_2)['total_certificados'];
    $porc_certificados_2 = ($total_registrados > 0) ? round(($total_certificados_2 / $total_registrados) * 100, 2) : 0;

    // Calcular porcentajes
    $porc_boyaca = ($total_usuarios > 0) ? round(($total_boyaca / $total_registrados) * 100, 2) : 0;
    $porc_cundinamarca = ($total_usuarios > 0) ? round(($total_cundinamarca / $total_registrados) * 100, 2) : 0;
    $porc_sinVerificar = ($total_usuarios > 0) ? round(($total_sinVerificar / $total_registrados) * 100, 2) : 0;
    $porc_GobernacionBoyaca = ($total_usuarios > 0) ? round(($total_GobernacionBoyaca / $total_registrados) * 100, 2) : 0;
    // Calcular el porcentaje de usuarios verificados
    $porc_usuarios = ($total_registrados > 0) ? round(($total_usuarios / $total_registrados) * 100, 2) : 0;


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
    // Obtener total de géneros
    $queryGeneros = "SELECT gender, COUNT(*) as cantidadGender FROM user_register GROUP BY gender";
    $resultadoGeneros = $conn->query($queryGeneros);

    // Procesar los resultados de géneros
    $generos = [];
    while ($row = $resultadoGeneros->fetch_assoc()) {
        $generos[] = [
            'gener' => $row['gender'], // Cambiado a 'gender'
            'cantidad' => $row['cantidadGender'] // Cambiado a 'cantidadGender'
        ];
    }

    // Obtener total de usuarios aceptados en Lote 1
    $sql_lote1 = "SELECT COUNT(*) AS total_lote1 FROM user_register WHERE status = '1' AND statusAdmin IN ('1', '8') AND lote = 1";
    $result_lote1 = mysqli_query($conn, $sql_lote1);
    $total_lote1 = mysqli_fetch_assoc($result_lote1)['total_lote1'];
    $porc_lote1 = ($total_registrados > 0) ? round(($total_lote1 / $total_registrados) * 100, 2) : 0;

    // Obtener total de usuarios aceptados en Lote 2
    $sql_lote2 = "SELECT COUNT(*) AS total_lote2 FROM user_register WHERE status = '1' AND statusAdmin IN ('1', '8') AND lote = 2";
    $result_lote2 = mysqli_query($conn, $sql_lote2);
    $total_lote2 = mysqli_fetch_assoc($result_lote2)['total_lote2'];
    $porc_lote2 = ($total_registrados > 0) ? round(($total_lote2 / $total_registrados) * 100, 2) : 0;

    // Obtener total de géneros por lote 1
    $queryGenerosLote1 = "SELECT gender, COUNT(*) as cantidadGender FROM user_register WHERE lote = 1 GROUP BY gender";
    $resultadoGenerosLote1 = $conn->query($queryGenerosLote1);
    $generosLote1 = [];
    while ($row = $resultadoGenerosLote1->fetch_assoc()) {
        $generosLote1[] = [
            'gener' => $row['gender'],
            'cantidad' => $row['cantidadGender']
        ];
    }

    // Obtener total de géneros por lote 2
    $queryGenerosLote2 = "SELECT gender, COUNT(*) as cantidadGender FROM user_register WHERE lote = 2 GROUP BY gender";
    $resultadoGenerosLote2 = $conn->query($queryGenerosLote2);
    $generosLote2 = [];
    while ($row = $resultadoGenerosLote2->fetch_assoc()) {
        $generosLote2[] = [
            'gener' => $row['gender'],
            'cantidad' => $row['cantidadGender']
        ];
    }

    // Total registrados Lote 1
    $sql_total_registrados_lote1 = "SELECT COUNT(*) AS total_registrados_lote1 FROM user_register WHERE lote = 1";
    $result_total_registrados_lote1 = mysqli_query($conn, $sql_total_registrados_lote1);
    $total_registrados_lote1 = mysqli_fetch_assoc($result_total_registrados_lote1)['total_registrados_lote1'];

    // Total registrados Lote 2
    $sql_total_registrados_lote2 = "SELECT COUNT(*) AS total_registrados_lote2 FROM user_register WHERE lote = 2";
    $result_total_registrados_lote2 = mysqli_query($conn, $sql_total_registrados_lote2);
    $total_registrados_lote2 = mysqli_fetch_assoc($result_total_registrados_lote2)['total_registrados_lote2'];

    // Obtener total de usuarios aceptados LOTE 1
    $sql_usuarios_aceptados_lote1 = "SELECT COUNT(*) AS total FROM user_register WHERE status = '1' AND statusAdmin = '1' AND lote = 1";
    $result_usuarios_aceptados_lote1 = mysqli_query($conn, $sql_usuarios_aceptados_lote1);
    $total_usuarios_aceptados_lote1 = mysqli_fetch_assoc($result_usuarios_aceptados_lote1)['total'];
    $porc_usuarios_aceptados_lote1 = ($total_registrados_lote1 > 0) ? round(($total_usuarios_aceptados_lote1 / $total_registrados_lote1) * 100, 2) : 0;

    // Obtener total de usuarios aceptados LOTE 2
    $sql_usuarios_aceptados_lote2 = "SELECT COUNT(*) AS total FROM user_register WHERE status = '1' AND statusAdmin = '1' AND lote = 2";
    $result_usuarios_aceptados_lote2 = mysqli_query($conn, $sql_usuarios_aceptados_lote2);
    $total_usuarios_aceptados_lote2 = mysqli_fetch_assoc($result_usuarios_aceptados_lote2)['total'];
    $porc_usuarios_aceptados_lote2 = ($total_registrados_lote2 > 0) ? round(($total_usuarios_aceptados_lote2 / $total_registrados_lote2) * 100, 2) : 0;

    // Obtener total de usuarios rechazados LOTE 1
    $sql_rechazados_lote1 = "SELECT COUNT(*) AS total_rechazados FROM user_register WHERE statusAdmin = 2 AND lote = 1";
    $result_rechazados_lote1 = mysqli_query($conn, $sql_rechazados_lote1);
    $total_rechazados_lote1 = mysqli_fetch_assoc($result_rechazados_lote1)['total_rechazados'];
    $porc_rechazados_lote1 = ($total_registrados_lote1 > 0) ? round(($total_rechazados_lote1 / $total_registrados_lote1) * 100, 2) : 0;

    // Obtener total de usuarios rechazados LOTE 2
    $sql_rechazados_lote2 = "SELECT COUNT(*) AS total_rechazados FROM user_register WHERE statusAdmin = 2 AND lote = 2";
    $result_rechazados_lote2 = mysqli_query($conn, $sql_rechazados_lote2);
    $total_rechazados_lote2 = mysqli_fetch_assoc($result_rechazados_lote2)['total_rechazados'];
    $porc_rechazados_lote2 = ($total_registrados_lote2 > 0) ? round(($total_rechazados_lote2 / $total_registrados_lote2) * 100, 2) : 0;

    // Obtener total de usuarios sin verificar LOTE 1
    $sql_sin_verificar_lote1 = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND (statusAdmin = '0' OR statusAdmin NOT IN ('1', '3')) AND lote = 1";
    $result_sinVerificar_lote1 = mysqli_query($conn, $sql_sin_verificar_lote1);
    $total_sinVerificar_lote1 = mysqli_fetch_assoc($result_sinVerificar_lote1)['total_sinVerificar'];
    $porc_sinVerificar_lote1 = ($total_registrados_lote1 > 0) ? round(($total_sinVerificar_lote1 / $total_registrados_lote1) * 100, 2) : 0;

    // Obtener total de usuarios sin verificar LOTE 2
    $sql_sin_verificar_lote2 = "SELECT COUNT(*) AS total_sinVerificar FROM user_register WHERE status = '1' AND (statusAdmin = '0' OR statusAdmin NOT IN ('1', '3')) AND lote = 2";
    $result_sinVerificar_lote2 = mysqli_query($conn, $sql_sin_verificar_lote2);
    $total_sinVerificar_lote2 = mysqli_fetch_assoc($result_sinVerificar_lote2)['total_sinVerificar'];
    $porc_sinVerificar_lote2 = ($total_registrados_lote2 > 0) ? round(($total_sinVerificar_lote2 / $total_registrados_lote2) * 100, 2) : 0;

    // Obtener total de contactos establecidos (Sí) LOTE 1
    $sql_contacto_si_lote1 = "SELECT 
                            COUNT(DISTINCT cl.number_id) AS total_contactos,
                            (COUNT(DISTINCT cl.number_id) / (SELECT COUNT(*) FROM user_register WHERE lote = 1) * 100) AS porcentaje
                        FROM contact_log cl
                        JOIN user_register ur ON cl.number_id = ur.number_id
                        WHERE cl.contact_established = 1 AND ur.lote = 1";
    $result_contacto_si_lote1 = mysqli_query($conn, $sql_contacto_si_lote1);
    $contacto_si_data_lote1 = mysqli_fetch_assoc($result_contacto_si_lote1);
    $total_contacto_si_lote1 = $contacto_si_data_lote1['total_contactos'];
    $porc_contacto_si_lote1 = round($contacto_si_data_lote1['porcentaje'], 2);

    // Obtener total de contactos NO establecidos LOTE 1
    $total_contacto_no_lote1 = $total_registrados_lote1 - $total_contacto_si_lote1;
    $porc_contacto_no_lote1 = ($total_registrados_lote1 > 0) ? round(($total_contacto_no_lote1 / $total_registrados_lote1) * 100, 2) : 0;

    // Obtener total de contactos establecidos (Sí) LOTE 2
    $sql_contacto_si_lote2 = "SELECT 
                            COUNT(DISTINCT cl.number_id) AS total_contactos,
                            (COUNT(DISTINCT cl.number_id) / (SELECT COUNT(*) FROM user_register WHERE lote = 2) * 100) AS porcentaje
                        FROM contact_log cl
                        JOIN user_register ur ON cl.number_id = ur.number_id
                        WHERE cl.contact_established = 1 AND ur.lote = 2";
    $result_contacto_si_lote2 = mysqli_query($conn, $sql_contacto_si_lote2);
    $contacto_si_data_lote2 = mysqli_fetch_assoc($result_contacto_si_lote2);
    $total_contacto_si_lote2 = $contacto_si_data_lote2['total_contactos'];
    $porc_contacto_si_lote2 = round($contacto_si_data_lote2['porcentaje'], 2);

    // Obtener total de contactos NO establecidos LOTE 2
    $total_contacto_no_lote2 = $total_registrados_lote2 - $total_contacto_si_lote2;
    $porc_contacto_no_lote2 = ($total_registrados_lote2 > 0) ? round(($total_contacto_no_lote2 / $total_registrados_lote2) * 100, 2) : 0;

    // Obtener total de usuarios matriculados por grupos (usando JOIN con tabla groups) LOTE 1
    $sql_matriculados_grupos_lote1 = "SELECT COUNT(DISTINCT g.number_id) as total_matriculados_grupos 
                                 FROM groups g 
                                 INNER JOIN user_register ur ON g.number_id = ur.number_id 
                                 WHERE ur.lote = 1";
    $result_matriculados_grupos_lote1 = mysqli_query($conn, $sql_matriculados_grupos_lote1);
    $total_matriculados_grupos_lote1 = mysqli_fetch_assoc($result_matriculados_grupos_lote1)['total_matriculados_grupos'];

    // Obtener total de usuarios matriculados por grupos (usando JOIN con tabla groups) LOTE 2
    $sql_matriculados_grupos_lote2 = "SELECT COUNT(DISTINCT g.number_id) as total_matriculados_grupos 
                                 FROM groups g 
                                 INNER JOIN user_register ur ON g.number_id = ur.number_id 
                                 WHERE ur.lote = 2";
    $result_matriculados_grupos_lote2 = mysqli_query($conn, $sql_matriculados_grupos_lote2);
    $total_matriculados_grupos_lote2 = mysqli_fetch_assoc($result_matriculados_grupos_lote2)['total_matriculados_grupos'];

    // Obtener total de usuarios matriculados por grupos (sin filtro de lote, para compatibilidad)
    $sql_matriculados_grupos_total = "SELECT COUNT(DISTINCT g.number_id) as total_matriculados_grupos 
                                 FROM groups g 
                                 INNER JOIN user_register ur ON g.number_id = ur.number_id";
    $result_matriculados_grupos_total = mysqli_query($conn, $sql_matriculados_grupos_total);
    $total_matriculados_grupos_total = mysqli_fetch_assoc($result_matriculados_grupos_total)['total_matriculados_grupos'];

    $sql_radio_lote1 = "SELECT COUNT(*) AS total_radio FROM user_register WHERE knowledge_program = 'Radio' AND lote = 1";
    $result_radio_lote1 = mysqli_query($conn, $sql_radio_lote1);
    $total_radio_lote1 = mysqli_fetch_assoc($result_radio_lote1)['total_radio'];

    // Obtener total de usuarios que conocieron el programa a través de Radio LOTE 2
    $sql_radio_lote2 = "SELECT COUNT(*) AS total_radio FROM user_register WHERE knowledge_program = 'Radio' AND lote = 2";
    $result_radio_lote2 = mysqli_query($conn, $sql_radio_lote2);
    $total_radio_lote2 = mysqli_fetch_assoc($result_radio_lote2)['total_radio'];

    // Obtener total de usuarios que conocieron el programa a través de Redes sociales LOTE 1
    $sql_redes_sociales_lote1 = "SELECT COUNT(*) AS total_redes_sociales FROM user_register WHERE knowledge_program = 'Redes sociales' AND lote = 1";
    $result_redes_sociales_lote1 = mysqli_query($conn, $sql_redes_sociales_lote1);
    $total_redes_sociales_lote1 = mysqli_fetch_assoc($result_redes_sociales_lote1)['total_redes_sociales'];

    // Obtener total de usuarios que conocieron el programa a través de Redes sociales LOTE 2
    $sql_redes_sociales_lote2 = "SELECT COUNT(*) AS total_redes_sociales FROM user_register WHERE knowledge_program = 'Redes sociales' AND lote = 2";
    $result_redes_sociales_lote2 = mysqli_query($conn, $sql_redes_sociales_lote2);
    $total_redes_sociales_lote2 = mysqli_fetch_assoc($result_redes_sociales_lote2)['total_redes_sociales'];

    // Obtener total de usuarios no válidos LOTE 1
    $sql_no_validos_lote1 = "SELECT COUNT(*) AS total_no_validos FROM user_register WHERE statusAdmin = 11 AND lote = 1";
    $result_no_validos_lote1 = mysqli_query($conn, $sql_no_validos_lote1);
    $total_no_validos_lote1 = mysqli_fetch_assoc($result_no_validos_lote1)['total_no_validos'];
    $porc_no_validos_lote1 = ($total_registrados_lote1 > 0) ? round(($total_no_validos_lote1 / $total_registrados_lote1) * 100, 2) : 0;

    // Obtener total de usuarios no válidos LOTE 2
    $sql_no_validos_lote2 = "SELECT COUNT(*) AS total_no_validos FROM user_register WHERE statusAdmin = 11 AND lote = 2";
    $result_no_validos_lote2 = mysqli_query($conn, $sql_no_validos_lote2);
    $total_no_validos_lote2 = mysqli_fetch_assoc($result_no_validos_lote2)['total_no_validos'];
    $porc_no_validos_lote2 = ($total_registrados_lote2 > 0) ? round(($total_no_validos_lote2 / $total_registrados_lote2) * 100, 2) : 0;

    // Contador de estudiantes por sede para Lote 1
    $querySedesLote1 = "SELECT headquarters, COUNT(*) as cantidad FROM user_register WHERE lote = 1 GROUP BY headquarters";
    $resultadoSedesLote1 = $conn->query($querySedesLote1);
    $sedesLote1 = [];
    while ($row = $resultadoSedesLote1->fetch_assoc()) {
        $sedesLote1[] = [
            'sede' => $row['headquarters'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Contador de estudiantes por sede para Lote 2
    $querySedesLote2 = "SELECT headquarters, COUNT(*) as cantidad FROM user_register WHERE lote = 2 GROUP BY headquarters";
    $resultadoSedesLote2 = $conn->query($querySedesLote2);
    $sedesLote2 = [];
    while ($row = $resultadoSedesLote2->fetch_assoc()) {
        $sedesLote2[] = [
            'sede' => $row['headquarters'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Matriculados por sede para Lote 1 (JOIN groups y user_register)
    $querySedesMatriculadosLote1 = "
        SELECT ur.headquarters, COUNT(DISTINCT g.number_id) as cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE ur.lote = 1
        GROUP BY ur.headquarters
    ";
    $resultadoSedesMatriculadosLote1 = $conn->query($querySedesMatriculadosLote1);
    $sedesMatriculadosLote1 = [];
    while ($row = $resultadoSedesMatriculadosLote1->fetch_assoc()) {
        $sedesMatriculadosLote1[] = [
            'sede' => $row['headquarters'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Matriculados por sede para Lote 2 (JOIN groups y user_register)
    $querySedesMatriculadosLote2 = "
        SELECT ur.headquarters, COUNT(DISTINCT g.number_id) as cantidad
        FROM groups g
        INNER JOIN user_register ur ON g.number_id = ur.number_id
        WHERE ur.lote = 2
        GROUP BY ur.headquarters
    ";
    $resultadoSedesMatriculadosLote2 = $conn->query($querySedesMatriculadosLote2);
    $sedesMatriculadosLote2 = [];
    while ($row = $resultadoSedesMatriculadosLote2->fetch_assoc()) {
        $sedesMatriculadosLote2[] = [
            'sede' => $row['headquarters'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Contador de inscritos por bootcamp para Lote 1 (cursos con L1 en el nombre)
    $queryBootcampsLote1 = "
        SELECT bootcamp_name, COUNT(*) as cantidad
        FROM groups
        WHERE bootcamp_name LIKE '%L1%' AND bootcamp_name IS NOT NULL AND bootcamp_name != ''
        GROUP BY bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoBootcampsLote1 = $conn->query($queryBootcampsLote1);
    $bootcampsLote1 = [];
    while ($row = $resultadoBootcampsLote1->fetch_assoc()) {
        $bootcampsLote1[] = [
            'bootcamp' => $row['bootcamp_name'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Contador de inscritos por bootcamp para Lote 2 (cursos con L2 en el nombre)
    $queryBootcampsLote2 = "
        SELECT bootcamp_name, COUNT(*) as cantidad
        FROM groups
        WHERE bootcamp_name LIKE '%L2%' AND bootcamp_name IS NOT NULL AND bootcamp_name != ''
        GROUP BY bootcamp_name
        ORDER BY cantidad DESC
    ";
    $resultadoBootcampsLote2 = $conn->query($queryBootcampsLote2);
    $bootcampsLote2 = [];
    while ($row = $resultadoBootcampsLote2->fetch_assoc()) {
        $bootcampsLote2[] = [
            'bootcamp' => $row['bootcamp_name'],
            'cantidad' => $row['cantidad']
        ];
    }

    // Devolver los datos en formato JSON
    header('Content-Type: application/json');
    echo json_encode([
        "total_registrados" => $total_registrados,
        "total_usuarios" => $total_usuarios,
        "porc_usuarios" => $porc_usuarios,
        "total_matriculados" => $total_matriculados,
        "porc_matriculados" => $porc_matriculados,
        "total_formados" => $total_formados,
        "porc_formados" => $porc_formados,
        "total_certificados" => $total_certificados,
        "porc_certificados" => $porc_certificados,
        "total_boyaca" => $total_boyaca,
        "porc_boyaca" => $porc_boyaca,
        "total_cundinamarca" => $total_cundinamarca,
        "porc_cundinamarca" => $porc_cundinamarca,
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
        "instituciones" => $instituciones,
        "total_registrados_por_fecha" => $total_registrados_por_fecha,
        //generos
        "generos" => $generos,
        "total_lote1" => $total_lote1,
        "porc_lote1" => $porc_lote1,
        "total_lote2" => $total_lote2,
        "porc_lote2" => $porc_lote2,
        "total_matriculados_1" => $total_matriculados_1,
        "porc_matriculados_1" => $porc_matriculados_1,
        "total_matriculados_2" => $total_matriculados_2,
        "porc_matriculados_2" => $porc_matriculados_2,
        "total_formados_1" => $total_formados_1,
        "porc_formados_1" => $porc_formados_1,
        "total_formados_2" => $total_formados_2,
        "porc_formados_2" => $porc_formados_2,
        "total_certificados_1" => $total_certificados_1,
        "porc_certificados_1" => $porc_certificados_1,
        "total_certificados_2" => $total_certificados_2,
        "porc_certificados_2" => $porc_certificados_2,
        "generosLote1" => $generosLote1,
        "generosLote2" => $generosLote2,
        "total_registrados_lote1" => $total_registrados_lote1,
        "total_registrados_lote2" => $total_registrados_lote2,
        "total_usuarios_aceptados_lote1" => $total_usuarios_aceptados_lote1,
        "porc_usuarios_aceptados_lote1" => $porc_usuarios_aceptados_lote1,
        "total_usuarios_aceptados_lote2" => $total_usuarios_aceptados_lote2,
        "porc_usuarios_aceptados_lote2" => $porc_usuarios_aceptados_lote2,
        "total_rechazados_lote1" => $total_rechazados_lote1,
        "porc_rechazados_lote1" => $porc_rechazados_lote1,
        "total_rechazados_lote2" => $total_rechazados_lote2,
        "porc_rechazados_lote2" => $porc_rechazados_lote2,
        "total_sinVerificar_lote1" => $total_sinVerificar_lote1,
        "porc_sinVerificar_lote1" => $porc_sinVerificar_lote1,
        "total_sinVerificar_lote2" => $total_sinVerificar_lote2,
        "porc_sinVerificar_lote2" => $porc_sinVerificar_lote2,
        "total_contacto_si_lote1" => $total_contacto_si_lote1,
        "porc_contacto_si_lote1" => $porc_contacto_si_lote1,
        "total_contacto_no_lote1" => $total_contacto_no_lote1,
        "porc_contacto_no_lote1" => $porc_contacto_no_lote1,
        "total_contacto_si_lote2" => $total_contacto_si_lote2,
        "porc_contacto_si_lote2" => $porc_contacto_si_lote2,
        "total_contacto_no_lote2" => $total_contacto_no_lote2,
        "porc_contacto_no_lote2" => $porc_contacto_no_lote2,
        "total_radio_lote1" => $total_radio_lote1,
        "total_radio_lote2" => $total_radio_lote2,
        "total_redes_sociales_lote1" => $total_redes_sociales_lote1,
        "total_redes_sociales_lote2" => $total_redes_sociales_lote2,
        "total_matriculados_grupos_lote1" => $total_matriculados_grupos_lote1,
        "total_matriculados_grupos_lote2" => $total_matriculados_grupos_lote2,
        "total_matriculados_grupos_total" => $total_matriculados_grupos_total,
        "total_no_validos_lote1" => $total_no_validos_lote1,
        "porc_no_validos_lote1" => $porc_no_validos_lote1,
        "total_no_validos_lote2" => $total_no_validos_lote2,
        "porc_no_validos_lote2" => $porc_no_validos_lote2,
        "sedesLote1" => $sedesLote1,
        "sedesLote2" => $sedesLote2,
        "sedesMatriculadosLote1" => $sedesMatriculadosLote1,
        "sedesMatriculadosLote2" => $sedesMatriculadosLote2,
        "bootcampsLote1" => $bootcampsLote1,
        "bootcampsLote2" => $bootcampsLote2
    ]);
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
    exit;
}
