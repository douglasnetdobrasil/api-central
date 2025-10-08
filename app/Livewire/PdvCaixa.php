<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\FormaPagamento;
use App\Services\NFCeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Caixa;
use App\Models\Cliente;

class PdvCaixa extends Component
{
    public ?Caixa $caixaSessao = null;
    public $valorAbertura;
    
    // Itens e totais da venda
    public $cart = [];
    public $subtotal = 0.00;
    public $descontoTotal = 0.00;
    public $total = 0.00;

    // Inputs da interface
    public $barcode = '';
    public $quantidade = 1;

    // Propriedades para o sistema de pagamento
    public $formasPagamento = [];
    public $pagamentos = [];
    public $valorRecebido = 0.00;
    public $troco = 0.00;
    public $faltaPagar = 0.00;
    public $valorPagamentoAtual;
    public $formaPagamentoAtual;

    // Propriedades para controle dos modais e estado da UI
    public $showPaymentModal = false;
    public $showOptionsMenu = false;
    public $showIdentificarModal = false;
    public $showDescontoModal = false;
    public $showPinModal = false;
    public $mensagemErro = '';
    public $vendaFinalizada = false;
    public $dadosUltimaNfce = [];
    
    // Propriedades para o cliente
    public $clienteId = null;
    public $clienteNome = '';
    public $documentoCliente = '';

    // Propriedades para desconto e autorização
    public $descontoTipo = 'valor';
    public $descontoValor = 0;
    public $supervisorPin = '';
    public $acaoParaAutorizar = null;
    public $parametrosDaAcao = null;
    public $pinIncorreto = false;

    protected $listeners = ['resetPdv' => 'resetarPdv'];

    public function mount()
    {
        $this->verificarSessaoCaixa();
        if ($this->caixaSessao) {
            $this->formasPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)
                                                   ->where('ativo', true)->orderBy('nome')->get();
            if ($this->formasPagamento->isNotEmpty()) {
                $this->formaPagamentoAtual = $this->formasPagamento->first()->id;
            }
        }
    }

    public function verificarSessaoCaixa()
    {
        $this->caixaSessao = Caixa::where('user_id', Auth::id())->where('status', 'aberto')->first();
    }

    public function abrirCaixa()
    {
        $this->validate(['valorAbertura' => 'required|numeric|min:0']);
        $this->caixaSessao = Caixa::create([
            'empresa_id' => Auth::user()->empresa_id,
            'user_id' => Auth::id(),
            'valor_abertura' => $this->valorAbertura,
            'status' => 'aberto',
        ]);
        $this->reset('valorAbertura');
        $this->mount();
    }

    public function addProduto()
    {
        $this->reset('mensagemErro');
        if ($this->vendaFinalizada) return;
        if (empty($this->barcode)) return;

        $produto = Produto::where('codigo_barras', $this->barcode)->where('empresa_id', Auth::user()->empresa_id)->first();

        if ($produto) {
            $cartKey = collect($this->cart)->search(fn($item) => $item['id'] === $produto->id);
            if ($cartKey !== false) {
                $this->cart[$cartKey]['qtd'] += $this->quantidade;
            } else {
                $this->cart[] = ['id' => $produto->id, 'nome' => $produto->nome, 'preco' => (float) $produto->preco_venda, 'qtd' => (int) $this->quantidade];
            }
            $this->recalcularTotal();
            $this->reset('barcode', 'quantidade');
            $this->dispatch('produto-adicionado');
        } else {
            $this->mensagemErro = 'Produto não encontrado.';
        }
    }

    public function removerItem($cartKey)
    {
        if (isset($this->cart[$cartKey]) && !$this->vendaFinalizada) {
            unset($this->cart[$cartKey]);
            $this->cart = array_values($this->cart);
            $this->recalcularTotal();
        }
    }

    public function recalcularTotal()
    {
        $this->subtotal = collect($this->cart)->sum(fn($item) => $item['preco'] * $item['qtd']);
        $this->total = $this->subtotal - $this->descontoTotal;
        $this->recalcularPagamentos();
    }

    public function recalcularPagamentos()
    {
        $this->valorRecebido = collect($this->pagamentos)->sum('valor');
        $diferenca = $this->valorRecebido - $this->total;
        $this->troco = $diferenca > 0 ? $diferenca : 0.00;
        $this->faltaPagar = $diferenca < 0 ? abs($diferenca) : 0.00;
    }

    public function addPagamento()
    {
        $this->validate(['valorPagamentoAtual' => 'required|numeric|min:0.01', 'formaPagamentoAtual' => 'required|exists:forma_pagamentos,id']);
        $forma = $this->formasPagamento->find($this->formaPagamentoAtual);
        $this->pagamentos[] = ['forma_pagamento_id' => $forma->id, 'nome' => $forma->nome, 'valor' => (float) $this->valorPagamentoAtual];
        $this->recalcularPagamentos();
        $this->valorPagamentoAtual = $this->faltaPagar > 0 ? number_format($this->faltaPagar, 2, '.', '') : '';
        $this->formaPagamentoAtual = $this->formasPagamento->first()->id ?? null;
    }

    public function removerPagamento($index)
    {
        if (isset($this->pagamentos[$index])) {
            unset($this->pagamentos[$index]);
            $this->pagamentos = array_values($this->pagamentos);
            $this->recalcularPagamentos();
        }
    }

    public function identificarCliente()
    {
        $this->resetErrorBag();
        if (empty($this->documentoCliente)) { $this->removerCliente(); return; }
        $documentoLimpo = preg_replace('/[^0-9]/', '', $this->documentoCliente);
        $cliente = Cliente::where('empresa_id', Auth::user()->empresa_id)->where('cpf_cnpj', $documentoLimpo)->first();
        if ($cliente) {
            $this->clienteId = $cliente->id;
            $this->clienteNome = $cliente->nome;
            $this->documentoCliente = $documentoLimpo;
            $this->showIdentificarModal = false; 
            $this->showOptionsMenu = false;      
        } else {
            $this->addError('finalizacao', 'Cliente não encontrado com este CPF/CNPJ.');
            $this->documentoCliente = '';
        }
    }

    public function removerCliente() 
    { 
        $this->reset('clienteId', 'clienteNome', 'documentoCliente'); 
    }

    public function abrirModalPagamento() 
    { 
        if(empty($this->cart) || $this->vendaFinalizada) return; 
        $this->recalcularPagamentos(); 
        $this->valorPagamentoAtual = $this->faltaPagar > 0 ? number_format($this->faltaPagar, 2, '.', '') : ''; 
        $this->showPaymentModal = true; 
    }
    
    public function fecharModalPagamento() { $this->showPaymentModal = false; }
    public function abrirMenuOpcoes() { $this->showOptionsMenu = true; }
    public function fecharMenuOpcoes() { $this->showOptionsMenu = false; }
    public function abrirModalDesconto() { $this->showOptionsMenu = false; $this->showDescontoModal = true; }
    public function fecharModalPin() { $this->showPinModal = false; }

    public function solicitarAutorizacao($acao, $parametros = null)
    {
        $this->acaoParaAutorizar = $acao;
        $this->parametrosDaAcao = $parametros;
        $this->pinIncorreto = false;
        $this->supervisorPin = '';
        $this->showPinModal = true;
    }

    public function verificarPin()
    {
        $pinCorreto = '1234';
        if ($this->supervisorPin === $pinCorreto) {
            $this->showPinModal = false;
            $this->pinIncorreto = false;
            if (method_exists($this, $this->acaoParaAutorizar)) {
                $this->{$this->acaoParaAutorizar}($this->parametrosDaAcao);
            }
            $this->reset('acaoParaAutorizar', 'parametrosDaAcao');
        } else {
            $this->pinIncorreto = true;
            $this->supervisorPin = '';
            $this->addError('pin', 'PIN incorreto!');
        }
    }

    public function aplicarDesconto()
    {
        $this->validate(['descontoValor' => 'required|numeric|min:0']);
        $valor = (float) $this->descontoValor;
        if ($this->descontoTipo == 'valor') {
            $this->descontoTotal = $valor > $this->subtotal ? $this->subtotal : $valor;
        } else {
            $this->descontoTotal = $this->subtotal * ($valor / 100);
        }
        $this->recalcularTotal();
        $this->showDescontoModal = false;
        $this->reset('descontoValor', 'descontoTipo');
    }

    public function handleF8()
    {
        if ($this->vendaFinalizada || empty($this->cart)) return;
        if ($this->showPaymentModal) {
            if ($this->faltaPagar <= 0) {
                $this->finalizarVenda(app(NFCeService::class));
            } else {
                $this->addPagamento();
            }
        } else {
            $this->abrirModalPagamento();
        }
    }

    public function finalizarVenda(NFCeService $nfceService)
    {
        if (!$this->caixaSessao) { $this->addError('finalizacao', 'Nenhum caixa aberto.'); return; }
        $this->resetErrorBag();

        DB::beginTransaction();
        try {
            $venda = Venda::create([
                'caixa_id' => $this->caixaSessao->id,
                'empresa_id' => Auth::user()->empresa_id,
                'user_id' => Auth::id(),
                'cliente_id' => $this->clienteId,
                'subtotal' => $this->subtotal,
                'desconto' => $this->descontoTotal,
                'total' => $this->total,
                'status' => 'concluida',
            ]);

            foreach ($this->cart as $item) {
                $venda->items()->create(['produto_id' => $item['id'], 'descricao_produto' => $item['nome'], 'quantidade' => $item['qtd'], 'preco_unitario' => $item['preco'], 'subtotal_item' => $item['preco'] * $item['qtd']]);
            }
            
            foreach ($this->pagamentos as $pagamento) {
                $venda->pagamentos()->create(['empresa_id' => Auth::user()->empresa_id, 'forma_pagamento_id' => $pagamento['forma_pagamento_id'], 'valor' => $pagamento['valor']]);
            }

            $venda->load('items.produto.dadosFiscais', 'empresa', 'cliente', 'pagamentos.formaPagamento');
            $resultado = $nfceService->emitir($venda);

            if ($resultado['success']) {
                $venda->update(['nfe_chave_acesso' => $resultado['chave']]);
                DB::commit();
                $this->vendaFinalizada = true;
                $this->dadosUltimaNfce = $resultado;
                $this->showPaymentModal = false;
            } else {
                throw new \Exception($resultado['message']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('finalizacao', 'Erro: ' . $e->getMessage());
        }
    }

    public function resetarPdv()
    {
        $this->reset();
        $this->mount();
        $this->removerCliente();
        $this->dispatch('pdv-resetado');
    }

    public function render()
    {
        return view('livewire.pdv-caixa');
    }
}