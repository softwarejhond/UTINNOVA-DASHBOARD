<?php
// filepath: c:\xampp\htdocs\DASBOARD-ADMIN-MINTICS\components\infoWeek\exportAll_from_file.php
require __DIR__ . '../../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;


// Configuraciones para evitar timeout
set_time_limit(300); // 5 minutos
ini_set('memory_limit', '1024M'); // 1GB
ini_set('max_execution_time', 300);
error_reporting(E_ALL);
ini_set('display_errors', 1);

$action = $_GET['action'] ?? '';

$filePath = __DIR__ . '/../../uploads/informe_subido.xlsx';
if (!file_exists($filePath)) {
    header('HTTP/1.1 404 Not Found');
    die('Archivo no encontrado');
}

$spreadsheet = IOFactory::load($filePath);
$sheet = $spreadsheet->getSheetByName('Data');
if (!$sheet) {
    header('HTTP/1.1 400 Bad Request');
    die('No se encontró la hoja "Data"');
}
$rows = $sheet->toArray(null, true, true, true);

switch ($action) {
    case 'export_E20':
        // Crear nuevo archivo
        $newSpreadsheet = new Spreadsheet();
        $newSheet = $newSpreadsheet->getActiveSheet();
        $newSheet->setTitle('Informe E20');

        // Encabezados
        $headers = [
            'A'  => 'Operador',
            'B'  => 'id',
            'C'  => 'Tipo_documento',
            'D'  => 'Documento',
            'E'  => 'Nombre 1',
            'F'  => 'Nombre 2',
            'G'  => 'Apellido 1',
            'H'  => 'Apellido 2',
            'I'  => 'Fecha_nacimiento',
            'J'  => 'Correo',
            'K'  => 'Codigo_epartamento',
            'L'  => 'Departamento',
            'M'  => 'Region',
            'N'  => 'Codigo_municipio',
            'O'  => 'Municipio',
            'P'  => 'Telefono_movil',
            'Q'  => 'Genero',
            'R'  => 'Campesino',
            'S'  => 'Estrato',
            'T'  => 'Autoidentificacion_Etnica',
            'U'  => 'Nivel_educacion',
            'V'  => 'Discapacidad',
            'W'  => 'Compromiso_10_horas',
            'X'  => 'Tipo_formacion',
            'Y'  => 'Acepta_requisitos_convotaria',
            'Z'  => 'Victima_del_conflicto',
            'AA' => 'Autoriza_manejo_datos_personales',
            'AB' => 'Disponibilidad_Equipo',
            'AC' => 'Presentó prueba',
            'AD' => 'Curso bootcamp al que se inscribió',
            'AE' => 'Origen',
            'AF' => 'Fecha inscripción',
            'AG' => 'Cumple requisitos',
            'AH' => 'Cohorte asignación',
            'AI' => 'Año cohorte',
            'AJ' => 'Fecha asignación',
            'AK' => 'Estado Admision'
        ];

        // Colores
        $colorAmarillo = 'FFF9CB6B'; // De A a AA
        $colorNaranja = 'FFFFB366';  // De AB a AF
        $colorMenta = 'FFB6FFD9';    // De AG a AK

        // Escribir encabezados y aplicar estilos
        foreach ($headers as $col => $header) {
            $newSheet->setCellValue($col . '1', $header);
            $newSheet->getStyle($col . '1')->getFont()->setBold(true);
            $newSheet->getStyle($col . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Asignar color según el rango de columna
            if (array_search($col, array_keys($headers)) <= array_search('AA', array_keys($headers))) {
                $color = $colorAmarillo;
            } elseif (array_search($col, array_keys($headers)) >= array_search('AB', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AF', array_keys($headers))) {
                $color = $colorNaranja;
            } elseif (array_search($col, array_keys($headers)) >= array_search('AG', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AJ', array_keys($headers))) {
                $color = $colorMenta;
            } else {
                $color = $colorMenta;
            }
            $newSheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
        }

        $rowIndex = 2;
        foreach ($rows as $i => $row) {
            if ($i == 1) continue; // Saltar encabezado original

            // Procesar todas las filas
            $newSheet->setCellValue('A' . $rowIndex, $row['A'] ?? '');
            $newSheet->setCellValue('B' . $rowIndex, $row['B' ?? '']);
            $newSheet->setCellValue('C' . $rowIndex, $row['C' ?? '']);
            $newSheet->setCellValue('D' . $rowIndex, $row['C' ?? '']);
            $newSheet->setCellValue('E' . $rowIndex, $row['E' ?? '']);
            $newSheet->setCellValue('F' . $rowIndex, $row['F' ?? '']);
            $newSheet->setCellValue('G' . $rowIndex, $row['G' ?? '']);
            $newSheet->setCellValue('H' . $rowIndex, $row['H' ?? '']);
            $newSheet->setCellValue('I' . $rowIndex, $row['I' ?? '']);
            $newSheet->setCellValue('J' . $rowIndex, $row['J' ?? '']);
            $newSheet->setCellValue('K' . $rowIndex, $row['K' ?? '']);
            $newSheet->setCellValue('L' . $rowIndex, $row['L' ?? '']);
            $newSheet->setCellValue('M' . $rowIndex, $row['M' ?? '']);
            $newSheet->setCellValue('N' . $rowIndex, $row['N' ?? '']);
            $newSheet->setCellValue('O' . $rowIndex, $row['O' ?? '']);
            $newSheet->setCellValue('P' . $rowIndex, $row['P' ?? '']);
            $newSheet->setCellValue('Q' . $rowIndex, $row['Q' ?? '']);
            $newSheet->setCellValue('R' . $rowIndex, $row['R' ?? '']);
            $newSheet->setCellValue('S' . $rowIndex, $row['S' ?? '']);
            $newSheet->setCellValue('T' . $rowIndex, $row['T' ?? '']);
            $newSheet->setCellValue('U' . $rowIndex, $row['U' ?? '']);
            $newSheet->setCellValue('V' . $rowIndex, $row['V' ?? '']);
            $newSheet->setCellValue('W' . $rowIndex, $row['Y' ?? '']);
            $newSheet->setCellValue('X' . $rowIndex, $row['Z' ?? '']);
            $newSheet->setCellValue('Y' . $rowIndex, $row['AA' ?? '']);
            $newSheet->setCellValue('Z' . $rowIndex, $row['AB' ?? '']);
            $newSheet->setCellValue('AA' . $rowIndex, $row['AC' ?? '']);
            $newSheet->setCellValue('AB' . $rowIndex, $row['AD' ?? '']);
            $newSheet->setCellValue('AC' . $rowIndex, $row['AF' ?? '']);
            $newSheet->setCellValue('AD' . $rowIndex, $row['AI' ?? '']);
            $newSheet->setCellValue('AE' . $rowIndex, $row['BG' ?? '']);
            $newSheet->setCellValue('AF' . $rowIndex, $row['AE' ?? '']);
            $newSheet->setCellValue('AG' . $rowIndex, 'SI');
            $newSheet->setCellValue('AH' . $rowIndex, $row['BO' ?? '']);
            $newSheet->setCellValue('AI' . $rowIndex, $row['BP' ?? '']);
            $newSheet->setCellValue('AJ' . $rowIndex, $row['BN' ?? '']);

            $rowIndex++;
        }

        // Descargar el archivo generado
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="informe_E20_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($newSpreadsheet);
        $writer->save('php://output');
        exit;

    case 'export_E21':
        // Filtrar donde columna BH (Matriculado) sea 'SI'
        $newSpreadsheet = new Spreadsheet();
        $newSheet = $newSpreadsheet->getActiveSheet();
        $newSheet->setTitle('Informe E21');

        // Encabezados
        $headers = [
            'A'  => 'Operador',
            'B'  => 'id',
            'C'  => 'Tipo_documento',
            'D'  => 'Documento',
            'E'  => 'Nombre 1',
            'F'  => 'Nombre 2',
            'G'  => 'Apellido 1',
            'H'  => 'Apellido 2',
            'I'  => 'Fecha_nacimiento',
            'J'  => 'Correo',
            'K'  => 'Codigo_epartamento',
            'L'  => 'Departamento',
            'M'  => 'Region',
            'N'  => 'Codigo_municipio',
            'O'  => 'Municipio',
            'P'  => 'Telefono_movil',
            'Q'  => 'Genero',
            'R'  => 'Campesino',
            'S'  => 'Estrato',
            'T'  => 'Autoidentificacion_Etnica',
            'U'  => 'Nivel_educacion',
            'V'  => 'Discapacidad',
            'W'  => 'Compromiso_10_horas',
            'X'  => 'Tipo_formacion',
            'Y'  => 'Acepta_requisitos_convotaria',
            'Z'  => 'Victima_del_conflicto',
            'AA' => 'Autoriza_manejo_datos_personales',
            'AB' => 'Disponibilidad_Equipo',
            'AC' => 'Presentó prueba',
            'AD' => 'Curso bootcamp al que se inscribió',
            'AE' => 'Origen',
            'AF' => 'Fecha inscripción',
            'AG' => 'Cumple requisitos',
            'AH' => 'Cohorte asignación',
            'AI' => 'Año cohorte asignación',
            'AJ' => 'Fecha asignación',
            'AK' => 'Matriculado',
            'AL' => 'Fecha matricula',
            'AM' => 'Curso bootcamp donde se matriculo',
            'AN' => 'Nivel',
            'AO' => 'En formacion',
            'AP' => 'Cohorte',
            'AQ' => 'Fecha inicio formacion'
        ];

        // Colores
        $colorAmarillo = 'FFF9CB6B'; // De A a AA
        $colorNaranja = 'FFFFB366';  // De AB a AF
        $colorMenta = 'FFB6FFD9';    // De AG a AJ
        $colorCeleste = 'FFB6E0FF';  // De AK a AQ

        // Escribir encabezados y aplicar estilos
        foreach ($headers as $col => $header) {
            $newSheet->setCellValue($col . '1', $header);
            $newSheet->getStyle($col . '1')->getFont()->setBold(true);
            $newSheet->getStyle($col . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Asignar color según el rango de columna
            if (array_search($col, array_keys($headers)) <= array_search('AA', array_keys($headers))) {
                $color = $colorAmarillo;
            } elseif (array_search($col, array_keys($headers)) >= array_search('AB', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AF', array_keys($headers))) {
                $color = $colorNaranja;
            } elseif (array_search($col, array_keys($headers)) >= array_search('AG', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AJ', array_keys($headers))) {
                $color = $colorMenta;
            } elseif (array_search($col, array_keys($headers)) >= array_search('AK', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AQ', array_keys($headers))) {
                $color = $colorCeleste;
            } else {
                $color = $colorAmarillo;
            }
            $newSheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
        }

        $rowIndex = 2;
        foreach ($rows as $i => $row) {
            if ($i == 1) continue; // Saltar encabezado original

            if (strtoupper(trim($row['BH'] ?? '')) !== 'SI') continue;

            $newSheet->setCellValue('A' . $rowIndex, $row['A' ?? '']);
            $newSheet->setCellValue('B' . $rowIndex, $row['B' ?? '']);
            $newSheet->setCellValue('C' . $rowIndex, $row['C' ?? '']);
            $newSheet->setCellValue('D' . $rowIndex, $row['C' ?? '']);
            $newSheet->setCellValue('E' . $rowIndex, $row['E' ?? '']);
            $newSheet->setCellValue('F' . $rowIndex, $row['F' ?? '']);
            $newSheet->setCellValue('G' . $rowIndex, $row['G' ?? '']);
            $newSheet->setCellValue('H' . $rowIndex, $row['H' ?? '']);
            $newSheet->setCellValue('I' . $rowIndex, $row['I' ?? '']);
            $newSheet->setCellValue('J' . $rowIndex, $row['J' ?? '']);
            $newSheet->setCellValue('K' . $rowIndex, $row['K' ?? '']);
            $newSheet->setCellValue('L' . $rowIndex, $row['L' ?? '']);
            $newSheet->setCellValue('M' . $rowIndex, $row['M' ?? '']);
            $newSheet->setCellValue('N' . $rowIndex, $row['N' ?? '']);
            $newSheet->setCellValue('O' . $rowIndex, $row['O' ?? '']);
            $newSheet->setCellValue('P' . $rowIndex, $row['P' ?? '']);
            $newSheet->setCellValue('Q' . $rowIndex, $row['Q' ?? '']);
            $newSheet->setCellValue('R' . $rowIndex, $row['R' ?? '']);
            $newSheet->setCellValue('S' . $rowIndex, $row['S' ?? '']);
            $newSheet->setCellValue('T' . $rowIndex, $row['T' ?? '']);
            $newSheet->setCellValue('U' . $rowIndex, $row['U' ?? '']);
            $newSheet->setCellValue('V' . $rowIndex, $row['V' ?? '']);
            $newSheet->setCellValue('W' . $rowIndex, $row['Y' ?? '']);
            $newSheet->setCellValue('X' . $rowIndex, $row['Z' ?? '']);
            $newSheet->setCellValue('Y' . $rowIndex, $row['AA' ?? '']);
            $newSheet->setCellValue('Z' . $rowIndex, $row['AB' ?? '']);
            $newSheet->setCellValue('AA' . $rowIndex, $row['AC' ?? '']);
            $newSheet->setCellValue('AB' . $rowIndex, $row['AD' ?? '']);
            $newSheet->setCellValue('AC' . $rowIndex, $row['AF' ?? '']);
            $newSheet->setCellValue('AD' . $rowIndex, $row['AI' ?? '']);
            $newSheet->setCellValue('AE' . $rowIndex, $row['BG' ?? '']);
            $newSheet->setCellValue('AF' . $rowIndex, $row['AE' ?? '']);
            $newSheet->setCellValue('AG' . $rowIndex, 'SI');
            $newSheet->setCellValue('AH' . $rowIndex, $row['BO' ?? '']);
            $newSheet->setCellValue('AI' . $rowIndex, $row['BP' ?? '']);
            $newSheet->setCellValue('AJ' . $rowIndex, $row['BN' ?? '']);
            $newSheet->setCellValue('AK' . $rowIndex, 'SI');
            $newSheet->setCellValue('AL' . $rowIndex, $row['BN' ?? '']);
            $newSheet->setCellValue('AM' . $rowIndex, $row['BJ' ?? '']);
            $newSheet->setCellValue('AN' . $rowIndex, $row['BK' ?? '']);
            $newSheet->setCellValue('AO' . $rowIndex, 'En formación');
            $newSheet->setCellValue('AP' . $rowIndex, $row['BO' ?? '']);
            $newSheet->setCellValue('AQ' . $rowIndex, $row['BN' ?? '']);

            $rowIndex++;
        }

        // Descargar el archivo generado
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="informe_E21_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($newSpreadsheet);
        $writer->save('php://output');
        exit;

    case 'export_E19_VF':
        // Filtrar donde columna BH (Matriculado) sea 'SI'
        $newSpreadsheet = new Spreadsheet();
        $newSheet = $newSpreadsheet->getActiveSheet();
        $newSheet->setTitle('E19_VF');

        // Encabezados
        $headers = [
            'A'  => 'Ejecutor (contratista)',
            'B'  => 'id',
            'C'  => 'Tipo_documento',
            'D'  => 'Documento',
            'E'  => 'Nombre1',
            'F'  => 'Nombre2',
            'G'  => 'Apellido1',
            'H'  => 'Apellido2',
            'I'  => 'Fecha_nacimiento',
            'J'  => 'Correo',
            'K'  => 'Codigo_epartamento',
            'L'  => 'Departamento',
            'M'  => 'Región',
            'N'  => 'Codigo_municipio',
            'O'  => 'Municipio',
            'P'  => 'Telefono_movil',
            'Q'  => 'Genero',
            'R'  => 'Campesino',
            'S'  => 'Estrato',
            'T'  => 'Autoidentificacion_Etnica',
            'U'  => 'Nivel_educacion',
            'V'  => 'Discapacidad',
            'W'  => 'IP',
            'X'  => 'Motivaciones',
            'Y'  => 'Compromiso_10_horas',
            'Z'  => 'Tipo_formacion_inscripcion',
            'AA' => 'Acepta_requisitos_convocatoria',
            'AB' => 'Victima_del_conflicto',
            'AC' => 'Autoriza_manejo_datos_personales',
            'AD' => 'Disponibilidad_Equipo',
            'AE' => 'creationdate',
            'AF' => 'Presento_prueba',
            'AG' => 'fecha_ini',
            'AH' => 'tiempo_segundos',
            'AI' => 'Curso_bootcamp_al_que_se_inscribio',
            'AJ' => 'Fecha_inscripción',
            'AK' => 'Curso_bootcamp_donde_se_matriculo',
            'AL' => 'Origen',
            'AM' => 'Matriculado',
            'AN' => 'Fecha_matricula',
            'AO' => 'Estado',
            'AP' => 'Nivel',
            'AQ' => 'Fecha Inicio de la formacion',
            'AR' => 'Cohorte_asignacion',
            'AS' => 'Año Cohorte asignacion',
            'AT' => 'Cohorte de formación',
            'AU' => 'Año_cohorte_formación',
            'AV' => 'Fecha_de_terminacion',
            'AW' => 'Cumple requisitos',
            'AX' => 'Tipo de formación_matricula',
            'AY' => 'Observaciones',
            'AZ' => 'codigo del curso',
            'BA' => 'Nombre del curso',
            'BB' => '% de asistencia',
            'BC' => 'Asistencias programadas',
            'BD' => 'Documento_Profesor principal a cargo del programa de formación',
            'BE' => 'Profesor principal a cargo del programa de formación',
            'BF' => 'Documento_Mentor',
            'BG' => 'Mentor',
            'BH' => 'Documento_Monitor',
            'BI' => 'Monitor',
            'BJ' => 'Documento_Ejecutor de ingles',
            'BK' => 'Ejecutor de ingles',
            'BL' => 'Documento_Ejecutor habilidades de poder',
            'BM' => 'Ejecutor habilidades de poder',
            'BN' => 'Estado Admision'
        ];

        // Colores
        $colorMostaza = 'FFF9CB6B'; // De A a AK
        $colorVerde = 'FFB6FFD9';   // De AL a AN, AP a AR, AT, AV a AY
        $colorAmarillo = 'FFFFFF99'; // AO, AZ a BM
        $colorNaranja = 'FFFFB366'; // AS y AU
        $colorCeleste = 'FFB6E0FF'; // No usado aquí, pero puedes agregar si lo necesitas

        // Escribir encabezados y aplicar estilos
        foreach ($headers as $col => $header) {
            $newSheet->setCellValue($col . '1', $header);
            $newSheet->getStyle($col . '1')->getFont()->setBold(true);

            // Color de fondo según rango
            if (array_search($col, array_keys($headers)) <= array_search('AK', array_keys($headers))) {
                $color = $colorMostaza;
            } elseif (
                (array_search($col, array_keys($headers)) >= array_search('AL', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AN', array_keys($headers))) ||
                (array_search($col, array_keys($headers)) >= array_search('AP', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AR', array_keys($headers))) ||
                $col == 'AT' ||
                (array_search($col, array_keys($headers)) >= array_search('AV', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('AY', array_keys($headers)))
            ) {
                $color = $colorVerde;
            } elseif ($col == 'AO' || (array_search($col, array_keys($headers)) >= array_search('AZ', array_keys($headers)) && array_search($col, array_keys($headers)) <= array_search('BM', array_keys($headers)))) {
                $color = $colorAmarillo;
            } elseif ($col == 'AS' || $col == 'AU') {
                $color = $colorNaranja;
            } else {
                $color = $colorMostaza;
            }
            $newSheet->getStyle($col . '1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB($color);
            $newSheet->getStyle($col . '1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $rowIndex = 2;
        foreach ($rows as $i => $row) {
            if ($i == 1) continue; // Saltar encabezado original

            // % asistencia
            $asistencia = '';
            if (isset($row['BV']) && is_numeric($row['BV'])) {
                $asistencia = round(($row['BV'] / 159) * 100, 2) . '%';
            }

            // Cumple requisitos - siempre 'SI'
            $cumple = 'SI';

            $newSheet->setCellValue('A' . $rowIndex, $row['A' ?? '']);
            $newSheet->setCellValue('B' . $rowIndex, $row['B' ?? '']);
            $newSheet->setCellValue('C' . $rowIndex, $row['C' ?? '']);
            $newSheet->setCellValue('D' . $rowIndex, $row['D' ?? '']);
            $newSheet->setCellValue('E' . $rowIndex, $row['E' ?? '']);
            $newSheet->setCellValue('F' . $rowIndex, $row['F' ?? '']);
            $newSheet->setCellValue('G' . $rowIndex, $row['G' ?? '']);
            $newSheet->setCellValue('H' . $rowIndex, $row['H' ?? '']);
            $newSheet->setCellValue('I' . $rowIndex, $row['I' ?? '']);
            $newSheet->setCellValue('J' . $rowIndex, $row['J' ?? '']);
            $newSheet->setCellValue('K' . $rowIndex, $row['K' ?? '']);
            $newSheet->setCellValue('L' . $rowIndex, $row['L' ?? '']);
            $newSheet->setCellValue('M' . $rowIndex, $row['M' ?? '']);
            $newSheet->setCellValue('N' . $rowIndex, $row['N' ?? '']);
            $newSheet->setCellValue('O' . $rowIndex, $row['O' ?? '']);
            $newSheet->setCellValue('P' . $rowIndex, $row['P' ?? '']);
            $newSheet->setCellValue('Q' . $rowIndex, $row['Q' ?? '']);
            $newSheet->setCellValue('R' . $rowIndex, $row['R' ?? '']);
            $newSheet->setCellValue('S' . $rowIndex, $row['S' ?? '']);
            $newSheet->setCellValue('T' . $rowIndex, $row['T' ?? '']);
            $newSheet->setCellValue('U' . $rowIndex, $row['U' ?? '']);
            $newSheet->setCellValue('V' . $rowIndex, $row['V' ?? '']);
            $newSheet->setCellValue('W' . $rowIndex, '');
            $newSheet->setCellValue('X' . $rowIndex, $row['X' ?? '']);
            $newSheet->setCellValue('Y' . $rowIndex, $row['Y' ?? '']);
            $newSheet->setCellValue('Z' . $rowIndex, $row['Z' ?? '']);
            $newSheet->setCellValue('AA' . $rowIndex, $row['AA' ?? '']);
            $newSheet->setCellValue('AB' . $rowIndex, $row['AB' ?? '']);
            $newSheet->setCellValue('AC' . $rowIndex, $row['AC' ?? '']);
            $newSheet->setCellValue('AD' . $rowIndex, $row['AD' ?? '']);
            $newSheet->setCellValue('AE' . $rowIndex, $row['AE' ?? '']);
            $newSheet->setCellValue('AF' . $rowIndex, $row['AF' ?? '']);
            $newSheet->setCellValue('AG' . $rowIndex, $row['AG' ?? '']);
            $newSheet->setCellValue('AH' . $rowIndex, '');
            $newSheet->setCellValue('AI' . $rowIndex, $row['AI' ?? '']);
            $newSheet->setCellValue('AJ' . $rowIndex, $row['AE' ?? '']);
            $newSheet->setCellValue('AK' . $rowIndex, $row['BJ' ?? '']);
            $newSheet->setCellValue('AL' . $rowIndex, $row['BG' ?? '']);
            $newSheet->setCellValue('AM' . $rowIndex, $row['BH' ?? '']);
            $newSheet->setCellValue('AN' . $rowIndex, $row['BN'] ?? '');
            $newSheet->setCellValue('AO' . $rowIndex, $row['BI' ?? '']);
            $newSheet->setCellValue('AP' . $rowIndex, $row['BK' ?? '']);
            $newSheet->setCellValue('AQ' . $rowIndex, $row['BN' ?? '']);
            $newSheet->setCellValue('AR' . $rowIndex, $row['BO' ?? '']);
            $newSheet->setCellValue('AS' . $rowIndex, $row['BP' ?? '']);
            $newSheet->setCellValue('AT' . $rowIndex, $row['BO' ?? '']);
            $newSheet->setCellValue('AU' . $rowIndex, $row['BP' ?? '']);
            $newSheet->setCellValue('AV' . $rowIndex, '');
            $newSheet->setCellValue('AW' . $rowIndex, $cumple);
            $newSheet->setCellValue('AX' . $rowIndex, $row['BQ' ?? '']);
            $newSheet->setCellValue('AY' . $rowIndex, '');
            $newSheet->setCellValue('AZ' . $rowIndex, $row['BT' ?? '']);
            $newSheet->setCellValue('BA' . $rowIndex, $row['BU' ?? '']);
            $newSheet->setCellValue('BB' . $rowIndex, $asistencia);
            $newSheet->setCellValue('BC' . $rowIndex, $row['BW' ?? '']);
            $newSheet->setCellValue('BD' . $rowIndex, $row['BL' ?? '']);
            $newSheet->setCellValue('BE' . $rowIndex, $row['BM' ?? '']);
            $newSheet->setCellValue('BF' . $rowIndex, $row['BX' ?? '']);
            $newSheet->setCellValue('BG' . $rowIndex, $row['BY' ?? '']);
            $newSheet->setCellValue('BH' . $rowIndex, $row['BZ' ?? '']);
            $newSheet->setCellValue('BI' . $rowIndex, $row['CA' ?? '']);
            $newSheet->setCellValue('BJ' . $rowIndex, $row['CB' ?? '']);
            $newSheet->setCellValue('BK' . $rowIndex, $row['CC' ?? '']);
            $newSheet->setCellValue('BL' . $rowIndex, $row['CD' ?? '']);
            $newSheet->setCellValue('BM' . $rowIndex, $row['CE' ?? '']);
            $newSheet->setCellValue('BN' . $rowIndex, '');

            $rowIndex++;
        }

        // Descargar el archivo generado
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="E19_VF_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($newSpreadsheet);
        $writer->save('php://output');
        exit;

    default:
        header('HTTP/1.1 400 Bad Request');
        die('Acción no válida');
}
