<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Produto;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use App\Models\Venda;



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

    public function mount()
    {
        $config = DB::table('configuracoes')
                    ->where('chave', 'baixar_estoque_pdv')
                    ->first();

        // Se a configuração for 'false', muda o texto do botão
        if ($config && $config->valor === 'false') {
            $this->textoBotaoFinalizar = 'Finalizar Pré-venda';
        }
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
                ->where('estoque_atual', '>', 0) // <-- CORRIGIDO
                ->limit(5)->get();
        } else {
            $this->produtosEncontrados = [];
        }
    }

    public function adicionarProduto(Produto $produto)
    {
        $cartIndex = collect($this->cart)->search(fn ($item) => $item['id'] === $produto->id);
    
        if ($cartIndex !== false) {
            // A lógica de aumentar a quantidade já está no método aumentarQuantidade()
            $this->aumentarQuantidade($cartIndex);
        } else {
            $this->cart[] = [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => $produto->preco_venda,
                'quantidade' => 1,
                'estoque_atual' => $produto->estoque_atual, // <-- CORRIGIDO
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

    // --- MÉTODOS DE CÁLCULO E FINALIZAÇÃO ---
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
        // VERSÃO CORRIGIDA E ROBUSTA DO MÉTODO
        $this->subtotal = 0;
        foreach ($this->cart as $index => $item) {
            // Primeiro, calcula e define a chave 'total_item'
            $total_item = $item['preco'] * $item['quantidade'];
            $this->cart[$index]['total_item'] = $total_item;
            // Depois, soma ao subtotal
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

    public function finalizarVenda()
    {
        // 1. Validação inicial
        if (empty($this->cart)) {
            session()->flash('error', 'Adicione pelo menos um produto à venda.');
            return;
        }

        // 2. Ler a configuração do sistema
        $config = DB::table('configuracoes')
                    ->where('chave', 'baixar_estoque_pdv')
                    ->first();

        // LÓGICA MELHORADA: Aceita 'true' ou o número 1 como verdadeiro.
        // Se a configuração não existir, assume 'true' como padrão.
        $baixarEstoqueAgora = !$config || $config->valor === 'true' || $config->valor === '1';
        
        // --- DEBUG: Mensagem para confirmar a ação ---
        // session()->flash('info', 'A configuração para baixar estoque é: ' . ($baixarEstoqueAgora ? 'VERDADEIRO' : 'FALSO'));
        // return; // Pode descomentar estas duas linhas temporariamente para testar apenas a configuração

        DB::beginTransaction();
        try {
            // 3. Criar a Venda
            $venda = Venda::create([
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'cliente_id' => $this->clienteSelecionado?->id,
                'subtotal' => $this->subtotal,
                'desconto' => $this->descontoCalculado,
                'total' => $this->total,
                'observacoes' => $this->observacoes,
                'status' => $baixarEstoqueAgora ? 'concluida' : 'pendente',
            ]);

            // 4. Salvar Itens e movimentar o estoque (se configurado)
            if ($baixarEstoqueAgora) {
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
                        $saldoNovo = $produto->fresh()->estoque_atual; // .fresh() para garantir que pega o valor atualizado do BD

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
            } else {
                // Se não for para baixar estoque, apenas salva os itens sem tocar no estoque
                 foreach ($this->cart as $item) {
                    $venda->items()->create([
                        'produto_id' => $item['id'],
                        'descricao_produto' => $item['nome'],
                        'quantidade' => $item['quantidade'],
                        'preco_unitario' => $item['preco'],
                        'subtotal_item' => $item['total_item'],
                    ]);
                }
            }


            DB::commit();

            session()->flash('success', 'Venda finalizada com sucesso!');
            return redirect()->route('pedidos.index');

        } catch (\Exception $e) {
            DB::rollBack();
            // MENSAGEM DE ERRO DETALHADA
            session()->flash('error', 'ERRO AO FINALIZAR VENDA: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.pdv')
            ->layout('layouts.app');
    }
}