<?php

namespace App\Livewire\ContasAReceber;

use App\Models\Cliente;
use App\Models\ContaAReceber;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ContaReceberForm extends Component
{
    // Campos do formulário
    public $cliente_id;
    public $descricao;
    public $valor_total;
    public $data_vencimento;
    public $numero_parcelas = 1;
    public $dias_intervalo = 30; // << NOVO >> Adiciona a propriedade com um padrão de 30 dias

    public $clientes = [];

    public function mount()
    {
        $this->clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
        $this->data_vencimento = now()->format('Y-m-d');
    }

    protected function rules() // << ALTERADO >>: Convertido para método para incluir a nova regra
    {
        return [
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string|max:255',
            'valor_total' => 'required|numeric|min:0.01',
            'data_vencimento' => 'required|date',
            'numero_parcelas' => 'required|integer|min:1',
            'dias_intervalo' => 'required|integer|min:0', // << NOVO >> Adiciona a regra de validação
        ];
    }

    public function save()
    {
        $this->validate();
        
        $totalParcelas = $this->numero_parcelas;
        $valorTotal = $this->valor_total;
        
        $valorParcela = bcdiv((string)$valorTotal, (string)$totalParcelas, 2);
        $valorAcumulado = 0;

        for ($i = 1; $i <= $totalParcelas; $i++) {
            $valorCorrente = $valorParcela;
            if ($i === $totalParcelas) {
                $valorCorrente = bcsub((string)$valorTotal, (string)$valorAcumulado, 2);
            }
            $valorAcumulado = bcadd((string)$valorAcumulado, (string)$valorCorrente, 2);

            // << ALTERADO >>: Lógica de cálculo da data de vencimento
            $diasParaAdicionar = ($i - 1) * $this->dias_intervalo;
            $dataVencimento = Carbon::parse($this->data_vencimento)->addDays($diasParaAdicionar)->toDateString();

            $descricaoParcela = $this->descricao . ($totalParcelas > 1 ? " ({$i}/{$totalParcelas})" : '');

            ContaAReceber::create([
                'empresa_id' => Auth::user()->empresa_id,
                'cliente_id' => $this->cliente_id,
                'venda_id' => null,
                'descricao' => $descricaoParcela,
                'parcela_numero' => $i,
                'parcela_total' => $totalParcelas,
                'valor' => $valorCorrente,
                'data_vencimento' => $dataVencimento,
                'status' => 'A Receber',
            ]);
        }

        session()->flash('success', 'Conta(s) a receber cadastrada(s) com sucesso!');
        return redirect()->route('contas_a_receber.index');
    }

    public function render()
    {
        return view('livewire.contas-a-receber.conta-receber-form');
    }
}