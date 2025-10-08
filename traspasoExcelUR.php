<?php
require 'vendor/autoload.php'; // PhpSpreadsheet
require_once __DIR__ . '/controller/conexion.php'; // Conexión BD

use PhpOffice\PhpSpreadsheet\IOFactory;

// Cargar el archivo Excel
$inputFileName = __DIR__ . '/uploads/convenio_Cesar.xlsx';
$spreadsheet = IOFactory::load($inputFileName);
$sheet = $spreadsheet->getSheetByName('Validos'); // Usar la hoja "Validos"

if (!$sheet) {
    die('No se encontró la hoja "Validos".');
}

// Recorrer las filas desde la fila 2 (ignorando encabezado)
foreach ($sheet->getRowIterator(2) as $row) {
    $cellIterator = $row->getCellIterator();
    $cellIterator->setIterateOnlyExistingCells(false);

    $data = [];
    foreach ($cellIterator as $cell) {
        $data[] = $cell->getValue();
    }

    // Asignar valores según columnas
    $nombres = isset($data[0]) && $data[0] != 0 ? explode(' ', $data[0]) : ['','','',''];
    $number_id = isset($data[1]) && $data[1] != 0 ? intval($data[1]) : 0;
    $email = isset($data[2]) && $data[2] != 0 ? $data[2] : '';
    $first_phone = isset($data[3]) && $data[3] != 0 ? $data[3] : '';
    $program = isset($data[4]) && $data[4] != 0 ? $data[4] : '';

    // Separar nombres (máximo 4 partes)
    $first_name = isset($nombres[0]) ? $nombres[0] : '';
    $second_name = isset($nombres[1]) ? $nombres[1] : '';
    $first_last = isset($nombres[2]) ? $nombres[2] : '';
    $second_last = isset($nombres[3]) ? $nombres[3] : '';

    // Evitamos usar bind_param con muchos parámetros y usamos una inserción más directa
    $sql = "INSERT INTO user_register SET 
        typeID = '',
        number_id = $number_id,
        number_id_very = $number_id,
        first_name = '" . $conn->real_escape_string($first_name) . "',
        second_name = '" . $conn->real_escape_string($second_name) . "',
        first_last = '" . $conn->real_escape_string($first_last) . "',
        second_last = '" . $conn->real_escape_string($second_last) . "',
        birthdate = '0000-00-00',
        expedition_date = '0000-00-00',
        gender = '',
        marital_status = '',
        email = '" . $conn->real_escape_string($email) . "',
        email_very = '" . $conn->real_escape_string($email) . "',
        first_phone = '" . $conn->real_escape_string($first_phone) . "',
        second_phone = '',
        password = '',
        emergency_contact_name = '',
        emergency_contact_number = '',
        nationality = '',
        department = '',
        municipality = '',
        address = '',
        latitud = '',
        longitud = '',
        people_charge = 0,
        vulnerable_population = '',
        vulnerable_type = '',
        ethnic_group = '',
        stratum = 0,
        residence_area = '',
        training_level = '',
        occupation = '',
        time_obligations = '',
        motivations_belong_program = '',
        current_situation = '',
        impediment_complete_course = '',
        availability = '',
        mode = '',
        headquarters = '',
        program = '" . $conn->real_escape_string($program) . "',
        schedules = '',
        schedules_alternative = '',
        prior_knowledge = '',
        level = '',
        languages = '',
        languages_level = '',
        medical_condition = '',
        disability = '',
        type_disability = '',
        pregnancy = '',
        country_person = '',
        technologies = '',
        internet = '',
        knowledge_program = '',
        accept_requirements = '',
        accepts_tech_talent = '',
        accept_data_policies = '',
        file_front_id = '',
        file_back_id = '',
        status = 0,
        statusAdmin = 0,
        lote = 0,
        directed_base = 0,
        idCourse = 0,
        contactMedium = '',
        institution = '',
        creationDate = NOW(),
        dayUpdate = NOW()";

    if (!$conn->query($sql)) {
        echo "Error en fila: " . $first_name . " " . $first_last . " - " . $conn->error . "<br>";
    } else {
        echo "Insertado: " . $first_name . " " . $first_last . " (" . $number_id . ")<br>";
    }
}

echo "<br><b>Traspaso completado.</b>";
?>