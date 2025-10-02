<?php

namespace App\Livewire\ContasAReceber;

use App\Models\Cliente;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Component;

class FiltrosModal extends Component
{
    public bool $mostrarModal = false;

    // Propriedades para os filtros
    public $clienteId = '';
    public $dataVencimentoInicio = '';
    public $dataVencimentoFim = '';
    public $status = '';

    // Propriedades para popular os dropdowns
    public $clientes = [];

    #[On('abrirModalFiltrosContasReceber')]
    public function abrir()
    {
        $this->clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
        $this->mostrarModal = true;
    }

    public function aplicarFiltros()
    {
        $filtros = [
            'clienteId' => $this->clienteId,
            'dataVencimentoInicio' => $this->dataVencimentoInicio,
            'dataVencimentoFim' => $this->dataVencimentoFim,
            'status' => $this->status,
        ];

        $this->dispatch('filtrosReceberAplicados', $filtros);
        $this->mostrarModal = false;
    }

    public function limparFiltros()
    {
        $this->reset(['clienteId', 'dataVencimentoInicio', 'dataVencimentoFim', 'status']);
        $this->aplicarFiltros();
    }

    public function render()
    {
        return view('livewire.contas-a-receber.filtros-modal');
    }
}