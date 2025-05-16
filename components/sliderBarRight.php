<?php
$rol = $infoUsuario['rol']; // Obtener el rol del usuario
?>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="offcanvasRightLabel"><i class="bi bi-boxes"></i> SIVP Opciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <div class="container-fluid">
            <fieldset class="checkbox-group">
                <legend class="checkbox-group-legend">
                </legend>
                <!-- Aquí puedes agregar los elementos del menú derecho similar al izquierdo -->
                <div class="checkbox" data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-title="Ejemplo">
                    <label class="checkbox-wrapper">
                        <span class="checkbox-tile">
                            <span class="checkbox-icon">
                                <i class="bi bi-gear-fill icono"></i>
                            </span>
                            <span class="checkbox-label">Configuración</span>
                        </span>
                    </label>
                </div>
                <!-- Agrega más elementos según necesites -->
            </fieldset>
        </div>
    </div>
    <?php include("controller/footer.php"); ?>
</div>