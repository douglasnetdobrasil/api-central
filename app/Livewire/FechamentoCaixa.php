<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Caixa;
use App\Models\VendaPagamento;
use App\Models\CaixaMovimentacao;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FechamentoCaixa extends Component
{
    public ?Caixa $caixaSessao = null;

    // Propriedades para o resumo
    public $valorAbertura = 0;
    public $vendasPorFormaPagamento = [];
    public $totalVendas = 0;
    public $totalSangrias = 0;
    public $totalSuprimentos = 0;
    public $totalGeral = 0;
    public $saldoEsperadoDinheiro = 0;

    // Propriedades para o formulário de fechamento
    public $valorContadoDinheiro = '';
    public $diferencaCaixa = 0;
    public $observacaoFechamento = '';

    public function mount()
    {
        $this->caixaSessao = Caixa::where('user_id', Auth::id())->where('status', 'aberto')->first();

        if ($this->caixaSessao) {
            $this->carregarResumo();
        }
    }

    public function carregarResumo()
    {
        $this->valorAbertura = $this->caixaSessao->valor_abertura;

        // Calcula o total de vendas agrupado por forma de pagamento
        $this->vendasPorFormaPagamento = VendaPagamento::join('forma_pagamentos', 'venda_pagamentos.forma_pagamento_id', '=', 'forma_pagamentos.id')
            ->join('vendas', 'venda_pagamentos.venda_id', '=', 'vendas.id')
            ->where('vendas.caixa_id', $this->caixaSessao->id)
            ->where('vendas.status', 'concluida') // Apenas vendas concluídas
            ->groupBy('forma_pagamentos.nome', 'forma_pagamentos.codigo_sefaz')
            ->select('forma_pagamentos.nome as forma_pagamento_nome', DB::raw('SUM(venda_pagamentos.valor) as total'))
            ->pluck('total', 'forma_pagamento_nome');
            
        $this->totalVendas = $this->vendasPorFormaPagamento->sum();

        // Calcula o total de Sangrias e Suprimentos
        $movimentacoes = CaixaMovimentacao::where('caixa_id', $this->caixaSessao->id)
            ->groupBy('tipo')
            ->select('tipo', DB::raw('SUM(valor) as total'))
            ->pluck('total', 'tipo');
            
        $this->totalSangrias = $movimentacoes->get('SANGRIA', 0);
        $this->totalSuprimentos = $movimentacoes->get('SUPRIMENTO', 0);

        // Calcula o saldo que DEVERIA ter em dinheiro na gaveta
        $vendasEmDinheiro = $this->vendasPorFormaPagamento->get('Dinheiro', 0);
        $this->saldoEsperadoDinheiro = ($this->valorAbertura + $vendasEmDinheiro + $this->totalSuprimentos) - $this->totalSangrias;
        
        $this->totalGeral = $this->valorAbertura + $this->totalVendas + $this->totalSuprimentos;
    }

    // Este método é chamado automaticamente sempre que a propriedade $valorContadoDinheiro é atualizada
    public function updatedValorContadoDinheiro($value)
    {
        $valorContado = is_numeric($value) ? (float)$value : 0;
        $this->diferencaCaixa = $valorContado - $this->saldoEsperadoDinheiro;
    }

    public function fecharCaixa()
    {
        $this->validate([
            'valorContadoDinheiro' => 'required|numeric|min:0'
        ], [
            'valorContadoDinheiro.required' => 'É obrigatório informar o valor contado em dinheiro.'
        ]);

        if ($this->caixaSessao) {
            $this->caixaSessao->update([
                'status' => 'fechado',
                'data_fechamento' => now(),
                'valor_fechamento' => $this->valorContadoDinheiro, // Salva o valor que o operador contou
                // Futuramente, podemos adicionar colunas para salvar a diferença, o total calculado, etc.
            ]);

            session()->flash('sucesso', 'Caixa fechado com sucesso!');

            return redirect()->route('pdv-caixa.index'); // Redireciona para a tela do PDV (que mostrará a tela de "Abrir Caixa")
        }
    }

    public function render()
    {
        return view('livewire.fechamento-caixa')->layout('layouts.app'); // Assume que você tem um layout padrão
    }
}