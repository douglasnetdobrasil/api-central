<?php

namespace App\Livewire\Financeiro;

use App\Models\OrdemServico;
use App\Models\Pedido;
use App\Models\Orcamento;
use App\Models\FormaPagamento;
use App\Services\OrdemServicoFaturamentoService;
use App\Services\PedidoFaturamentoService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Exception;
use Livewire\WithPagination;
use Livewire\Attributes\Layout; // <-- 1. ADICIONE ESTA LINHA

#[Layout('layouts.app')] // <-- 2. ADICIONE ESTA LINHA (Assumindo que seu layout é 'layouts.app')
class CentralFaturamento extends Component
{
    use WithPagination;

    // 1. KPIs (O Dinheiro na Mesa)
    public $kpiEmAndamento = 0;
    public $kpiProntoParaFaturar = 0;
    public $kpiPropostas = 0;

    // 2. Abas
    public $abaAtual = 'pronto'; // 'pronto', 'andamento', 'propostas'

    protected $listeners = ['faturarRapido'];

    // Define o tema do paginador para o Tailwind
    protected $paginationTheme = 'tailwind';

    /**
     * Fatura uma Ordem de Serviço (chamado pelo include)
     */
    public function faturarRapido(int $osId, OrdemServicoFaturamentoService $osFaturamentoService)
    {
        $os = OrdemServico::find($osId);
        
        if ($os->status !== 'Concluida' || $os->venda_id) {
            session()->flash('error', "A OS #{$os->id} não está apta para faturamento rápido.");
            return;
        }

        $formaPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)
                                        ->where('tipo', 'a_vista')
                                        ->first();
        if (!$formaPagamento) {
            session()->flash('error', "Não foi encontrada uma forma de pagamento 'à vista' padrão.");
            return;
        }

        try {
            $dadosPagamento = ['data_vencimento' => now()->format('Y-m-d')];
            $venda = $osFaturamentoService->faturar($os, $formaPagamento, $dadosPagamento);
            
            session()->flash('success', "OS #{$os->id} faturada para a Venda #{$venda->id}.");
        } catch (Exception $e) {
            session()->flash('error', 'Erro no faturamento: ' . $e->getMessage());
        }
    }

    /**
     * Fatura um Pedido de Venda
     */
    public function faturarPedido(int $pedidoId, PedidoFaturamentoService $pedidoFaturamentoService)
    {
        $pedido = Pedido::find($pedidoId);
        
        if ($pedido->status !== 'Aprovado' || $pedido->venda_id) {
            session()->flash('error', "O Pedido #{$pedido->id} não está apto para faturamento.");
            return;
        }

        $formaPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)
                                        ->where('tipo', 'a_vista')
                                        ->first();
        if (!$formaPagamento) {
            session()->flash('error', "Não foi encontrada uma forma de pagamento 'à vista' padrão.");
            return;
        }
        
        try {
            session()->flash('error', 'O Faturamento de Pedido (PedidoFaturamentoService) ainda precisa ser implementado.');
        } catch (Exception $e) {
            session()->flash('error', 'Erro no faturamento: ' . $e->getMessage());
        }
    }

    public function faturarTodasOS(OrdemServicoFaturamentoService $osFaturamentoService)
    {
        // Busca a forma de pagamento padrão uma única vez
        $formaPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)
                                        ->where('tipo', 'a_vista')
                                        ->first();
        if (!$formaPagamento) {
            session()->flash('error', "Não foi encontrada uma forma de pagamento 'à vista' padrão para faturamento em lote.");
            return;
        }

        // Busca todas as OS que atendem aos critérios
        $osParaFaturar = OrdemServico::where('status', 'Concluida') // Ou 'Concluída' se for o caso
                                     ->whereNull('venda_id')
                                     ->get();

        if ($osParaFaturar->isEmpty()) {
            session()->flash('info', 'Nenhuma Ordem de Serviço pendente para faturar em lote.');
            return;
        }

        $faturadasComSucesso = 0;
        $erros = [];
        $dadosPagamento = ['data_vencimento' => now()->format('Y-m-d')];

        foreach ($osParaFaturar as $os) {
            try {
                // Chama o serviço para cada OS
                $osFaturamentoService->faturar($os, $formaPagamento, $dadosPagamento);
                $faturadasComSucesso++;
            } catch (Exception $e) {
                // Guarda a mensagem de erro se alguma OS falhar
                $erros[] = "OS #{$os->id}: " . $e->getMessage();
            }
        }

        // Monta a mensagem final para o usuário
        $mensagem = "Faturamento em lote concluído: {$faturadasComSucesso} OS faturadas com sucesso.";
        if (!empty($erros)) {
            $mensagem .= "\n Ocorreram erros em " . count($erros) . " OS:\n - " . implode("\n - ", $erros);
            session()->flash('error', nl2br($mensagem)); // Usa nl2br para quebras de linha no flash
        } else {
            session()->flash('success', $mensagem);
        }

        // A re-renderização do Livewire atualizará a tabela
    }

    /**
     * Método para resetar a paginação ao trocar de aba
     */
    public function updatingAbaAtual()
    {
        $this->resetPage();
    }


    public function render()
    {
        // ==========================================================
        // ATUALIZA OS KPIs (SEPARAÇÃO DAS CONSULTAS)
        // ==========================================================
        $this->kpiProntoParaFaturar = OrdemServico::where('status', 'Concluida')->whereNull('venda_id')->sum('valor_total')
                                    + Pedido::where('status', 'Aprovado')->whereNull('venda_id')->sum('valor_total');

        $statusEmAndamento = ['Aberta', 'Em Execução', 'Aguardando Peças', 'Aguardando Aprovação', 'Aprovada'];
        $this->kpiEmAndamento = OrdemServico::whereIn('status', $statusEmAndamento)
                                    ->where(function ($query) {
                                        $query->where('valor_produtos', '>', 0)->orWhere('valor_servicos', '>', 0);
                                    })->sum('valor_total');
        
        $this->kpiPropostas = Orcamento::where('status', 'Pendente')->sum('valor_total');

        // ==========================================================
        // BUSCA DAS TABELAS (PAGINADAS)
        // ==========================================================

        $osProntasParaFaturar = OrdemServico::with('cliente')
            ->where('status', 'Concluida')
            ->whereNull('venda_id')
            ->latest()
            ->paginate(10, ['*'], 'osProntas');

        $pedidosProntosParaFaturar = Pedido::with('cliente')
            ->where('status', 'Aprovado')
            ->whereNull('venda_id')
            ->latest('data_pedido')
            ->paginate(10, ['*'], 'pedidosProntos');

        $osEmAndamento = OrdemServico::with('cliente', 'tecnico')
            ->whereIn('status', $statusEmAndamento)
            ->where(function ($query) {
                $query->where('valor_produtos', '>', 0)
                      ->orWhere('valor_servicos', '>', 0);
            })
            ->latest()
            ->paginate(10, ['*'], 'osAndamento');

        $orcamentosPendentes = Orcamento::with('cliente')
            ->where('status', 'Pendente')
            ->latest()
            ->paginate(10, ['*'], 'orcamentos');
        

        return view('livewire.financeiro.central-faturamento', [
            'osProntasParaFaturar' => $osProntasParaFaturar,
            'pedidosProntosParaFaturar' => $pedidosProntosParaFaturar,
            'osEmAndamento' => $osEmAndamento,
            'orcamentosPendentes' => $orcamentosPendentes,
        ]);
    }
}