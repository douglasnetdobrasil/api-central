<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Venda;
use App\Models\Pagamento;
use App\Models\FormaPagamento; // 1. IMPORTAMOS O MODEL DE FORMAS DE PAGAMENTO
use Livewire\Component;

use Illuminate\Support\Facades\DB;

class Pdv extends Component
{
    // Bloco 1: Cliente
    public string $clienteSearch = '';
    public ?Cliente $clienteSelecionado = null;
    public $clientesEncontrados = [];

    // Bloco 2: Produtos
    public array $cart = [];
    public string $produtoSearch = '';
    public $produtosEncontrados = [];
    
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
    public $formaPagamentoSelecionada = null; // 2. AGORA ARMAZENA O ID
    public $valorPagamentoAtual = null;

    // 3. NOVA PROPRIEDADE PARA GUARDAR AS OPÇÕES DE PAGAMENTO DO BANCO
    public $formasPagamentoOpcoes = [];

    public function mount()
    {
        $config = DB::table('configuracoes')->where('chave', 'baixar_estoque_pdv')->first();
        if ($config && $config->valor === 'false') {
            $this->textoBotaoFinalizar = 'Finalizar Pré-venda';
        }

        // 4. BUSCA AS FORMAS DE PAGAMENTO ATIVAS QUANDO O PDV É CARREGADO
        $this->formasPagamentoOpcoes = FormaPagamento::where('ativo', true)->orderBy('nome')->get();
        // Define um valor padrão para a primeira forma de pagamento da lista
        $this->formaPagamentoSelecionada = $this->formasPagamentoOpcoes->first()->id ?? null;
    }
    
    // --- MÉTODOS PARA CLIENTES ---
    // (Seus métodos de cliente, como updatedClienteSearch, selecionarCliente, etc., permanecem aqui, sem alterações)
    
    // --- MÉTODOS PARA PRODUTOS E CARRINHO ---
    // (Seus métodos de produto, como updatedProdutoSearch, adicionarProduto, etc., permanecem aqui, sem alterações)

    // --- MÉTODOS DE CÁLCULO, DESCONTO E FINALIZAÇÃO ---
    // (Seus métodos de cálculo, como calcularTotais, etc., permanecem aqui, sem alterações)

    public function finalizarVenda()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Adicione pelo menos um produto à venda.');
            return;
        }

        $config = DB::table('configuracoes')->where('chave', 'baixar_estoque_pdv')->first();
        $baixarEstoqueAgora = !$config || $config->valor === 'true' || $config->valor === '1';

        if ($baixarEstoqueAgora) {
            $this->resetarPagamentos();
            $this->faltaPagar = $this->total;
            $this->valorPagamentoAtual = number_format($this->total, 2, '.', '');
            $this->showPagamentoModal = true;
        } else {
            $this->salvarPreVenda();
        }
    }

    private function salvarPreVenda()
    {
        // (Seu método de salvar pré-venda permanece aqui, sem alterações)
    }
    
    public function confirmarVendaComPagamentos()
    {
        if ($this->faltaPagar > 0.009) { // Adicionada uma pequena margem para arredondamento
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
                // 5. CORREÇÃO AO SALVAR O PAGAMENTO
                // Agora salva o ID da forma de pagamento na coluna correta
                $venda->pagamentos()->create([
                    'empresa_id' => $venda->empresa_id,
                    'forma_pagamento_id' => $pagamento['id'], // <- CORRIGIDO
                    'valor' => $pagamento['valor'],
                    // Adicione outras colunas se sua tabela 'venda_pagamentos' tiver
                ]);
            }
            
            // ... (resto da sua lógica de salvar itens e baixar estoque permanece igual)

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

        // 6. LÓGICA ATUALIZADA PARA ADICIONAR PAGAMENTO
        // Busca a forma de pagamento completa para pegar o nome e o código
        $formaPagamento = FormaPagamento::find($this->formaPagamentoSelecionada);
        if (!$formaPagamento) return; // Se não encontrar, não faz nada

        $this->pagamentos[] = [
            'id' => $formaPagamento->id, // Guarda o ID para salvar no banco
            'nome' => $formaPagamento->nome, // Guarda o nome para exibir na tela
            'valor' => $valor,
            'codigo_sefaz' => $formaPagamento->codigo_sefaz, // Guarda o código para a NF-e
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