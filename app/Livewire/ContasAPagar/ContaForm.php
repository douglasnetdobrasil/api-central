<?php

namespace App\Livewire\ContasAPagar;

use App\Models\CategoriaContaAPagar;
use App\Models\ContaAPagar;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\DB;
use App\Models\FormaPagamento;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Illuminate\Validation\Rule;
use Illuminate\Support\Collection;
use Livewire\Attributes\On; // Adicionado

class ContaForm extends Component
{
  
    public ContaAPagar $conta;
    public Collection $categorias;
    public Collection $fornecedores;
    public bool $isEditing = false;
    public array $formattedCategorias = [];

    // ADICIONE ESTAS PROPRIEDADES
    public $descricao;
    public $fornecedor_id;
    public $numero_documento;
    public $valor_total;
    public $data_emissao;
    public $data_vencimento;
    public $observacoes;
    public $categoria_conta_a_pagar_id;

    public Collection $formasPagamento; // << ADICIONE ESTA PROPRIEDADE

    // Propriedades para o novo recurso
    public bool $foiPaga = false;
    public $data_pagamento;
    public $forma_pagamento_id;

    // A propriedade $listeners foi REMOVIDA

    protected function rules()
    {
        return [
            'descricao' => 'required|string|max:255',
            'fornecedor_id' => 'nullable|exists:fornecedores,id',
            'numero_documento' => 'nullable|string|max:100',
            'valor_total' => 'required|numeric|min:0.01',
            'data_emissao' => 'required|date',
            'data_vencimento' => 'required|date',
            'observacoes' => 'nullable|string',
            'categoria_conta_a_pagar_id' => 'nullable|exists:categoria_contas_a_pagar,id',
            // Regras condicionais para os novos campos
            'data_pagamento' => [Rule::requiredIf($this->foiPaga), 'nullable', 'date'],
            'forma_pagamento_id' => [Rule::requiredIf($this->foiPaga), 'nullable', 'exists:forma_pagamentos,id'],
        ];
    }

    public function mount(ContaAPagar $conta)
    {
        $this->conta = $conta;
        $this->isEditing = $conta->exists;
    
        // Popula as propriedades públicas com os dados do modelo
        $this->descricao = $this->conta->descricao;
        $this->fornecedor_id = $this->conta->fornecedor_id;
        $this->numero_documento = $this->conta->numero_documento;
        $this->valor_total = $this->conta->valor_total;
        $this->observacoes = $this->conta->observacoes;
        $this->categoria_conta_a_pagar_id = $this->conta->categoria_conta_a_pagar_id;
    
        if (!$this->isEditing) {
            $this->data_emissao = now()->format('Y-m-d');
            $this->data_vencimento = now()->addDays(30)->format('Y-m-d');
        } else {
            $this->data_emissao = \Carbon\Carbon::parse($this->conta->data_emissao)->format('Y-m-d');
            $this->data_vencimento = \Carbon\Carbon::parse($this->conta->data_vencimento)->format('Y-m-d');
        }
    
       
        if (!$this->isEditing) {
            $this->data_pagamento = now()->format('Y-m-d');
        }
        // ...
        $this->carregarDependencias();
    }
    public function carregarDependencias()
    {
        $empresaId = Auth::user()->empresa_id;
        $this->fornecedores = Fornecedor::where('empresa_id', $empresaId)->orderBy('razao_social')->get();
        $this->categorias = CategoriaContaAPagar::where('empresa_id', $empresaId)->get();
        $this->formatarCategoriasParaDropdown();

        $this->formasPagamento = FormaPagamento::where('ativo', true)->get(); // << ADICIONE ESTA LINHA
        
        // Garante que uma forma de pagamento padrão seja selecionada
        if (!$this->forma_pagamento_id) {
            $this->forma_pagamento_id = $this->formasPagamento->first()->id ?? null;
        }
    }
    
    // Formata as categorias para exibir a hierarquia no <select>
    private function formatarCategoriasParaDropdown()
    {
        $this->formattedCategorias = [];
        $rootCategorias = $this->categorias->whereNull('parent_id')->sortBy('nome');
        
        foreach ($rootCategorias as $categoria) {
            $this->formattedCategorias[] = [
                'id' => $categoria->id,
                'nome' => $categoria->nome,
            ];
            $this->adicionarSubCategorias($categoria->id, 1);
        }
    }

    private function adicionarSubCategorias($parentId, $level)
    {
        $subCategorias = $this->categorias->where('parent_id', $parentId)->sortBy('nome');
        foreach ($subCategorias as $subCategoria) {
            $this->formattedCategorias[] = [
                'id' => $subCategoria->id,
                'nome' => str_repeat('--', $level) . ' ' . $subCategoria->nome,
            ];
            $this->adicionarSubCategorias($subCategoria->id, $level + 1);
        }
    }

    // Atributo #[On] adicionado
    #[On('categoriaCriada')]
    public function categoriaCriada($categoriaId)
    {
        $this->carregarDependencias();
        $this->categoria_conta_a_pagar_id = $categoriaId; // ATUALIZE ESTA LINHA
    }
    public function save()
    {
        $this->validate();
    
        // Use uma transação para garantir que ambas as operações funcionem ou nenhuma delas
        DB::transaction(function () {
            // Preenche o modelo $conta com os dados do formulário
            $this->conta->descricao = $this->descricao;
            $this->conta->fornecedor_id = $this->fornecedor_id;
            $this->conta->numero_documento = $this->numero_documento;
            $this->conta->valor_total = $this->valor_total;
            $this->conta->data_emissao = $this->data_emissao;
            $this->conta->data_vencimento = $this->data_vencimento;
            $this->conta->observacoes = $this->observacoes;
            $this->conta->categoria_conta_a_pagar_id = $this->categoria_conta_a_pagar_id;
            $this->conta->empresa_id = Auth::user()->empresa_id;
    
            // LÓGICA DO PAGAMENTO NO LANÇAMENTO
            if ($this->foiPaga) {
                $this->conta->status = 'Paga';
                $this->conta->valor_pago = $this->valor_total;
                $this->conta->data_pagamento = $this->data_pagamento;
                // A forma de pagamento principal pode ser salva, opcional
                $this->conta->forma_pagamento_id = $this->forma_pagamento_id;
            } else {
                if (!$this->isEditing) {
                    $this->conta->status = 'A Pagar';
                    $this->conta->valor_pago = 0; // Garante que começa com zero
                }
            }
    
            $this->conta->save(); // Salva a conta primeiro para obter um ID
    
            // **NOVO**: Se a conta foi marcada como paga, crie o registro de pagamento no histórico
            if ($this->foiPaga && !$this->isEditing) {
                $this->conta->pagamentos()->create([
                    'valor' => $this->valor_total,
                    'data_pagamento' => $this->data_pagamento,
                    'forma_pagamento_id' => $this->forma_pagamento_id,
                    'empresa_id' => $this->conta->empresa_id,
                ]);
            }
        });
    
        session()->flash('success', 'Conta a pagar salva com sucesso!');
        return redirect()->route('contas_a_pagar.index');
    }
}