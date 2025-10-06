<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\FormaPagamento; // Importante: Adicionar o Model
use App\Services\NFCeService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PdvCaixa extends Component
{
    // Itens e totais da venda
    public $cart = [];
    public $total = 0.00;

    // Inputs da interface
    public $barcode = '';
    public $quantidade = 1;

    // --- PROPRIEDADES PARA O SISTEMA DE PAGAMENTO ---
    public $formasPagamento = [];
    public $pagamentos = [];
    public $valorRecebido = 0.00;
    public $troco = 0.00;
    public $faltaPagar = 0.00;
    public $showPaymentModal = false;
    public $valorPagamentoAtual;
    public $formaPagamentoAtual;
    // ------------------------------------------------

    // Mensagens e estado da UI
    public $mensagemErro = '';
    public $vendaFinalizada = false;
    public $dadosUltimaNfce = [];

    protected $listeners = ['resetPdv' => 'resetarPdv'];

    /**
     * Executado quando o componente é inicializado.
     */
    public function mount()
    {
        // Carrega as formas de pagamento disponíveis uma única vez
        $this->formasPagamento = FormaPagamento::where('empresa_id', Auth::user()->empresa_id)
                                               ->where('ativo', true)
                                               ->orderBy('nome')
                                               ->get();
        // Define uma forma de pagamento padrão para o select
        $this->formaPagamentoAtual = $this->formasPagamento->first()->id ?? null;
    }

    /**
     * Adiciona um produto ao carrinho pelo código de barras.
     */
    public function addProduto()
    {
        $this->reset('mensagemErro');
        if ($this->vendaFinalizada) return;

        if (empty($this->barcode)) {
            return;
        }

        $produto = Produto::where('codigo_barras', $this->barcode)
                          ->where('empresa_id', Auth::user()->empresa_id)
                          ->first();

        if ($produto) {
            $cartKey = collect($this->cart)->search(fn($item) => $item['id'] === $produto->id);

            if ($cartKey !== false) {
                $this->cart[$cartKey]['qtd'] += $this->quantidade;
            } else {
                $this->cart[] = [
                    'id' => $produto->id,
                    'nome' => $produto->nome,
                    'preco' => (float) $produto->preco_venda,
                    'qtd' => (int) $this->quantidade,
                ];
            }
            
            $this->recalcularTotal();
            $this->reset('barcode', 'quantidade');
            $this->dispatch('produto-adicionado');
        } else {
            $this->mensagemErro = 'Produto não encontrado.';
        }
    }

    /**
     * Remove um item do carrinho.
     */
    public function removerItem($cartKey)
    {
        if (isset($this->cart[$cartKey]) && !$this->vendaFinalizada) {
            unset($this->cart[$cartKey]);
            $this->cart = array_values($this->cart);
            $this->recalcularTotal();
        }
    }

    /**
     * Recalcula o valor total da venda e os pagamentos.
     */
    public function recalcularTotal()
    {
        $this->total = collect($this->cart)->sum(fn($item) => $item['preco'] * $item['qtd']);
        $this->recalcularPagamentos();
    }

    // --- MÉTODOS NOVOS E ATUALIZADOS PARA O PAGAMENTO ---

    /**
     * Abre o modal de pagamentos.
     */
    public function abrirModalPagamento()
    {
        if(empty($this->cart) || $this->vendaFinalizada) return;
        $this->recalcularPagamentos();
        $this->valorPagamentoAtual = $this->faltaPagar > 0 ? number_format($this->faltaPagar, 2, '.', '') : '';
        $this->showPaymentModal = true;
    }

    /**
     * Fecha o modal de pagamentos.
     */
    public function fecharModalPagamento()
    {
        $this->showPaymentModal = false;
    }
    
    /**
     * Adiciona um pagamento à lista da venda atual.
     */
    public function addPagamento()
    {
        $this->validate([
            'valorPagamentoAtual' => 'required|numeric|min:0.01',
            'formaPagamentoAtual' => 'required|exists:forma_pagamentos,id',
        ]);

        $forma = $this->formasPagamento->find($this->formaPagamentoAtual);

        $this->pagamentos[] = [
            'forma_pagamento_id' => $forma->id,
            'nome' => $forma->nome,
            'valor' => (float) $this->valorPagamentoAtual,
        ];

        $this->recalcularPagamentos();
        // Prepara o campo de valor para o próximo pagamento
        $this->valorPagamentoAtual = $this->faltaPagar > 0 ? number_format($this->faltaPagar, 2, '.', '') : '';
        $this->formaPagamentoAtual = $this->formasPagamento->first()->id ?? null;
    }

    /**
     * Remove um pagamento da lista.
     */
    public function removerPagamento($index)
    {
        if (isset($this->pagamentos[$index])) {
            unset($this->pagamentos[$index]);
            $this->pagamentos = array_values($this->pagamentos);
            $this->recalcularPagamentos();
        }
    }

    /**
     * Recalcula os totais de pagamento (recebido, falta, troco).
     */
    public function recalcularPagamentos()
    {
        $this->valorRecebido = collect($this->pagamentos)->sum('valor');
        $diferenca = $this->valorRecebido - $this->total;

        $this->troco = $diferenca > 0 ? $diferenca : 0.00;
        $this->faltaPagar = $diferenca < 0 ? abs($diferenca) : 0.00;
    }

    /**
     * Método ATUALIZADO para finalizar a venda.
     */
    public function finalizarVenda(NFCeService $nfceService)
    {
        if ($this->faltaPagar > 0.00 || empty($this->pagamentos)) {
            $this->addError('finalizacao', 'O valor pago é menor que o total da venda.');
            return;
        }

        DB::beginTransaction();
        try {
            // 1. Salva a Venda no banco de dados
            $venda = Venda::create([
                'empresa_id' => Auth::user()->empresa_id,
                'user_id' => Auth::id(),
                'cliente_id' => null,
                'subtotal' => $this->total,
                'desconto' => 0,
                'total' => $this->total,
                'status' => 'concluida',
            ]);

            // 2. Salva os Itens da Venda
            foreach ($this->cart as $item) {
                $venda->items()->create([
                    'produto_id' => $item['id'],
                    'descricao_produto' => $item['nome'],
                    'quantidade' => $item['qtd'],
                    'preco_unitario' => $item['preco'],
                    'subtotal_item' => $item['preco'] * $item['qtd'],
                ]);
            }
            
            // 3. Salva os Pagamentos da Venda
            foreach ($this->pagamentos as $pagamento) {
                $venda->pagamentos()->create([
                    'empresa_id' => Auth::user()->empresa_id,
                    'forma_pagamento_id' => $pagamento['forma_pagamento_id'],
                    'valor' => $pagamento['valor'],
                ]);
            }

            // 4. Prepara o objeto Venda para o serviço de emissão
            $venda->load('items.produto.dadosFiscais', 'empresa', 'cliente', 'pagamentos.formaPagamento');

            // 5. Chama o serviço para emitir a NFC-e
            $resultado = $nfceService->emitir($venda);

            if ($resultado['success']) {
                $venda->update(['nfe_chave_acesso' => $resultado['chave']]);
                DB::commit();

                $this->vendaFinalizada = true;
                $this->dadosUltimaNfce = $resultado;
                $this->showPaymentModal = false; // Fecha o modal após sucesso
            } else {
                throw new \Exception($resultado['message']);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            $this->addError('finalizacao', 'Erro: ' . $e->getMessage());
        }
    }

    /**
     * Limpa todos os dados para uma nova venda.
     */
    public function resetarPdv()
    {
        $this->reset();
        $this->mount(); // Re-inicializa o componente para buscar as formas de pagamento
        $this->dispatch('pdv-resetado');
    }

    public function render()
    {
        return view('livewire.pdv-caixa');
    }
}