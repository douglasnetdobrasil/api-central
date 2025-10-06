<?php

namespace App\Livewire\Estoque;

use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class MovimentacaoProduto extends Component
{
    use WithPagination;

    public int $produtoId;
    public ?Produto $produto; // Usamos ? para indicar que pode ser nulo antes do mount

    public $data_inicio;
    public $data_fim;

    /**
     * O método mount é chamado quando o componente é inicializado.
     * Ele recebe o ID do produto e carrega o modelo correspondente.
     */
    public function mount(int $produtoId)
    {
        $this->produtoId = $produtoId;
        $this->produto = Produto::findOrFail($this->produtoId);
    }

    public function render()
    {
        // Query para buscar os movimentos, com filtro de data
        $movimentosQuery = $this->produto->movimentos()
            ->when($this->data_inicio, function ($query) {
                $query->whereDate('created_at', '>=', $this->data_inicio);
            })
            ->when($this->data_fim, function ($query) {
                $query->whereDate('created_at', '<=', $this->data_fim);
            })
            ->orderBy('created_at', 'desc');

        return view('livewire.estoque.movimentacao-produto', [
            'movimentos' => $movimentosQuery->paginate(15), // Paginar os resultados
        ]);
    }
}