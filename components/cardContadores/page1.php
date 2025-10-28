<div class="row page align-items-stretch" id="page-1">

    <!-- Primeras tarjetas normales -->
    <div class="col-md-6 col-lg-4 col-sm-12 pb-4">
        <!-- Tarjeta Registrados Lote 1 -->
        <div class="card h-100">
            <div class="card-body"><span class="corner corner-success"></span>
                <div class="d-flex mb-0">
                    <div class="">
                        <h4 class="card-title text-black">
                            <b>
                                <i class="fas fa-users fa-1x"></i> Registrados Lote 1
                                <label for="date-select" class="btn btn-link p-0 ml-2 text-success" style="font-size: 1.2rem; cursor: pointer;" id="date-button">
                                    <i class="fa-solid fa-calendar-days fa-beat"></i>
                                </label>
                                <input type="hidden" id="date-select" class="form-control d-inline-block ml-2" style="width: auto; display: none;">
                            </b>
                        </h4>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const dateButton = document.getElementById('date-button');
                                const dateSelect = document.getElementById('date-select');

                                dateButton.addEventListener('click', function() {
                                    Swal.fire({
                                        title: 'Selecciona una fecha',
                                        html: '<input type="date" id="swal-date-select" class="form-control">',
                                        showCancelButton: true,
                                        confirmButtonText: 'Aceptar',
                                        cancelButtonText: 'Cancelar',
                                        preConfirm: () => {
                                            const selectedDate = document.getElementById('swal-date-select').value;
                                            if (!selectedDate) {
                                                Swal.showValidationMessage('Por favor selecciona una fecha');
                                            }
                                            return selectedDate;
                                        }
                                    }).then((result) => {
                                        if (result.isConfirmed) {
                                            const selectedDate = result.value;
                                            dateSelect.value = selectedDate;
                                            dateSelect.dispatchEvent(new Event('change'));

                                            // Realizar la solicitud AJAX para obtener los datos
                                            $.ajax({
                                                url: 'components/cardContadores/actualizarContadores.php',
                                                method: 'GET',
                                                data: {
                                                    date: selectedDate,
                                                    lote: 1 // Agregar el parámetro lote
                                                },
                                                success: function(data) {
                                                    Swal.fire({
                                                        title: 'Resultados',
                                                        html: `<p>Usuarios registrados hasta la fecha seleccionada en <strong>Lote 1</strong>: <br><h2><b>${data.total_registrados_por_fecha}</b></h2></p>`,
                                                        icon: 'info',
                                                        confirmButtonText: 'Cerrar'
                                                    });
                                                },
                                                error: function(error) {
                                                    Swal.fire({
                                                        title: 'Error',
                                                        text: 'No se pudieron obtener los datos. Intenta nuevamente.',
                                                        icon: 'error',
                                                        confirmButtonText: 'Cerrar'
                                                    });
                                                    console.error('Error al obtener los datos:', error);
                                                }
                                            });
                                        }
                                    });
                                });
                            });
                        </script>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const dateButton = document.getElementById('date-button');
                                const dateSelect = document.getElementById('date-select');

                                dateButton.addEventListener('click', function() {
                                    dateSelect.style.display = dateSelect.style.display === 'none' ? 'inline-block' : 'none';
                                });
                            });
                        </script>
                        <h5 class="mb-1 font-weight-bold text-black"><b id="usuers_registrados_lote1">0</b> - <b>100%</b></h5>

                        <p class="mb-2 text-xs text-muted"><i class="fa fa-arrow-circle-up text-success"></i><span class="opacity-75">
                                Información obtenida a través de formularios oficiales</span></p>
                        <div class="progress progress-sm h-5 mt-2 mb-3">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h6 class="p-0">
                            <span id="current-time"></span>
                            <small class="text-muted text-xs ml-1">Hora actual</small>
                        </h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-sm-12 ">
        <div class="card  relative ">
            <div class="card-body z-10">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black "><b><i class="bi bi-pie-chart-fill"></i> Matriculados Lote 1</b>
                        <h><br>

                            <?php include("components/graphics/registerVsEnrolled.php"); ?>
                        </h>
                    </h5>
                </div>
            </div>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320" class="absolute bottom-0  right-0">
                <path fill="#0099ff" fill-opacity="1" d="M0,192L48,208C96,224,192,256,288,272C384,288,480,288,576,250.7C672,213,768,139,864,138.7C960,139,1056,213,1152,208C1248,203,1344,117,1392,74.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" style="fill: #ffc107;"></path>
            </svg>
        </div>
    </div>


    <!-- Nueva tarjeta: Matriculados vs Formados vs Certificados -->
    <div class="col-md-6 col-lg-4 col-sm-12">
        <div class="card relative">
            <div class="card-body z-10">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-black"><b><i class="bi bi-pie-chart-fill"></i> Progreso de campistas Lote 1</b>
                        <h><br>
                            <?php include("components/graphics/enrolledVsGraduated.php"); ?>
                        </h>
                    </h5>
                </div>
            </div>
            <svg class="absolute bottom-0 right-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#0099ff" fill-opacity="1" d="M0,192L48,208C96,224,192,256,288,272C384,288,480,288,576,250.7C672,213,768,139,864,138.7C960,139,1056,213,1152,208C1248,203,1344,117,1392,74.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" style="fill: #02d7ff;"></path>
            </svg>
        </div>
    </div>


    <div class="col-md-6 col-lg-4 col-sm-12 pb-4">
        <div class="card h-100">
            <div class="card-body"><span class="corner corner-warning"></span>
                <div class="d-flex mb-0">
                    <h4 class="card-title text-black "><b><i class="fa-solid fa-user-check fa-1x"></i> Usuarios aceptados Lote 1</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_usuarios_aceptados_lote1">0</b> | <b id="porc_usuarios_aceptados_lote1"></b>%</h4>
                            <p class="mb-2 text-xs text-muted"><i class="fa fa-arrow-circle-up text-warning"></i><span class="opacity-75">
                                    Usuarios del Lote 1 que cumplen con los requisitos</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-usuarios-aceptados-lote1" class="progress-bar bg-warning" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-muted text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-4 col-sm-12 pb-4">
        <div class="card h-100">
            <div class="card-body"><span class="corner corner-danger"></span>
                <div class="d-flex mb-0">
                    <h4 class="card-title text-black "><b><i class="fas fa-user-clock fa-1x "></i> Usuarios rechazados Lote 1</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_rechazados_lote1">0</b> | <b id="porc_rechazados_lote1"></b>%</h4>
                            <p class="mb-2 text-xs text-muted"><i class="fa fa-arrow-circle-down text-danger"></i><span class="opacity-75">
                                    Usuarios del Lote 1 que no cumplen con los requisitos</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-rechazados-lote1" class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-muted text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6 col-lg-4 col-sm-12 pb-4">
        <div class="card bg-warning text-white h-100">
            <div class="card-body">
                <div class="d-flex mb-0">
                    <h3 class="card-title text-black "><b><i class="fas fa-user-clock fa-1x "></i> Usuarios por Verificar Lote 1</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_sinVerificar_lote1">0</b> | <b id="porc_sinVerificar_lote1"></b>%</h4>
                            <p class="mb-2 text-xs text-white"><i class="bi bi-exclamation-octagon-fill"></i><span class="opacity-75">
                                    Usuarios del Lote 1 sin verificar información</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-sin-verificar-lote1" class="progress-bar bg-black" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-white text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>

    <!-- DISTRIBUCIÓN ESPECIAL: Tarjeta alta + 6 tarjetas en 2 filas -->
    <div class="row w-100">
        <!-- Tarjeta alta a la izquierda -->
        <div class="col-md-4 d-flex flex-column mb-4">
            <div class="card h-100" style="max-height: 650px;">
                <div class="card-body d-flex flex-column">
                    <!-- Título fijo arriba -->
                    <div class="mb-3">
                        <h4 class="card-title text-black mb-0">
                            <b>
                                <i class="fa-solid fa-laptop-code"></i> Matriculados en Bootcamps Lote 1
                                <button id="openModalBootcampsLoteUno" type="button" class="btn btn-link p-0 ml-2 text-primary" style="font-size: 1.2rem; cursor: pointer;" title="Ver detalle por bootcamp">
                                    <i class="fa-solid fa-laptop-code fa-beat"></i>
                                </button>
                            </b>
                        </h4>
                    </div>

                    <!-- Lista scrolleable en el medio -->
                    <div id="bootcampListLoteUno" class="flex-grow-1 h-100 w-100" style="max-height: 450px; min-height: 280px; overflow-y: auto; border: 1px solid #eee; border-radius: 8px; padding: 10px;">
                        <p class="text-muted text-center">Cargando bootcamps...</p>
                    </div>

                    <!-- Total fijo abajo -->
                    <div class="mt-3">
                        <h5 class="mb-1 font-weight-bold text-black text-center">
                            Total: <span id="totalBootcampsLoteUno" class="badge badge-primary">0</span> matriculados
                        </h5>
                        <div class="progress progress-sm h-5 mt-2">
                            <div id="progress-bar-bootcamps-lote1" class="progress-bar bg-primary" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjetas agrupadas a la derecha (2 filas x 2 tarjetas) -->
        <div class="col-md-8">
            <div class="row">
                <!-- Primera fila de dos tarjetas -->
                <div class="col-md-6 mb-3">
                    <div class="card h-100">
                        <div class="card-body"><span class="corner corner-warning"></span>
                            <div class="d-flex mb-0">
                                <h3 class="card-title text-black "><b><i class="bi bi-person-slash"></i> Total de No Válidos Lote 1</b>
                                    <h><br><br>
                                        <h4 class="mb-1 font-weight-bold text-black"><b id="total_no_validos_lote1">0</b> | <b id="porc_no_validos_lote1"></b>%</h4>
                                        <p class="mb-2 text-xs text-muted"><i class="bi bi-person-slash"></i> <span class="opacity-75">
                                                Usuarios del Lote 1 No válidos según Interventoría</span></p>
                                        <div class="progress progress-sm h-5 mt-2 mb-3">
                                            <div id="progress-bar-no-validos-lote1" class="progress-bar bg-warning" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <h6 class="p-0">
                                            <span id="current-time"></span>
                                            <small class="text-muted text-xs ml-1">Hora actual</small>
                                        </h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="card bg-danger text-white h-100">
                        <div class="card-body">
                            <div class="d-flex mb-0">
                                <h3 class="card-title text-white "><b><i class="bi bi-telephone-inbound fa-1x "></i> Contacto a beneficiarios Lote 1</b>
                                    <h><br>
                                        <h5 class="mb-1 font-weight-bold text-white">
                                            <b>SÍ: <span id="total_contacto_si_lote1"></span> -
                                                <span id="porc_contacto_si_lote1"></span>%</b> |
                                            <b>NO: <span id="total_contacto_no_lote1"></span> -
                                                <span id="porc_contacto_no_lote1"></span>%</b>
                                        </h5>
                                        <p class="mb-2 text-xs text-white"><i class="bi bi-telephone-fill"></i><span class="opacity-75">
                                                Contacto establecido con beneficiarios del Lote 1</span></p>
                                        <div class="progress progress-sm h-5 mt-2 mb-3">
                                            <div id="progress-bar-contacto-lote1" class="progress-bar bg-black" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <h6 class="p-0">
                                            <span id="current-time"></span>
                                            <small class="text-white text-xs ml-1">Hora actual</small>
                                        </h6>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Segunda fila de dos tarjetas -->
                <div class="col-md-6">
                    <div class="card h-100">
                        <span class="corner corner-danger"></span>
                        <div class="d-flex mb-0 mt-3">
                            <div>
                                <h3 class="card-title text-black">
                                    <b>
                                        <i class="fa-solid fa-building"></i> Inscritos por sede
                                        <button id="openModalSedeLoteUno" type="button" class="btn btn-link p-0 ml-2 text-danger" style="font-size: 1.2rem; cursor: pointer;" title="Ver detalle por sede">
                                            <i class="fa-solid fa-building fa-beat"></i>
                                        </button>
                                    </b>
                                </h3>
                                <h4 class="mb-1 font-weight-bold text-black"><b id="cantidadSedeLoteUno">0</b></h4>
                                <p class="mb-2 text-xs text-muted">
                                    <i class="fa fa-arrow-circle-up text-danger"></i>
                                    <span class="opacity-75">Estudiantes inscritos por sede (Lote 1)</span>
                                </p>
                                <div class="progress progress-sm h-5 mt-2 mb-3">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <h6 class="p-0">
                                    <span id="current-time"></span>
                                    <small class="text-muted text-xs ml-1">Hora actual</small>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100">
                        <span class="corner corner-danger"></span>
                        <div class="d-flex mb-0 mt-3">
                            <div>
                                <h3 class="card-title text-black">
                                    <b>
                                        <i class="fa-solid fa-building"></i> Matriculados por sede
                                        <button id="openModalSedeMatriculadosLoteUno" type="button" class="btn btn-link p-0 ml-2 text-danger" style="font-size: 1.2rem; cursor: pointer;" title="Ver detalle por sede">
                                            <i class="fa-solid fa-building fa-beat"></i>
                                        </button>
                                    </b>
                                </h3>
                                <h4 class="mb-1 font-weight-bold text-black"><b id="cantidadSedeMatriculadosLoteUno">0</b></h4>
                                <p class="mb-2 text-xs text-muted">
                                    <i class="fa fa-arrow-circle-up text-danger"></i>
                                    <span class="opacity-75">Estudiantes matriculados por sede (Lote 1)</span>
                                </p>
                                <div class="progress progress-sm h-5 mt-2 mb-3">
                                    <div class="progress-bar bg-danger" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <h6 class="p-0">
                                    <span id="current-time"></span>
                                    <small class="text-muted text-xs ml-1">Hora actual</small>
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const openModal = document.getElementById('openModalSedeMatriculadosLoteUno');
        const cantidadSede = document.getElementById('cantidadSedeMatriculadosLoteUno');
        const progressBar = document.querySelector('#page-1 .progress-bar.bg-danger');

        let isLoading = false; // Variable para evitar doble click

        openModal.addEventListener('click', async () => {
            // Prevenir doble click
            if (isLoading) return;

            isLoading = true;

            // Mostrar loader
            Swal.fire({
                title: 'Cargando...',
                text: 'Obteniendo matriculados por sede',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
                const datos = await respuesta.json();
                const sedes = datos.sedesMatriculadosLote1;

                if (!sedes || sedes.length === 0) {
                    throw new Error('No se encontraron sedes.');
                }

                let listHtml = '<ul class="list-group" style="max-height:300px;overflow-y:auto;">';
                sedes.forEach((sede, idx) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer" 
                        style="cursor:pointer;" 
                        data-idx="${idx}">
                        ${sede.sede}
                        <span class="badge bg-magenta-dark rounded-pill">${sede.cantidad}</span>
                    </li>`;
                });
                listHtml += '</ul>';

                // Cerrar loader y mostrar datos
                await Swal.fire({
                    title: 'Matriculados por sede (Lote 1)',
                    html: listHtml,
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Cerrar',
                    didOpen: () => {
                        Swal.getHtmlContainer().querySelectorAll('li').forEach(li => {
                            li.addEventListener('click', () => {
                                Swal.close();
                                const idx = li.getAttribute('data-idx');
                                const sede = sedes[idx];
                                cantidadSede.textContent = `Sede: ${sede.sede} | Cantidad: ${sede.cantidad}`;
                                progressBar.style.width = `${sede.cantidad}%`;
                                progressBar.setAttribute('aria-valuenow', sede.cantidad);
                            });
                        });
                    }
                });

            } catch (error) {
                console.error('Error al cargar las sedes:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar las sedes matriculadas.',
                    icon: 'error'
                });
                cantidadSede.textContent = 'Error al cargar las sedes.';
            } finally {
                isLoading = false; // Liberar el lock
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const openModal = document.getElementById('openModalSedeLoteUno');
        const cantidadSede = document.getElementById('cantidadSedeLoteUno');
        const progressBar = document.querySelector('#page-1 .progress-bar.bg-danger');

        let isLoading = false; // Variable para evitar doble click

        openModal.addEventListener('click', async () => {
            // Prevenir doble click
            if (isLoading) return;

            isLoading = true;

            // Mostrar loader
            Swal.fire({
                title: 'Cargando...',
                text: 'Obteniendo inscritos por sede',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
                const datos = await respuesta.json();
                const sedes = datos.sedesLote1;

                if (!sedes || sedes.length === 0) {
                    throw new Error('No se encontraron sedes.');
                }

                let listHtml = '<ul class="list-group" style="max-height:300px;overflow-y:auto;">';
                sedes.forEach((sede, idx) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer" 
                        style="cursor:pointer;" 
                        data-idx="${idx}">
                        ${sede.sede}
                        <span class="badge bg-magenta-dark rounded-pill">${sede.cantidad}</span>
                    </li>`;
                });
                listHtml += '</ul>';

                // Cerrar loader y mostrar datos
                await Swal.fire({
                    title: 'Inscritos por sede (Lote 1)',
                    html: listHtml,
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Cerrar',
                    didOpen: () => {
                        Swal.getHtmlContainer().querySelectorAll('li').forEach(li => {
                            li.addEventListener('click', () => {
                                Swal.close();
                                const idx = li.getAttribute('data-idx');
                                const sede = sedes[idx];
                                cantidadSede.textContent = `Sede: ${sede.sede} | Cantidad: ${sede.cantidad}`;
                                progressBar.style.width = `${sede.cantidad}%`;
                                progressBar.setAttribute('aria-valuenow', sede.cantidad);
                            });
                        });
                    }
                });

            } catch (error) {
                console.error('Error al cargar las sedes:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar las sedes.',
                    icon: 'error'
                });
                cantidadSede.textContent = 'Error al cargar las sedes.';
            } finally {
                isLoading = false; // Liberar el lock
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const openModal = document.getElementById('openModalLoteUno');
        const cantidadGenero = document.getElementById('cantidadGeneroLoteUno');
        const progressBar = document.querySelector('#page-1 .progress-bar.bg-danger');

        openModal.addEventListener('click', async () => {
            try {
                // Obtener los géneros desde el backend
                const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
                const datos = await respuesta.json();
                const generos = datos.generosLote1; // <-- Cambia aquí

                if (!generos || generos.length === 0) {
                    throw new Error('No se encontraron géneros.');
                }

                // Construir la lista HTML para Swal
                let listHtml = '<ul class="list-group" style="max-height:300px;overflow-y:auto;">';
                generos.forEach((genero, idx) => {
                    // Corregir nombre si es necesario
                    if (genero.gener === 'LGBIQ+') genero.gener = 'LGTBIQ+';
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center cursor-pointer" 
                        style="cursor:pointer;" 
                        data-idx="${idx}">
                        ${genero.gener}
                        <span class="badge bg-magenta-dark rounded-pill">${genero.cantidad}</span>
                    </li>`;
                });
                listHtml += '</ul>';

                // Mostrar Swal
                await Swal.fire({
                    title: 'Totales por género',
                    html: listHtml,
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Cerrar',
                    didOpen: () => {
                        // Agregar eventos a los items
                        Swal.getHtmlContainer().querySelectorAll('li').forEach(li => {
                            li.addEventListener('click', () => {
                                Swal.close();
                                const idx = li.getAttribute('data-idx');
                                const genero = generos[idx];
                                cantidadGenero.textContent = `Género: ${genero.gener} | Cantidad: ${genero.cantidad}`;
                                progressBar.style.width = `${genero.cantidad}%`;
                                progressBar.setAttribute('aria-valuenow', genero.cantidad);
                            });
                        });
                    }
                });

            } catch (error) {
                console.error('Error al cargar los géneros:', error);
                cantidadGenero.textContent = 'Error al cargar los géneros.';
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const openModal = document.getElementById('openModalBootcampsLoteUno');
        const bootcampList = document.getElementById('bootcampListLoteUno');
        const totalBootcamps = document.getElementById('totalBootcampsLoteUno');

        let isLoading = false; // Variable para evitar doble click

        // Función para cargar y mostrar los bootcamps
        async function cargarBootcamps() {
            try {
                const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsLote1;

                if (!bootcamps || bootcamps.length === 0) {
                    bootcampList.innerHTML = '<p class="text-muted text-center">No se encontraron bootcamps.</p>';
                    totalBootcamps.textContent = '0';
                    return;
                }

                // Calcular total de inscritos
                const total = bootcamps.reduce((sum, bootcamp) => sum + parseInt(bootcamp.cantidad), 0);
                totalBootcamps.textContent = total;

                // Mostrar TODOS los bootcamps (scrolleable)
                let listHtml = '';
                bootcamps.forEach((bootcamp, index) => {
                    listHtml += `
                    <div class="d-flex justify-content-between align-items-center py-2 ${index < bootcamps.length - 1 ? 'border-bottom' : ''}" style="border-color: #f0f0f0;">
                        <span class="text-dark" style="font-size: 0.85rem; font-weight: 500;" title="${bootcamp.bootcamp}">
                            ${bootcamp.bootcamp}
                        </span>
                        <span class="badge badge-primary">${bootcamp.cantidad}</span>
                    </div>
                `;
                });

                bootcampList.innerHTML = listHtml;

            } catch (error) {
                console.error('Error al cargar bootcamps:', error);
                bootcampList.innerHTML = '<p class="text-danger text-center">Error al cargar bootcamps.</p>';
            }
        }

        // Modal con loader y protección contra doble click
        openModal.addEventListener('click', async () => {
            // Prevenir doble click
            if (isLoading) return;

            isLoading = true;

            // Mostrar loader
            Swal.fire({
                title: 'Cargando...',
                text: 'Obteniendo lista completa de bootcamps',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
                const datos = await respuesta.json();
                const bootcamps = datos.bootcampsLote1;

                if (!bootcamps || bootcamps.length === 0) {
                    throw new Error('No se encontraron bootcamps.');
                }

                let listHtml = '<ul class="list-group" style="max-height:400px;overflow-y:auto;">';
                bootcamps.forEach((bootcamp, idx) => {
                    listHtml += `
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span>${bootcamp.bootcamp}</span>
                        <span class="badge bg-primary rounded-pill">${bootcamp.cantidad}</span>
                    </li>
                `;
                });
                listHtml += '</ul>';

                const total = bootcamps.reduce((sum, bootcamp) => sum + parseInt(bootcamp.cantidad), 0);

                // Cerrar loader y mostrar datos
                await Swal.fire({
                    title: 'Inscritos en Bootcamps (Lote 1)',
                    html: `
                    <div class="mb-3">
                        <h5>Total de inscritos: <span class="badge badge-primary">${total}</span></h5>
                        <p class="text-muted">Mostrando ${bootcamps.length} bootcamps</p>
                    </div>
                    ${listHtml}
                `,
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Cerrar',
                    width: '600px'
                });

            } catch (error) {
                console.error('Error al cargar bootcamps:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar los bootcamps.',
                    icon: 'error'
                });
            } finally {
                isLoading = false; // Liberar el lock
            }
        });

        // CARGA INICIAL ÚNICAMENTE (SIN POLLING)
        cargarBootcamps();

        // EXPONER LA FUNCIÓN GLOBALMENTE PARA EL BOTÓN
        window.actualizarBootcampsLoteUno = cargarBootcamps;
    });
</script>