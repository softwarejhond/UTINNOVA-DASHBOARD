<div class="row page align-items-stretch" id="page-2">
    <div class="text-center">
        <small id="countdown-timer" class="text-muted">Actualización en tiempo real</small>
    </div>
    <div class="col-md-6 col-lg-3 col-sm-12 pb-4">
        <div class="card h-100">
            <div class="card-body"><span class="corner corner-success"></span>
                <div class="d-flex mb-0">
                    <div class="">
                        <h4 class="card-title text-black">
                            <b>
                                <i class="fas fa-users fa-1x"></i> Registrados Lote 2
                                <label for="date-select-lote2" class="btn btn-link p-0 ml-2 text-success" style="font-size: 1.2rem; cursor: pointer;" id="date-button-lote2">
                                    <i class="fa-solid fa-calendar-days fa-beat"></i>
                                </label>
                                <input type="hidden" id="date-select-lote2" class="form-control d-inline-block ml-2" style="width: auto; display: none;">
                            </b>
                        </h4>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const dateButton = document.getElementById('date-button-lote2');
                                const dateSelect = document.getElementById('date-select-lote2');

                                dateButton.addEventListener('click', function() {
                                    Swal.fire({
                                        title: 'Selecciona una fecha',
                                        html: '<input type="date" id="swal-date-select-lote2" class="form-control">',
                                        showCancelButton: true,
                                        confirmButtonText: 'Aceptar',
                                        cancelButtonText: 'Cancelar',
                                        preConfirm: () => {
                                            const selectedDate = document.getElementById('swal-date-select-lote2').value;
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
                                                    lote: 2 // Agregar el parámetro lote
                                                },
                                                success: function(data) {
                                                    Swal.fire({
                                                        title: 'Resultados',
                                                        html: `<p>Usuarios registrados hasta la fecha seleccionada en <strong>Lote 2</strong>: <br><h2><b>${data.total_registrados_por_fecha}</b></h2></p>`,
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
                        <h5 class="mb-1 font-weight-bold text-black"><b id="usuers_registrados_lote2">0</b> - <b>100%</b></h5>

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
    <div class="col-md-6 col-lg-3 col-sm-12 ">
        <div class="card  relative ">
            <div class="card-body z-10">
                <div class="d-flex align-items-center">
                    <h5 class="card-title text-black "><b><i class="bi bi-pie-chart-fill"></i> Matriculados Lote 2</b>
                        <h><br>

                            <?php include("components/graphics/registerVsEnrolledDos.php"); ?>
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
    <div class="col-md-6 col-lg-3 col-sm-12">
        <div class="card relative">
            <div class="card-body z-10">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-black"><b><i class="bi bi-pie-chart-fill"></i> Progreso Lote 2</b>
                        <h><br>
                            <?php include("components/graphics/enrolledVsGraduatedDos.php"); ?>
                        </h>
                    </h5>
                </div>
            </div>
            <svg class="absolute bottom-0 right-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#0099ff" fill-opacity="1" d="M0,192L48,208C96,224,192,256,288,272C384,288,480,288,576,250.7C672,213,768,139,864,138.7C960,139,1056,213,1152,208C1248,203,1344,117,1392,74.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" style="fill: #02d7ff;"></path>
            </svg>
        </div>
    </div>

    <!-- Nueva tarjeta: Rangos de Edad -->
    <div class="col-md-6 col-lg-3 col-sm-12">
        <div class="card relative">
            <div class="card-body z-10">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="card-title text-black"><b><i class="bi bi-people-fill"></i> Rangos de Edad Lote 2</b>
                        <h><br>
                            <?php include("components/graphics/ageRangesDos.php"); ?>
                        </h>
                    </h5>
                </div>
            </div>
            <svg class="absolute bottom-0 right-0" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
                <path fill="#0099ff" fill-opacity="1" d="M0,192L48,208C96,224,192,256,288,272C384,288,480,288,576,250.7C672,213,768,139,864,138.7C960,139,1056,213,1152,208C1248,203,1344,117,1392,74.7L1440,32L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z" style="fill: #FF6B6B;"></path>
            </svg>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 col-sm-12 pb-4">
        <div class="card h-100">
            <div class="card-body"><span class="corner corner-warning"></span>
                <div class="d-flex mb-0">
                    <h4 class="card-title text-black "><b><i class="fa-solid fa-user-check fa-1x"></i> Usuarios aceptados Lote 2</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_usuarios_aceptados_lote2">0</b> | <b id="porc_usuarios_aceptados_lote2"></b>%</h4>
                            <p class="mb-2 text-xs text-muted"><i class="fa fa-arrow-circle-up text-warning"></i><span class="opacity-75">
                                    Usuarios del Lote 2 que cumplen con los requisitos</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-usuarios-aceptados-lote2" class="progress-bar bg-warning" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-muted text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 col-sm-12 pb-4">
        <div class="card h-100">
            <div class="card-body"><span class="corner corner-danger"></span>
                <div class="d-flex mb-0">
                    <h4 class="card-title text-black "><b><i class="fas fa-user-clock fa-1x "></i> Usuarios rechazados Lote 2</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_rechazados_lote2">0</b> | <b id="porc_rechazados_lote2"></b>%</h4>
                            <p class="mb-2 text-xs text-muted"><i class="fa fa-arrow-circle-down text-danger"></i><span class="opacity-75">
                                    Usuarios del Lote 2 que no cumplen con los requisitos</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-rechazados-lote2" class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-muted text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>


    <div class="col-md-6 col-lg-3 col-sm-12 pb-4">
        <div class="card h-100"><span class="corner corner-danger"></span>
            <div class="d-flex mb-0">
                <div class="">
                    <h3 class="card-title text-black">
                        <b>
                            <i class="fa-solid fa-transgender "></i> Registros por géneros
                            <button id="openModalLoteDos" type="button" class="btn btn-link p-0 ml-2 text-danger" style="font-size: 1.2rem; cursor: pointer;" title="Ver detalle por género">
                                <i class="fa-solid fa-transgender fa-beat"></i>
                            </button>
                        </b>
                    </h3>
                    <h4 class="mb-1 font-weight-bold text-black"><b id="cantidadGeneroLoteDos">0</b></h4>
                    <p class="mb-2 text-xs text-muted"><i class="fa fa-arrow-circle-up text-danger"></i><span class="opacity-75">
                            Información obtenida a través de formularios oficiales</span></p>
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

    <div class="col-md-6 col-lg-3 col-sm-12 pb-4">
        <div class="card h-100 bg-warning text-white">
            <div class="card-body">
                <div class="d-flex mb-0">
                    <h3 class="card-title text-black "><b><i class="fas fa-user-clock fa-1x "></i> Usuarios por Verificar Lote 2</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_sinVerificar_lote2">0</b> | <b id="porc_sinVerificar_lote2"></b>%</h4>
                            <p class="mb-2 text-xs text-white"><i class="bi bi-exclamation-octagon-fill"></i><span class="opacity-75">
                                    Usuarios del Lote 2 sin verificar información</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-sin-verificar-lote2" class="progress-bar bg-black" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-white text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 col-sm-12 ">
        <div class="card bg-info text-white">
            <div class="card-body">
                <div class="d-flex mb-0">
                    <h3 class="card-title text-black "><b><i class="fas fa-layer-group fa-1x "></i> Lote 2 aceptados</b>
                        <h><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_lote2">0</b> | <b id="porc_lote2"></b></h4>
                            <p class="mb-2 text-xs text-mute"><i class="bi bi-check-circle-fill"></i><span class="opacity-75">
                                    Usuarios del Lote 2 que aprueban los requisitos</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div id="progress-bar-lote2" class="progress-bar bg-black" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-mute text-xs ml-1">Hora actual</small>
                            </h6>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 col-sm-12 ">
        <div class="card">
            <div class="card-body"><span class="corner corner-danger"></span>
                <div class="d-flex mb-0">
                    <h3 class="card-title text-black "><b><i class="bi bi-broadcast"></i> Impacto por radio</b>
                        <h><br><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_radio">0</b> | <b></b>100%</h4>
                            <p class="mb-2 text-xs text-muted"><i class="bi bi-broadcast"></i> <span class="opacity-75">
                                    Usuarios que se han registrado por pauta radial</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <br>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-muted text-xs ml-1">Hora actual</small>
                            </h6>
                            <br>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-lg-3 col-sm-12 ">
        <div class="card">
            <div class="card-body"><span class="corner corner-info"></span>
                <div class="d-flex mb-0">
                    <h3 class="card-title text-black "><b><i class="bi bi-wechat"></i> Impacto en redes</b>
                        <h><br><br>
                            <h4 class="mb-1 font-weight-bold text-black"><b id="total_redes_sociales">0</b> | <b></b>100%</h4>
                            <p class="mb-2 text-xs text-muted"><i class="bi bi-wechat"></i><span class="opacity-75">
                                    Usuarios que se han registrado por pauta en redes sociales</span></p>
                            <div class="progress progress-sm h-5 mt-2 mb-3">
                                <div class="progress-bar bg-info" role="progressbar" style="width: 75%;" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <br>
                            <h6 class="p-0">
                                <span id="current-time"></span>
                                <small class="text-muted text-xs ml-1">Hora actual</small>
                            </h6>
                            <br>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 col-sm-12 ">
    <div class="card bg-danger text-white">
        <div class="card-body">
            <div class="d-flex mb-0">
                <h3 class="card-title text-white "><b><i class="bi bi-telephone-inbound fa-1x "></i> Contacto a beneficiarios Lote 2</b>
                    <h><br>
                        <h5 class="mb-1 font-weight-bold text-white">
                            <b>SÍ: <span id="total_contacto_si_lote2"></span> -
                                <span id="porc_contacto_si_lote2"></span>%</b> |
                            <b>NO: <span id="total_contacto_no_lote2"></span> -
                                <span id="porc_contacto_no_lote2"></span>%</b>
                        </h5>
                        <p class="mb-2 text-xs text-white"><i class="bi bi-telephone-fill"></i><span class="opacity-75">
                                Contacto establecido con beneficiarios del Lote 2</span></p>
                        <div class="progress progress-sm h-5 mt-2 mb-3">
                            <div id="progress-bar-contacto-lote2" class="progress-bar bg-black" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <h6 class="p-0">
                            <span id="current-time"></span>
                            <small class="text-white text-xs ml-1">Hora actual</small>
                        </h6>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const openModal = document.getElementById('openModalLoteDos');
        const cantidadGenero = document.getElementById('cantidadGeneroLoteDos');
        const progressBar = document.querySelector('#page-2 .progress-bar.bg-danger');

        openModal.addEventListener('click', async () => {
            try {
                // Obtener los géneros desde el backend
                const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
                const datos = await respuesta.json();
                const generos = datos.generosLote2; // <-- Cambia aquí

                if (!generos || generos.length === 0) {
                    throw new Error('No se encontraron géneros.');
                }

                // Construir la lista HTML para Swal
                let listHtml = '<ul class="list-group">';
                generos.forEach((genero, idx) => {
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
                    title: 'Selecciona un género',
                    html: listHtml,
                    showCancelButton: true,
                    showConfirmButton: false,
                    didOpen: () => {
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

