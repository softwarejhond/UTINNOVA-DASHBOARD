
    <script src="https://cdn.jsdelivr.net/npm/echarts/dist/echarts.min.js"></script>
    <style>
        #grafica { width: 100%; height: 200px; }
    </style>


<div id="graficaVirtuales"></div>

<script>
    async function cargarDatos() {
        try {
            const respuesta = await fetch('components/graphics/registerVirtualsQuery.php');
            const datos = await respuesta.json();

            if (!datos.labels || !datos.data) {
                throw new Error('Datos incorrectos');
            }

            const chart = echarts.init(document.getElementById('graficaVirtuales'));

            const opciones = {
                title: {
                    text: 'Inscritos vs Matriculados Virtuales',
                    left: 'center'
                },
                tooltip: {
                    trigger: 'item',
                    formatter: '{b}: {c} registros ({d}%)'
                },
                series: [{
                    type: 'pie',
                    radius: '60%',
                    data: datos.labels.map((label, i) => ({
                        name: label,
                        value: datos.data[i]
                    })),
                    label: {
                        show: true,
                        formatter: '{b}: {c} ({d}%)',
                        fontSize: 14,
                        fontWeight: 'bold'
                    }
                }]
            };

            chart.setOption(opciones);
        } catch (error) {
            console.error('Error al cargar datos:', error);
        }
    }

    cargarDatos();
</script>

