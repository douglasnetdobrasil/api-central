<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Produto; // Para ranking de produtos/serviços
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RelatorioOSController extends Controller
{
    /**
     * Dashboard de Métricas de Ordem de Serviço.
     */
    public function dashboard(Request $request)
    {
        // --- 1. FILTROS ---
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->toDateString());
        $clienteId = $request->input('cliente_id');
        $tecnicoId = $request->input('tecnico_id');

        // --- 2. QUERY BASE FILTRADA ---
        $queryBase = OrdemServico::whereBetween('created_at', [$dataInicio, $dataFim]);

        if ($clienteId) {
            $queryBase->where('cliente_id', $clienteId);
        }
        if ($tecnicoId) {
            $queryBase->where('tecnico_id', $tecnicoId);
        }
        
        // ==========================================================
        // CÁLCULO DAS MÉTRICAS
        // ==========================================================
        $tmr = $this->getTempoMedioReparo($queryBase);
        $volumeOSConcluidas = $this->getVolumeOSConcluidas($queryBase);
        $valorMedioOS = $this->getValorMedioOS($queryBase);
        
        // --- NOVOS CÁLCULOS ---
        $totalOSAbertas = (clone $queryBase)->count(); // Total de OS criadas no período
        
        // Status que indicam OS pendente (Ajuste conforme seus status)
        $statusPendentes = ['Aberta', 'Em Análise', 'Aguardando Aprovação', 'Aprovada', 'Em Andamento', 'Aguardando Peça']; 
        $totalOSPendentes = (clone $queryBase)->whereIn('status', $statusPendentes)->count();

        // --- NOVOS RANKINGS ---
        $topTecnicosProdutividade = $this->getTopTecnicosProdutividade($queryBase);
        $topClientesOS = $this->getTopClientesOS($queryBase); // Chama o novo método
        $topProdutosOS = $this->getTopProdutosOS($queryBase); // Chama o novo método
        $topServicosOS = $this->getTopServicosOS($queryBase); // Chama o novo método (Opcional)


        // --- DADOS PARA FILTROS ---
        $clientes = Cliente::orderBy('nome')->get(['id', 'nome']);
        $tecnicos = User::where('empresa_id', Auth::user()->empresa_id)->orderBy('name')->get(['id', 'name']);

        return view('admin.relatorios.os.dashboard', compact(
            'dataInicio', 'dataFim', 'clienteId', 'tecnicoId',
            'tmr', 'volumeOSConcluidas', 'valorMedioOS', 
            'totalOSAbertas', 'totalOSPendentes', // Novas KPIs
            'topTecnicosProdutividade', 'topClientesOS', 'topProdutosOS', 'topServicosOS', // Novos Rankings
            'clientes', 'tecnicos'
        ));
    }

    private function getTopClientesOS($queryBase)
    {
        return (clone $queryBase)
            ->select('cliente_id', DB::raw('count(*) as total_os'))
            ->groupBy('cliente_id')
            ->with('cliente') // Assuming relationship 'cliente' exists in OrdemServico model
            ->orderByDesc('total_os')
            ->take(5)
            ->get();
    }


    private function getTopProdutosOS($queryBase)
    {
        // Precisamos dos IDs das OSs filtradas pela query base
        $osIds = (clone $queryBase)->pluck('id');

        // Agora buscamos os produtos usados nessas OSs
        return DB::table('os_produtos')
            ->join('produtos', 'os_produtos.produto_id', '=', 'produtos.id')
            ->whereIn('os_produtos.ordem_servico_id', $osIds)
            // Agrupa por produto e soma a quantidade total usada
            ->select('produtos.nome as nome_produto', DB::raw('SUM(os_produtos.quantidade) as quantidade_total'))
            ->groupBy('os_produtos.produto_id', 'produtos.nome')
            ->orderByDesc('quantidade_total')
            ->take(5)
            ->get();
    }


    private function getTopServicosOS($queryBase)
    {
        // IDs das OSs filtradas
        $osIds = (clone $queryBase)->pluck('id');

        // Busca os serviços usados nessas OSs
        return DB::table('os_servicos')
            ->join('produtos', 'os_servicos.servico_id', '=', 'produtos.id') // Assumindo que serviços estão na tabela produtos com tipo 'servico'
            ->whereIn('os_servicos.ordem_servico_id', $osIds)
            ->select('produtos.nome as nome_servico', DB::raw('COUNT(os_servicos.id) as total_vezes')) // Conta quantas vezes o serviço foi listado
            ->groupBy('os_servicos.servico_id', 'produtos.nome')
            ->orderByDesc('total_vezes')
            ->take(5)
            ->get();
    }

    // --- MÉTODOS PRIVADOS PARA CÁLCULO DE KPIs ---

    private function getTempoMedioReparo($queryBase)
    {
        // Considera apenas OS Concluídas com data de entrada e conclusão
        $tempoMedioSegundos = (clone $queryBase)
            ->where('status', 'Concluída') // Ajuste o status conforme seu sistema
            ->whereNotNull('data_entrada')
            ->whereNotNull('data_conclusao')
            ->avg(DB::raw('TIMESTAMPDIFF(SECOND, data_entrada, data_conclusao)')); // Diferença em segundos

        if (!$tempoMedioSegundos) return 'N/A';

        // Converte para Dias e Horas para facilitar a leitura
        $dias = floor($tempoMedioSegundos / (3600 * 24));
        $horas = floor(($tempoMedioSegundos % (3600 * 24)) / 3600);

        return sprintf('%d d %02d h', $dias, $horas);
    }

    private function getVolumeOSConcluidas($queryBase)
    {
        return (clone $queryBase)->where('status', 'Concluída')->count();
    }

    private function getValorMedioOS($queryBase)
    {
         $valorMedio = (clone $queryBase)
             ->where('status', 'Concluída')
             ->avg('valor_total');
             
         return $valorMedio ? number_format($valorMedio, 2, ',', '.') : '0,00';
    }

     private function getTopTecnicosProdutividade($queryBase)
    {
        return (clone $queryBase)
            ->select('tecnico_id', DB::raw('count(*) as total_concluidas'))
            ->where('status', 'Concluída')
            ->whereNotNull('tecnico_id')
            ->groupBy('tecnico_id')
            ->with('tecnico') // Assuming relationship 'tecnico' exists in OrdemServico model
            ->orderByDesc('total_concluidas')
            ->take(5)
            ->get();
    }
    
     /**
     * Exibe a lista detalhada de OS para um cliente ou técnico específico.
     * (Similar ao RelatorioSuporteController, a ser implementado)
     */
    public function detalhe(Request $request)
    {
        $tipo = $request->input('tipo'); // 'cliente' ou 'tecnico'
        $id = $request->input('id'); 
        $dataInicio = $request->input('data_inicio');
        $dataFim = $request->input('data_fim');

        // Validação básica dos parâmetros
        if (empty($tipo) || empty($id) || empty($dataInicio) || empty($dataFim)) {
             return redirect()->route('admin.relatorios.os.dashboard')->with('error', 'Filtros insuficientes para o detalhe.');
        }

        // Query base para buscar OS, incluindo relacionamentos para a tabela
        $query = OrdemServico::with(['cliente', 'tecnico', 'clienteEquipamento']) // Adicionado clienteEquipamento
            ->whereBetween('created_at', [$dataInicio, $dataFim])
            ->latest('created_at'); // Ordena pelas mais recentes

        // Aplica o filtro principal (cliente ou técnico)
        if ($tipo === 'cliente') {
            $entidade = Cliente::find($id);
            $query->where('cliente_id', $id);
            $titulo = "Detalhe: OS do Cliente - " . ($entidade->nome ?? 'ID: '.$id);
        } elseif ($tipo === 'tecnico') {
            $entidade = User::find($id);
            $query->where('tecnico_id', $id);
            $titulo = "Detalhe: OS do Técnico - " . ($entidade->name ?? 'ID: '.$id);
        } else {
            return redirect()->route('admin.relatorios.os.dashboard')->with('error', 'Filtro de detalhe inválido.');
        }

        // Pagina os resultados
        $ordensServico = $query->paginate(20)->withQueryString(); // Mantém os filtros na URL da paginação

        // Retorna a view com os dados
        return view('admin.relatorios.os.detalhe', compact('ordensServico', 'titulo')); // Passa a variável correta $ordensServico
    }

}