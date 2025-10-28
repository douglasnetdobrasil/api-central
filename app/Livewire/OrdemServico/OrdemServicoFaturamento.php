<?php

namespace App\Livewire\OrdemServico;

use App\Models\OrdemServico;
use App\Models\FormaPagamento;
use App\Services\OrdemServicoFaturamentoService;
use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Exception;

class OrdemServicoFaturamento extends Component
{
    public OrdemServico $os;
    public $formasPagamento;
    
    // Dados para o formulário de faturamento
    public $forma_pagamento_id;
    public $data_vencimento; // Data da primeira parcela
    
    // Venda gerada após o faturamento
    public $vendaFaturada = null;

    protected $osFaturamentoService;

    public function mount(OrdemServico $os)
    {
        $this->os = $os;
        $this->formasPagamento = FormaPagamento::where('empresa_id', $os->empresa_id)
                                                ->where('ativo', 1)
                                                ->get();
        
        // Verifica se a OS já foi faturada
        if ($os->venda_id) {
             $this->vendaFaturada = $os->venda; // Carrega a venda existente
        }
        
        // Valores iniciais para faturamento
        $this->data_vencimento = now()->format('Y-m-d');
        $this->forma_pagamento_id = $this->formasPagamento->first()->id ?? null;
    }

    public function boot(OrdemServicoFaturamentoService $osFaturamentoService)
    {
        $this->osFaturamentoService = $osFaturamentoService;
    }

    public function faturar()
    {
        // 1. Validação
        $this->validate([
            'forma_pagamento_id' => 'required|exists:forma_pagamentos,id',
            'data_vencimento' => 'required|date',
        ]);
        
        // REGRA: A OS deve estar 'Concluida' antes de faturar
        if ($this->os->status !== 'Concluída') {
             session()->flash('error', 'A Ordem de Serviço deve estar no status "Concluída" antes de faturar.');
             return;
        }

        try {
            $formaPagamento = FormaPagamento::find($this->forma_pagamento_id);
            
            // Dados necessários para o serviço de Contas a Receber
            $dadosPagamento = [
                'data_vencimento' => $this->data_vencimento,
            ];

            $venda = $this->osFaturamentoService->faturar($this->os, $formaPagamento, $dadosPagamento);
            
            // Atualiza o estado do componente
            $this->vendaFaturada = $venda;
            $this->os->refresh();

            session()->flash('success', "OS faturada com sucesso! Venda #{$venda->id} gerada e Contas a Receber lançadas.");
            
            // Opcional: Emite evento para a tela principal (pai)
            $this->dispatch('osFaturada', vendaId: $venda->id);

        } catch (Exception $e) {
            session()->flash('error', 'Erro ao faturar OS: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.ordem-servico.ordem-servico-faturamento');
    }
}