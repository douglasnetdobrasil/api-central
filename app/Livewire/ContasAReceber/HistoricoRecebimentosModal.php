<?php

namespace App\Livewire\ContasAReceber;

use App\Models\ContaAReceber;
use Livewire\Attributes\On;
use Livewire\Component;

class HistoricoRecebimentosModal extends Component
{
    public bool $mostrarModal = false;
    public ?ContaAReceber $conta = null;
    public $recebimentos = [];

    #[On('abrirModalHistoricoRecebimentos')]
    public function abrir(int $contaId)
    {
        // <<-- ALTERADO AQUI -->>
        // Agora carregamos muito mais informações:
        // - recebimentos com sua forma de pagamento
        // - a venda, com seu usuário (vendedor) e seus itens (e o produto de cada item)
        $this->conta = ContaAReceber::with([
            'recebimentos.formaPagamento',
            'venda.user',
            'venda.items.produto'
        ])->find($contaId);
        
        if ($this->conta) {
            $this->recebimentos = $this->conta->recebimentos->sortByDesc('data_recebimento');
            $this->mostrarModal = true;
        }
    }

    public function render()
    {
        return view('livewire.contas-a-receber.historico-recebimentos-modal');
    }
}