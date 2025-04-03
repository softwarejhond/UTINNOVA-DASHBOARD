<!-- Sección de Filtro y Búsqueda -->
<?php include 'components/pqr/filterPQRS.php' ?>

    <?php
    // Generar los modales después de la tabla
    foreach ($pqrs as $fila) {
        $id_pqr_actual = $fila['id'];
        include 'components/modals/detalle_pqr.php';
        include('components/modals/editar_pqr.php');
    }
    ?>

    
</div>