<?php

namespace App\Livewire\CentrosDeCusto;

use App\Models\CentroCusto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CentroCustoIndex extends Component
{
    public $search = ''; // Propriedade para o campo de busca

    public function delete($id)
    {
        // ... seu método delete continua igual ...
        $centroCusto = CentroCusto::find($id);

        if (!$centroCusto || $centroCusto->empresa_id !== Auth::user()->empresa_id) {
            session()->flash('error', 'Centro de custo não encontrado ou acesso negado.');
            return;
        }
        $centroCusto->delete();
        session()->flash('success', 'Centro de custo excluído com sucesso.');
    }

    public function render()
    {
        $query = CentroCusto::where('empresa_id', Auth::user()->empresa_id)
            ->with('children'); // Eager load dos filhos

        // Se houver busca, a lógica muda:
        if (!empty($this->search)) {
            // Busca por todos que correspondem e então busca os pais deles para montar a árvore
            $query->where(function ($q) {
                $q->where('nome', 'like', '%' . $this->search . '%')
                  ->orWhere('codigo', 'like', '%' . $this->search . '%');
            });
        } else {
            // Se não houver busca, mostra apenas os de nível superior
            $query->whereNull('parent_id');
        }

        $centrosCusto = $query->get();

        return view('livewire.centros-de-custo.centro-custo-index', [
            'centrosCusto' => $centrosCusto
        ]);
    }
}