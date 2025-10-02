<?php

namespace App\Livewire\ContasAPagar;


use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ContaAPagar;
use Livewire\Attributes\On;

class ContaIndex extends Component
{
    use WithPagination; // Habilitar paginação

    // Propriedades para armazenar os filtros ativos
    public $filtroFornecedorId = '';
    public $filtroCategoriaId = '';
    public $filtroDataVencimentoInicio = '';
    public $filtroDataVencimentoFim = '';
    public $filtroStatus = '';
    public $filtroAgruparPor = '';

    // Listener para o evento 'contaPaga'
    protected $listeners = ['contaPaga' => '$refresh'];

    #[On('filtrosAplicados')]
    public function atualizarFiltros($filtros)
    {
        $this->filtroFornecedorId = $filtros['fornecedorId'];
        $this->filtroCategoriaId = $filtros['categoriaId'];
        $this->filtroDataVencimentoInicio = $filtros['dataVencimentoInicio'];
        $this->filtroDataVencimentoFim = $filtros['dataVencimentoFim'];
        $this->filtroStatus = $filtros['status'];
        $this->filtroAgruparPor = $filtros['agruparPor'];
        
        // Reseta a paginação ao aplicar novos filtros
        $this->resetPage();
    }

    public function render()
    {
        // <<-- LÓGICA DE FILTRO APLICADA À CONSULTA -->>
        $query = ContaAPagar::with(['categoriaContaAPagar.parent', 'fornecedor']);

        // Filtro por Fornecedor
        if ($this->filtroFornecedorId) {
            $query->where('fornecedor_id', $this->filtroFornecedorId);
        }

        // Filtro por Categoria
        if ($this->filtroCategoriaId) {
            $query->where('categoria_conta_a_pagar_id', $this->filtroCategoriaId);
        }
        
        // Filtro por Status
        if ($this->filtroStatus) {
            if ($this->filtroStatus == 'Vencida') {
                $query->where('status', '!=', 'Paga')->where('data_vencimento', '<', now()->toDateString());
            } else {
                 $query->where('status', $this->filtroStatus);
            }
        }

        // Filtro por Data de Vencimento
        if ($this->filtroDataVencimentoInicio) {
            $query->where('data_vencimento', '>=', $this->filtroDataVencimentoInicio);
        }
        if ($this->filtroDataVencimentoFim) {
            $query->where('data_vencimento', '<=', $this->filtroDataVencimentoFim);
        }

        // Agrupamento (Ordenação)
        if ($this->filtroAgruparPor === 'categoria') {
            $query->orderBy('categoria_conta_a_pagar_id', 'asc')->orderBy('data_vencimento', 'asc');
        } elseif ($this->filtroAgruparPor === 'fornecedor') {
            $query->orderBy('fornecedor_id', 'asc')->orderBy('data_vencimento', 'asc');
        } elseif ($this->filtroAgruparPor === 'data_vencimento') {
            $query->orderBy('data_vencimento', 'asc');
        } else {
            // Ordem padrão
            $query->orderBy('data_vencimento', 'asc');
        }

        $contas = $query->paginate(15);

        return view('livewire.contas-a-pagar.conta-index', [
            'contas' => $contas,
        ]);
    }
}
