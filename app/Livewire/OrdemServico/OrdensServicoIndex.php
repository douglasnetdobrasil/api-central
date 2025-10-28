<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\OrdemServico;
use App\Models\FormaPagamento;
use App\Services\OrdemServicoFaturamentoService;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrdensServicoIndex extends Component
{
    // ... Suas outras propriedades e métodos (filtros, paginação, etc.)

    protected $listeners = ['faturarRapido']; // Listener do evento

    protected $osFaturamentoService;
    
    // Injeta o serviço
    public function boot(OrdemServicoFaturamentoService $osFaturamentoService)
    {
        $this->osFaturamentoService = $osFaturamentoService;
    }

    /**
     * Método acionado pelo botão "Faturar Rápido" na tabela.
     * @param int $osId ID da Ordem de Serviço.
     */
    public function faturarRapido($osId)
    {
        $os = OrdemServico::with('cliente')->findOrFail($osId);

        // REGRA 1: Só fatura se estiver Concluída e não tiver Venda
        if ($os->status !== 'Concluida' || $os->venda_id) {
            session()->flash('error', "A OS #{$os->id} não está apta para faturamento rápido.");
            return;
        }

        // REGRA 2: Requer Forma de Pagamento Padrão (Vamos usar a primeira 'a_vista')
        $formaPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)
                                        ->where('tipo', 'a_vista')
                                        ->first();

        if (!$formaPagamento) {
            session()->flash('error', "Não foi encontrada uma forma de pagamento 'à vista' padrão para faturamento rápido.");
            return;
        }

        try {
            // Dados mínimos para o serviço (Vencimento = hoje)
            $dadosPagamento = ['data_vencimento' => now()->format('Y-m-d')];

            $venda = $this->osFaturamentoService->faturar($os, $formaPagamento, $dadosPagamento);

            // Atualiza o componente visualmente
            $this->js('$refresh'); 
            session()->flash('success', "OS #{$os->id} faturada para a Venda #{$venda->id} (Pagamento: {$formaPagamento->nome}).");

        } catch (Exception $e) {
            session()->flash('error', 'Erro no faturamento: ' . $e->getMessage());
        }
    }

    // Seu método render() deve estar aqui, carregando as $ordensServico e chamando a view.
    // public function render() { ... }
}