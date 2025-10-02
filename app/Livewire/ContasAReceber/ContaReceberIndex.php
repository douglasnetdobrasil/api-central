<?php

namespace App\Livewire\ContasAReceber;

use App\Models\ContaAReceber;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On; // << NOVO

class ContaReceberIndex extends Component
{
    use WithPagination;

    // << NOVAS PROPRIEDADES PARA OS FILTROS >>
    public $filtroClienteId = '';
    public $filtroDataVencimentoInicio = '';
    public $filtroDataVencimentoFim = '';
    public $filtroStatus = '';

    // Listener para atualizar a lista
    protected $listeners = ['recebimentoEfetuado' => '$refresh'];

    // << NOVO MÉTODO PARA RECEBER OS FILTROS >>
    #[On('filtrosReceberAplicados')]
    public function atualizarFiltros($filtros)
    {
        $this->filtroClienteId = $filtros['clienteId'];
        $this->filtroDataVencimentoInicio = $filtros['dataVencimentoInicio'];
        $this->filtroDataVencimentoFim = $filtros['dataVencimentoFim'];
        $this->filtroStatus = $filtros['status'];
        $this->resetPage(); // Reseta a paginação ao aplicar filtros
    }


    public function render()
    {
        // << CONSULTA ATUALIZADA COM A LÓGICA DE FILTROS >>
        $query = ContaAReceber::with(['venda', 'cliente']);

        if ($this->filtroClienteId) {
            $query->whereHas('cliente', fn($q) => $q->where('id', $this->filtroClienteId));
        }

        if ($this->filtroStatus) {
            if ($this->filtroStatus == 'Vencida') {
                $query->where('status', '!=', 'Recebido')->where('data_vencimento', '<', now()->toDateString());
            } else {
                 $query->where('status', $this->filtroStatus);
            }
        }

        if ($this->filtroDataVencimentoInicio) {
            $query->where('data_vencimento', '>=', $this->filtroDataVencimentoInicio);
        }
        if ($this->filtroDataVencimentoFim) {
            $query->where('data_vencimento', '<=', $this->filtroDataVencimentoFim);
        }

        $contas = $query->orderBy('data_vencimento', 'asc')->paginate(15);

        return view('livewire.contas-a-receber.conta-receber-index', [
            'contas' => $contas
        ]);
    }
}