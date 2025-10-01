<?php

namespace App\Livewire\ContasAPagar;

use App\Models\ContaAPagar;
use App\Models\FormaPagamento;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;

class PagarModal extends Component
{
    public bool $mostrarModal = false;
    public ?ContaAPagar $conta = null;

    public string $valorAPagar = '';
    public string $dataPagamento = '';
    public ?int $formaPagamentoId = null;
    public float $valorRestante = 0;

    public $formasPagamento = [];

    #[On('abrirModalPagar')]
    public function abrir(int $contaId)
    {
        $this->conta = ContaAPagar::find($contaId);
        $this->formasPagamento = FormaPagamento::where('ativo', true)->get();
        $this->formaPagamentoId = $this->formasPagamento->first()->id ?? null;
        $this->dataPagamento = now()->format('Y-m-d');

        $valorRestante = bcsub((string)$this->conta->valor_total, (string)$this->conta->valor_pago, 2);
        $this->valorAPagar = number_format((float)$valorRestante, 2, ',', '.');
        $this->valorRestante = (float)$valorRestante;

        $this->mostrarModal = true;
    }

    // Este método limpa o valor (ex: "1.250,50") para um formato que a validação entende (ex: "1250.50")
    protected function prepareForValidation($attributes)
    {
        if (isset($attributes['valorAPagar'])) {
            $attributes['valorAPagar'] = str_replace(['.', ','], ['', '.'], $attributes['valorAPagar']);
        }
        return $attributes;
    }

    public function salvarPagamento()
    {
        // A validação agora funcionará com o valor limpo pelo método acima
        $validatedData = $this->validate([
            'valorAPagar' => 'required|numeric|min:0.01|max:' . $this->valorRestante,
            'dataPagamento' => 'required|date',
            'formaPagamentoId' => 'required|exists:forma_pagamentos,id',
        ]);
        
        $valorPago = (float) $validatedData['valorAPagar'];

        DB::transaction(function () use ($valorPago) {
            // <<-- LÓGICA CORRETA -->>
            // ETAPA 1: Criar o registro de pagamento (o histórico)
            $this->conta->pagamentos()->create([
                'valor' => $valorPago,
                'data_pagamento' => $this->dataPagamento,
                'forma_pagamento_id' => $this->formaPagamentoId,
                'empresa_id' => $this->conta->empresa_id,
            ]);

            // ETAPA 2: Recalcular o total pago e atualizar a conta principal
            $novoValorPago = $this->conta->pagamentos()->sum('valor');
            $this->conta->valor_pago = $novoValorPago;

            // ETAPA 3: Atualizar o status da conta
            if (bccomp((string)$novoValorPago, (string)$this->conta->valor_total, 2) >= 0) {
                $this->conta->status = 'Paga';
            } else {
                $this->conta->status = 'Paga Parcialmente';
            }
            
            $this->conta->data_pagamento = $this->dataPagamento; 
            $this->conta->save();
        });

        $this->mostrarModal = false;
        $this->dispatch('contaPaga');
        session()->flash('success', 'Pagamento registrado com sucesso!');
    }

    public function render()
    {
        return view('livewire.contas-a-pagar.pagar-modal');
    }
}