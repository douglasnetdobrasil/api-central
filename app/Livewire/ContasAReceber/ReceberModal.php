<?php

namespace App\Livewire\ContasAReceber;

use App\Models\ContaAReceber;
use App\Models\FormaPagamento;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
use Livewire\Component;

class ReceberModal extends Component
{
    public bool $mostrarModal = false;
    public ?ContaAReceber $conta = null;

    public string $valorAReceber = '';
    public string $dataRecebimento = '';
    public ?int $formaPagamentoId = null;
    public float $valorPendente = 0;
    public string $juros = '';
    public string $multa = '';
    public string $desconto = '';

    public $formasPagamento = [];

    #[On('abrirModalReceber')]
    public function abrir(int $contaId)
    {
        $this->resetErrorBag();
        $this->conta = ContaAReceber::findOrFail($contaId);
        $this->formasPagamento = FormaPagamento::where('ativo', true)->get();
        $this->formaPagamentoId = $this->formasPagamento->first()->id ?? null;
        $this->dataRecebimento = now()->format('Y-m-d');
        $this->valorPendente = $this->conta->valor_pendente;
        $this->valorAReceber = number_format($this->valorPendente, 2, ',', '.');
        $this->juros = '';
        $this->multa = '';
        $this->desconto = '';

        $this->mostrarModal = true;
    }

    protected function prepareForValidation($attributes)
    {
        // Converte valores monetários do formato brasileiro para o formato de banco de dados
        foreach (['valorAReceber', 'juros', 'multa', 'desconto'] as $field) {
            if (isset($attributes[$field]) && is_string($attributes[$field])) {
                $attributes[$field] = str_replace(['.', ','], ['', '.'], $attributes[$field]);
            }
        }
        return $attributes;
    }

    public function salvarRecebimento()
    {
        $validatedData = $this->validate([
            'valorAReceber' => 'required|numeric|min:0.01',
            'dataRecebimento' => 'required|date',
            'formaPagamentoId' => 'required|exists:forma_pagamentos,id',
            'juros' => 'nullable|numeric|min:0',
            'multa' => 'nullable|numeric|min:0',
            'desconto' => 'nullable|numeric|min:0',
        ]);

        $valorRecebido = (float) $validatedData['valorAReceber'];
        $juros = (float) ($validatedData['juros'] ?? 0);
        $multa = (float) ($validatedData['multa'] ?? 0);
        $desconto = (float) ($validatedData['desconto'] ?? 0);

        DB::transaction(function () use ($valorRecebido, $juros, $multa, $desconto) {
            // 1. Cria o registro do recebimento no histórico
            $this->conta->recebimentos()->create([
                'valor_recebido' => $valorRecebido,
                'data_recebimento' => $this->dataRecebimento,
                'forma_pagamento_id' => $this->formaPagamentoId,
                'juros' => $juros,
                'multa' => $multa,
                'desconto' => $desconto,
                'empresa_id' => $this->conta->empresa_id,
            ]);

            // 2. Recalcula o total recebido e atualiza a conta principal
            $novoValorRecebido = $this->conta->recebimentos()->sum('valor_recebido');
            $this->conta->valor_recebido = $novoValorRecebido;

            // 3. Atualiza o status da conta
            // Usamos bccomp para comparar floats com precisão
            if (bccomp((string)$novoValorRecebido, (string)$this->conta->valor, 2) >= 0) {
                $this->conta->status = 'Recebido';
            } else {
                $this->conta->status = 'Recebido Parcialmente';
            }

            $this->conta->save();
        });

        $this->mostrarModal = false;
        $this->dispatch('recebimentoEfetuado'); // Avisa a lista para atualizar
        session()->flash('success', 'Recebimento registrado com sucesso!');
    }

    public function render()
    {
        return view('livewire.contas-a-receber.receber-modal');
    }
}