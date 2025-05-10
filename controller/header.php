<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
require_once __DIR__ . '/../components/modals/cohortes.php';
?>

<nav class="navbar navbar-expand-lg bg-body-tertiary fixed-top">
    <div class="container-fluid">
        <button class="btn btn-tertiary mr-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptionsLabel">
            <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand" href="#"><img src="img/uttInnova.png" alt="logo" width="120px"></a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="main.php">Inicio</a>
                </li>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <!-- <li class="nav-item">
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#cohortModal">
                            Cohortes
                        </a>
                    </li> -->
                <?php endif; ?>

                <?php if ($rol === 'Administrador' || $rol === 'Empleabilidad' || $rol === 'Control maestro'): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="encuestas.php">Empleabilidad</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="codigosQR.php">Generar QR</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="asistencias.php">Asistencia Talleres</a>
                    </li>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <!-- Sistema PQRS -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPQRS" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            PQRS
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownPQRS">
                            <li><a class="dropdown-item" href="seguimiento_pqr.php">Seguimiento PQRS</a></li>

                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="profile.php">Perfil</a>
                    </li>
                <?php endif; ?>
                <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownPQRS" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Informes
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownPQRS">
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAll.php?action=export', 'semanal')">Informe semanal</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportHours.php?action=export', 'asistencia')">Informe de asistencia</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportHoursEL.php?action=export', 'asistencia')">Informe de asistencia LE</a></li>
                            <li><a class="dropdown-item" href="#" onclick="descargarInforme('components/infoWeek/exportAbsence.php?action=export', 'ausencias')">Registros de ausencia</a></li>

                        </ul>
                    </li>
                <?php endif; ?>

            </ul>

            <!-- Mostrar el nombre del usuario logueado -->
            <?php if ($rol === 'Administrador' || $rol === 'Control maestro'): ?>
                <?php include 'components/pqr/pqrButton.php'; ?>
            <?php endif; ?>

            <div class="pqr-notification-container me-4">
    <div class="dropdown">
        <button id="previousStudentsDropdown" type="button" class="btn bg-warning position-relative" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fa-solid fa-user-graduate fa-shake"></i>

            <!-- Badge con contador -->
            <span id="totalCertificados" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-magenta-dark">
                <b><?php echo isset($totalConCertificacion) ? $totalConCertificacion : 0; ?></b>
            </span>

            <!-- Spinner de carga -->
            <div id="spinnerCertificados" class="spinner-border spinner-border-sm text-light position-absolute top-0 start-50 translate-middle d-none" role="status" style="z-index: 2;">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </button>

        <div id="certificadosDropdown" class="dropdown-menu pqr-dropdown">
            <h6 class="dropdown-header">Estudiantes certificados de otras regiones</h6>
            <ul id="certificadosList" class="pqr-list text-uppercase mb-2">
            <li class="dropdown-item text-center">Cargando...</li>
            </ul>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item text-center" href="#">Ver todos</a>
        </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
        const totalCertificados = document.getElementById('totalCertificados');
        const certificadosList = document.getElementById('certificadosList');

        const fetchCertificados = async () => {
            try {
            const response = await fetch('components/registrationsContact/previous_students_button.php');
            const data = await response.json();

            if (data.status === 'success' && data.data) {
                totalCertificados.innerHTML = `<b>${data.data.total_certificados}</b>`;

                certificadosList.innerHTML = '';
                if (data.data.estudiantes.length > 0) {
                data.data.estudiantes.forEach(est => {
                    const item = document.createElement('li');
                    item.className = 'dropdown-item';
                    item.textContent = `Documento: ${est.numero_documento}, Nombre: ${est.first_name} ${est.second_name} ${est.first_last} ${est.second_last}, Región: ${est.region}`;
                    certificadosList.appendChild(item);
                });
                } else {
                certificadosList.innerHTML = '<li class="dropdown-item text-center">No se encontraron estudiantes</li>';
                }
            } else {
                certificadosList.innerHTML = '<li class="dropdown-item text-center text-danger">Error al cargar datos</li>';
                console.error('Error en los datos:', data);
            }
            } catch (err) {
            certificadosList.innerHTML = '<li class="dropdown-item text-center text-danger">Error de conexión</li>';
            console.error('Error en la solicitud:', err);
            }
        };

        setInterval(fetchCertificados, 5000); // Cada 5 segundos
        });
    </script>

</script>

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="<?php echo htmlspecialchars($infoUsuario['foto']); ?>" alt="Perfil" class="rounded-circle" width="40" height="40">
                    <?php echo htmlspecialchars($infoUsuario['nombre']); ?>
                    <div class="spinner-grow spinner-grow-sm" role="status" style="color:#00976a">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <button type="button" class="btn" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Usuario: <?php echo htmlspecialchars($infoUsuario['rol']); ?>">
                        <i class="bi bi-info-circle-fill colorVerde" style="color: #00976a;"></i>
                    </button>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                    <li><a class="dropdown-item" href="profile.php">Perfil</a></li>
                    <li><a class="dropdown-item" href="close.php">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>
<script>
    function descargarInforme(url, tipo) {
        // Mostrar SweetAlert sin timer
        Swal.fire({
            title: 'Generando informe...',
            html: `<div class="text-center">
                <div class="spinner-border text-success" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2">Preparando la descarga del informe de ${tipo}</p>
               </div>`,
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                // Iniciar la descarga
                fetch(url)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // Crear URL del blob
                        const blobUrl = window.URL.createObjectURL(blob);
                        // Crear enlace temporal
                        const a = document.createElement('a');
                        a.href = blobUrl;
                        a.download = `informe_${tipo}_${new Date().toISOString().split('T')[0]}.xlsx`;
                        // Simular clic
                        document.body.appendChild(a);
                        a.click();
                        // Limpiar
                        window.URL.revokeObjectURL(blobUrl);
                        document.body.removeChild(a);

                        // Mostrar mensaje de éxito solo después de completar la descarga
                        Swal.fire({
                            icon: 'success',
                            title: '¡Descarga completada!',
                            text: `El informe de ${tipo} se ha descargado correctamente`,
                            showConfirmButton: true,
                            confirmButtonColor: '#30336b'
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al generar el informe',
                            confirmButtonColor: '#dc3545'
                        });
                    });
            }
        });
    }


    // Inicializa todos los tooltips en la página
    $(function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
</script>
