<style>
    .lote1-total-container {
        width: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 180px;
        margin: 0 auto;
    }
    .lote2-title {
        font-size: 20px;
        font-weight: bold;
        color: #6610f2;
        margin-bottom: 10px;
        letter-spacing: 1px;
        text-shadow: 0 2px 8px rgba(102,16,242,0.15);
        text-transform: uppercase;
    }
    .lote2-valor {
        font-size: 48px;
        font-weight: bold;
        color: #fff;
        background: linear-gradient(90deg, #6610f2 60%, #20c997 100%);
        padding: 18px 38px;
        border-radius: 18px;
        box-shadow: 0 4px 24px rgba(102,16,242,0.12);
        margin-bottom: 10px;
        display: flex;
        align-items: baseline;
        gap: 18px;
    }
    .lote2-porcentaje {
        font-size: 28px;
        font-weight: bold;
        color: #fff;
        text-shadow: 0 2px 8px rgba(32,201,151,0.12);
    }
    .lote2-desc {
        font-size: 15px;
        color: #666;
        margin-top: 8px;
        font-weight: 500;
        letter-spacing: 0.5px;
    }
</style>

<div class="lote1-total-container">
    <div class="lote2-valor" id="lote2Valor">
        0 <span class="lote2-porcentaje" id="lote2Porcentaje">0%</span>
    </div>
    <div class="lote2-desc">Usuarios registrados en el Lote 2</div>
</div>

<script>
    async function cargarLote1() {
        try {
            // Obtener el total general de usuarios
            const totalResp = await fetch('components/graphics/registerLotesQuery.php?json=total');
            const totalData = await totalResp.json();
            const totalGeneral = totalData.total;

            // Obtener los datos de lote 1
            const respuesta = await fetch('components/graphics/registerLotesQuery.php?json=lote2');
            const datos = await respuesta.json();
            const totalLote2 = datos.data[0];

            // Calcular porcentaje
            let porcentaje = totalGeneral > 0 ? ((totalLote2 / totalGeneral) * 100).toFixed(1) : 0;

            // Mostrar datos
            document.getElementById('lote2Valor').childNodes[0].nodeValue = totalLote2 + ' ';
            document.getElementById('lote2Porcentaje').textContent = porcentaje + '%';
        } catch (error) {
            document.getElementById('lote2Valor').innerHTML = 'Error';
            document.getElementById('lote2Porcentaje').textContent = '';
        }
    }
    document.addEventListener('DOMContentLoaded', cargarLote1);
</script>