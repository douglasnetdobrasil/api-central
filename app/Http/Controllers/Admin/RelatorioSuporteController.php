<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuporteChamado;
use App\Models\ClienteEquipamento;
use App\Models\Cliente;
use App\Models\User; // <-- Import necessário para o filtro de técnico
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth; // <-- Import necessário para pegar a empresa
use Carbon\Carbon;

class RelatorioSuporteController extends Controller
{
    /**
     * Dashboard de Métricas de Suporte para Supervisores.
     */
    public function dashboard(Request $request)
    {
        // --- 1. FILTROS DE PERÍODO (DATA INÍCIO/FIM) E OPÇÕES ---
        // Define as datas padrão como o início e fim do mês atual se não forem enviadas
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->toDateString());
        // Obtém os IDs dos filtros opcionais
        $clienteId = $request->input('cliente_id');
        $tecnicoId = $request->input('tecnico_id');

        // --- 2. QUERY BASE FILTRADA PELOS FILTROS DA REQUEST ---
        // Começa a query buscando chamados dentro do período de datas
        $queryBase = SuporteChamado::whereBetween('created_at', [$dataInicio, $dataFim]);

        // --- FILTROS CONDICIONAIS (Aplicados se os IDs foram enviados) ---
        if ($clienteId) {
            $queryBase->where('cliente_id', $clienteId); // Filtra por cliente específico
        }
        if ($tecnicoId) {
            $queryBase->where('tecnico_atribuido_id', $tecnicoId); // Filtra por técnico específico
        }
        
        // ==========================================================
        // CÁLCULOS DAS MÉTRICAS (Usando os métodos privados e a query base já filtrada)
        // ==========================================================
        // Cada chamada clona a $queryBase para não interferir nos outros cálculos
        $tecnicosAtivos = $this->getTecnicosMaisAtivos($queryBase);
        $topClientes = $this->getTopClientes($queryBase);
        $equipamentosProblematicos = $this->getEquipamentosMaisProblematicos($queryBase);
        $tma = $this->getTempoMedioAtendimento($queryBase);
        $taxaResolucaoOnline = $this->getTaxaResolucaoOnline($queryBase); // Calcula a taxa

        // --- CÁLCULO: TOTAL CHAMADOS ABERTOS NO PERÍODO ---
        $queryTotal = clone $queryBase;
        $totalChamadosAbertos = $queryTotal->count();

        // --- CÁLCULO: CHAMADOS PENDENTES ---
        $queryPendentes = clone $queryBase; 
        $statusNaoFinalizados = ['Aberto', 'Em Atendimento', 'Aguardando Cliente', 'Aguardando Atendimento'];
        $totalPendentes = $queryPendentes->whereIn('status', $statusNaoFinalizados)->count(); 
        
        // ==========================================================
        // DADOS PARA OS <SELECT> DOS FILTROS NA VIEW
        // ==========================================================
        // Busca todos os clientes para o dropdown de filtro
        $clientes = Cliente::orderBy('nome')->get(['id', 'nome']);
        // Busca todos os usuários (técnicos) da empresa logada para o dropdown de filtro
        $tecnicos = User::where('empresa_id', Auth::user()->empresa_id)->orderBy('name')->get(['id', 'name']);

        // ==========================================================
        // RETORNO PARA A VIEW (Enviando todas as variáveis necessárias)
        // ==========================================================
        // O compact() cria um array associativo com os nomes das variáveis e seus valores
        return view('admin.relatorios.suporte.dashboard', compact(
            'dataInicio',               // Variável para o campo de data início
            'dataFim',                  // Variável para o campo de data fim
            'clienteId',                // ID do cliente selecionado (ou null)
            'tecnicoId',                // ID do técnico selecionado (ou null)
            'tecnicosAtivos',           // Resultados para o ranking de técnicos
            'topClientes',              // Resultados para o ranking de clientes
            'equipamentosProblematicos',// Resultados para o ranking de equipamentos
            'tma',                      // Resultado do cálculo do TMA
            'taxaResolucaoOnline',      // Resultado do cálculo da taxa
            'totalChamadosAbertos',     // Resultado do Total de Chamados Abertos
            'totalPendentes',           // Resultado do Total de Pendentes
            'clientes',                 // Coleção de clientes para o <select>
            'tecnicos'
        ));
    }


    private function getTaxaResolucaoOnline($queryBase)
    {
        // 1. Clona a query base para não afetar outros cálculos
        $query = clone $queryBase;

        // 2. Conta o total de chamados criados no período (com os filtros aplicados)
        $totalChamadosPeriodo = $query->count();

        if ($totalChamadosPeriodo === 0) {
            return 0; // Evita divisão por zero
        }

        // 3. Conta quantos desses chamados foram resolvidos online
        $resolvidosOnline = $query->where('status', 'Resolvido Online')->count();

        // 4. Calcula a taxa percentual
        $taxa = ($resolvidosOnline / $totalChamadosPeriodo) * 100;

        // Retorna formatado com uma casa decimal
        return number_format($taxa, 1); 
    }


    // --- MÉTODOS DE CÁLCULO DAS MÉTRICAS ---

    private function getTecnicosMaisAtivos($queryBase)
    {
        // Conta chamados atribuídos e ordena
        return (clone $queryBase)
            ->select('tecnico_atribuido_id', DB::raw('count(*) as total_chamados'))
            ->whereNotNull('tecnico_atribuido_id')
            ->groupBy('tecnico_atribuido_id')
            ->with('tecnico') 
            ->orderByDesc('total_chamados')
            ->take(5)
            ->get();
    }

    private function getTopClientes($queryBase)
    {
        // Conta chamados por cliente e ordena
        return (clone $queryBase)
            ->select('cliente_id', DB::raw('count(*) as total_chamados'))
            ->groupBy('cliente_id')
            ->with('cliente') 
            ->orderByDesc('total_chamados')
            ->take(5)
            ->get();
    }

    private function getEquipamentosMaisProblematicos($queryBase)
    {
        // Conta chamados por equipamento
        return (clone $queryBase)
            ->select('cliente_equipamento_id', DB::raw('count(*) as total_chamados'))
            ->whereNotNull('cliente_equipamento_id')
            ->groupBy('cliente_equipamento_id')
            ->with('equipamento') 
            ->orderByDesc('total_chamados')
            ->take(5)
            ->get();
    }

    /**
     * Exibe a lista detalhada de chamados para um cliente ou técnico específico.
     */
    public function detalhe(Request $request)
    {
        $tipo = $request->input('tipo'); // 'cliente' ou 'tecnico'
        $id = $request->input('id'); 
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        // Validação básica
        if (empty($tipo) || empty($id) || empty($dataInicio) || empty($dataFim)) {
             return redirect()->route('admin.relatorios.suporte.dashboard')->with('error', 'Filtros insuficientes para o detalhe.');
        }

        $query = SuporteChamado::with(['cliente', 'tecnico', 'equipamento'])
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->latest();

        if ($tipo === 'cliente') {
            $entidade = Cliente::find($id);
            $query->where('cliente_id', $id);
            $titulo = "Detalhe: Chamados do Cliente - " . ($entidade->nome ?? 'N/A');
        } elseif ($tipo === 'tecnico') {
            $entidade = User::find($id);
            $query->where('tecnico_atribuido_id', $id);
            $titulo = "Detalhe: Chamados do Técnico - " . ($entidade->name ?? 'N/A');
        } else {
            return redirect()->route('admin.relatorios.suporte.dashboard')->with('error', 'Filtro de detalhe inválido.');
        }

        $chamados = $query->paginate(20)->withQueryString(); // Mantém os filtros na paginação

        return view('admin.relatorios.suporte.detalhe', compact('chamados', 'titulo'));
    }

    private function getTempoMedioAtendimento($queryBase)
    {
        // Calcula a média da diferença entre a data de resolução e a data de criação
        $tempoMedioEmSegundos = (clone $queryBase)
            ->whereIn('status', ['Resolvido Online', 'Fechado', 'Convertido em OS'])
            ->whereNotNull('data_resolucao')
            ->avg(DB::raw('UNIX_TIMESTAMP(data_resolucao) - UNIX_TIMESTAMP(created_at)'));

        if (!$tempoMedioEmSegundos) {
            return 'N/A';
        }

        // Converte segundos para formato HH:MM:SS
        $horas = floor($tempoMedioEmSegundos / 3600);
        $minutos = floor(($tempoMedioEmSegundos % 3600) / 60);
        $segundos = $tempoMedioEmSegundos % 60;

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
    }
}