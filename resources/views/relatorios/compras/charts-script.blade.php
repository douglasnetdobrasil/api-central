<script>
    // --- Gráfico de Compras por Fornecedor (Pizza) ---
    const fornecedorCtx = document.getElementById('comprasPorFornecedorChart');
    const fornecedorData = {!! json_encode($comprasPorFornecedor) !!};

    new Chart(fornecedorCtx, {
        type: 'pie',
        data: {
            labels: fornecedorData.map(row => row.razao_social),
            datasets: [{
                label: 'Total Comprado R$',
                data: fornecedorData.map(row => row.total_comprado),
                backgroundColor: [
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)',
                    'rgba(99, 255, 132, 0.7)'
                ],
                hoverOffset: 4
            }]
        }
    });

    // --- Gráfico de Produtos Mais Comprados (Barras) ---
    const produtosCtx = document.getElementById('produtosCompradosChart');
    const produtosData = {!! json_encode($produtosMaisComprados) !!};

    new Chart(produtosCtx, {
        type: 'bar',
        data: {
            labels: produtosData.map(row => row.nome),
            datasets: [{
                label: 'Valor Total Comprado R$',
                data: produtosData.map(row => row.total_valor),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
            }]
        },
        options: {
            indexAxis: 'y', // Deixa as barras na horizontal
            scales: {
                x: { beginAtZero: true }
            }
        }
    });
</script>