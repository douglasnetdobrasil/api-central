<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Caixa;
use App\Models\Venda;
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

    // ==========================================================
    // ||||||||||||||||||| NOVA PROPRIEDADE |||||||||||||||||
    // ==========================================================
    public $caixaFechado = false;

    public function mount()
    {
        // Só procuramos por um caixa aberto se ainda não tivermos fechado um nesta sessão.
        if (! $this->caixaFechado) {
            $this->caixaSessao = Caixa::where('user_id', Auth::id())->where('status', 'aberto')->first();
    
            if ($this->caixaSessao) {
                $this->carregarResumo();
            }
        }
    }
    public function carregarResumo()
    {
        $this->valorAbertura = $this->caixaSessao->valor_abertura;

        $vendasDoCaixa = Venda::where('caixa_id', $this->caixaSessao->id)
                              ->where('status', 'concluida')
                              ->with('pagamentos.formaPagamento')
                              ->get();

        $this->vendasPorFormaPagamento = $vendasDoCaixa->pluck('pagamentos')
                                                      ->flatten()
                                                      ->groupBy('formaPagamento.nome')
                                                      ->map(function ($group) {
                                                          return $group->sum('valor');
                                                      });
            
        $this->totalVendas = $this->vendasPorFormaPagamento->sum();

        $movimentacoes = CaixaMovimentacao::where('caixa_id', $this->caixaSessao->id)
            ->groupBy('tipo')
            ->select('tipo', DB::raw('SUM(valor) as total'))
            ->pluck('total', 'tipo');
            
        $this->totalSangrias = $movimentacoes->get('SANGRIA', 0);
        $this->totalSuprimentos = $movimentacoes->get('SUPRIMENTO', 0);

        $vendasEmDinheiro = $this->vendasPorFormaPagamento->get('Dinheiro', 0);
        $this->saldoEsperadoDinheiro = ($this->valorAbertura + $vendasEmDinheiro + $this->totalSuprimentos) - $this->totalSangrias;
        
        $this->totalGeral = $this->valorAbertura + $this->totalVendas + $this->totalSuprimentos;
    }

    public function updatedValorContadoDinheiro($value)
    {
        $valorContado = is_numeric($value) ? (float)$value : 0;
        $this->diferencaCaixa = $valorContado - $this->saldoEsperadoDinheiro;
    }

    // ==========================================================
    // ||||||||||||||||| FUNÇÃO fecharCaixa ATUALIZADA |||||||||||||||||
    // ==========================================================
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
                'valor_fechamento' => $this->valorContadoDinheiro,
            ]);

            // Atualiza a propriedade da sessão para refletir o fecho no comprovativo
            $this->caixaSessao->refresh();

            // Em vez de redirecionar, agora apenas mudamos o estado
            $this->caixaFechado = true;
        }
    }

    public function render()
    {
        return view('livewire.fechamento-caixa')->layout('layouts.app');
    }
}