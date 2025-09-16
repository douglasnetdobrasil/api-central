<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\FormaPagamento;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Transportadora;
use App\Models\Venda;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class NfeAvulsaCreate extends Component
{
    // PROPRIEDADES FUNDIDAS DO PDV E DA NF-E AVULSA
    public string $clienteSearch = '', $produtoSearch = '', $transportadoraSearch = ''; // Adicionado transportadoraSearch
    public ?Cliente $clienteSelecionado = null;
    public ?Transportadora $transportadoraSelecionada = null; // **PROPRIEDADE ADICIONADA**

    public $clientesEncontrados = [], $produtosEncontrados = [], $transportadorasEncontradas = []; // Adicionado transportadorasEncontradas
    public int $highlightClienteIndex = -1, $highlightProdutoIndex = -1;
    public array $cart = [];
    public float $subtotal = 0, $desconto = 0, $descontoCalculado = 0, $total = 0, $totalNota = 0; // Adicionado totalNota
    public string $tipoDesconto = 'valor';
    public bool $showDesconto = false;
    public ?int $natureza_operacao_id = null;
    public int $serie = 1, $finalidade_emissao = 1, $tipo_operacao = 1, $consumidor_final = 0;
    
    // Propriedades de Transporte
    public int $frete_modalidade = 9; // Renomeado para consistência com o blade
    public float $frete_valor = 0.00;
    public ?int $volume_quantidade = null; // Renomeado
    public string $volume_especie = ''; // Renomeado
    public string $volume_marca = ''; // Adicionado
    public ?float $peso_bruto = 0; // Renomeado
    public ?float $peso_liquido = 0; // Renomeado

    // Propriedades de Totais e Finalização
    public int $totalQuantidadeProdutos = 0; // Adicionado
    public string $informacoes_adicionais = ''; // Adicionado

    public string $observacoes = ''; // Mantido, pode ser usado para observações internas
    public $itemImpostos = [], $pagamentos = [];
    public ?int $indexImpostos = null;
    public bool $showPagamentoModal = false;
    public float $total_base_calculo_icms = 0.00, $total_valor_icms = 0.00, $total_valor_ipi = 0.00;
    public float $valorRecebido = 0, $troco = 0, $faltaPagar = 0;
    public $valorPagamentoAtual = null, $formaPagamentoSelecionada = null;
    public $formasPagamentoOpcoes = [];

    public function testar()
    {
        dd('COMUNICAÇÃO OK! Livewire está funcionando!');
    }

    public function mount()
    {
        $this->formasPagamentoOpcoes = FormaPagamento::where('ativo', true)->orderBy('nome')->get();
        $this->formaPagamentoSelecionada = $this->formasPagamentoOpcoes->first()->id ?? null;
        $this->natureza_operacao_id = NaturezaOperacao::first()->id ?? null;
    }
    
    // --- MÉTODOS DE SELEÇÃO COM ENTER ---
    public function selecionarClienteComEnter() { if (isset($this->clientesEncontrados[$this->highlightClienteIndex])) { $this->selecionarCliente($this->clientesEncontrados[$this->highlightClienteIndex]->id); } }
    public function selecionarProdutoComEnter() { if (isset($this->produtosEncontrados[$this->highlightProdutoIndex])) { $this->adicionarProduto($this->produtosEncontrados[$this->highlightProdutoIndex]->id); } }

    // --- LÓGICA DE CLIENTE ---
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
    public function selecionarCliente($clienteId) { $this->clienteSelecionado = Cliente::find($clienteId); $this->clienteSearch = ''; $this->clientesEncontrados = []; }
    public function removerCliente() { $this->clienteSelecionado = null; }

    // --- LÓGICA DE PRODUTO ---
    public function updatedProdutoSearch($value)
    {
        $this->highlightProdutoIndex = -1;
        if (strlen($value) >= 1) {
            $this->produtosEncontrados = Produto::where('empresa_id', Auth::user()->empresa_id)
                ->where(function ($query) use ($value) {
                    $query->where('id', $value)
                          ->orWhere('nome', 'like', '%' . $value . '%')
                          ->orWhere('codigo_barras', $value);
                })->limit(5)->get();
        } else { $this->produtosEncontrados = []; }
    }

    public function adicionarProduto($produtoId)
    {
        $produto = Produto::find($produtoId);
        if (!$produto) return;

        $cartIndex = collect($this->cart)->search(fn ($item) => $item['id'] === $produto->id);
        if ($cartIndex !== false) {
            $this->aumentarQuantidade($cartIndex);
        } else {
            $this->cart[] = [
                'id' => $produto->id, 'nome' => $produto->nome, 'preco' => $produto->preco_venda, 'quantidade' => 1,
                'estoque_atual' => $produto->estoque_atual, 'ncm' => $produto->ncm, 'unidade' => $produto->unidade,
                'peso_bruto' => $produto->peso_bruto ?? 0, 'peso_liquido' => $produto->peso_liquido ?? 0,
                'cfop' => $produto->cfop_padrao ?? '5102',
                'impostos' => [ /* ... impostos ... */ ]
            ];
        }
        $this->produtoSearch = ''; $this->produtosEncontrados = []; $this->calcularTotais();
    }
    public function removerProduto($cartIndex) { unset($this->cart[$cartIndex]); $this->cart = array_values($this->cart); $this->calcularTotais(); }
    public function aumentarQuantidade($cartIndex) { $this->cart[$cartIndex]['quantidade']++; $this->calcularTotais(); }
    public function diminuirQuantidade($cartIndex) { if ($this->cart[$cartIndex]['quantidade'] > 1) { $this->cart[$cartIndex]['quantidade']--; $this->calcularTotais(); } }

    // --- LÓGICA DA TRANSPORTADORA (NOVOS MÉTODOS) ---
    public function updatedTransportadoraSearch($value)
    {
        if (strlen($value) >= 2) {
            $this->transportadorasEncontradas = Transportadora::where('empresa_id', Auth::user()->empresa_id)
                ->where('ativo', true)
                ->where(function ($query) use ($value) {
                    $query->where('razao_social', 'like', '%' . $value . '%')
                          ->orWhere('cnpj', 'like', '%' . $value . '%');
                })
                ->limit(5)->get();
        } else {
            $this->transportadorasEncontradas = [];
        }
    }
    public function selecionarTransportadora($id) { $this->transportadoraSelecionada = Transportadora::find($id); $this->transportadoraSearch = ''; $this->transportadorasEncontradas = []; }
    public function removerTransportadora() { $this->transportadoraSelecionada = null; }
    
    // --- CÁLCULOS TOTAIS (ATUALIZADO) ---
    public function calcularTotais()
    {
        $subtotalProdutos = 0; $totalBCIcms = 0; $totalValorIcms = 0; $totalValorIpi = 0;
        $totalQuantidade = 0; $pesoBruto = 0; $pesoLiquido = 0;

        foreach ($this->cart as $index => &$item) {
            $item['total_item'] = $item['preco'] * $item['quantidade'];
            $subtotalProdutos += $item['total_item'];
            
            // Soma quantidades e pesos
            $totalQuantidade += $item['quantidade'];
            $pesoBruto += (float)($item['peso_bruto'] ?? 0) * $item['quantidade'];
            $pesoLiquido += (float)($item['peso_liquido'] ?? 0) * $item['quantidade'];

            $totalBCIcms += (float)($item['impostos']['icms_base_calculo'] ?? 0);
            $totalValorIcms += (float)($item['impostos']['icms_valor'] ?? 0);
            $totalValorIpi += (float)($item['impostos']['ipi_valor'] ?? 0);
        }

        $this->subtotal = $subtotalProdutos;
        $this->total_base_calculo_icms = $totalBCIcms; 
        $this->total_valor_icms = $totalValorIcms; 
        $this->total_valor_ipi = $totalValorIpi;
        
        // Atribui os novos totais
        $this->totalQuantidadeProdutos = $totalQuantidade;
        $this->peso_bruto = $pesoBruto;
        $this->peso_liquido = $pesoLiquido;

        if ($this->tipoDesconto === 'valor') { $this->descontoCalculado = (float)$this->desconto; }
        elseif ($this->tipoDesconto === 'percentual') { $this->descontoCalculado = ($this->subtotal * (float)$this->desconto) / 100; }
        else { $this->descontoCalculado = 0; }

        $this->descontoCalculado = min($this->descontoCalculado, $this->subtotal);
        $this->total = $this->subtotal - $this->descontoCalculado + (float)$this->frete_valor;
        $this->totalNota = $this->total; // Garante que totalNota reflita o total final
    }
    
    // --- MÉTODOS DE IMPOSTOS E PAGAMENTO (SEM ALTERAÇÃO) ---
    public function updatedFreteValor() { $this->calcularTotais(); }
    public function abrirModalImpostos($index) { $this->indexImpostos = $index; $this->itemImpostos = $this->cart[$index]['impostos']; }
    public function salvarImpostos() { if ($this->indexImpostos !== null) { $this->cart[$this->indexImpostos]['impostos'] = $this->itemImpostos; $this->fecharModalImpostos(); $this->calcularTotais(); } }
    public function fecharModalImpostos() { $this->indexImpostos = null; $this->itemImpostos = []; }
    public function abrirModalPagamento() { $this->validate(['clienteSelecionado' => 'required', 'cart' => 'required|array|min:1']); $this->resetarPagamentos(); $this->showPagamentoModal = true; }
    private function resetarPagamentos() { $this->pagamentos = []; $this->valorRecebido = 0; $this->troco = 0; $this->faltaPagar = $this->total; $this->valorPagamentoAtual = number_format($this->total, 2, '.', ''); }
    public function adicionarPagamento() { $valor = (float)str_replace(',', '.', $this->valorPagamentoAtual); if ($valor <= 0.009) return; $formaPagamento = FormaPagamento::find($this->formaPagamentoSelecionada); if (!$formaPagamento) { session()->flash('error_modal', 'Forma de pagamento inválida.'); return; } $this->pagamentos[] = ['id' => $formaPagamento->id, 'nome' => $formaPagamento->nome, 'valor' => $valor]; $this->recalcularValoresPagamento(); }
    public function removerPagamento($index) { unset($this->pagamentos[$index]); $this->pagamentos = array_values($this->pagamentos); $this->recalcularValoresPagamento(); }
    private function recalcularValoresPagamento() { $this->valorRecebido = collect($this->pagamentos)->sum('valor'); $this->faltaPagar = $this->total - $this->valorRecebido; $this->troco = 0; if ($this->faltaPagar < 0) { $this->troco = abs($this->faltaPagar); $this->faltaPagar = 0; } $this->valorPagamentoAtual = number_format($this->faltaPagar > 0 ? $this->faltaPagar : 0, 2, '.', ''); }

    // --- MÉTODOS DE FINALIZAÇÃO DA NOTA (A SEREM IMPLEMENTADOS) ---
    public function salvarRascunho()
    {
        // Lógica para salvar a nota com status "rascunho" no banco de dados
        session()->flash('message', 'Rascunho salvo com sucesso!');
    }
    
    public function emitirNFe()
    {
        $this->validate([
            'clienteSelecionado' => 'required',
            'cart' => 'required|array|min:1',
        ]);
        
        // 1. Coletar todos os dados das propriedades públicas ($this->clienteSelecionado, $this->cart, etc)
        // 2. Montar o array/objeto de dados no formato exigido pela sua API de emissão de NF-e
        // 3. Fazer a chamada para a API
        // 4. Tratar o retorno (sucesso ou erro)
        
        session()->flash('message', 'Função de emissão chamada! Implementar a lógica de comunicação com a API.');
    }


    public function render()
    {
        $naturezasOperacao = NaturezaOperacao::orderBy('descricao')->get();
        // A busca de transportadoras agora é feita dinamicamente, não precisa mais passar para a view
        return view('livewire.nfe-avulsa-create', [
            'naturezasOperacao' => $naturezasOperacao,
        ])->layout('layouts.app');
    }
}