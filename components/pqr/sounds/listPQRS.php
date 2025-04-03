<!-- Sección de Filtro y Búsqueda -->
<?php include 'components/pqr/filterPQRS.php' ?>
<div class="card card-radius mb-4 p-3">
    <form method="GET" action="" class="row g-3 align-items-center">
        

    </form>
</div>



    <?php
    // Generar los modales después de la tabla
    foreach ($pqrs as $fila) {
        $id_pqr_actual = $fila['id'];
        include 'components/modals/detalle_pqr.php';
        include('components/modals/editar_pqr.php');
    }
    ?>

    
</div>