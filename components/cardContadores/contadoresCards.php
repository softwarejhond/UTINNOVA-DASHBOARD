<!-- Barra de progreso global -->
<div class="progress mt-3">
    <div id="progress-bar-global" class="progress-bar progress-bar-striped progress-bar-animated bg-indigo-dark" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
</div>
<div class="text-center">
    <small id="countdown-timer" class="text-muted">Actualización en tiempo real</small>
</div>
<!-- HTML de las tarjetas -->
<div class="row">
    <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="row">
            <!-- Tarjeta Total Usuarios Registrados -->
            <div class="col-sm-12 col-lg-6 col-md- mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-teal-dark text-white shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-people-fill fa-3x text-white"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Total usuarios registrados</h5>
                            <h2>
                                <div class="spinner-grow text-light" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <span id="usuers_registrados"></span>
                                <br>
                                <h6 id="countdown-timer" class="text-white">Registros</h6>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta Usuarios por Verificar -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-magenta-light text-white shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-user-clock fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios por verificar</h5>
                            <h2>
                                <span id="total_sinVerificar"></span> |
                                <span id="porc_sinVerificar"></span>
                            </h2>
                            <a href="registrarionsContact.php" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Total de Usuarios -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-amber-light text-dark shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-users fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Total de Usuarios aceptados</h5>
                            <h2><span id="total_usuarios"></span></h2>
                            <a href="verifiedUsers.php" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Usuarios en Cundinamarca
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-indigo-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios Cundinamarca aceptados</h5>
                            <h2>
                                <span id="total_cundinamarca"></span> |
                                <span id="porc_cundinamarca"></span>
                            </h2>
                            <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div> -->

            <!-- Tarjeta Usuarios en Boyacá -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-teal-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-map-marker-alt fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios aceptados</h5>
                            <h2>
                                <span id="total_boyaca"></span> |
                                <span id="porc_boyaca"></span>
                            </h2>
                            <a href="#" class="btn btn-light btn-sm">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>
     <!-- Tarjeta Usuarios rechazados -->
     <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-info-light text-dark shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-person-x-fill fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios rechazados</h5>
                            <h2><span id="total_rechazados"></span> | <span id="porc_rechazados"></span>%</h2>
                            <a href="#" class="btn btn-light btn-sm">Ver detalles en contrucción <i class="bi bi-hammer"></i></a>
                        </div>
                    </div>
                </div>
            </div>
    
            <!-- Tarjeta establecio contacto de los verificados -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-brown-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-telephone-inbound fa-3x text-white"></i>
                        </div>
                        <div class="text-container text-white">
                            <h5 class="card-title">Se estableció contacto a verificados</h5>
                            <h2>
                                Sí: <span id="total_contacto_si_admin"></span> |
                                <span id="porc_contacto_si_admin"></span><br>
                                No: <span id="total_contacto_no_admin"></span> |
                                <span id="porc_contacto_no_admin"></span>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta Total de matriculados -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-indigo-dark text-white shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fa-brands fa-the-red-yeti fa-4x text-white"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Total de campistas matriculados</h5>
                            <h2><span id="total_matriculados"></span> | <span id="porc_matriculados"></span>%</h2>
                            <hr class="m-1 mb-2">
                            <h6 id="current-time" class="text-white"><i class="bi bi-hourglass-split"></i> </h6>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta Usuarios que conocieron el programa por Radio -->

        </div>
    </div>
    <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
        <div class="row">
            <!-- Tarjeta establecio contacto de los verificados -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-teal-light shadow">
                    <div class="card-body d-flex align-items-center">

                        <div class="text-container text-black">
                            <h5 class="card-title"> <i class="bi bi-geo-alt-fill fa-2x text-black"></i> Registros por departamento</h5>
                            <?php include("components/graphics/registerDeparments.php");  ?>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-warning-light shadow">
                    <div class="card-body d-flex align-items-center">

                        <div class="text-container text-black">
                            <h5 class="card-title"> <i class="fa-solid fa-code-compare fa-2x text-black"></i> Registros VS  matriculados</h5>
                            <?php include("components/graphics/registerVsEnrolled.php");  ?>
                           
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-purple-light text-dark shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-broadcast fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Impacto por Radio</h5>
                            <h2><span id="total_radio"></span></h2>
                            <hr class="m-1 mb-2">
                            <canvas id="voice-waveform" width="300" height="20"></canvas>
                            <script>
                                const canvas = document.getElementById('voice-waveform');
                                const ctx = canvas.getContext('2d');
                                let waveOffset = 0;

                                function drawWaveform() {
                                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                                    ctx.beginPath();
                                    ctx.moveTo(0, canvas.height / 2);

                                    for (let x = 0; x < canvas.width; x++) {
                                        const y = canvas.height / 2 + Math.sin((x + waveOffset) * 0.1) * 5;
                                        ctx.lineTo(x, y);
                                    }

                                    ctx.strokeStyle = '#6f42c1'; // Color de la onda
                                    ctx.lineWidth = 2;
                                    ctx.stroke();

                                    waveOffset += 1;
                                    requestAnimationFrame(drawWaveform);
                                }

                                drawWaveform();
                            </script>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tarjeta Usuarios que conocieron el programa por Redes Sociales -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-pink-light text-dark shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-share-fill fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Impacto por redes sociales</h5>
                            <h2><span id="total_redes_sociales"></span></h2>
                            <hr class="m-1 mb-2">
                            <i class="bi bi-facebook fa-1x text-gray-dark me-2"></i>
                            <i class="bi bi-instagram fa-1x text-gray-dark me-2"></i>
                            <i class="bi bi-whatsapp fa-1x text-gray-dark"></i>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Tarjeta establecio contacto -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-lime-light shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="bi bi-telephone-inbound fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Se estableció contacto</h5>
                            <h2>
                                Sí: <span id="total_contacto_si"></span> |
                                <span id="porc_contacto_si"></span><br>
                                No: <span id="total_contacto_no"></span> |
                                <span id="porc_contacto_no"></span>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tarjeta Usuarios que se inscribieron por institucion -->
            <div class="col-sm-12 col-lg-6 col-md-6 mb-3 mb-sm-0 mb-md-1">
                <div class="card bg-cyan-light text-dark shadow">
                    <div class="card-body d-flex align-items-center">
                        <div class="icon-container me-3">
                            <i class="fas fa-building fa-3x text-gray-dark"></i>
                        </div>
                        <div class="text-container">
                            <h5 class="card-title">Usuarios por evento</h5>
                            <select id="institucionSelect" class="form-select mb-2">
                                <option value="">Seleccione la localidad del evento</option>
                            </select>
                            <h2>
                                <span id="total_institucion">0</span> 
                                <small>usuarios</small>
                            </h2>
                            <div id="nombre_institucion" class="text-muted small"></div>
                        </div>
                    </div>
                </div>
            </div>
            
                
        </div>
    </div>
</div>
<br>
<br>
<!-- Asegúrate de incluir jQuery -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        function actualizarContadores() {
            $.ajax({
                url: 'components/cardContadores/actualizarContadores.php',
                method: 'GET',
                success: function(data) {
                    console.log('Datos recibidos:', data); // Agregar esta línea para depuración
                    $('#usuers_registrados').text(data.total_registrados);
                    $('#total_usuarios').text(data.total_usuarios);
                    $('#total_boyaca').text(data.total_boyaca);
                    $('#porc_boyaca').text(data.porc_boyaca + '%');
                    $('#total_cundinamarca').text(data.total_cundinamarca);
                    $('#porc_cundinamarca').text(data.porc_cundinamarca + '%');
                    $('#total_sinVerificar').text(data.total_sinVerificar);
                    $('#porc_sinVerificar').text(data.porc_sinVerificar + '%');
                    $('#total_GobernacionBoyaca').text(data.total_GobernacionBoyaca);
                    $('#porc_GobernacionBoyaca').text(data.porc_GobernacionBoyaca + '%');
                    $('#total_contacto_si').text(data.total_contacto_si);
                    $('#porc_contacto_si').text(data.porc_contacto_si + '%');
                    $('#total_contacto_no').text(data.total_contacto_no);
                    $('#porc_contacto_no').text(data.porc_contacto_no + '%');
                    $('#total_contacto_si_admin').text(data.total_contacto_si_admin);
                    $('#porc_contacto_si_admin').text(data.porc_contacto_si_admin + '%');
                    $('#total_contacto_no_admin').text(data.total_contacto_no_admin);
                    $('#porc_contacto_no_admin').text(data.porc_contacto_no_admin + '%');
                    $('#total_matriculados').text(data.total_matriculados); // Actualizar total matriculados
                    $('#porc_matriculados').text(data.porc_matriculados + '%'); // Actualizar porcentaje matriculados
                    $('#total_radio').text(data.total_radio);
                    $('#total_redes_sociales').text(data.total_redes_sociales);
                    $('#total_rechazados').text(data.total_rechazados); // Agregar esta línea
                    $('#porc_rechazados').text(data.porc_rechazados ); // Agregar esta línea
                    $('#porc_matriculados').text(data.porc_matriculados ); // Agregar esta línea
                    
                    // Actualizar select de instituciones
                    var select = $('#institucionSelect');
                    select.empty();
                    select.append('<option value="">Seleccione una institución</option>');
                    
                    data.instituciones.forEach(function(inst) {
                        select.append(`<option value="${inst.total}">${inst.nombre}</option>`);
                    });
                },
                error: function(error) {
                    console.error('Error al obtener los datos:', error);
                }
            });
        }

        function actualizarBarraProgreso() {
            var progreso = 0;
            var intervalo = setInterval(function() {
                progreso += 20; // Incremento para completar 100% en 5 segundos
                $('#progress-bar-global').css('width', progreso + '%').attr('aria-valuenow', progreso);
                if (progreso >= 100) {
                    clearInterval(intervalo);
                    $('#progress-bar-global').css('width', '0%').attr('aria-valuenow', 0); // Reiniciar la barra de progreso
                }
            }, 1000); // Actualizar cada 1 segundo
        }

        function actualizarHoraActual() {
            var now = new Date();
            var horas = now.getHours();
            var minutos = now.getMinutes().toString().padStart(2, '0');
            var segundos = now.getSeconds().toString().padStart(2, '0');
            var ampm = horas >= 12 ? 'PM' : 'AM';
            horas = horas % 12;
            horas = horas ? horas : 12; // La hora '0' debe ser '12'
            var horaActual = '<i class="bi bi-hourglass-split"></i> Actualiza en tiempo real: ' + horas + ':' + minutos + ':' + segundos + ' ' + ampm;
            $('#current-time').html(horaActual);
        }

        // Ejecutar la función cada 5 segundos para actualizar en tiempo real
        function iniciarActualizacion() {
            actualizarContadores();
            actualizarBarraProgreso();
        }

        iniciarActualizacion();
        setInterval(iniciarActualizacion, 5000);
        setInterval(actualizarHoraActual, 1000); // Actualizar la hora cada segundo

        // Agregar el evento change para el select
        $('#institucionSelect').change(function() {
            var total = $(this).val();
            var nombre = $(this).find('option:selected').text();
            $('#total_institucion').text(total);
            $('#nombre_institucion').text(nombre !== 'Seleccione una institución' ? nombre : '');
        });
    });
</script>