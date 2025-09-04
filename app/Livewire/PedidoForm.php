<?php

namespace App\Livewire;

use App\Models\Venda;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class PedidoForm extends Component
{
    public Venda $venda;
    public $cart = [];
    public $pagamentos = [];
    public $total, $subtotal, $desconto; // E outras propriedades que precisar

    // Propriedades para a busca de produtos
    public $produtoSearch = '';
    public $produtosEncontrados = [];

    // Propriedades para o modal de pagamentos
    public $showPagamentoModal = false;
    public $formaPagamentoSelecionada = 'dinheiro';
    public $valorPagamentoAtual = null;
    public $valorRecebido = 0;
    public $troco = 0;
    public $faltaPagar = 0;

    public function mount(Venda $venda)
    {
        $this->venda = $venda;
        $this->carregarItens();
        $this->calcularTotais();
    }

    public function carregarItens()
    {
        // Converte os itens do Eloquent para um array simples, como no PDV
        foreach ($this->venda->items as $item) {
            $this->cart[] = [
                'id' => $item->produto_id,
                'nome' => $item->descricao_produto,
                'preco' => $item->preco_unitario,
                'quantidade' => $item->quantidade,
                'estoque_atual' => $item->produto->estoque_atual + $item->quantidade, // Soma o que está na venda ao estoque atual para cálculo
            ];
        }
    }

    // AQUI VOCÊ PODE COPIAR OS MÉTODOS DO SEU PDV PARA MANIPULAR O CARRINHO
    // Ex: updatedProdutoSearch, adicionarProduto, removerProduto, aumentarQuantidade, calcularTotais, etc.
    // E também os métodos de pagamento: adicionarPagamento, removerPagamento, etc.

    public function finalizarPedido()
    {
        // Validação: Exige que o pagamento seja informado e completo
        if(collect($this->pagamentos)->sum('valor') < $this->total) {
            session()->flash('error', 'O valor dos pagamentos é inferior ao total do pedido.');
            return;
        }

        DB::beginTransaction();
        try {
            // Atualiza o cabeçalho da venda (ex: totais, observações)
            $this->venda->update([
                'subtotal' => $this->subtotal,
                'desconto' => $this->desconto,
                'total' => $this->total,
                'status' => 'concluida', // Agora sim, o pedido é finalizado!
            ]);

            // Sincroniza os itens e baixa o estoque
            $this->venda->items()->delete(); // Apaga os itens antigos
            foreach ($this->cart as $item) {
                $this->venda->items()->create([
                    'produto_id' => $item['id'],
                    'descricao_produto' => $item['nome'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'subtotal_item' => $item['preco'] * $item['quantidade'],
                ]);

                // Baixa o estoque e registra o movimento (LÓGICA FINAL)
                $produto = Produto::find($item['id']);
                if($produto){
                    $saldoAnterior = $produto->estoque_atual;
                    $produto->decrement('estoque_atual', $item['quantidade']);
                    //... (adicionar a inserção em estoque_movimentos aqui) ...
                }
            }

            // Salva os pagamentos
            $this->venda->pagamentos()->delete();
            foreach($this->pagamentos as $pagamento){
                 $this->venda->pagamentos()->create([
                    'empresa_id' => $this->venda->empresa_id,
                    'forma_pagamento' => $pagamento['forma'],
                    'valor' => $pagamento['valor'],
                 ]);
            }

            DB::commit();
            
            return redirect()->route('pedidos.index')->with('success', 'Pedido #' . $this->venda->id . ' salvo e finalizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erro ao finalizar o pedido: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pedido-form');
    }
}