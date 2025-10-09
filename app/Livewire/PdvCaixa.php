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
use App\Models\Nfe;
use App\Models\CaixaMovimentacao;
use App\Models\User;

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

     // NOVAS PROPRIEDADES PARA CANCELAMENTO DE NFCE
     public $showCancelNfceModal = false;
     public $justificativaCancelamento = '';
     public ?Nfe $ultimaNfeAutorizada = null;

      // NOVAS PROPRIEDADES PARA SANGRIA
    public $showSangriaModal = false;
    public $valorSangria = '';
    public $observacaoSangria = '';

      // NOVAS PROPRIEDADES PARA SUPRIMENTO
      public $showSuprimentoModal = false;
      public $valorSuprimento = '';
      public $observacaoSuprimento = '';

     

    // NOVA PROPRIEDADE PARA GUARDAR O SUPERVISOR
    public ?User $supervisorAutorizado = null;

    

     protected $listeners = ['resetPdv' => 'resetarPdvAutorizado'];

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
        $this->solicitarAutorizacao('removerItemAutorizado', $cartKey);
    }

    public function removerItemAutorizado($cartKey)
    {
        if (isset($this->cart[$cartKey]) && !$this->vendaFinalizada) {
            unset($this->cart[$cartKey]);
            $this->cart = array_values($this->cart);
            $this->recalcularTotal();
            $this->dispatch('produto-adicionado'); // Apenas para focar no input
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
        $this->resetErrorBag();
        $this->pinIncorreto = false;

        if (empty($this->supervisorPin)) {
            $this->addError('pin', 'O PIN não pode estar em branco.');
            $this->pinIncorreto = true;
            return;
        }

        $supervisor = User::where('pin', $this->supervisorPin)->first();

        if ($supervisor && $supervisor->hasRole('Supervisor')) {
            // SUCESSO! AGORA VAMOS SALVAR O SUPERVISOR NA PROPRIEDADE
            $this->supervisorAutorizado = $supervisor;
            
            $this->showPinModal = false;
            
            if (method_exists($this, $this->acaoParaAutorizar)) {
                // A ação (ex: abrirModalSuprimento) é chamada sem parâmetros extras
                $this->{$this->acaoParaAutorizar}($this->parametrosDaAcao);
            }
            
            $this->reset('acaoParaAutorizar', 'parametrosDaAcao', 'supervisorPin');

        } else {
            $this->pinIncorreto = true;
            $this->supervisorPin = '';
            $this->addError('pin', 'PIN inválido ou usuário não é um Supervisor.');
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
        // Se o carrinho estiver vazio, pode resetar sem PIN. Se não, precisa autorizar.
        if(empty($this->cart)){
            $this->resetarPdvAutorizado();
        } else {
            $this->solicitarAutorizacao('resetarPdvAutorizado');
        }
    }

    public function resetarPdvAutorizado()
    {
        $this->reset();
        $this->mount();
        $this->removerCliente();
        $this->dispatch('pdv-resetado');
    }

    public function abrirModalCancelarNfce()
    {
        $this->ultimaNfeAutorizada = Nfe::where('empresa_id', Auth::user()->empresa_id)
                                        ->where('modelo', '65')
                                        ->where('status', 'autorizada')
                                        ->orderBy('created_at', 'desc')
                                        ->first();

        if ($this->ultimaNfeAutorizada) {
            $this->showOptionsMenu = false;
            $this->showCancelNfceModal = true;
            $this->reset('justificativaCancelamento');
        } else {
            // Emite um evento para o front-end mostrar um alerta
            $this->dispatch('show-toast', ['message' => 'Nenhuma NFC-e autorizada encontrada para cancelar.', 'type' => 'error']);
        }
    }

    public function fecharModalCancelarNfce()
    {
        $this->showCancelNfceModal = false;
    }

    public function cancelarUltimaNfce(NFCeService $nfceService)
    {
        $this->validate([
            'justificativaCancelamento' => 'required|min:15|string'
        ], [
            'justificativaCancelamento.min' => 'A justificativa deve ter no mínimo 15 caracteres.'
        ]);

        if ($this->ultimaNfeAutorizada) {
            $resultado = $nfceService->cancelar($this->ultimaNfeAutorizada, $this->justificativaCancelamento);

            if ($resultado['success']) {
                $this->dispatch('show-toast', ['message' => $resultado['message'], 'type' => 'success']);
                $this->fecharModalCancelarNfce();
            } else {
                $this->addError('cancelamento_nfce', $resultado['message']);
            }
        }
    }

    public function trocarOperador()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login'); // Certifique-se que sua rota de login tem o nome 'login'
    }

    public function abrirModalSangria()
    {
        $this->reset('valorSangria', 'observacaoSangria');
        $this->showOptionsMenu = false; // Fecha o menu de opções
        $this->showSangriaModal = true;
    }

    public function fecharModalSangria()
    {
        $this->showSangriaModal = false;
        $this->reset('supervisorAutorizado');
    }

    public function executarSangria() // Removidos os parâmetros ($params = null, User $supervisor = null)
    {
        $this->validate(['valorSangria' => 'required|numeric|min:0.01']);

        CaixaMovimentacao::create([
            'caixa_id' => $this->caixaSessao->id,
            'user_id' => auth()->id(),
            'tipo' => 'SANGRIA',
            'valor' => $this->valorSangria,
            'observacao' => $this->observacaoSangria,
        ]);
        
        // Usamos a propriedade que guardou o supervisor
        $nomeSupervisor = $this->supervisorAutorizado ? $this->supervisorAutorizado->name : 'Supervisor';
        $this->dispatch('show-toast', ['message' => 'Sangria autorizada por ' . $nomeSupervisor . ' e registrada!', 'type' => 'success']);
        $this->fecharModalSangria();
    }

    public function abrirModalSuprimento()
    {
        $this->reset('valorSuprimento', 'observacaoSuprimento');
        $this->showOptionsMenu = false; // Fecha o menu de opções
        $this->showSuprimentoModal = true;
    }

    public function fecharModalSuprimento()
    {
        $this->showSuprimentoModal = false;
        $this->reset('supervisorAutorizado');
    }

    public function executarSuprimento() // Removidos os parâmetros
    {
        $this->validate([
            'valorSuprimento' => 'required|numeric|min:0.01'
        ], [
            'valorSuprimento.required' => 'O valor é obrigatório.',
            'valorSuprimento.min' => 'O valor deve ser positivo.'
        ]);

        try {
            CaixaMovimentacao::create([
                'caixa_id' => $this->caixaSessao->id,
                'user_id' => auth()->id(),
                'tipo' => 'SUPRIMENTO',
                'valor' => $this->valorSuprimento,
                'observacao' => $this->observacaoSuprimento,
            ]);

            $this->dispatch('show-toast', ['message' => 'Suprimento registrado com sucesso!', 'type' => 'success']);
            $this->fecharModalSuprimento();

        } catch (\Exception $e) {
            $this->dispatch('show-toast', ['message' => 'Erro ao registrar o suprimento: ' . $e->getMessage(), 'type' => 'error']);
        }
    }


    public function render()
    {
        return view('livewire.pdv-caixa');
    }
}