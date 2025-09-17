<style>
    .controles-paginacion {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .btn-paginador {
        padding: 0.75rem 2rem;
        font-size: 1.1rem;
        border-radius: 0.5rem;
        border: none;
        background-color: #30336b;
        color: #fff;
        font-weight: 600;
        transition: background 0.2s, color 0.2s;
        cursor: pointer;
        box-shadow: 0 2px 8px 0 rgba(48, 51, 107, 0.08);
    }
    .btn-paginador:disabled {
        background-color: #30336b;
        color: #fff;
        opacity: 0.7;
        cursor: not-allowed;
    }
    .btn-paginador:not(:disabled):hover {
        background-color: #23255a;
        color: #fff;
    }
</style>

<div class="paginador">
    <div class="pagina" id="pagina1" style="display: block;">
        <?php include 'components/proyecciones/contadoresL1.php'; ?>
    </div>
    <div class="pagina" id="pagina2" style="display: none;">
        <?php include 'components/proyecciones/contadoresL2.php'; ?>
    </div>
    <div class="controles-paginacion mb-4">
        <button id="btnAnterior" class="btn-paginador" disabled>Lote 1</button>
        <button id="btnSiguiente" class="btn-paginador">Lote 2</button>
    </div>
</div>
<script>
    const pagina1 = document.getElementById('pagina1');
    const pagina2 = document.getElementById('pagina2');
    const btnAnterior = document.getElementById('btnAnterior');
    const btnSiguiente = document.getElementById('btnSiguiente');

    btnSiguiente.onclick = function() {
        pagina1.style.display = 'none';
        pagina2.style.display = 'block';
        btnAnterior.disabled = false;
        btnSiguiente.disabled = true;
    };

    btnAnterior.onclick = function() {
        pagina1.style.display = 'block';
        pagina2.style.display = 'none';
        btnAnterior.disabled = true;
        btnSiguiente.disabled = false;
    };
</script>