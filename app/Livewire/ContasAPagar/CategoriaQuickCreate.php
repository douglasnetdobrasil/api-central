<?php

namespace App\Livewire\ContasAPagar;

use App\Models\CategoriaContaAPagar;
use Livewire\Component;
use Livewire\Attributes\On; // Adicionado

class CategoriaQuickCreate extends Component
{
    public $mostrarModal = false;
    
    // Campos do formulário do modal
    public $nome;
    public $cor = '#808080';
    public $parent_id;

    public $categoriasPai;

    // A propriedade $listeners foi REMOVIDA

    // Atributo #[On] adicionado
    #[On('abrirModalCriarCategoria')]
    public function abrirModalCriarCategoria()
    {
        $this->resetValidation();
        $this->reset(['nome', 'cor', 'parent_id']);
        $this->categoriasPai = CategoriaContaAPagar::whereNull('parent_id')
            ->where('empresa_id', auth()->user()->empresa_id)
            ->orderBy('nome')->get();
        $this->mostrarModal = true;
    }

    public function salvarCategoria()
    {
        $validated = $this->validate([
            'nome' => 'required|string|max:255',
            'cor' => 'required|string',
            'parent_id' => 'nullable|exists:categoria_contas_a_pagar,id'
        ]);

        $novaCategoria = CategoriaContaAPagar::create([
            'nome' => $validated['nome'],
            'cor' => $validated['cor'],
            'parent_id' => $validated['parent_id'],
            'empresa_id' => auth()->user()->empresa_id
        ]);

        $this->mostrarModal = false;
        // Avisa o componente PAI (o formulário) que uma nova categoria foi criada, enviando o ID dela
        $this->dispatch('categoriaCriada', categoriaId: $novaCategoria->id);
    }

    public function render()
    {
        return view('livewire.contas-a-pagar.categoria-quick-create');
    }
}