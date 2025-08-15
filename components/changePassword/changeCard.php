<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-12">
            <!-- Tarjeta de búsqueda (siempre visible) -->
            <div class="card shadow mb-4">
                <div class="card-header bg-indigo-dark text-white text-center">
                    <i class="bi bi-search"></i> Buscar Campista Matriculado
                </div>
                <div class="card-body">
                    <form id="formBuscarCampista" autocomplete="off" class="w-100">
                        <div class="input-group">
                            <input type="number" class="form-control text-center fs-5" id="number_id" name="number_id" placeholder="Número de identificación" required>
                            <button class="btn bg-indigo-dark text-white" type="submit">
                                <i class="bi bi-search"></i> Buscar
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Contenedor para los resultados de la búsqueda -->
            <div id="resultadoBusqueda" class="mt-0 mb-4 pb-4"></div>
        </div>
    </div>
</div>

<script>
    // Función para quitar tildes y poner en mayúsculas
    function limpiarYMayusculas(texto) {
        if (!texto) return '';
        return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toUpperCase();
    }

    document.getElementById('formBuscarCampista').addEventListener('submit', function(e) {
        e.preventDefault();
        const number_id = document.getElementById('number_id').value.trim();
        if (!number_id) return;

        const resultado = document.getElementById('resultadoBusqueda');
        resultado.innerHTML = '<div class="text-center my-3"><div class="spinner-border text-primary"></div></div>';

        fetch('components/changePassword/buscarCampista.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: 'number_id=' + encodeURIComponent(number_id)
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const fullName = limpiarYMayusculas(data.campista.full_name);
                    resultado.innerHTML = `
                <!-- Tarjeta con el nombre del campista -->
                <div class="card shadow mb-4">
                    <div class="card-body py-3 d-flex justify-content-center align-items-center">
                        <h4 class="text-indigo-dark fw-bold mb-0 text-center w-100">${fullName}</h4>
                    </div>
                </div>
                <!-- Tarjeta con los 4 cuadros de información -->
                <div class="card shadow mb-4">
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi bi-geo-alt-fill text-indigo-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block">Sede</small>
                                        <strong class="text-dark">${data.campista.headquarters}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi bi-laptop text-indigo-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block">Modalidad</small>
                                        <strong class="text-dark">${data.campista.mode}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi bi-code-slash text-indigo-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block">Bootcamp</small>
                                        <strong class="text-dark">${data.campista.bootcamp_name}</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center p-3 bg-light rounded">
                                    <div class="flex-shrink-0 me-3">
                                        <i class="bi bi-envelope-at text-indigo-dark fs-4"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block">Email Institucional</small>
                                        <strong class="text-primary">${data.campista.institutional_email}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Tarjeta con los campos de contraseña -->
                <div class="card shadow">
                    <div class="card-header bg-light border-0">
                        <h6 class="text-indigo-dark mb-0">
                            <i class="bi bi-key-fill me-2"></i>Cambiar Contraseña
                        </h6>
                    </div>
                    <div class="card-body">
                        <!-- NUEVO: Contenedor principal para asegurar el flujo vertical correcto -->
                        <div class="d-flex flex-column w-100">
                            <!-- Sección de campos de contraseña -->
                            <div class="row g-3 mb-2">
                                <!-- Primera contraseña -->
                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label fw-bold">Nueva contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="nuevaPassword" autocomplete="new-password" placeholder="Nueva contraseña">
                                        <button class="btn btn-outline-secondary" type="button" id="verPassword1">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <!-- Segunda contraseña -->
                                <div class="col-12 col-md-6 mb-3">
                                    <label class="form-label fw-bold">Confirmar contraseña</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control" id="confirmarPassword" autocomplete="new-password" placeholder="Confirmar contraseña">
                                        <button class="btn btn-outline-secondary" type="button" id="verPassword2">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- NUEVO: Botón en una fila completamente separada -->
                            <div class="row">
                                <div class="col-12 text-center">
                                    <button class="btn bg-indigo-dark text-white px-5" type="button" id="btnCambiarPassword" data-number-id="${number_id}">
                                        <i class="bi bi-check-circle me-2"></i>Cambiar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                    `;

                    // Implementar botones de ver contraseña
                    document.getElementById('verPassword1').addEventListener('click', function() {
                        const input = document.getElementById('nuevaPassword');
                        input.type = input.type === 'password' ? 'text' : 'password';
                        this.innerHTML = input.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
                    });

                    document.getElementById('verPassword2').addEventListener('click', function() {
                        const input = document.getElementById('confirmarPassword');
                        input.type = input.type === 'password' ? 'text' : 'password';
                        this.innerHTML = input.type === 'password' ? '<i class="bi bi-eye"></i>' : '<i class="bi bi-eye-slash"></i>';
                    });

                    // NUEVO: Funcionalidad para cambiar contraseña
                    document.getElementById('btnCambiarPassword').addEventListener('click', function() {
                        const number_id = this.getAttribute('data-number-id');
                        const nuevaPassword = document.getElementById('nuevaPassword').value.trim();
                        const confirmarPassword = document.getElementById('confirmarPassword').value.trim();

                        // Validaciones del lado del cliente
                        if (!nuevaPassword || !confirmarPassword) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Campos incompletos',
                                text: 'Por favor completa todos los campos de contraseña'
                            });
                            return;
                        }

                        if (nuevaPassword !== confirmarPassword) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Las contraseñas no coinciden'
                            });
                            return;
                        }

                        if (nuevaPassword.length < 8) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Contraseña muy corta',
                                text: 'La contraseña debe tener al menos 8 caracteres'
                            });
                            return;
                        }

                        // Confirmar el cambio
                        Swal.fire({
                            title: '¿Confirmar cambio de contraseña?',
                            text: `Se cambiará la contraseña para ${fullName}`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Sí, cambiar',
                            cancelButtonText: 'Cancelar'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // Mostrar loading
                                Swal.fire({
                                    title: 'Cambiando contraseña...',
                                    text: 'Por favor espera',
                                    allowOutsideClick: false,
                                    didOpen: () => {
                                        Swal.showLoading();
                                    }
                                });

                                // Enviar solicitud
                                const formData = new FormData();
                                formData.append('number_id', number_id);
                                formData.append('nueva_password', nuevaPassword);
                                formData.append('confirmar_password', confirmarPassword);

                                fetch('components/changePassword/cambiarPassword.php', {
                                    method: 'POST',
                                    body: formData
                                })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: '¡Éxito!',
                                            text: 'Contraseña actualizada correctamente',
                                            confirmButtonText: 'Aceptar'
                                        }).then(() => {
                                            // Limpiar campos
                                            document.getElementById('nuevaPassword').value = '';
                                            document.getElementById('confirmarPassword').value = '';
                                        });
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: data.message || 'Error al cambiar la contraseña'
                                        });
                                    }
                                })
                                .catch(error => {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: 'Error de conexión. Intenta de nuevo.'
                                    });
                                });
                            }
                        });
                    });

                } else {
                    resultado.innerHTML = `<div class="alert alert-danger text-center"><i class="bi bi-exclamation-triangle me-2"></i>${data.message}</div>`;
                }
            })
            .catch(() => {
                resultado.innerHTML = `<div class="alert alert-danger text-center">Error al buscar el campista. Intenta de nuevo.</div>`;
            });
    });
</script>