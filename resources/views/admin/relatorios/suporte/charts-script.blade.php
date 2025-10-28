<script>
    // Verifica se a biblioteca Chart.js está carregada antes de usar
    if (typeof Chart === 'undefined') {
        console.error("Chart.js não está carregado. Verifique a inclusão no layout.");
    }

    // ==========================================================
    // --- Gráfico 1: Top Técnicos Mais Ativos (Barras Horizontais) ---
    // ==========================================================
    const tecnicosCtx = document.getElementById('tecnicosAtivosChart');
    const tecnicosData = {!! json_encode($tecnicosAtivos) !!};
    
    // Mapeia os dados: o nome do técnico está no relacionamento 'tecnico'
    const nomesTecnicos = tecnicosData.map(row => row.tecnico ? row.tecnico.name : 'Não Atribuído');
    const totalChamados = tecnicosData.map(row => row.total_chamados);

    new Chart(tecnicosCtx, {
        type: 'bar',
        data: {
            labels: nomesTecnicos,
            datasets: [{
                label: 'Total de Chamados Atribuídos',
                data: totalChamados,
                backgroundColor: 'rgba(54, 162, 235, 0.7)', // Azul
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            indexAxis: 'y', // Barras na horizontal
            responsive: true,
            scales: {
                x: {
                    beginAtZero: true,
                    title: { display: true, text: 'Número de Chamados' }
                }
            }
        }
    });

    // ==========================================================
    // --- Gráfico 2: Equipamentos Mais Problemáticos (Pizza) ---
    // ==========================================================
    const equipamentosCtx = document.getElementById('equipamentosProblematicosChart');
    const equipamentosData = {!! json_encode($equipamentosProblematicos) !!};

    const nomesEquipamentos = equipamentosData.map(row => 
        row.equipamento ? row.equipamento.descricao.substring(0, 30) + (row.equipamento.descricao.length > 30 ? '...' : '') : 'Não Informado'
    );

    new Chart(equipamentosCtx, {
        type: 'pie',
        data: {
            labels: nomesEquipamentos,
            datasets: [{
                label: 'Total de Chamados',
                data: equipamentosData.map(row => row.total_chamados),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)', // Vermelho
                    'rgba(255, 159, 64, 0.7)', // Laranja
                    'rgba(255, 205, 86, 0.7)', // Amarelo
                    'rgba(75, 192, 192, 0.7)', // Verde
                    'rgba(153, 102, 255, 0.7)', // Roxo
                ],
                hoverOffset: 4
            }]
        },
        options: {
            responsive: true,
        }
    });
</script>