<script>
    // Verifica se a biblioteca Chart.js está carregada
    if (typeof Chart !== 'undefined') {

        // ==========================================================
        // --- Gráfico 1: Produtividade por Técnico (OS Concluídas) ---
        // ==========================================================
        const produtividadeCtx = document.getElementById('produtividadeTecnicoChart');
        // A variável $topTecnicosProdutividade já contém os dados do Controller
        const produtividadeData = {!! json_encode($topTecnicosProdutividade) !!};

        // Mapeia os dados para o formato do Chart.js
        const nomesTecnicos = produtividadeData.map(row => row.tecnico ? row.tecnico.name : 'Não Identificado');
        const totalOSConcluidas = produtividadeData.map(row => row.total_concluidas);

        if (produtividadeCtx) { // Verifica se o elemento canvas existe
            new Chart(produtividadeCtx, {
                type: 'bar', // Gráfico de Barras
                data: {
                    labels: nomesTecnicos,
                    datasets: [{
                        label: 'Total de OS Concluídas',
                        data: totalOSConcluidas,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)', // Cor Verde-água (Teal)
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    indexAxis: 'y', // Barras na horizontal para melhor leitura dos nomes
                    responsive: true,
                    plugins: {
                        legend: { display: false } // Opcional: Oculta a legenda se só tiver 1 dataset
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'Número de OS Concluídas' }
                        }
                    }
                }
            });
        } else {
            console.error("Elemento canvas 'produtividadeTecnicoChart' não encontrado.");
        }
        
        // ==========================================================
        // --- Gráfico 2: Faturamento por Técnico (A IMPLEMENTAR) ---
        // ==========================================================
        // const faturamentoCtx = document.getElementById('faturamentoTecnicoChart');
        // Se faturamentoCtx { ... } -> Lógica similar, mas buscando a soma de valor_total por técnico


    } else {
        console.error("Chart.js não está carregado. Verifique a inclusão no layout.");
    }
</script>