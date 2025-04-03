<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Contactos</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* Ajustes para que las imágenes sean miniaturas */
        .thumbnail {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-4">
        <h2 class="text-center mb-4">Lista de Registros de Contactos</h2>

        <!-- Barra de búsqueda -->
        <div class="row mb-3">
            <div class="col-md-6">
                <input type="text" id="search" class="form-control" placeholder="Buscar contacto...">
            </div>
        </div>

        <!-- Tabla de datos -->
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Tipo ID</th>
                        <th>Número</th>
                        <th>Foto de CC</th>
                        <th>Nombre</th>
                        <th>Edad</th>
                        <th>Correo</th>
                        <th>Teléfono 1</th>
                        <th>Teléfono 2</th>
                        <th>Medio de contacto</th>
                        <th>Actualizar contacto</th>
                        <th>Contacto de emergencia</th>
                        <th>Teléfono del contacto</th>
                        <th>Nacionalidad</th>
                        <th>Departamento</th>
                        <th>Municipio</th>
                        <th>Ocupación</th>
                        <th>Tiempo de obligaciones</th>
                        <th>Sede de elección</th>
                        <th>Modalidad</th>
                        <th>Actualizar modalidad</th>
                        <th>Programa de interés</th>
                        <th>Horario</th>
                        <th>Cambiar Horario</th>
                        <th>Dispositivo</th>
                        <th>Internet</th>
                        <th>Estado</th>
                        <th>Estado de admisión</th>
                        <th>Actualizar medio de contacto</th>
                        <th>Puntaje de prueba</th>
                        <th>Nivel obtenido</th>
                        <th>Actualizar admisión</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Datos cargados dinámicamente -->
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let dataTable = $('#usersTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": function(data, callback, settings) {
                    let limit = data.length; // Número de registros por página
                    let offset = data.start; // Offset para paginación
                    let search = $('#search').val(); // Valor de búsqueda

                    $.ajax({
                        url: "registrationContact.php", // Ruta de la API
                        type: "GET",
                        data: {
                            limit: limit,
                            offset: offset,
                            search: search
                        },
                        dataType: "json",
                        success: function(response) {
                            callback({
                                recordsTotal: response.total,
                                recordsFiltered: response.total,
                                data: response.data
                            });
                        },
                        error: function(xhr, status, error) {
                            console.error("Error en la API:", error);
                        }
                    });
                },
                "columns": [{
                        "data": "typeID"
                    },
                    {
                        "data": "number_id"
                    },
                    {
                        "data": "number_id",
                        "render": function(data, type, row) {
                            return `
            <td>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalID_${data}">
                    <i class="bi bi-card-image"></i>
                </button>

                <!-- Modal para mostrar las imágenes -->
                <div class="modal fade" id="modalID_${data}" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-indigo-dark">
                                <h5 class="modal-title">Imágenes de Identificación</h5>
                                <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body position-relative" style="overflow: visible;">
                                <div class="row">
                                    <!-- Frente del documento -->
                                    <div class="col-12 mb-4 text-center">
                                        <h6>Frente del documento</h6>
                                        <div class="position-relative overflow-visible">
                                            <img id="idImageFront_${data}"
                                                src="https://dashboard.uttalento.co/files/idFilesFront/${row.file_front_id}"
                                                class="img-fluid w-100 zoomable"
                                                style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                alt="Frente ID"
                                                onclick="toggleZoom('idImageFront_${data}')">
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-primary" onclick="rotateImage('idImageFront_${data}', -90)">↺ Rotar Izquierda</button>
                                            <button class="btn btn-primary" onclick="rotateImage('idImageFront_${data}', 90)">↻ Rotar Derecha</button>
                                        </div>
                                    </div>

                                    <!-- Reverso del documento -->
                                    <div class="col-12 text-center">
                                        <h6>Reverso del documento</h6>
                                        <div class="position-relative overflow-visible">
                                            <img id="idImageBack_${data}"
                                                src="https://dashboard.uttalento.co/files/idFilesBack/${row.file_back_id}"
                                                class="img-fluid w-100 zoomable"
                                                style="max-height: 400px; object-fit: contain; transition: transform 0.3s ease; position: relative; z-index: 1055;"
                                                alt="Reverso ID"
                                                onclick="toggleZoom('idImageBack_${data}')">
                                        </div>
                                        <div class="mt-2">
                                            <button class="btn btn-primary" onclick="rotateImage('idImageBack_${data}', -90)">↺ Rotar Izquierda</button>
                                            <button class="btn btn-primary" onclick="rotateImage('idImageBack_${data}', 90)">↻ Rotar Derecha</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </td>
        `;
                        }
                    },

                    {
                        "data": null, // Se usa null porque concatenaremos varios campos
                        "render": function(data, type, row) {
                            return row.first_name + ' ' +
                                (row.second_name ? row.second_name + ' ' : '') +
                                row.first_last + ' ' +
                                row.second_last;
                        }
                    },
                    {
                        "data": "birthdate",
                        "render": function(data, type, row) {
                            if (!data) return '';
                            var birthdate = new Date(data);
                            var today = new Date();
                            var age = today.getFullYear() - birthdate.getFullYear();
                            var m = today.getMonth() - birthdate.getMonth();
                            if (m < 0 || (m === 0 && today.getDate() < birthdate.getDate())) {
                                age--;
                            }
                            return age;
                        }
                    },

                    {
                        "data": "email"
                    },
                    {
                        "data": "first_phone"
                    },
                    {
                        "data": "second_phone"
                    },
                    {
                        "data": "contact_medium",
                        "render": function(data, type, row) {
                            let btnClass = '';
                            let btnText = data; // El texto del tooltip
                            let icon = '';

                            switch (data) {
                                case 'WhatsApp':
                                    btnClass = 'btn bg-lime-dark text-white';
                                    icon = '<i class="bi bi-whatsapp"></i>';
                                    break;
                                case 'Teléfono':
                                    btnClass = 'btn bg-teal-dark text-white';
                                    icon = '<i class="bi bi-telephone"></i>';
                                    break;
                                case 'Correo':
                                    btnClass = 'btn bg-orange-light';
                                    icon = '<i class="bi bi-envelope"></i>';
                                    break;
                                default:
                                    btnClass = 'btn btn-secondary';
                                    icon = '<i class="bi bi-question-circle"></i>';
                                    btnText = 'Desconocido';
                                    break;
                            }

                            return '<button type="button" class="' + btnClass + '" data-bs-toggle="tooltip" data-bs-placement="top" title="' + btnText + '">' +
                                icon +
                                '</button>';
                        }
                    },
                    {
                        "data": "update_contact",
                        "render": function(data, type, row) {
                            return '<button class="btn bg-magenta-dark text-white" onclick="mostrarModalActualizar(' + row.number_id + ')" ' +
                                'data-bs-toggle="tooltip" data-bs-placement="top" ' +
                                'data-bs-custom-class="custom-tooltip" ' +
                                'data-bs-title="Cambiar medio de contacto">' +
                                '<i class="bi bi-arrow-left-right"></i></button>';
                        }
                    },
                    {
                        "data": "emergency_contact_name"
                    },
                    {
                        "data": "emergency_contact_number"
                    },
                    {
                        "data": "nationality"
                    },
                    {
                        "data": "departamento"
                    },
                    {
                        "data": "municipio"
                    },
                    {
                        "data": "occupation"
                    },
                    {
                        "data": "time_obligations"
                    },
                    {
                        "data": "headquarters"
                    },
                    {
                        "data": "mode"
                    },
                    {
                        "data": "update_modality",
                        "render": function(data, type, row) {
                            return `<td>
                            <button class="btn text-white" style="background-color: #fc4b08;" 
                                    onclick="modalActualizarModalidad(${row.number_id})" 
                                    data-bs-toggle="tooltip" data-bs-placement="top"
                                    data-bs-custom-class="custom-tooltip"
                                    data-bs-title="Cambiar modalidad">
                                <i class="bi bi-arrow-left-right"></i>
                            </button>
                        </td>`;
                        }
                    },
                    {
                        "data": "program"
                    },
                    {
                        "data": "schedules",
                        "render": function(data, type, row) {
                            return `<td class="text-center">
                    <button type="button" class="btn bg-indigo-light"
                        data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-custom-class="custom-tooltip"
                        data-bs-title="${data}">
                        <i class="bi bi-clock-history"></i>
                    </button>
                </td>`;
                        }
                    },

                    {
                        "data": "change_schedule",
                        "render": function(data, type, row) {
                            return `<button class="btn text-white" style="background-color: #b624d5;" 
                        onclick="mostrarModalActualizarHorario(${row.number_id})" 
                        data-bs-toggle="tooltip" data-bs-placement="top"
                        data-bs-custom-class="custom-tooltip"
                        data-bs-title="Cambiar horario">
                    <i class="bi bi-arrow-left-right"></i>
                </button>`;
                        }
                    },

                    {
    "data": "device",
    "render": function (data, type, row) {
        let btnClass = '';
        let icon = '';
        let btnText = row.technologies ? row.technologies : 'Desconocido'; 

        switch (row.technologies) {
            case 'computador':
                btnClass = 'bg-indigo-dark text-white';
                icon = '<i class="bi bi-laptop"></i>';
                break;
            case 'smartphone':
                btnClass = 'bg-teal-dark text-white';
                icon = '<i class="bi bi-phone"></i>';
                break;
            case 'tablet':
                btnClass = 'bg-amber-light text-white';
                icon = '<i class="bi bi-tablet"></i>';
                break;
            default:
                btnClass = 'btn-secondary';
                icon = '<i class="bi bi-question-circle"></i>';
                break;
        }

        return `
            <td class="text-center">
                <button class="btn ${btnClass}" data-bs-toggle="tooltip" data-bs-placement="top" 
                    data-bs-custom-class="custom-tooltip" data-bs-title="${btnText}">
                    ${icon}
                </button>
            </td>
        `;
    }
},

{
    "data": "internet",
    "render": function (data, type, row) {
        let btnClass = data === 'Sí' ? 'bg-indigo-dark text-white' : 'bg-red-dark text-white';
        let icon = data === 'Sí' ? '<i class="bi bi-router-fill"></i>' : '<i class="bi bi-wifi-off"></i>';

        return `
            <td class="text-center">
                <button class="btn ${btnClass}" data-bs-toggle="tooltip" data-bs-placement="top" 
                        data-bs-custom-class="custom-tooltip" data-bs-title="${data}">
                    ${icon}
                </button>
            </td>
        `;
    }
},
{
    "data": "status",
    "render": function (data, type, row) {
        let isAccepted = false;

        if (row.mode === 'Presencial') {
            isAccepted = row.typeID === 'C.C' && row.age > 17 &&
                         (row.departamento.toUpperCase() === 'CUNDINAMARCA' || row.departamento.toUpperCase() === 'BOYACÁ') &&
                         row.internet === 'Sí';
        } else if (row.mode === 'Virtual') {
            isAccepted = row.typeID === 'C.C' && row.age > 17 &&
                         (row.departamento.toUpperCase() === 'CUNDINAMARCA' || row.departamento.toUpperCase() === 'BOYACÁ') &&
                         row.internet === 'Sí' && row.technologies === 'computador';
        }

        let btnClass = isAccepted ? 'bg-teal-dark w-100' : 'bg-danger text-white w-100';
        let icon = isAccepted ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-x-circle"></i>';
        let tooltipText = isAccepted ? 'CUMPLE' : 'NO CUMPLE';

        return `
            <td>
                <button class="btn ${btnClass}" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipText}">
                    ${icon}
                </button>
            </td>
        `;
    }
},
{
    "data": "admission_status",
    "render": function (data, type, row) {
        let btnClass = row.statusAdmin === '1' ? 'bg-teal-dark w-100' : 'bg-silver text-white w-100';
        let icon = row.statusAdmin === '1' ? '<i class="bi bi-check-circle"></i>' : '<i class="bi bi-question-circle"></i>';
        let tooltipText = row.statusAdmin === '1' ? 'ACEPTADO' : 'SIN ESTADO';

        return `
            <td>
                <button class="btn ${btnClass}" data-bs-toggle="tooltip" data-bs-placement="top" title="${tooltipText}">
                    ${icon}
                </button>
            </td>
        `;
    }
},

{
    "data": "update_contact_method",
    "render": function (data, type, row) {
        return `
            <td>
                <button class="btn bg-magenta-dark text-white" onclick="mostrarModalActualizar(${row.number_id})" 
                        data-bs-toggle="tooltip" data-bs-placement="top" 
                        data-bs-custom-class="custom-tooltip" 
                        data-bs-title="Cambiar medio de contacto">
                    <i class="bi bi-arrow-left-right"></i>
                </button>
            </td>
        `;
    }
},

    {
        "data": "test_score",
        "render": function (data, type, row) {
            if (row.test_score !== null) {
                let colorClass = "bg-silver";
                if (row.test_score >= 1 && row.test_score <= 5) {
                    colorClass = "bg-magenta-dark";
                } else if (row.test_score >= 6 && row.test_score <= 10) {
                    colorClass = "bg-orange-dark";
                } else if (row.test_score >= 11 && row.test_score <= 15) {
                    colorClass = "bg-teal-dark";
                }
                return `<button class="btn ${colorClass} w-100" role="alert">${row.test_score}</button>`;
            } else {
                return `<button class="btn bg-silver w-100" role="alert" data-bs-toggle="tooltip" 
                        data-bs-placement="top" data-bs-custom-class="custom-tooltip" 
                        data-bs-title="No ha presentado la prueba">
                            <i class="bi bi-ban"></i>
                        </button>`;
            }
        }
    },
    
  

    {
        "data": "update_admission",
        "render": function (data, type, row) {
            return `
                <button class="btn bg-indigo-dark text-white" onclick="mostrarModalActualizarAdmision(${row.number_id})" 
                        data-bs-toggle="tooltip" data-bs-placement="top" 
                        data-bs-custom-class="custom-tooltip" 
                        data-bs-title="Cambiar estado de admisión">
                    <i class="bi bi-arrow-left-right"></i>
                </button>`;
        }
    },
    {
        "data": "call_button",
        "render": function (data, type, row) {
            return `
                <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#modalLlamada_${row.number_id}">
                    <i class="bi bi-telephone"></i>
                </button>`;
        }
    }


                ],
                "language": {
                    "lengthMenu": "Mostrar _MENU_ registros por página",
                    "zeroRecords": "No se encontraron resultados",
                    "info": "Mostrando página _PAGE_ de _PAGES_",
                    "infoEmpty": "No hay registros disponibles",
                    "infoFiltered": "(filtrado de _MAX_ registros totales)",
                    "search": "Buscar:",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                }
            });

            // Evento de búsqueda en tiempo real
            $('#search').keyup(function() {
                dataTable.ajax.reload();
            });
        });
    </script>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function mostrarModalActualizar(id) {
            // Remover cualquier modal previo del DOM
            $('#modalActualizar_' + id).remove();

            // Crear el modal dinámicamente con un identificador único
            const modalHtml = `
    <div id="modalActualizar_${id}" class="modal fade"  aria-hidden="true" aria-labelledby="exampleModalToggleLabel" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-indigo-dark">
                    <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Medio de Contacto</h5>
                      <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
            
                </div>
                <div class="modal-body">
                    <form id="formActualizarMedio_${id}">
                        <div class="form-group">
                            <label for="nuevoMedio_${id}">Seleccionar nuevo medio de contacto:</label>
                            <select class="form-control" id="nuevoMedio_${id}" name="nuevoMedio" required>
                                <option value="Correo">Correo</option>
                                <option value="Teléfono">Teléfono</option>
                                <option value="WhatsApp">WhatsApp</option>
                            </select>
                        </div>
                        <br>
                        <input type="hidden" name="id" value="${id}">
                        <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    `;

            // Añadir el modal al DOM
            document.body.insertAdjacentHTML('beforeend', modalHtml);

            // Mostrar el modal
            $('#modalActualizar_' + id).modal('show');

            // Manejar el envío del formulario con confirmación
            $('#formActualizarMedio_' + id).on('submit', function(e) {
                e.preventDefault();

                if (confirm("¿Está seguro de que desea actualizar el medio de contacto?")) {
                    const nuevoMedio = $('#nuevoMedio_' + id).val();
                    actualizarMedioContacto(id, nuevoMedio);
                    $('#modalActualizar_' + id).modal('hide');
                } else {
                    toastr.info("La actualización ha sido cancelada.");
                }
            });
        }

        function actualizarMedioContacto(id, nuevoMedio) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "components/registrationsContact/actualizar_medio_contacto.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    const response = xhr.responseText;
                    console.log("Respuesta del servidor: " + response);

                    if (response == "success") {
                        const result = getBtnClass(nuevoMedio);
                        const botonHtml = `<button class="btn ${result.btnClass}">${result.icon} ${nuevoMedio}</button>`;

                        // Actualizar solo el botón específico
                        document.querySelector("#medioContacto_" + id).innerHTML = botonHtml;

                        toastr.success("El medio de contacto se actualizó correctamente.");
                    } else {
                        toastr.error("Hubo un error al actualizar el medio de contacto.");
                    }
                }
            };
            xhr.send("id=" + id + "&nuevoMedio=" + encodeURIComponent(nuevoMedio));
        }

        // Función para obtener la clase del botón según el medio de contacto
        function getBtnClass(medio) {
            let btnClass = '';
            let icon = '';

            if (medio == 'WhatsApp') {
                btnClass = 'bg-lime-dark w-100';
                icon = '<i class="bi bi-whatsapp"></i>';
            } else if (medio == 'Teléfono') {
                btnClass = 'bg-teal-dark w-100';
                icon = '<i class="bi bi-telephone"></i>';
            } else if (medio == 'Correo') {
                btnClass = 'bg-amber-light w-100';
                icon = '<i class="bi bi-envelope"></i>';
            }

            return {
                btnClass,
                icon
            };
        }

        function actualizarLlamada(id) {
            const form = document.getElementById('formActualizarLlamada_' + id);
            if (!form) {
                console.error('Formulario no encontrado');
                return false;
            }

            const formData = new FormData(form);
            formData.append('number_id', id);

            const xhr = new XMLHttpRequest();
            xhr.open("POST", "components/registrationsContact/actualizar_llamada.php", true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        const response = xhr.responseText.trim();

                        if (response === "success") {
                            // Cerrar el modal
                            const modal = bootstrap.Modal.getInstance(document.getElementById('modalLlamada_' + id));
                            modal.hide();


                            Swal.fire({
                                title: '¡Exitoso! 🎉',
                                text: 'La información se ha guardado correctamente.',
                                toast: true,
                                position: 'center',
                            }).then(() => {
                                // Recargar la página después de 2 segundos
                                setTimeout(() => {
                                    location.reload();
                                }, 2000);
                            });

                        } else {
                            // Mostrar notificación de error

                            Swal.fire({
                                title: 'Error! ❌',
                                text: 'Hubo un problema al guardar la información: ' + response,

                                toast: true,
                                position: 'center',

                                icon: 'error',

                                showConfirmButton: false,
                                timer: 4000,
                            });
                        }
                    } else {
                        console.error("Error en la conexión con el servidor");
                    }
                }
            };

            xhr.onerror = function() {

                Swal.fire({
                    title: 'Error! ❌',
                    text: 'No se pudo conectar con el servidor.',

                    toast: true,
                    position: 'center',


                    icon: 'error',

                    showConfirmButton: false,
                    timer: 4000,
                });
            };

            xhr.send(formData);
            return false;
        }
        function mostrarModalActualizarAdmision(id) {
        // Remover cualquier modal previo del DOM
        $('#modalActualizarAdmision_' + id).remove();

        // Crear el modal dinámicamente con un identificador único
        const modalHtml = `
        <div id="modalActualizarAdmision_${id}" class="modal fade" aria-hidden="true" aria-labelledby="modalActualizarAdmisionLabel" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-indigo-dark">
                        <h5 class="modal-title text-center"><i class="bi bi-arrow-left-right"></i> Actualizar Estado de Admisión</h5>
                        <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formActualizarAdmision_${id}">
                            <div class="form-group">
                                <label for="nuevoEstado_${id}">Seleccionar nuevo estado:</label>
                                <select class="form-control" id="nuevoEstado_${id}" name="nuevoEstado" required>
                                   <option value="">Seleccionar</option>
                                    <option value="1">Beneficiario</option>
                                    <option value="2">Rechazado</option>
                                </select>
                            </div>
                            <br>
                            <input type="hidden" name="id" value="${id}">
                            <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        `;

        // Añadir el modal al DOM
        document.body.insertAdjacentHTML('beforeend', modalHtml);

        // Mostrar el modal
        $('#modalActualizarAdmision_' + id).modal('show');

        // Manejar el envío del formulario
        $('#formActualizarAdmision_' + id).on('submit', function(e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Está seguro?',
                text: "¿Desea actualizar el estado de admisión?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, actualizar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const nuevoEstado = $('#nuevoEstado_' + id).val();
                    actualizarEstadoAdmision(id, nuevoEstado);
                    $('#modalActualizarAdmision_' + id).modal('hide');
                }
            });
        });
    }

        function actualizarEstadoAdmision(id, nuevoEstado) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "components/registrationsContact/actualizar_admision.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        const response = xhr.responseText.trim();
                        if (response === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'El estado de admisión se ha actualizado correctamente.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema al actualizar el estado de admisión.'
                            });
                        }
                    }
                }
            };

            xhr.send("id=" + id + "&nuevoEstado=" + encodeURIComponent(nuevoEstado));
        }

        function actualizarModalidad(id, nuevaModalidad) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "components/registrationsContact/actualizar_modalidad.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        const response = xhr.responseText.trim();
                        if (response === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'La modalidad se ha actualizado correctamente.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema al actualizar la modalidad.'
                            });
                        }
                    }
                }
            };

            xhr.send("id=" + id + "&nuevaModalidad=" + encodeURIComponent(nuevaModalidad));
        }

        function modalActualizarModalidad(id) {
            $('#modalActualizarModalidad_' + id).remove();

            const modalHtml = `
            <div id="modalActualizarModalidad_${id}" class="modal fade" aria-hidden="true" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-indigo-dark">
                            <h5 class="modal-title text-center">
                                <i class="bi bi-arrow-left-right"></i> Actualizar Modalidad
                            </h5>
                            <button type="button" class="btn-close bg-gray-light" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="formActualizarModalidad_${id}">
                                <div class="form-group">
                                    <label for="nuevaModalidad_${id}">Seleccionar nueva modalidad:</label>
                                    <select class="form-control" id="nuevaModalidad_${id}" name="nuevaModalidad" required>
                                        <option value="">Seleccionar</option>
                                        <option value="Presencial">Presencial</option>
                                        <option value="Virtual">Virtual</option>
                                    </select>
                                </div>
                                <br>
                                <input type="hidden" name="id" value="${id}">
                                <button type="submit" class="btn bg-indigo-dark text-white">Actualizar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            $('#modalActualizarModalidad_' + id).modal('show');

            $('#formActualizarModalidad_' + id).on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea actualizar la modalidad?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const nuevaModalidad = $('#nuevaModalidad_' + id).val();
                        actualizarModalidad(id, nuevaModalidad);
                        $('#modalActualizarModalidad_' + id).modal('hide');
                    }
                });
            });
        }

        function mostrarModalActualizarHorario(id) {
            $('#modalActualizarHorario_' + id).modal('show');

            $('#formActualizarHorario_' + id).on('submit', function(e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Está seguro?',
                    text: "¿Desea actualizar el horario?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, actualizar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        const nuevoHorario = $('#nuevoHorario_' + id).val();
                        actualizarHorario(id, nuevoHorario);
                        $('#modalActualizarHorario_' + id).modal('hide');
                    }
                });
            });
        }

        function actualizarHorario(id, nuevoHorario) {
            const xhr = new XMLHttpRequest();
            xhr.open("POST", "components/registrationsContact/actualizar_Horario.php", true);
            xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4) {
                    if (xhr.status == 200) {
                        const response = xhr.responseText.trim();
                        if (response === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Actualizado!',
                                text: 'El horario se ha actualizado correctamente.',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema al actualizar el horario.'
                            });
                        }
                    }
                }
            };

            xhr.send("id=" + id + "&nuevoHorario=" + encodeURIComponent(nuevoHorario));
        }
     
    </script>
</body>

</html>