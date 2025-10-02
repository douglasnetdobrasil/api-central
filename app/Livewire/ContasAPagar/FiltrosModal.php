<?php

namespace App\Livewire\ContasAPagar;

use App\Models\CategoriaContaAPagar;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\Attributes\On;

class FiltrosModal extends Component
{
    public bool $mostrarModal = false;

    // Propriedades para os filtros
    public $fornecedorId = '';
    public $categoriaId = '';
    public $dataVencimentoInicio = '';
    public $dataVencimentoFim = '';
    public $status = '';
    public $agruparPor = '';

    // Propriedades para popular os dropdowns
    public $fornecedores = [];
    public $categorias = [];

    #[On('abrirModalFiltros')]
    public function abrir()
    {
        $this->carregarDependencias();
        $this->mostrarModal = true;
    }

    public function carregarDependencias()
    {
        $empresaId = Auth::user()->empresa_id;
        $this->fornecedores = Fornecedor::where('empresa_id', $empresaId)->orderBy('razao_social')->get();
        // Carregando categorias de forma simples, pode ser formatado se necessário
        $this->categorias = CategoriaContaAPagar::where('empresa_id', $empresaId)->orderBy('nome')->get();
    }

    public function aplicarFiltros()
    {
        $filtros = [
            'fornecedorId' => $this->fornecedorId,
            'categoriaId' => $this->categoriaId,
            'dataVencimentoInicio' => $this->dataVencimentoInicio,
            'dataVencimentoFim' => $this->dataVencimentoFim,
            'status' => $this->status,
            'agruparPor' => $this->agruparPor,
        ];

        // Emite o evento para o componente pai (ContaIndex)
        $this->dispatch('filtrosAplicados', $filtros);
        $this->mostrarModal = false;
    }

    public function limparFiltros()
    {
        // Reseta as propriedades locais
        $this->reset(['fornecedorId', 'categoriaId', 'dataVencimentoInicio', 'dataVencimentoFim', 'status', 'agruparPor']);
        
        // Emite o evento com valores vazios para limpar no componente pai
        $this->aplicarFiltros();
    }

    public function render()
    {
        return view('livewire.contas-a-pagar.filtros-modal');
    }
}