<?php

namespace App\Livewire\CentrosDeCusto;

use App\Models\CentroCusto;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CentroCustoForm extends Component
{
    public CentroCusto $centroCusto;
    public $paisDisponiveis = [];

    // Propriedades do formulário
    public $nome;
    public $codigo;
    public $parent_id;
    public $tipo = 'ANALITICO'; // Valor padrão
    public $aceita_despesas = true;
    public $aceita_receitas = true;
    public $ativo = true;

    public function mount(CentroCusto $centroCusto)
    {
        $this->centroCusto = $centroCusto;
        $this->carregarPaisDisponiveis();

        // Se estiver editando, preenche as propriedades
        if ($this->centroCusto->exists) {
            $this->nome = $this->centroCusto->nome;
            $this->codigo = $this->centroCusto->codigo;
            $this->parent_id = $this->centroCusto->parent_id;
            $this->tipo = $this->centroCusto->tipo;
            $this->aceita_despesas = (bool) $this->centroCusto->aceita_despesas;
            $this->aceita_receitas = (bool) $this->centroCusto->aceita_receitas;
            $this->ativo = (bool) $this->centroCusto->ativo;
        }
    }

    protected function rules()
    {
        return [
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50|unique:centros_custo,codigo,' . $this->centroCusto->id . ',id,empresa_id,' . Auth::user()->empresa_id,
            'parent_id' => 'nullable|exists:centros_custo,id',
            'tipo' => 'required|in:SINTETICO,ANALITICO',
            'aceita_despesas' => 'boolean',
            'aceita_receitas' => 'boolean',
            'ativo' => 'boolean',
        ];
    }

    public function carregarPaisDisponiveis()
    {
        $query = CentroCusto::sinteticos()->ativos()->where('empresa_id', Auth::user()->empresa_id);

        // Evita que um centro de custo seja pai de si mesmo
        if ($this->centroCusto->exists) {
            $query->where('id', '!=', $this->centroCusto->id);
        }

        $this->paisDisponiveis = $query->get();
    }

    public function save()
    {
        $this->validate();

        $this->centroCusto->fill([
            'nome' => $this->nome,
            'codigo' => $this->codigo,
            'parent_id' => $this->parent_id ?: null,
            'tipo' => $this->tipo,
            'aceita_despesas' => $this->aceita_despesas,
            'aceita_receitas' => $this->aceita_receitas,
            'ativo' => $this->ativo,
            'empresa_id' => Auth::user()->empresa_id,
        ]);

        $this->centroCusto->save();

        session()->flash('success', 'Centro de Custo salvo com sucesso!');
        return redirect()->route('centros-de-custo.index');
    }

    public function render()
    {
        return view('livewire.centros-de-custo.centro-custo-form');
    }
}