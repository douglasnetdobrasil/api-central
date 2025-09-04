<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Produto;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Venda;
use App\Models\Pagamento;

class Pdv extends Component
{
    // Bloco 1: Cliente
    public string $clienteSearch = '';
    public ?Cliente $clienteSelecionado = null;
    public $clientesEncontrados = [];
    public int $highlightClienteIndex = -1;

    // Bloco 2: Produtos
    public array $cart = [];
    public string $produtoSearch = '';
    public $produtosEncontrados = [];
    public int $highlightProdutoIndex = -1;
    
    // Bloco 3: Totais e Finalização
    public float $subtotal = 0;
    public float $desconto = 0;
    public string $tipoDesconto = 'valor';
    public float $descontoCalculado = 0;
    public float $total = 0;
    public string $observacoes = '';
    public bool $showDesconto = false;
    public string $textoBotaoFinalizar = 'Finalizar Venda';

    // Bloco 4: Pagamentos
    public bool $showPagamentoModal = false;
    public array $pagamentos = [];
    public float $valorRecebido = 0;
    public float $troco = 0;
    public float $faltaPagar = 0;
    public string $formaPagamentoSelecionada = 'dinheiro';
    public $valorPagamentoAtual = null;

    public function mount()
    {
        $config = DB::table('configuracoes')
                    ->where('chave', 'baixar_estoque_pdv')
                    ->first();

        if ($config && $config->valor === 'false') {
            $this->textoBotaoFinalizar = 'Finalizar Pré-venda';
        }
    }
    
    // --- MÉTODOS DE NAVEGAÇÃO POR TECLADO ---
    public function selecionarClienteComEnter()
    {
        $cliente = $this->clientesEncontrados[$this->highlightClienteIndex] ?? null;
        if ($cliente) {
            $this->selecionarCliente($cliente);
        }
    }

    public function selecionarProdutoComEnter()
    {
        $produto = $this->produtosEncontrados[$this->highlightProdutoIndex] ?? null;
        if ($produto) {
            $this->adicionarProduto($produto);
        }
    }

    // --- MÉTODOS PARA CLIENTES ---
    public function updatedClienteSearch($value)
    {
        $this->highlightClienteIndex = -1;
        if (strlen($value) >= 1) {
            $this->clientesEncontrados = Cliente::where('id', $value)
                ->orWhere('nome', 'like', '%' . $value . '%')
                ->orWhere('cpf_cnpj', 'like', '%' . $value . '%')
                ->limit(5)->get();
        } else {
            $this->clientesEncontrados = [];
        }
    }

    public function selecionarCliente(Cliente $cliente)
    {
        $this->clienteSelecionado = $cliente;
        $this->clienteSearch = '';
        $this->clientesEncontrados = [];
    }
    
    public function removerCliente()
    {
        $this->clienteSelecionado = null;
    }

    // --- MÉTODOS PARA PRODUTOS E CARRINHO ---
    public function updatedProdutoSearch($value)
    {
        if (strlen($value) >= 1) {
            $this->produtosEncontrados = Produto::where('id', $value)
                ->orWhere('nome', 'like', '%' . $value . '%')
                ->orWhere('codigo_barras', $value)
                ->where('estoque_atual', '>', 0)
                ->limit(5)->get();
        } else {
            $this->produtosEncontrados = [];
        }
    }

    public function adicionarProduto(Produto $produto)
    {
        $cartIndex = collect($this->cart)->search(fn ($item) => $item['id'] === $produto->id);
    
        if ($cartIndex !== false) {
            $this->aumentarQuantidade($cartIndex);
        } else {
            $this->cart[] = [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => $produto->preco_venda,
                'quantidade' => 1,
                'estoque_atual' => $produto->estoque_atual,
            ];
        }
    
        $this->produtoSearch = '';
        $this->produtosEncontrados = [];
        $this->calcularTotais();
    }
    
    public function removerProduto($cartIndex)
    {
        unset($this->cart[$cartIndex]);
        $this->cart = array_values($this->cart);
        $this->calcularTotais();
    }

    public function aumentarQuantidade($cartIndex)
    {
        if ($this->cart[$cartIndex]['quantidade'] < $this->cart[$cartIndex]['estoque_atual']) {
            $this->cart[$cartIndex]['quantidade']++;
            $this->calcularTotais();
        }
    }
    
    public function diminuirQuantidade($cartIndex)
    {
        if ($this->cart[$cartIndex]['quantidade'] > 1) {
            $this->cart[$cartIndex]['quantidade']--;
            $this->calcularTotais();
        }
    }

    // --- MÉTODOS DE CÁLCULO, DESCONTO E FINALIZAÇÃO ---
    public function toggleDesconto()
    {
        $this->showDesconto = !$this->showDesconto;
        if (!$this->showDesconto) {
            $this->desconto = 0;
            $this->calcularTotais();
        }
    }

    public function updatedDesconto($value)
    {
        $value = floatval($value);
        if ($this->tipoDesconto === 'valor') {
            $this->desconto = max(0, min($value, $this->subtotal));
        } elseif ($this->tipoDesconto === 'percentual') {
            $this->desconto = max(0, min($value, 100));
        }
        $this->calcularTotais();
    }
    
    public function definirTipoDesconto(string $tipo)
    {
        $this->tipoDesconto = $tipo;
        $this->desconto = 0;
        $this->calcularTotais();
    }
    
    public function calcularTotais()
    {
        $this->subtotal = 0;
        foreach ($this->cart as $index => $item) {
            $total_item = $item['preco'] * $item['quantidade'];
            $this->cart[$index]['total_item'] = $total_item;
            $this->subtotal += $total_item;
        }

        if ($this->tipoDesconto === 'valor') {
            $this->descontoCalculado = $this->desconto;
        } elseif ($this->tipoDesconto === 'percentual') {
            $this->descontoCalculado = ($this->subtotal * $this->desconto) / 100;
        } else {
            $this->descontoCalculado = 0;
        }
        $this->descontoCalculado = min($this->descontoCalculado, $this->subtotal);
        $this->total = $this->subtotal - $this->descontoCalculado;
    }
    
    /**
     * Ponto de entrada ao clicar no botão "Finalizar Venda".
     * Agora ele apenas decide se abre o modal ou salva uma pré-venda.
     */
    public function finalizarVenda()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Adicione pelo menos um produto à venda.');
            return;
        }

        $config = DB::table('configuracoes')->where('chave', 'baixar_estoque_pdv')->first();
        $baixarEstoqueAgora = !$config || $config->valor === 'true' || $config->valor === '1';

        if ($baixarEstoqueAgora) {
            // Ação 1: É VENDA DIRETA -> Prepara os dados e ABRE O MODAL
            $this->resetarPagamentos();
            $this->faltaPagar = $this->total;
            $this->valorPagamentoAtual = number_format($this->total, 2, '.', '');
            $this->showPagamentoModal = true;
        } else {
            // Ação 2: É PRÉ-VENDA -> Salva sem pedir pagamento
            $this->salvarPreVenda();
        }
    }

    /**
     * Contém a lógica para salvar a venda como "pendente" sem baixar o estoque.
     */
    private function salvarPreVenda()
    {
        DB::beginTransaction();
        try {
            $venda = Venda::create([
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'cliente_id' => $this->clienteSelecionado?->id,
                'subtotal' => $this->subtotal,
                'desconto' => $this->descontoCalculado,
                'total' => $this->total,
                'observacoes' => $this->observacoes,
                'status' => 'pendente',
            ]);

            foreach ($this->cart as $item) {
                $venda->items()->create([
                    'produto_id' => $item['id'],
                    'descricao_produto' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'subtotal_item' => $item['total_item'],
                ]);
            }

            DB::commit();
            session()->flash('success', 'Pré-venda salva com sucesso!');
            return redirect()->route('pedidos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao salvar pré-venda: ' . $e->getMessage());
        }
    }

    /**
     * Chamado pelo botão "Confirmar Venda" DENTRO DO MODAL.
     * Salva a venda, os pagamentos e baixa o estoque.
     */
    public function confirmarVendaComPagamentos()
    {
        if ($this->faltaPagar > 0) {
             session()->flash('error_modal', 'O valor recebido é menor que o total da venda.');
             return;
        }

        DB::beginTransaction();
        try {
            $venda = Venda::create([
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'cliente_id' => $this->clienteSelecionado?->id,
                'subtotal' => $this->subtotal,
                'desconto' => $this->descontoCalculado,
                'total' => $this->total,
                'observacoes' => $this->observacoes,
                'status' => 'concluida',
            ]);

            foreach($this->pagamentos as $pagamento) {
                $venda->pagamentos()->create([
                    'empresa_id' => $venda->empresa_id,
                    'forma_pagamento' => $pagamento['forma'],
                    'valor' => $pagamento['valor'],
                    'parcelas' => 1,
                ]);
            }
            
            foreach ($this->cart as $item) {
                $venda->items()->create([
                    'produto_id' => $item['id'],
                    'descricao_produto' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'subtotal_item' => $item['total_item'],
                ]);

                $produto = Produto::find($item['id']);
                if ($produto) {
                    $saldoAnterior = $produto->estoque_atual;
                    $produto->decrement('estoque_atual', $item['quantidade']);
                    $saldoNovo = $produto->fresh()->estoque_atual;

                    DB::table('estoque_movimentos')->insert([
                        'empresa_id' => auth()->user()->empresa_id,
                        'produto_id' => $produto->id,
                        'user_id' => auth()->id(),
                        'tipo_movimento' => 'saida_venda',
                        'quantidade' => $item['quantidade'],
                        'saldo_anterior' => $saldoAnterior,
                        'saldo_novo' => $saldoNovo,
                        'origem_id' => $venda->id,
                        'origem_type' => Venda::class,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();
            session()->flash('success', 'Venda finalizada com sucesso!');
            return redirect()->route('pedidos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'ERRO AO FINALIZAR VENDA: ' . $e->getMessage());
        }
    }

    // --- MÉTODOS AUXILIARES DE PAGAMENTO ---
    public function adicionarPagamento()
    {
        $valor = (float)str_replace(',', '.', $this->valorPagamentoAtual);

        if ($valor <= 0.009) return;

        $this->pagamentos[] = [
            'forma' => $this->formaPagamentoSelecionada,
            'valor' => $valor,
        ];
        $this->recalcularValoresPagamento();
    }

    public function removerPagamento($index)
    {
        unset($this->pagamentos[$index]);
        $this->pagamentos = array_values($this->pagamentos);
        $this->recalcularValoresPagamento();
    }

    private function recalcularValoresPagamento()
    {
        $this->valorRecebido = collect($this->pagamentos)->sum('valor');
        $this->faltaPagar = $this->total - $this->valorRecebido;
        $this->troco = 0;

        if ($this->faltaPagar < 0) {
            $this->troco = abs($this->faltaPagar);
            $this->faltaPagar = 0;
        }

        $this->valorPagamentoAtual = number_format($this->faltaPagar > 0 ? $this->faltaPagar : 0, 2, '.', '');
    }

    private function resetarPagamentos()
    {
        $this->pagamentos = [];
        $this->valorRecebido = 0;
        $this->troco = 0;
        $this->faltaPagar = $this->total;
    }

    public function render()
    {
        return view('livewire.pdv')
            ->layout('layouts.app');
    }
}