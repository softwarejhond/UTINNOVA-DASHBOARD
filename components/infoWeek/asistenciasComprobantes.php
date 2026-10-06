<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../controller/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Cache-Control: max-age=0');

try {
    // PEGUE AQUI LOS number_id DENTRO DEL ARRAY (SIN COMILLAS)
    $number_id_list = [
        80442478,
        1000511602,
        1052701048,
        80217644,
        1030599050,
        1070730577,
        79906530,
        28980314,
        1033820282,
        1023860736,
        1016949034,
        1022418367,
        1122921158,
        19293001,
        80832599,
        1024576975,
        1042850378,
        1010188303,
        52900161,
        1075317725,
        24738465,
        1026290292,
        1016114006,
        1012438446,
        1018420268,
        1023881097,
        80228363,
        1013671071,
        1007652333,
        1032499816,
        1028782539,
        1093783491,
        1001301297,
        1003569212,
        1022392904,
        5951325,
        1022359770,
        1075653927,
        1026558724,
        93409790,
        80850598,
        1023956543,
        1020821749,
        79304345,
        1000474027,
        1026589382,
        1023946972,
        91531754,
        1016108814,
        1018429913,
        1121849034,
        1023935463,
        1022351122,
        1018437009,
        1014215462,
        79339782,
        79691190,
        1121827290,
        4266834,
        1070709001,
        1110505489,
        1019013873,
        1046430453,
        1032460591,
        80009353,
        53028601,
        1075675467,
        1108640973,
        1000120864,
        1030688089,
        1032426971,
        1019129695,
        1019067288,
        79949773,
        80799368,
        80167877,
        79902541,
        52931637,
        1065603891,
        1094886671,
        1013676781,
        93410313,
        1015419726,
        1019066229,
        1018446942,
        52984786,
        80728997,
        51910843,
        1012391626,
        73009554,
        1023946203,
        1012392574,
        1016010907,
        1000287509,
        79539679,
        1001172336,
        52794109,
        80920272,
        1192792452,
        1013641883,
        80058946,
        52357488,
        3276152,
        1026563863,
        19343603,
        80123548,
        80824923,
        1032486242,
        80128000,
        79916236,
        1000463951,
        1016025365,
        1033736907,
        80844602,
        1070014782,
        52828866,
        1024521239,
        79880200,
        1019031414,
        1004543251,
        1031541017,
        80206922,
        1000378873,
        1121917930,
        1025533771,
        74339583,
        1023968130,
        1023035210,
        1019052092,
        1238351360,
        1018452765,
        1014232470,
        1003950480,
        91448500,
        1116499697,
        1030692028,
        1018422585,
        1024537924,
        1030681418,
        1013662458,
        80075818,
        1012388897,
        1088731960,
        79509035,
        1010043808,
        1022379045,
        52964949,
        91497951,
        1013654655,
        1020779406,
        1014666142,
        80062179,
        1000380012,
        1000708574,
        1010235183,
        1019055328,
        38361551,
        52750208,
        1020735599,
        1026293113,
        1033714941,
        1043974531,
        1027152061,
        1123208311,
        1003070297,
        1014199596,
        1107976864,
        79591884,
        14012008,
        80161921,
        19295965,
        80135402,
        1069264235,
        1000383076,
        91246577,
        1149684179,
        40219923,
        1140847063,
        1019992443,
        1010182637,
        1014990203,
        1023863794,
        80845573,
        1031811439,
        1034517967,
        1023962943,
        1033706113,
        7307425,
        1107978016,
        1031170421,
        1033716166,
        1016092242,
        1026294276,
        1034780656,
        1033711812,
        1019084125,
        1026266992,
        1012344824,
        1016094278,
        1030670120,
        11448590,
        1019117378,
        1000786327,
        80738734,
        52497309,
        1233512860,
        80728108,
        1023363948,
        1026274119,
        1047475512,
        18415798,
        1014217233,
        52751991,
        1140419450,
        1233899193,
        79940484,
        1233511107,
        1024548275,
        63497760,
        80856602,
        1049373314,
        1018462296,
        1141314393,
        1024469676,
        1020770613,
        80897692,
        1024512514,
        1022980433,
        51867855,
        1094169671,
        52918431,
        1015440636,
        1015480376,
        79602578,
        1019133560,
        1000832223,
        1032936455,
        79896560,
        1013623896,
        1024524608,
        1012415790,
        1015455258,
        1023891316,
        1015458484,
        1013632807,
        1031143230,
        1049616897,
        1019086729,
        1023946646,
        1022415111,
        1069722051,
        51822288,
        1024487337,
        1013655273,
        1018475238,
        7715942,
        52853827,
        40445344,
        79333038,
        1007450552,
        1000350326,
        1007698675,
        1005091347,
        1072496953,
        1022400973,
        80927172,
        1033752345,
        1024572008,
        1024555244,
        1031421225,
        1020823245,
        1022418222,
        1024516668,
        1005772474,
        1087209056,
        1007435250,
        1000378855,
        1070324986,
        1073712411,
        1073252857,
        1013679155,
        1110549937,
        1007290093,
        1031159382,
        1015452798,
        1192778048,
        1034778409,
        1001328047,
        1005827241,
        1022404446,
        1030607003,
        1003814231,
        1007000203,
        1001280164,
        1014228643,
        1024581368,
        1013579778,
        1023928365,
        1024496240,
        1019991940,
        1014305271,
        1003409082,
        1233893189,
        1069462121,
        1014250355,
        1004035592,
        79838438,
        1032836663,
        1014596445,
        1112098977,
        1013659882,
        1000856219,
        1033535090,
        1073714243,
        1001066232,
        1022409377,
        1014279145,
        1020826407,
        1022347226,
        1030679645,
        1136910662,
        1024555599,
        1014188873,
        1005718343,
        1000463522,
        1031162426,
        1022946598,
        1022982899
    ];

    $numberIdsArray = array_map('strval', $number_id_list);
    $numberIdsArray = array_values(array_unique(array_filter($numberIdsArray)));

    if (empty($numberIdsArray)) {
        throw new Exception('Debe agregar al menos un number_id en el array $number_id_list.');
    }

    // Crear placeholders para la consulta preparada
    $placeholders = implode(',', array_fill(0, count($numberIdsArray), '?'));

    // Consultar students en user_register + groups por los number_ids proporcionados
    $sql = "SELECT DISTINCT 
        ur.number_id,
        ur.level,
        ur.lote,
        g.full_name,
        g.program,
        g.headquarters,
        g.mode,
        g.id_bootcamp,
        g.bootcamp_name,
        g.id_english_code,
        g.english_code_name,
        g.id_skills,
        g.skills_name
    FROM user_register ur
    LEFT JOIN groups g ON ur.number_id = g.number_id
    WHERE ur.number_id IN ($placeholders)
    ORDER BY ur.number_id";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Error al preparar la consulta: ' . mysqli_error($conn));
    }

    // Bind dinámico de parámetros
    $types = str_repeat('s', count($numberIdsArray));
    $stmt->bind_param($types, ...$numberIdsArray);
    $stmt->execute();
    $result = $stmt->get_result();

    $allStudents = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $allStudents[] = $row;
    }
    $stmt->close();

    // Separar estudiantes por modo (Presencial / Virtual)
    $studentsPresencial = [];
    $studentsVirtual = [];
    foreach ($allStudents as $student) {
        $mode = strtolower(trim($student['mode'] ?? ''));
        if ($mode === 'presencial') {
            $studentsPresencial[] = $student;
        } else {
            $studentsVirtual[] = $student;
        }
    }

    // Función para obtener el color según el estado de asistencia
    function getAttendanceColor($status)
    {
        switch (strtolower($status)) {
            case 'presente':
            case 'present':
                return 'FF28A745';
            case 'ausente':
            case 'absent':
                return 'FFDC3545';
            case 'tarde':
            case 'late':
                return 'FFFFC107';
            case 'excusa':
            case 'excused':
                return 'FF17A2B8';
            default:
                return 'FF6C757D';
        }
    }

    // Función para formatear el estado de asistencia
    function formatAttendanceStatus($date, $status)
    {
        if (!$date || !$status) {
            return '-';
        }

        $dateObj = DateTime::createFromFormat('Y-m-d', $date);
        $formattedDate = $dateObj ? $dateObj->format('d/m/Y') : $date;

        $statusText = '';
        switch (strtolower($status)) {
            case 'presente':
            case 'present':
                $statusText = 'Presente';
                break;
            case 'ausente':
            case 'absent':
                $statusText = 'Ausente';
                break;
            case 'tarde':
            case 'late':
                $statusText = 'Tarde';
                break;
            case 'excusa':
            case 'excused':
                $statusText = 'Excusa';
                break;
            default:
                $statusText = 'N/A';
                break;
        }

        return $formattedDate . "\n" . $statusText;
    }

    // Definir los componentes a procesar
    $components = [
        'tecnico' => [
            'field' => 'id_bootcamp',
            'name_field' => 'bootcamp_name',
            'label' => 'Tecnico'
        ],
        'english_code' => [
            'field' => 'id_english_code',
            'name_field' => 'english_code_name',
            'label' => 'English Code'
        ],
        'habilidades' => [
            'field' => 'id_skills',
            'name_field' => 'skills_name',
            'label' => 'Habilidades'
        ]
    ];

    /**
     * Procesa los datos de asistencia para un grupo de estudiantes y devuelve
     * los arrays necesarios para escribir la hoja de cálculo.
     */
    function buildAttendanceData($conn, $students, $components)
    {
        $componentMaxDates = [];
        $studentsAttendanceByComponent = [];
        $maxAttendancesByComponent = [];

        foreach ($components as $componentType => $componentConfig) {
            $studentsByComponent = [];

            foreach ($students as $student) {
                $course_id = $student[$componentConfig['field']];
                if (!empty($course_id)) {
                    if (!isset($studentsByComponent[$course_id])) {
                        $studentsByComponent[$course_id] = [];
                    }
                    $studentsByComponent[$course_id][] = $student;
                }
            }

            $componentCourseMaxDates = [];
            foreach ($studentsByComponent as $course_id => $courseStudents) {
                $allCourseDates = [];

                foreach ($courseStudents as $student) {
                    $number_id = $student['number_id'];

                    $attendanceSql = "SELECT DISTINCT class_date 
                                     FROM attendance_records 
                                     WHERE student_id = ? AND course_id = ? 
                                     ORDER BY class_date ASC";

                    $stmt = $conn->prepare($attendanceSql);
                    if ($stmt) {
                        $stmt->bind_param('si', $number_id, $course_id);
                        $stmt->execute();
                        $attendanceResult = $stmt->get_result();

                        while ($dateRow = $attendanceResult->fetch_assoc()) {
                            if (!in_array($dateRow['class_date'], $allCourseDates)) {
                                $allCourseDates[] = $dateRow['class_date'];
                            }
                        }
                        $stmt->close();
                    }
                }

                sort($allCourseDates);
                $componentCourseMaxDates[$course_id] = $allCourseDates;
            }

            $componentMaxDates[$componentType] = $componentCourseMaxDates;

            $componentStudentsAttendance = [];
            foreach ($students as $student) {
                $number_id = $student['number_id'];
                $course_id = $student[$componentConfig['field']];

                if (!empty($course_id)) {
                    $courseDates = $componentCourseMaxDates[$course_id] ?? [];

                    $attendanceSql = "SELECT class_date, attendance_status 
                                     FROM attendance_records 
                                     WHERE student_id = ? AND course_id = ? 
                                     ORDER BY class_date ASC";

                    $stmt = $conn->prepare($attendanceSql);
                    $existingAttendances = [];

                    if ($stmt) {
                        $stmt->bind_param('si', $number_id, $course_id);
                        $stmt->execute();
                        $attendanceResult = $stmt->get_result();

                        while ($attendanceRow = $attendanceResult->fetch_assoc()) {
                            $existingAttendances[$attendanceRow['class_date']] = $attendanceRow['attendance_status'];
                        }
                        $stmt->close();
                    }

                    $completeAttendances = [];
                    foreach ($courseDates as $date) {
                        if (isset($existingAttendances[$date])) {
                            $completeAttendances[] = [
                                'class_date' => $date,
                                'attendance_status' => $existingAttendances[$date]
                            ];
                        } else {
                            $completeAttendances[] = [
                                'class_date' => $date,
                                'attendance_status' => 'ausente'
                            ];
                        }
                    }

                    $componentStudentsAttendance[$number_id] = $completeAttendances;
                } else {
                    $componentStudentsAttendance[$number_id] = [];
                }
            }

            $studentsAttendanceByComponent[$componentType] = $componentStudentsAttendance;
        }

        // Calcular el número máximo de asistencias por componente
        foreach ($components as $componentType => $componentConfig) {
            $maxAttendances = 0;
            if (isset($componentMaxDates[$componentType])) {
                foreach ($componentMaxDates[$componentType] as $dates) {
                    $maxAttendances = max($maxAttendances, count($dates));
                }
            }
            $maxAttendancesByComponent[$componentType] = $maxAttendances;
        }

        return [
            'componentMaxDates' => $componentMaxDates,
            'studentsAttendanceByComponent' => $studentsAttendanceByComponent,
            'maxAttendancesByComponent' => $maxAttendancesByComponent
        ];
    }

    /**
     * Escribe una hoja de datos de asistencia con los estudiantes proporcionados.
     */
    function writeAttendanceSheet($sheet, $students, $components, $attendanceData)
    {
        $componentMaxDates = $attendanceData['componentMaxDates'];
        $studentsAttendanceByComponent = $attendanceData['studentsAttendanceByComponent'];
        $maxAttendancesByComponent = $attendanceData['maxAttendancesByComponent'];

        if (empty($students)) {
            $sheet->setCellValue('A1', 'No hay estudiantes en esta modalidad');
            $sheet->getStyle('A1')->getFont()->setBold(true);
            return;
        }

        // Crear encabezados básicos
        $headers = [
            'Cedula',
            'Region',
            'Lote',
            'Curso Tecnico',
            'Nivel',
            'Ano Certificacion',
            'Sede Formacion'
        ];

        // Agregar columnas de asistencias a los encabezados para cada componente
        foreach ($components as $componentType => $componentConfig) {
            $maxAttendances = $maxAttendancesByComponent[$componentType];
            for ($i = 1; $i <= $maxAttendances; $i++) {
                $headers[] = $componentConfig['label'] . " - Asistencia $i";
            }
        }

        // Escribir encabezados
        $col = 1;
        foreach ($headers as $header) {
            $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . '1';
            $sheet->setCellValue($cellRef, $header);

            $sheet->getStyle($cellRef)->getFont()->setBold(true);
            $sheet->getStyle($cellRef)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFE9ECEF');
            $sheet->getStyle($cellRef)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER);

            $col++;
        }

        // Escribir datos de estudiantes
        $row = 2;
        foreach ($students as $student) {
            $col = 1;
            $number_id = $student['number_id'];

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['number_id']);

            $lote = $student['lote'] ?? '1';
            $regionText = 'Region 7 - Lote ' . $lote;
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $regionText);

            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $lote);
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['bootcamp_name'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['level'] ?? 'N/A');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, '2026');
            $sheet->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col++) . $row, $student['headquarters'] ?? 'N/A');

            foreach ($components as $componentType => $componentConfig) {
                $attendances = $studentsAttendanceByComponent[$componentType][$number_id] ?? [];
                $maxAttendances = $maxAttendancesByComponent[$componentType];

                for ($i = 0; $i < $maxAttendances; $i++) {
                    $cellRef = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col) . $row;

                    if (isset($attendances[$i])) {
                        $attendance = $attendances[$i];
                        $cellValue = formatAttendanceStatus(
                            $attendance['class_date'],
                            $attendance['attendance_status']
                        );
                        $sheet->setCellValue($cellRef, $cellValue);

                        $color = getAttendanceColor($attendance['attendance_status']);
                        $sheet->getStyle($cellRef)->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()->setARGB($color);

                        $sheet->getStyle($cellRef)->getFont()->getColor()->setARGB('FFFFFFFF');
                    } else {
                        $sheet->setCellValue($cellRef, '-');
                        $sheet->getStyle($cellRef)->getFont()->getColor()->setARGB('FF000000');
                    }

                    $sheet->getStyle($cellRef)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER)
                        ->setWrapText(true);

                    $col++;
                }
            }

            $row++;
        }

        // Ajustar ancho de columnas básicas
        $sheet->getColumnDimension('A')->setWidth(15);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(8);
        $sheet->getColumnDimension('D')->setWidth(25);
        $sheet->getColumnDimension('E')->setWidth(12);
        $sheet->getColumnDimension('F')->setWidth(12);
        $sheet->getColumnDimension('G')->setWidth(20);

        // Ajustar ancho de columnas de asistencias
        $totalBasicColumns = 7;
        $totalAttendanceColumns = array_sum($maxAttendancesByComponent);

        for ($i = $totalBasicColumns + 1; $i <= ($totalBasicColumns + $totalAttendanceColumns); $i++) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($colLetter)->setWidth(15);
        }

        // Aplicar bordes a toda la tabla
        $highestRow = $sheet->getHighestRow();
        $highestCol = $sheet->getHighestColumn();
        $sheet->getStyle('A1:' . $highestCol . $highestRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Configurar altura de filas
        for ($i = 2; $i <= $highestRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(40);
        }
        $sheet->getRowDimension(1)->setRowHeight(25);
    }

    $spreadsheet = new Spreadsheet();

    // ---- Hoja Presencial ----
    $sheetPresencial = $spreadsheet->getActiveSheet();
    $sheetPresencial->setTitle('Presencial');
    $attendanceDataPresencial = buildAttendanceData($conn, $studentsPresencial, $components);
    writeAttendanceSheet($sheetPresencial, $studentsPresencial, $components, $attendanceDataPresencial);

    // ---- Hoja Virtual ----
    $sheetVirtual = $spreadsheet->createSheet();
    $sheetVirtual->setTitle('Virtual');
    $attendanceDataVirtual = buildAttendanceData($conn, $studentsVirtual, $components);
    writeAttendanceSheet($sheetVirtual, $studentsVirtual, $components, $attendanceDataVirtual);

    // ---- Hoja de Leyenda ----
    $legendSheet = $spreadsheet->createSheet();
    $legendSheet->setTitle('Leyenda');

    $legendSheet->setCellValue('A1', 'LEYENDA DE COLORES DE ASISTENCIA');
    $legendSheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

    $legendSheet->setCellValue('A3', 'Color');
    $legendSheet->setCellValue('B3', 'Estado');
    $legendSheet->setCellValue('C3', 'Descripcion');

    $legendSheet->getStyle('A3:C3')->getFont()->setBold(true);
    $legendSheet->getStyle('A3:C3')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFE9ECEF');

    $legendSheet->setCellValue('A4', '');
    $legendSheet->setCellValue('B4', 'Presente');
    $legendSheet->setCellValue('C4', 'El estudiante asistio a la clase');
    $legendSheet->getStyle('A4')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF28A745');

    $legendSheet->setCellValue('A5', '');
    $legendSheet->setCellValue('B5', 'Ausente');
    $legendSheet->setCellValue('C5', 'El estudiante no asistio a la clase');
    $legendSheet->getStyle('A5')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFDC3545');

    $legendSheet->setCellValue('A6', '');
    $legendSheet->setCellValue('B6', 'Tarde');
    $legendSheet->setCellValue('C6', 'El estudiante llego tarde a la clase');
    $legendSheet->getStyle('A6')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FFFFC107');

    $legendSheet->setCellValue('A7', '');
    $legendSheet->setCellValue('B7', 'Excusa');
    $legendSheet->setCellValue('C7', 'El estudiante tuvo una excusa justificada');
    $legendSheet->getStyle('A7')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF17A2B8');

    $legendSheet->setCellValue('A8', '');
    $legendSheet->setCellValue('B8', 'Sin registro');
    $legendSheet->setCellValue('C8', 'No hay registro de asistencia para esta clase');
    $legendSheet->getStyle('A8')->getFill()
        ->setFillType(Fill::FILL_SOLID)
        ->getStartColor()->setARGB('FF6C757D');

    $legendSheet->setCellValue('A10', 'INFORMACION SOBRE COMPONENTES:');
    $legendSheet->getStyle('A10')->getFont()->setBold(true)->setSize(12);
    $legendSheet->setCellValue('A11', '- Tecnico: Curso principal de formacion tecnica');
    $legendSheet->setCellValue('A12', '- English Code: Curso de ingles tecnico especializado');
    $legendSheet->setCellValue('A13', '- Habilidades: Curso de habilidades blandas y complementarias');

    $legendSheet->setCellValue('A15', 'NOTA IMPORTANTE:');
    $legendSheet->getStyle('A15')->getFont()->setBold(true)->setSize(12);
    $legendSheet->setCellValue('A16', 'Todos los estudiantes del mismo curso tienen el mismo numero de registros.');
    $legendSheet->setCellValue('A17', 'Las fechas faltantes se marcan automaticamente como "Ausente".');
    $legendSheet->setCellValue('A18', 'Esto garantiza que cada grupo tenga una matriz completa de asistencias.');
    $legendSheet->setCellValue('A19', 'Cada componente (Tecnico, English Code, Habilidades) se procesa independientemente.');

    $legendSheet->getStyle('A3:C8')->getBorders()->getAllBorders()
        ->setBorderStyle(Border::BORDER_THIN);

    $legendSheet->getColumnDimension('A')->setWidth(10);
    $legendSheet->getColumnDimension('B')->setWidth(15);
    $legendSheet->getColumnDimension('C')->setWidth(50);

    $legendSheet->getStyle('A3:C8')->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER);

    // Activar la primera hoja (Presencial)
    $spreadsheet->setActiveSheetIndex(0);

    // Configurar el nombre del archivo y descargar
    $filename = 'Asistencias_Comprobantes_Presencial_Virtual_' . date('Y-m-d_H-i-s') . '.xlsx';

    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} catch (Exception $e) {
    http_response_code(500);
    echo 'Error al generar el archivo: ' . $e->getMessage();
}
