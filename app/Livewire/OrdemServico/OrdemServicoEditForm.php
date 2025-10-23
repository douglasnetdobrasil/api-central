<?php

namespace App\Livewire\OrdemServico;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Produto;
use App\Models\OsProduto;
use App\Models\OsServico;
use App\Services\EstoqueService; // <-- IMPORTAMOS NOSSO SERVIÇO!
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class OrdemServicoEditForm extends Component
{
    public OrdemServico $os;

    // Dados para os dropdowns
    public $clientes = [];
    public $tecnicos = [];
    public $pecasDisponiveis = [];
    public $servicosDisponiveis = [];

    // Propriedades para os formulários de adição
    public $peca_id;
    public $peca_quantidade = 1;
    public $servico_id;
    public $servico_tecnico_id;
    public $servico_quantidade = 1;

    public function mount(OrdemServico $ordemServico)
    {
        $this->os = $ordemServico;
        $this->loadInitialData();
    }

    public function loadInitialData()
    {
        $this->clientes = Cliente::orderBy('nome')->get();
        $this->tecnicos = User::orderBy('name')->get();
        $this->pecasDisponiveis = Produto::where('ativo', 1)->whereIn('tipo', ['venda', 'produto_acabado'])->orderBy('nome')->get();
        $this->servicosDisponiveis = Produto::where('ativo', 1)->where('tipo', 'servico')->orderBy('nome')->get();
    }

    public function addPeca()
    {
        $this->validate([
            'peca_id' => 'required|exists:produtos,id',
            'peca_quantidade' => 'required|numeric|min:0.01',
        ]);
    
        $peca = Produto::find($this->peca_id);
    
        if ($peca->estoque_atual < $this->peca_quantidade) {
            $this->addError('peca_quantidade', 'Estoque insuficiente.');
            return;
        }
    
        // ==========================================================
        // ||||||||||||||||||| AQUI ESTÁ A MUDANÇA |||||||||||||||||||
        // ==========================================================
        try {
            DB::transaction(function () use ($peca) {
                $this->os->produtos()->create([
                    'produto_id'     => $peca->id,
                    'quantidade'     => $this->peca_quantidade,
                    'preco_unitario' => $peca->preco_venda,
                    'subtotal'       => $this->peca_quantidade * $peca->preco_venda,
                ]);
    
                // Chamando o serviço para dar baixa no estoque
                EstoqueService::registrarMovimento($peca, 'saida_os', $this->peca_quantidade, $this->os, "Saída para OS #{$this->os->id}");
    
                $this->os->atualizarValores();
            });
    
        } catch (\Exception $e) {
            // Se QUALQUER coisa dentro do 'try' falhar, o código vai pular para cá.
            // E vai parar a execução mostrando a mensagem de erro exata.
          //  dd($e->getMessage());
        }
        // ==========================================================
        // ||||||||||||||||||| FIM DA MUDANÇA |||||||||||||||||||
        // ==========================================================
    
    
        $this->reset(['peca_id', 'peca_quantidade']);
        $this->os->refresh();
        session()->flash('success', 'Peça adicionada com sucesso!');
    }

    public function removePeca($osProdutoId)
    {
        $osProduto = OsProduto::find($osProdutoId);
        if (!$osProduto) return;
        $peca = $osProduto->produto;
        $quantidade = $osProduto->quantidade;
        DB::transaction(function () use ($osProduto, $peca, $quantidade) {
            $osProduto->delete();
            EstoqueService::registrarMovimento($peca, 'estorno_os', $quantidade, $this->os, "Estorno da OS #{$this->os->id}");
            $this->os->atualizarValores();
        });
        $this->os->refresh();
        session()->flash('success', 'Peça removida com sucesso!');
    }

    public function addServico()
    {
        $this->validate([
            'servico_id' => 'required|exists:produtos,id',
            'servico_tecnico_id' => 'nullable|exists:users,id',
            'servico_quantidade' => 'required|numeric|min:0.01',
        ]);

        $servico = Produto::find($this->servico_id);

        DB::transaction(function () use ($servico) {
            // 1. Montamos o array com os dados para inspeção
            $dadosServico = [
                'servico_id'     => $servico->id,
                'tecnico_id'     => $this->servico_tecnico_id,
                'quantidade'     => $this->servico_quantidade,
                'preco_unitario' => $servico->preco_venda,
                'subtotal'       => $this->servico_quantidade * $servico->preco_venda,
            ];
            
            // 2. Usamos o dd() para parar e inspecionar os dados
            //dd($dadosServico);

            // O código abaixo não será executado por enquanto
            $this->os->servicos()->create($dadosServico);
            $this->os->atualizarValores();
        });

        $this->reset(['servico_id', 'servico_tecnico_id', 'servico_quantidade']);
        $this->os->refresh();
        session()->flash('success', 'Serviço adicionado com sucesso!');
    }

    public function removeServico($osServicoId)
    {
        $osServico = OsServico::find($osServicoId);
        if (!$osServico) return;
        DB::transaction(function () use ($osServico) {
            $osServico->delete();
            $this->os->atualizarValores();
        });
        $this->os->refresh();
        session()->flash('success', 'Serviço removido com sucesso!');
    }

    public function render()
    {
        return view('livewire.ordem-servico.ordem-servico-edit-form');
    }
}