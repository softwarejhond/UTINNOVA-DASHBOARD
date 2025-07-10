<div class="row mt-4 page" id="page-2">
    <div class="card-body"><span class="corner corner-danger"></span>
        <div class="d-flex mb-0">
            <div class="">
                <h3 class="card-title text-black">
                    <b>
                        <i class="fa-solid fa-transgender "></i> Registros por géneros
                        <label for="date-select" id="openModal" class="btn btn-link p-0 ml-2 text-danger cursor-pointer" style="font-size: 1.2rem; cursor: pointer;" id="date-button">
                            <i class="fa-solid fa-transgender fa-beat"></i>
                        </label>
                        <input type="hidden" class="form-control d-inline-block ml-2" style="width: auto; display: none;">
                    </b>
                </h3>
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        const dateButton = document.getElementById('date-button');
                        const dateSelect = document.getElementById('date-select');

                        dateButton.addEventListener('click', function() {
                            dateSelect.style.display = dateSelect.style.display === 'none' ? 'inline-block' : 'none';
                        });
                    });
                </script>
                <h4 class="mb-1 font-weight-bold text-black"><b id="cantidadGenero">0</b></h4>

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


</div>

<!-- Modal para seleccionar género -->
<div class="modal fade" id="generoModal" tabindex="-1" aria-labelledby="generoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="generoModalLabel">Selecciona un género</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <ul id="generoList" class="list-group">
                    <!-- Los géneros se cargarán aquí dinámicamente -->
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', async () => {
        const openModal = document.getElementById('openModal');
        const generoList = document.getElementById('generoList');
        const cantidadGenero = document.getElementById('cantidadGenero');
        const progressBar = document.getElementById('progress-bar-usuarios');

        try {
            // Obtener los géneros desde el backend
            const respuesta = await fetch('components/cardContadores/actualizarContadores.php');
            const datos = await respuesta.json();

            // Verificar si los datos son válidos
            if (!datos.generos || datos.generos.length === 0) {
                throw new Error('No se encontraron géneros.');
            }

            // Poblar la lista de géneros en el modal
            generoList.innerHTML = '';
            datos.generos.forEach(genero => {
                // Verificar si el género es "LGBIQ+" y cambiarlo a "LGTBIQ+"
                if (genero.gener === 'LGBIQ+') {
                    genero.gener = 'LGTBIQ+';
                }

                const listItem = document.createElement('li');
                listItem.className = 'list-group-item d-flex justify-content-between align-items-center cursor-pointer';
                listItem.textContent = genero.gener;
                listItem.style.cursor = 'pointer';
                const badge = document.createElement('span');
                badge.className = 'badge bg-magenta-dark rounded-pill';
                badge.textContent = genero.cantidad;
                listItem.appendChild(badge);

                // Manejar clic en un género
                listItem.addEventListener('click', () => {
                    // Mostrar el género seleccionado y su cantidad
                    cantidadGenero.textContent = `Género: ${genero.gener} | Cantidad: ${genero.cantidad}`;

                    // Actualizar la barra de progreso
                    progressBar.style.width = `${genero.cantidad}%`;
                    progressBar.setAttribute('aria-valuenow', genero.cantidad);

                    // Cerrar el modal
                    const modal = bootstrap.Modal.getInstance(document.getElementById('generoModal'));
                    modal.hide();
                });

                generoList.appendChild(listItem);
            });

            // Abrir el modal al hacer clic en el ícono
            openModal.addEventListener('click', () => {
                const modal = new bootstrap.Modal(document.getElementById('generoModal'));
                modal.show();
            });
        } catch (error) {
            console.error('Error al cargar los géneros:', error);
            cantidadGenero.textContent = 'Error al cargar los géneros.';
        }
    });
</script>