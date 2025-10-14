<script>
    const valorPorCategoriaCtx = document.getElementById('valorPorCategoriaChart');
    const chartData = {!! json_encode($valorPorCategoria) !!};

    new Chart(valorPorCategoriaCtx, {
        type: 'doughnut', // Gráfico de rosca, uma variação do de pizza
        data: {
            labels: chartData.map(row => row.nome),
            datasets: [{
                label: 'Valor de Custo',
                data: chartData.map(row => row.total_custo),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                hoverOffset: 4
            }]
        }
    });
</script>