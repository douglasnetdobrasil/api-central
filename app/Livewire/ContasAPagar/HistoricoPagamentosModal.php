<?php

namespace App\Livewire\ContasAPagar;

use App\Models\ContaAPagar;
use Livewire\Component;
use Livewire\Attributes\On;

class HistoricoPagamentosModal extends Component
{
    public bool $mostrarModal = false;
    public ?ContaAPagar $conta = null;
    public $pagamentos = [];

    #[On('abrirModalHistorico')]
    public function abrir(int $contaId)
    {
        // Carrega a conta com suas relações de pagamentos e formas de pagamento
        $this->conta = ContaAPagar::with('pagamentos.formaPagamento')->find($contaId);
        
        if ($this->conta) {
            $this->pagamentos = $this->conta->pagamentos->sortByDesc('data_pagamento');
            $this->mostrarModal = true;
        }
    }

    public function render()
    {
        return view('livewire.contas-a-pagar.historico-pagamentos-modal');
    }
}