<?php

// Mostrar errores para debugging (quitar en producción)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Certificados</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personalizado -->
    <style>
        .search-container {
            max-width: 100%;
            width: 100%;
            margin: 20px auto;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 5px;
            position: relative;
            background-color: white;
            z-index: 10;
        }

        .result-card {
            margin-top: 30px;
            display: none;
            width: 100%;
        }

        .card-body {
            width: 100%;
        }

        .text-center.mt-4 {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 32px;
        }

        @media (max-width: 768px) {

            .search-container,
            .result-card,
            .card-body {
                padding: 10px;
            }
        }

        .loader {
            display: none;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 2s linear infinite;
            margin: 20px auto;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .form-control-lg {
            font-size: 1.2rem;
            text-align: center;
            height: 50px;
        }

        .bg-indigo-dark {
            background-color: #6610f2;
            color: white;
        }

        .clear-both {
            clear: both;
        }

        .search-section {
            margin-bottom: 20px;
        }

        .data-section {
            padding: 15px;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            margin-bottom: 15px;
            background-color: #f8f9fa;
        }
    </style>
</head>

<body>
    <div class="container-fluid px-2">
        <div class="bg-white rounded shadow-sm p-4 my-4">
            <div class="mb-4">
                <h2 class="text-center mb-4">Buscador de Certificados</h2>
                <div class="mb-3 text-center">
                    <label for="number_id" class="form-label fs-4">Número de identificación:</label>
                    <input type="number" class="form-control form-control-lg w-100 mx-auto" id="number_id" placeholder="Ingrese el número de identificación" autofocus>
                </div>
                <div class="loader" id="loader"></div>
                <div class="alert alert-warning mt-3" id="noResults" style="display:none;">
                    No se encontraron resultados para esta búsqueda.
                </div>
            </div>

            <div class="card w-100" id="resultCard" style="display:none;">
                <div class="card-header bg-indigo-dark text-white text-center">
                    <h5 class="mb-0">Información del Certificado</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-4 w-100">
                        <div class="col-md-6">
                            <div class="border rounded bg-light p-3 h-100">
                                <h5 class="mb-3">Información Personal</h5>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Nombre:</span>
                                    <span class="fw-bold text-end" id="fullName"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Tipo y Número de ID:</span>
                                    <span class="text-end"><span id="typeId"></span> - <span id="numberId"></span></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Programa:</span>
                                    <span class="text-end" id="program"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Modalidad:</span>
                                    <span class="text-end" id="mode"></span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Email:</span>
                                    <span class="text-end" id="email"></span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="border rounded bg-light p-3 h-100">
                                <h5 class="mb-3">Información del Bootcamp</h5>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Nombre del Bootcamp:</span>
                                    <span class="text-end" id="bootcampName"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Código:</span>
                                    <span class="text-end" id="bootcampCode"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Fecha de Inicio:</span>
                                    <span class="text-end" id="bootcampStartDate"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Fecha de Finalización:</span>
                                    <span class="text-end" id="bootcampEndDate"></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center w-100 border-bottom pb-2 mb-2">
                                    <span class="text-muted">Horario:</span>
                                    <span class="text-end" id="schedules"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="d-flex justify-content-center" style="padding: 0 0 10px 0;">
                    <button type="button" id="generateCertBtn" class="btn bg-indigo-dark text-white btn-lg pb-2" style="max-width:350px;">
                        <i class="fas fa-file-pdf me-2"></i>Generar Certificado
                    </button>
                </div>
            </div>
        </div>
        <div class="clear-both"></div>
    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Variable para controlar el tiempo de espera entre pulsaciones de teclas
            let typingTimer;
            const doneTypingInterval = 500; // tiempo en ms

            // Evento al escribir en el campo de búsqueda
            $('#number_id').on('input', function() {
                clearTimeout(typingTimer);

                const numberId = $(this).val().trim();

                // Ocultar resultados anteriores
                $('#resultCard').hide();
                $('#noResults').hide();

                if (numberId.length > 0) {
                    // Mostrar loader
                    $('#loader').show();

                    // Iniciar temporizador
                    typingTimer = setTimeout(function() {
                        searchStudent(numberId);
                    }, doneTypingInterval);
                } else {
                    $('#loader').hide();
                }
            });

            // Función para buscar estudiante
            function searchStudent(numberId) {
                $.ajax({
                    url: 'components/certificateModel/search_student.php',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        number_id: numberId
                    },
                    success: function(data) {
                        $('#loader').hide();

                        if (data.success) {
                            // Mostrar datos del estudiante simplificados
                            $('#fullName').text(data.student.full_name || 'N/A');
                            $('#typeId').text(data.student.type_id || 'N/A');
                            $('#numberId').text(data.student.number_id || 'N/A');
                            $('#program').text(data.student.program || 'N/A');
                            $('#bootcampName').text(data.student.bootcamp_name || 'N/A');
                            $('#bootcampCode').text(data.student.id_bootcamp || 'N/A');
                            $('#mode').text(data.student.mode || 'N/A'); // NUEVO
                            $('#schedules').text(data.schedules || 'N/A'); // NUEVO
                            $('#email').text(data.student.email || 'N/A');

                            // Datos del periodo
                            if (data.period) {
                                $('#bootcampStartDate').text(data.period.start_date || 'N/A');
                                $('#bootcampEndDate').text(data.period.end_date || 'N/A');
                            } else {
                                $('#bootcampStartDate').text('N/A');
                                $('#bootcampEndDate').text('N/A');
                            }

                            // Cambiar el texto del botón según si existe certificado
                            if (data.cert_exists) {
                                $('#generateCertBtn').text('Descargar Certificado Existente');
                                $('#generateCertBtn').data('exists', true);
                                $('#generateCertBtn').removeClass('btn-primary').css({
                                    'background-color': '#007a7a',
                                    'border-color': '#007a7a',
                                    'color': '#fff'
                                });
                            } else {
                                $('#generateCertBtn').text('Generar Certificado');
                                $('#generateCertBtn').data('exists', false);
                                $('#generateCertBtn').removeAttr('style');
                            }

                            $('#resultCard').show();
                        } else {
                            $('#noResults').show();
                            console.error('Error:', data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        $('#loader').hide();
                        $('#noResults').show();

                        let errorMsg = 'No se pudo generar el certificado. Intenta nuevamente.';
                        let errorDetail = null;

                        if (xhr && xhr.responseText) {
                            try {
                                const json = JSON.parse(xhr.responseText);
                                if (json.message) {
                                    errorMsg = json.message;
                                }
                                if (json.phpmailer_error) {
                                    errorDetail = json.phpmailer_error;
                                }
                            } catch (e) {
                                // No es JSON, mantener mensaje genérico
                                errorDetail = xhr.responseText;
                            }
                        }

                        // Mostrar en consola todos los detalles del error
                        console.error('Error AJAX:', error);
                        if (errorMsg) console.error('Mensaje:', errorMsg);
                        if (errorDetail) console.error('Detalle:', errorDetail);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la solicitud',
                            text: errorMsg + (errorDetail ? '\nDetalle: ' + errorDetail : '')
                        });
                    }
                });
            }

            // Función para generar certificado - VERSIÓN FINAL
            $('#generateCertBtn').on('click', function() {
                const studentData = {
                    nombre_estudiante: $('#fullName').text(),
                    tipo_id: $('#typeId').text(),
                    cedula: $('#numberId').text(),
                    programa: $('#program').text(),
                    nombre_bootcamp: $('#bootcampName').text(),
                    codigo_bootcamp: $('#bootcampCode').text(),
                    modalidad: $('#mode').text(),
                    fecha_inicio: $('#bootcampStartDate').text(),
                    fecha_fin: $('#bootcampEndDate').text(),
                    schedules: $('#schedules').text(),
                    email: $('#email').text()
                };

                // Mostrar loading
                Swal.fire({
                    title: 'Procesando...',
                    text: 'Generando y enviando certificado',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'components/certificateModel/export_format.php',
                    type: 'POST',
                    data: studentData,
                    dataType: 'json', // Especificar que esperamos JSON
                    success: function(data) {
                        Swal.close(); // Cerrar loading

                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Certificado enviado',
                                text: data.message,
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message || 'Ocurrió un error al procesar el certificado.',
                                footer: data.phpmailer_error ? '<pre style="text-align:left;white-space:pre-wrap;">' + data.phpmailer_error + '</pre>' : ''
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        Swal.close(); // Cerrar loading

                        let errorMsg = 'No se pudo generar el certificado. Intenta nuevamente.';
                        let errorDetail = null;

                        if (xhr && xhr.responseText) {
                            try {
                                const json = JSON.parse(xhr.responseText);
                                if (json.message) {
                                    errorMsg = json.message;
                                }
                                if (json.phpmailer_error) {
                                    errorDetail = json.phpmailer_error;
                                }
                            } catch (e) {
                                errorDetail = xhr.responseText;
                            }
                        }

                        console.error('Error AJAX:', error);
                        console.error('Status:', status);
                        console.error('Response:', xhr.responseText);

                        Swal.fire({
                            icon: 'error',
                            title: 'Error en la solicitud',
                            text: errorMsg,
                            footer: errorDetail ? '<pre style="text-align:left;white-space:pre-wrap;">' + errorDetail + '</pre>' : ''
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>