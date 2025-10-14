<script>
    // --- Gráfico de Vendas por Dia (Gráfico de Linha) ---
    const vendasPorDiaCtx = document.getElementById('vendasPorDiaChart');
    const vendasPorDiaData = {!! json_encode($vendasPorDia) !!};

    new Chart(vendasPorDiaCtx, {
        type: 'line',
        data: {
            labels: vendasPorDiaData.map(row => new Date(row.data).toLocaleDateString('pt-BR', {timeZone: 'UTC'})),
            datasets: [{
                label: 'Total Vendido R$',
                data: vendasPorDiaData.map(row => row.total),
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1
            }]
        }
    });

    // --- Gráfico de Produtos Mais Vendidos (Gráfico de Barras) ---
    const produtosCtx = document.getElementById('produtosChart');
    const produtosData = {!! json_encode($produtosMaisVendidos) !!};

    new Chart(produtosCtx, {
        type: 'bar',
        data: {
            labels: produtosData.map(row => row.nome),
            datasets: [{
                label: 'Quantidade Vendida',
                data: produtosData.map(row => row.total_vendido),
                backgroundColor: 'rgba(54, 162, 235, 0.6)'
            }]
        },
        options: {
            indexAxis: 'y', // Deixa as barras na horizontal para melhor leitura
        }
    });
</script>