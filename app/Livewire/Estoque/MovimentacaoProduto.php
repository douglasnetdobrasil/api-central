<?php

namespace App\Livewire\Estoque;

use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class MovimentacaoProduto extends Component
{
    use WithPagination;

    // << ALTERADO >>: Armazenamos apenas o ID e o produto será carregado depois.
    public int $produtoId;
    public ?Produto $produto;
    public $data_inicio;
    public $data_fim;

    public function mount(Produto $produto)
    {
        // No início, pegamos o ID do produto que foi passado.
        $this->produtoId = $produto->id;
    }

    public function render()
    {
        // << ALTERADO >>: Buscamos o produto fresco do banco de dados em toda renderização.
        $this->produto = Produto::findOrFail($this->produtoId);

        // Agora a consulta de movimentos funcionará, pois $this->produto é um objeto válido.
        $query = $this->produto->movimentos()->with(['user', 'origem'])->latest();

        if ($this->data_inicio) {
            $query->whereDate('created_at', '>=', $this->data_inicio);
        }
        if ($this->data_fim) {
            $query->whereDate('created_at', '<=', $this->data_fim);
        }
        
        $movimentos = $query->paginate(20);

        return view('livewire.estoque.movimentacao-produto', ['movimentos' => $movimentos]);
    }
}