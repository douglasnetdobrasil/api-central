<?php

namespace App\Livewire\ContasAPagar;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContaAPagar;

class ContaIndex extends Component
{
    use WithPagination; // Habilitar paginação

    // Listener para o evento 'contaPaga'
    protected $listeners = ['contaPaga' => '$refresh'];

    public function render()
    {
        $contas = ContaAPagar::with(['categoriaContaAPagar.parent', 'fornecedor'])
            ->orderBy('data_vencimento', 'asc')
            ->paginate(15); // Use a paginação do Livewire

        return view('livewire.contas-a-pagar.conta-index', [
            'contas' => $contas,
        ]);
    }
}
