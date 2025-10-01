<div class="card col-12">
    <div class="card-header bg-indigo-dark text-white dark d-flex justify-content-center align-items-center">
        <h5 class="mb-0 fw-bold">Subir archivo Excel</h5>
    </div>
    <div class="card-body d-flex flex-column justify-content-center align-items-center">
        <form id="excelUploadForm" enctype="multipart/form-data" class="w-100 text-center">
            <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required class="form-control mb-3 mx-auto" style="max-width:30vw; min-width:180px;">
            <button type="submit" class="btn bg-magenta-dark text-white">Subir</button>
            <button id="truncateVerificationBtn" type="button" class="btn btn-danger ms-2" style="display:none;">
                Vaciar Verificaciones
            </button>
            <button id="exportExcelBtn" type="button" class="btn btn-success ms-2" style="display:none;">
                📊 Exportar a Excel
            </button>
        </form>
        <div id="uploadResult" class="mt-3"></div>
    </div>
</div>

<!-- Tabla de resultados de verificación -->
<div class="card col-12 mt-4" id="verificationTableCard" style="display: none;">
    <div class="card-header bg-teal-dark text-white">
        <h5 class="mb-0 fw-bold">Resultados de Verificación</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive" style="width: 100%;">
            <table id="listaVerificacion" class="table table-striped table-bordered w-100" style="width:100%">
                <thead>
                    <tr>
                        <th>Documento</th>
                        <th>Valid. Doc</th>
                        <th>Nombre 1</th>
                        <th>Valid. N1</th>
                        <th>Nombre 2</th>
                        <th>Valid. N2</th>
                        <th>Apellido 1</th>
                        <th>Valid. A1</th>
                        <th>Apellido 2</th>
                        <th>Valid. A2</th>
                        <th>Fecha Nac.</th>
                        <th>Valid. Fecha</th>
                        <th>% Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>

<script>
    let dataTable;

    $('#excelUploadForm').on('submit', function(e) {
        e.preventDefault();
        var formData = new FormData(this);

        // Mostrar loading
        Swal.fire({
            title: 'Subiendo archivo...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        $.ajax({
            url: 'components/scanDocs/uploadExcel.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function(response) {
                Swal.close();

                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Éxito!',
                        text: response.message,
                        showCancelButton: true,
                        confirmButtonText: 'Iniciar Verificación',
                        cancelButtonText: 'Cerrar',
                        confirmButtonColor: '#28a745'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            startVerification();
                        }
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message
                    });
                }
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al subir el archivo.'
                });
            }
        });
    });

    function startVerification() {
        // Inicializar el progreso
        let progressInterval;
        
        // Mostrar SweetAlert con barra de progreso
        Swal.fire({
            title: 'Procesando verificación...',
            html: `
                <div class="text-center">
                    <div class="mb-3">Esto puede tomar varios minutos</div>
                    <div class="progress mb-2" style="height: 20px;">
                        <div id="verification-progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" 
                            role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div id="verification-progress-text">Preparando datos...</div>
                    <div id="verification-progress-count" class="mt-2">0 de 0 documentos procesados</div>
                </div>
            `,
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
                
                // Consultar el progreso cada segundo
                progressInterval = setInterval(function() {
                    $.ajax({
                        url: 'components/scanDocs/getProgress.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function(progressData) {
                            if (progressData && progressData.status) {
                                const percentage = progressData.percentage || 0;
                                const current = progressData.current || 0;
                                const total = progressData.total || 0;
                                const status = progressData.status || 'procesando';
                                
                                // Actualizar barra de progreso
                                $('#verification-progress-bar').css('width', percentage + '%').attr('aria-valuenow', percentage);
                                
                                // Actualizar texto de progreso
                                if (status === 'procesando') {
                                    $('#verification-progress-text').html('Verificando documentos...');
                                } else if (status === 'descargando') {
                                    $('#verification-progress-text').html('Descargando imágenes...');
                                } else if (status === 'analizando') {
                                    $('#verification-progress-text').html('Analizando con OCR...');
                                } else if (status === 'guardando') {
                                    $('#verification-progress-text').html('Guardando resultados...');
                                } else if (status === 'completado') {
                                    $('#verification-progress-text').html('<strong>¡Verificación completada!</strong>');
                                }
                                
                                // Actualizar contador
                                $('#verification-progress-count').html(`${current} de ${total} documentos procesados`);
                            }
                        }
                    });
                }, 1000); // Actualizar cada segundo
            },
            willClose: () => {
                clearInterval(progressInterval); // Detener las actualizaciones cuando se cierra
            }
        });

        $.ajax({
            url: 'components/scanDocs/processExcelVerification.php',
            type: 'POST',
            data: {
                process_verification: true
            },
            dataType: 'json',
            timeout: 600000, // 5 minutos de timeout
            success: function(response) {
                console.log('Respuesta recibida:', response);
                
                // Detener el intervalo de progreso
                clearInterval(progressInterval);
                
                Swal.close();

                if (response && response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Verificación Completada!',
                        text: `Documentos procesados: ${response.processed}`,
                        confirmButtonText: 'Ver Resultados'
                    }).then(() => {
                        // Cargar resultados inmediatamente después de cerrar el SweetAlert
                        loadVerificationResults();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la verificación',
                        text: response ? response.message : 'Error desconocido'
                    });
                }
            },
            error: function(xhr, status, error) {
                // Detener el intervalo de progreso
                clearInterval(progressInterval);
                
                console.error('Error AJAX:', status, error);
                console.error('Respuesta:', xhr.responseText);

                Swal.close();

                if (status === 'timeout') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tiempo agotado',
                        text: 'El proceso está tomando más tiempo del esperado. Los resultados pueden estar disponibles.'
                    }).then(() => {
                        // Intentar cargar resultados de todas formas
                        loadVerificationResults();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la verificación',
                        text: 'Error de conexión o procesamiento'
                    });
                }
            }
        });
    }

    function loadVerificationResults() {
        console.log('Cargando resultados de verificación...');

        $.ajax({
            url: 'components/scanDocs/getVerificationResults.php',
            type: 'GET',
            dataType: 'json',
            success: function(data) {
                console.log('Datos recibidos:', data);

                if (data && data.length > 0) {
                    // Mostrar la tabla
                    $('#verificationTableCard').show();

                    // Destruir DataTable existente si existe
                    if (dataTable) {
                        dataTable.destroy();
                    }

                    // Limpiar el tbody
                    $('#listaVerificacion tbody').empty();

                    // Inicializar DataTable
                    dataTable = $('#listaVerificacion').DataTable({
                        data: data,
                        destroy: true,
                        columns: [
                            // Documento
                            {
                                data: 'number_id',
                                render: function(data, type, row) {
                                    const dbData = row.db_number_id || 'No encontrado';
                                    return `
                                        <div>
                                            <strong>${data}</strong><br>
                                            <small class="text-muted">BD: ${dbData}</small>
                                        </div>
                                    `;
                                }
                            },
                            // Validación Documento
                            {
                                data: 'document_match',
                                render: function(data, type, row) {
                                    const match = parseInt(data) === 1;
                                    const icon = match ? '✅' : '❌';
                                    const bgColor = match ? 'bg-success' : 'bg-danger';
                                    return `<span class="badge ${bgColor}">${icon}</span>`;
                                }
                            },
                            // Nombre 1
                            {
                                data: 'db_first_name',
                                render: function(data, type, row) {
                                    return data || '-';
                                }
                            },
                            // Validación Nombre 1
                            {
                                data: 'name1_match',
                                render: function(data, type, row) {
                                    const match = parseInt(data) === 1;
                                    const icon = match ? '✅' : '❌';
                                    const bgColor = match ? 'bg-success' : 'bg-danger';
                                    return `<span class="badge ${bgColor}">${icon}</span>`;
                                }
                            },
                            // Nombre 2
                            {
                                data: 'db_second_name',
                                render: function(data, type, row) {
                                    return data || '-';
                                }
                            },
                            // Validación Nombre 2
                            {
                                data: 'name2_match',
                                render: function(data, type, row) {
                                    const match = parseInt(data) === 1;
                                    const icon = match ? '✅' : '❌';
                                    const bgColor = match ? 'bg-success' : 'bg-danger';
                                    return `<span class="badge ${bgColor}">${icon}</span>`;
                                }
                            },
                            // Apellido 1
                            {
                                data: 'db_first_last',
                                render: function(data, type, row) {
                                    return data || '-';
                                }
                            },
                            // Validación Apellido 1
                            {
                                data: 'lastname1_match',
                                render: function(data, type, row) {
                                    const match = parseInt(data) === 1;
                                    const icon = match ? '✅' : '❌';
                                    const bgColor = match ? 'bg-success' : 'bg-danger';
                                    return `<span class="badge ${bgColor}">${icon}</span>`;
                                }
                            },
                            // Apellido 2
                            {
                                data: 'db_second_last',
                                render: function(data, type, row) {
                                    return data || '-';
                                }
                            },
                            // Validación Apellido 2
                            {
                                data: 'lastname2_match',
                                render: function(data, type, row) {
                                    const match = parseInt(data) === 1;
                                    const icon = match ? '✅' : '❌';
                                    const bgColor = match ? 'bg-success' : 'bg-danger';
                                    return `<span class="badge ${bgColor}">${icon}</span>`;
                                }
                            },
                            // Fecha Nacimiento
                            {
                                data: 'db_birthdate',
                                render: function(data, type, row) {
                                    return data || 'No encontrado';
                                }
                            },
                            // Validación Fecha
                            {
                                data: 'birthdate_match',
                                render: function(data, type, row) {
                                    const match = parseInt(data) === 1;
                                    const icon = match ? '✅' : '❌';
                                    const bgColor = match ? 'bg-success' : 'bg-danger';
                                    return `<span class="badge ${bgColor}">${icon}</span>`;
                                }
                            },
                            // Porcentaje Total
                            {
                                data: 'overall_match_percentage',
                                render: function(data, type, row) {
                                    return parseFloat(data).toFixed(1) + '%';
                                }
                            },
                            // Estado General
                            {
                                data: 'overall_match_percentage',
                                render: function(data, type, row) {
                                    const percentage = parseFloat(data);
                                    if (percentage >= 80) {
                                        return '<span class="badge bg-success">Válido</span>';
                                    } else if (percentage >= 60) {
                                        return '<span class="badge bg-warning text-dark">Revisar</span>';
                                    } else {
                                        return '<span class="badge bg-danger">Fallido</span>';
                                    }
                                }
                            },
                            // Acciones
                            {
                                data: null,
                                render: function(data, type, row) {
                                    return `
                                        <button class="btn btn-sm bg-indigo-dark text-white" onclick="viewDetails('${row.number_id}')">
                                            Ver Detalles
                                        </button>
                                    `;
                                }
                            }
                        ],
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json'
                        },
                        responsive: true,
                        pageLength: 25,
                        order: [
                            [12, 'desc']
                        ], // Ordenar por porcentaje (columna 12)
                        scrollX: true // Permitir scroll horizontal para tantas columnas
                    });

                    console.log('Tabla inicializada correctamente');

                    // Scroll hacia la tabla
                    $('html, body').animate({
                        scrollTop: $("#verificationTableCard").offset().top
                    }, 1000);

                    // Mostrar ambos botones al mismo tiempo
                    $('#truncateVerificationBtn').show();
                    $('#exportExcelBtn').show();

                } else {
                    // Ocultar ambos botones si no hay datos
                    $('#truncateVerificationBtn').hide();
                    $('#exportExcelBtn').hide();

                    Swal.fire({
                        icon: 'info',
                        title: 'Sin resultados',
                        text: 'No hay datos de verificación disponibles'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.error('Error cargando resultados:', status, error);
                console.error('Respuesta:', xhr.responseText);

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los resultados'
                });
            }
        });
    }

    function viewDetails(numberId) {
        $.ajax({
            url: 'components/scanDocs/getVerificationDetails.php',
            type: 'GET',
            data: {
                number_id: numberId
            },
            dataType: 'json',
            success: function(data) {
                let detailsHtml = `
                <div class="row">
                    <div class="col-md-8">
                        <h6>Comparación de Datos:</h6>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Campo</th>
                                        <th>Base de Datos</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Documento</strong></td>
                                        <td>${data.db_number_id || 'No encontrado'}</td>
                                        <td>
                                            <span class="badge ${parseInt(data.document_match) === 1 ? 'bg-success' : 'bg-danger'}">
                                                ${parseInt(data.document_match) === 1 ? '✅ Válido' : '❌ Inválido'}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Primer Nombre</strong></td>
                                        <td>${data.db_first_name || 'No encontrado'}</td>
                                        <td>
                                            <span class="badge ${parseInt(data.name1_match) === 1 ? 'bg-success' : 'bg-danger'}">
                                                ${parseInt(data.name1_match) === 1 ? '✅ Válido' : '❌ Inválido'}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Segundo Nombre</strong></td>
                                        <td>${data.db_second_name || 'No encontrado'}</td>
                                        <td>
                                            <span class="badge ${parseInt(data.name2_match) === 1 ? 'bg-success' : 'bg-danger'}">
                                                ${parseInt(data.name2_match) === 1 ? '✅ Válido' : '❌ Inválido'}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Primer Apellido</strong></td>
                                        <td>${data.db_first_last || 'No encontrado'}</td>
                                        <td>
                                            <span class="badge ${parseInt(data.lastname1_match) === 1 ? 'bg-success' : 'bg-danger'}">
                                                ${parseInt(data.lastname1_match) === 1 ? '✅ Válido' : '❌ Inválido'}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Segundo Apellido</strong></td>
                                        <td>${data.db_second_last || 'No encontrado'}</td>
                                        <td>
                                            <span class="badge ${parseInt(data.lastname2_match) === 1 ? 'bg-success' : 'bg-danger'}">
                                                ${parseInt(data.lastname2_match) === 1 ? '✅ Válido' : '❌ Inválido'}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>Fecha Nacimiento</strong></td>
                                        <td>${data.db_birthdate || 'No encontrado'}</td>
                                        <td>
                                            <span class="badge ${parseInt(data.birthdate_match) === 1 ? 'bg-success' : 'bg-danger'}">
                                                ${parseInt(data.birthdate_match) === 1 ? '✅ Válido' : '❌ Inválido'}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            <strong>Porcentaje de Coincidencia: </strong>
                            <span class="badge ${parseFloat(data.overall_match_percentage) >= 80 ? 'bg-success' : parseFloat(data.overall_match_percentage) >= 60 ? 'bg-warning' : 'bg-danger'}">
                                ${parseFloat(data.overall_match_percentage).toFixed(1)}%
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex flex-column gap-3">
                        <div class="d-flex flex-column">
                            <h6>Texto OCR Extraído:</h6>
                            <div class="card mb-2">
                                <div class="card-body d-flex flex-column">
                                    <h6>Imagen Frontal:</h6>
                                    <small style="font-size: 10px;">${data.front_ocr_text || 'No disponible'}</small>
                                    <hr>
                                    <h6>Imagen Posterior:</h6>
                                    <small style="font-size: 10px;">${data.back_ocr_text || 'No disponible'}</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;

                Swal.fire({
                    title: `Detalles - Documento: ${numberId}`,
                    html: detailsHtml,
                    width: '90%',
                    showCloseButton: true,
                    focusConfirm: false
                });
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudieron cargar los detalles del documento'
                });
            }
        });
    }

    $('#truncateVerificationBtn').on('click', function() {
        Swal.fire({
            icon: 'warning',
            title: '¿Estás seguro?',
            text: 'Esto eliminará TODOS los resultados de verificación.',
            showCancelButton: true,
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#d33'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'error',
                    title: 'Advertencia Final',
                    text: 'Esta acción es IRREVERSIBLE. ¿Deseas continuar?',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar todo',
                    cancelButtonText: 'Cancelar',
                    confirmButtonColor: '#d33'
                }).then((finalResult) => {
                    if (finalResult.isConfirmed) {
                        // Llamada AJAX para truncar la tabla
                        $.ajax({
                            url: 'components/scanDocs/truncateVerificationTable.php',
                            type: 'POST',
                            success: function(response) {
                                Swal.fire({
                                    icon: 'success',
                                    title: '¡Tabla vaciada!',
                                    text: 'Todos los resultados han sido eliminados.'
                                });
                                $('#verificationTableCard').hide();
                                $('#truncateVerificationBtn').hide();
                                $('#exportExcelBtn').hide(); // Agregar esta línea
                            },
                            error: function() {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: 'No se pudo vaciar la tabla.'
                                });
                            }
                        });
                    }
                });
            }
        });
    });

    // Función para exportar resultados a Excel
    $('#exportExcelBtn').on('click', function() {
        // Mostrar loading
        Swal.fire({
            title: 'Exportando a Excel...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading()
            }
        });

        $.ajax({
            url: 'components/scanDocs/exportVerificationResults.php',
            type: 'GET',
            xhrFields: {
                responseType: 'blob' // Importante para manejar la descarga del archivo
            },
            success: function(blob, status, xhr) {
                Swal.close();

                // Crear un enlace temporal para la descarga
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = 'resultados_verificacion.xlsx';
                document.body.append(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: 'Los resultados han sido exportados a Excel',
                    showConfirmButton: false,
                    timer: 1500
                });
            },
            error: function() {
                Swal.close();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo exportar a Excel'
                });
            }
        });
    });
</script>