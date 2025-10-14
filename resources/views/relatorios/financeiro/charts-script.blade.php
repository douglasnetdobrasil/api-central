<script>
    const fluxoCaixaCtx = document.getElementById('fluxoCaixaChart');

    // Pegamos os dados do PHP
    const entradasData = {!! json_encode($entradasPorDia) !!};
    const saidasData = {!! json_encode($saidasPorDia) !!};

    // Precisamos de um array com todas as datas únicas para o eixo X
    const allDates = [...new Set([...entradasData.map(e => e.data), ...saidasData.map(s => s.data)])].sort();

    const formattedLabels = allDates.map(date => new Date(date).toLocaleDateString('pt-BR', { timeZone: 'UTC' }));

    const entradasPorData = allDates.map(date => {
        const found = entradasData.find(e => e.data === date);
        return found ? found.total : 0;
    });

    const saidasPorData = allDates.map(date => {
        const found = saidasData.find(s => s.data === date);
        return found ? found.total : 0;
    });

    new Chart(fluxoCaixaCtx, {
        type: 'bar',
        data: {
            labels: formattedLabels,
            datasets: [
                {
                    label: 'Entradas R$',
                    data: entradasPorData,
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                },
                {
                    label: 'Saídas R$',
                    data: saidasPorData,
                    backgroundColor: 'rgba(255, 99, 132, 0.6)',
                }
            ]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
</script>