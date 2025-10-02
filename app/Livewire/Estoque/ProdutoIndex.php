<?php

namespace App\Livewire\Estoque;

use App\Models\Produto;
use Livewire\Component;
use Livewire\WithPagination;

class ProdutoIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function render()
    {
        $query = Produto::query();

        if ($this->search) {
            $query->where('nome', 'like', "%{$this->search}%")
                  ->orWhere('id', $this->search);
        }

        $produtos = $query->orderBy('nome')->paginate(20);

        return view('livewire.estoque.produto-index', ['produtos' => $produtos]);
    }
}