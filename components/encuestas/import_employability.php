<?php
require __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

$archivo = __DIR__ . '/formulario_2.xlsx';

try {
    $spreadsheet = IOFactory::load($archivo);
    $sheet = $spreadsheet->getActiveSheet();
    $rows = $sheet->toArray(null, true, true, true);

    // Asume que la primera fila es el header
    $headers = array_map('trim', $rows[1]);
    unset($rows[1]);

    foreach ($rows as $row) {
        $data = array_combine($headers, $row);

        // Salta filas vacías (ajusta el campo clave según tu archivo)
        if (
            empty($data['typeID']) &&
            empty($data['number_id']) &&
            empty($data['first_name']) &&
            empty($data['first_last'])
        ) {
            continue;
        }

        // Prepara los valores para los campos de la tabla
        $typeID = $data['typeID'] ?? '';
        $number_id = $data['number_id'] ?? '';
        $lote = 0; // No existe en el archivo, valor por defecto
        $first_name = $data['first_name'] ?? '';
        $second_name = $data['second_name'] ?? '';
        $first_last = $data['first_last'] ?? '';
        $second_last = $data['second_last'] ?? '';
        $email = ''; // No existe en el archivo
        $interest = $data['interest'] ?? '';
        $start_training_date = !empty($data['start_training_date']) ? date('Y-m-d', strtotime($data['start_training_date'])) : '0000-00-00';
        $personal_description = null;
        $localidad = $data['localidad'] ?? '';
        $nivel_educativo = $data['nivel_educativo'] ?? '';
        $gender = $data['gender'] ?? '';
        $work_experience = null;
        $current_employment_status = $data['current_employment_status'] ?? '';
        $tech_experience = $data['tech_experience'] ?? '';
        $job_profile = null;
        $tech_experience_years = $data['tech_experience_years'] ?? 0;
        $last_tech_role = $data['last_tech_role'] ?? '';
        $skills_knowledge = null;
        $digital_skills = $data['digital_skills'] ?? null;
        $soft_skills = $data['soft_skills'] ?? null;
        $professional_networks = $data['professional_networks'] ?? null;
        $desired_role = $data['desired_role'] ?? '';
        $accept_requirements = 1;
        $accept_data_policies = 1;
        $fecha_registro = !empty($data['fecha_registro']) ? date('Y-m-d H:i:s', strtotime($data['fecha_registro'])) : date('Y-m-d H:i:s');

        $stmt = $conn->prepare("
            INSERT INTO employability (
                typeID, number_id, lote, first_name, second_name, first_last, second_last, email, interest,
                start_training_date, personal_description, localidad, nivel_educativo, gender, work_experience,
                current_employment_status, tech_experience, job_profile, tech_experience_years, last_tech_role,
                skills_knowledge, digital_skills, soft_skills, professional_networks, desired_role,
                accept_requirements, accept_data_policies, fecha_registro
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->bind_param(
            "sissssssssssssssssisssssiiis",
            $typeID, $number_id, $lote, $first_name, $second_name, $first_last, $second_last, $email, $interest,
            $start_training_date, $personal_description, $localidad, $nivel_educativo, $gender, $work_experience,
            $current_employment_status, $tech_experience, $job_profile, $tech_experience_years, $last_tech_role,
            $skills_knowledge, $digital_skills, $soft_skills, $professional_networks, $desired_role,
            $accept_requirements, $accept_data_policies, $fecha_registro
        );

        $stmt->execute();
    }

    echo "Importación completada correctamente.";
} catch (Exception $e) {
    echo "Error al importar: " . $e->getMessage();
}
?>