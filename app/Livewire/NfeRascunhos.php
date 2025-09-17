<?php

namespace App\Livewire;

use App\Models\Venda;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

class NfeRascunhos extends Component
{
    use WithPagination;

    public function render()
    {
        $rascunhos = Venda::where('empresa_id', Auth::user()->empresa_id)
            ->where('status', 'Em Digitação') // Busca apenas as vendas com este status
            ->with('cliente')
            ->latest()
            ->paginate(15);

        return view('livewire.nfe-rascunhos', ['rascunhos' => $rascunhos])
            ->layout('layouts.app'); // Define o layout padrão do seu sistema
    }
}