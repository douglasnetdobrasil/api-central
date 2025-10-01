<?php

namespace App\Livewire;

use App\Models\Cliente;
use App\Models\FormaPagamento;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\Transportadora;
use App\Models\Venda;
use App\Services\NFeService;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

class NfeAvulsaCriar extends Component
{
    // --- PROPRIEDADES DA NF-e ---
    public string $clienteSearch = '', $produtoSearch = '', $transportadoraSearch = '';
    public ?Cliente $clienteSelecionado = null;
    public ?Transportadora $transportadoraSelecionada = null;
    public array $cart = [];
    public $clientesEncontrados = [], $produtosEncontrados = [], $transportadorasEncontradas = [];
    public int $highlightClienteIndex = -1, $highlightProdutoIndex = -1;
    public float $subtotal = 0, $desconto = 0, $totalNota = 0;
    public ?int $natureza_operacao_id = null;
    public int $serie = 1, $finalidade_emissao = 1, $tipo_operacao = 1, $consumidor_final = 0;
    public int $frete_modalidade = 9;
    public float $frete_valor = 0.00;
    public ?int $volume_quantidade = null;
    public string $volume_especie = '';
    public string $volume_marca = '';
    public float $peso_bruto = 0, $peso_liquido = 0;
    public int $totalQuantidadeProdutos = 0;
    public string $observacoes = '';
    public ?Venda $venda = null;
    public $itemImpostos = [];
    public ?int $indexImpostos = null;
    public float $total_base_calculo_icms = 0.00, $total_valor_icms = 0.00, $total_valor_ipi = 0.00;
    
    // --- PROPRIEDADES DE PAGAMENTO (Reutilizadas do seu Pdv.php) ---
    public array $pagamentos = [];
    public $formasPagamentoOpcoes = [];
    public $formaPagamentoSelecionada = null;
    public $valorPagamentoAtual = '';
    public float $valorRecebido = 0;
    public float $troco = 0;
    public float $faltaPagar = 0;

    public function mount()
    {
        $this->formasPagamentoOpcoes = FormaPagamento::where('ativo', true)->orderBy('nome')->get();
        $this->formaPagamentoSelecionada = $this->formasPagamentoOpcoes->first()->id ?? null;
        $this->natureza_operacao_id = NaturezaOperacao::first()->id ?? null;
        $this->calcularTotais();
    }
    
    public function carregarRascunho()
    {
        if (!$this->venda) return;
        
        $this->clienteSelecionado = $this->venda->cliente;
        $this->transportadoraSelecionada = $this->venda->transportadora;
        $this->subtotal = $this->venda->subtotal;
        $this->desconto = $this->venda->desconto;
        $this->totalNota = $this->venda->total;
        $this->observacoes = $this->venda->observacoes;
        $this->frete_modalidade = $this->venda->frete_modalidade ?? 9;
        $this->frete_valor = $this->venda->frete_valor ?? 0.00;
        $this->peso_bruto = $this->venda->peso_bruto;
        $this->peso_liquido = $this->venda->peso_liquido;

        $this->cart = [];
        foreach ($this->venda->items as $item) {
            $produto = $item->produto;
            $this->cart[] = [
                'id' => $item->produto_id, 'nome' => $item->descricao_produto, 'preco' => $item->preco_unitario,
                'quantidade' => $item->quantidade, 'cfop' => $item->cfop ?? '5102', 'unidade' => $produto->unidade ?? 'UN',
                'peso_bruto' => $produto->peso_bruto ?? 0, 'peso_liquido' => $produto->peso_liquido ?? 0, 'impostos' => json_decode($item->impostos, true) ?? [],
            ];
        }

        // >>>>>>>>>>>>>>>> ALTERAÇÃO APLICADA AQUI <<<<<<<<<<<<<<<<
        // Carrega os pagamentos salvos no rascunho
        $this->pagamentos = [];
        foreach($this->venda->pagamentos as $p) {
            $this->pagamentos[] = ['id' => $p->forma_pagamento_id, 'nome' => $p->forma->nome, 'valor' => $p->valor];
        }
        // >>>>>>>>>>>>>>>>>>>>>>>>> FIM DA ALTERAÇÃO <<<<<<<<<<<<<<<<<<<<<<<<<<

        $this->calcularTotais();
    }
    
    public function emitirNFe()
    {
        $this->salvarRascunho('Processando NFe');

        try {
            $nfeService = new NFeService();
            $resultado = $nfeService->emitir($this->venda);

            if ($resultado['success']) {
                session()->flash('message', $resultado['message']);
                return redirect()->route('nfe.index');
            } else {
                throw new Exception($resultado['message']);
            }
        } catch (Exception $e) {
            if ($this->venda) {
                $this->venda->update(['status' => 'Em Digitação']);
            }
            session()->flash('error', 'Falha ao emitir NF-e: ' . $e->getMessage());
        }
    }
    
    public function salvarRascunho($status = 'Em Digitação')
    {
        $this->validate(['clienteSelecionado' => 'required', 'cart' => 'required|array|min:1']);
        $this->calcularTotais();
        
        DB::transaction(function () use ($status) {
            $dadosVenda = [
                'empresa_id' => Auth::user()->empresa_id, 'user_id' => Auth::id(), 'cliente_id' => $this->clienteSelecionado->id,
                'transportadora_id' => $this->transportadoraSelecionada->id ?? null, 'subtotal' => $this->subtotal,
                'desconto' => $this->desconto, 'total' => $this->totalNota, 'status' => $status,
                'observacoes' => $this->observacoes, 'frete_modalidade' => $this->frete_modalidade, 'frete_valor' => $this->frete_valor,
                'peso_bruto' => $this->peso_bruto, 'peso_liquido' => $this->peso_liquido,
                'natureza_operacao_id' => $this->natureza_operacao_id, 'finalidade_emissao' => $this->finalidade_emissao,
                'tipo_operacao' => $this->tipo_operacao, 'consumidor_final' => $this->consumidor_final, 'serie' => $this->serie,
            ];
            $vendaSalva = Venda::updateOrCreate(['id' => $this->venda?->id], $dadosVenda);
            
            $vendaSalva->items()->delete();
            foreach ($this->cart as $item) {
                $vendaSalva->items()->create([
                    'produto_id' => $item['id'], 'descricao_produto' => $item['nome'], 'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'], 'subtotal_item' => $item['total_item'], 'cfop' => $item['cfop'], 'impostos' => json_encode($item['impostos'])
                ]);
            }
    
            $vendaSalva->pagamentos()->delete();
            foreach($this->pagamentos as $pagamento) {
                // ======================= INÍCIO DA CORREÇÃO =======================
                $vendaSalva->pagamentos()->create([
                    'empresa_id' => $vendaSalva->empresa_id, // <-- ADICIONADO
                    'forma_pagamento_id' => $pagamento['id'],
                    'valor' => $pagamento['valor']
                ]);
                // ======================= FIM DA CORREÇÃO =======================
            }
            
            $this->venda = $vendaSalva->fresh();
        });
    
        if ($status == 'Em Digitação') {
            session()->flash('message', 'Rascunho salvo com sucesso!');
        }
    }
    
    // --- MÉTODOS DE PAGAMENTO (Seus métodos do PDV, agora integrados) ---
    public function adicionarPagamento() { $valor = (float)str_replace(',', '.', $this->valorPagamentoAtual); if ($valor <= 0.009) return; $formaPagamento = FormaPagamento::find($this->formaPagamentoSelecionada); if (!$formaPagamento) { session()->flash('error_modal', 'Forma de pagamento inválida.'); return; } $this->pagamentos[] = ['id' => $formaPagamento->id, 'nome' => $formaPagamento->nome, 'valor' => $valor]; $this->recalcularValoresPagamento(); }
    public function removerPagamento($index) { unset($this->pagamentos[$index]); $this->pagamentos = array_values($this->pagamentos); $this->recalcularValoresPagamento(); }
    private function recalcularValoresPagamento() { $this->valorRecebido = collect($this->pagamentos)->sum('valor'); $this->faltaPagar = $this->totalNota - $this->valorRecebido; $this->troco = 0; if ($this->faltaPagar < 0) { $this->troco = abs($this->faltaPagar); $this->faltaPagar = 0; } $this->valorPagamentoAtual = number_format($this->faltaPagar > 0 ? $this->faltaPagar : 0, 2, '.', ''); }

    // --- SEUS MÉTODOS ORIGINAIS (MANTIDOS 100% FIÉIS) ---
    public function calcularTotais()
{
    // Inicializa as variáveis monetárias como strings para precisão
    $subtotal = '0.00';
    $totalBCIcms = '0.00';
    $totalValorIcms = '0.00';
    $totalValorIpi = '0.00';
    
    // Variáveis não monetárias podem continuar como estão
    $totalQuantidade = 0;
    $pesoBruto = 0;
    $pesoLiquido = 0;

    foreach ($this->cart as $index => &$item) {
        // Usa bcmul para multiplicação precisa (preço * quantidade)
        // Usamos 4 casas de precisão para o cálculo intermediário
        $item['total_item'] = bcmul((string)$item['preco'], (string)$item['quantidade'], 4);
        
        // Usa bcadd para somar o subtotal de forma precisa
        $subtotal = bcadd($subtotal, $item['total_item'], 4);
        
        // Cálculos não monetários continuam iguais
        $totalQuantidade += $item['quantidade'];
        $pesoBruto += (float)($item['peso_bruto'] ?? 0) * $item['quantidade'];
        $pesoLiquido += (float)($item['peso_liquido'] ?? 0) * $item['quantidade'];
        
        // Usa bcadd para somar os totais de impostos de forma precisa
        $totalBCIcms = bcadd($totalBCIcms, (string)($item['impostos']['icms_base_calculo'] ?? '0.00'), 2);
        $totalValorIcms = bcadd($totalValorIcms, (string)($item['impostos']['icms_valor'] ?? '0.00'), 2);
        $totalValorIpi = bcadd($totalValorIpi, (string)($item['impostos']['ipi_valor'] ?? '0.00'), 2);
    }

    // Arredonda o subtotal final para 2 casas decimais antes de atribuir
    $this->subtotal = number_format((float)$subtotal, 2, '.', '');
    
    $this->totalQuantidadeProdutos = $totalQuantidade;
    $this->peso_bruto = $pesoBruto;
    $this->peso_liquido = $pesoLiquido;
    $this->total_base_calculo_icms = (float)$totalBCIcms;
    $this->total_valor_icms = (float)$totalValorIcms;
    $this->total_valor_ipi = (float)$totalValorIpi;

    // Usa bcsub e bcadd para o cálculo final do total da nota
    $subtotalMenosDesconto = bcsub((string)$this->subtotal, (string)$this->desconto, 2);
    $this->totalNota = bcadd($subtotalMenosDesconto, (string)$this->frete_valor, 2);
    
    // Chama o método que recalcula os pagamentos (que já corrigimos anteriormente)
    $this->recalcularValoresPagamento();
}
    public function selecionarClienteComEnter() { if (isset($this->clientesEncontrados[$this->highlightClienteIndex])) { $this->selecionarCliente($this->clientesEncontrados[$this->highlightClienteIndex]->id); } }
    public function selecionarProdutoComEnter() { if (isset($this->produtosEncontrados[$this->highlightProdutoIndex])) { $this->adicionarProduto($this->produtosEncontrados[$this->highlightProdutoIndex]->id); } }
    public function updatedClienteSearch($value){ $this->highlightClienteIndex = -1; if (strlen($value) >= 1) { $this->clientesEncontrados = Cliente::where('id', $value)->orWhere('nome', 'like', '%' . $value . '%')->orWhere('cpf_cnpj', 'like', '%' . $value . '%')->limit(5)->get(); } else { $this->clientesEncontrados = []; } }
    public function selecionarCliente($clienteId) { $this->clienteSelecionado = Cliente::find($clienteId); $this->clienteSearch = ''; $this->clientesEncontrados = []; }
    public function removerCliente() { $this->clienteSelecionado = null; }
    public function updatedProdutoSearch($value){ $this->highlightProdutoIndex = -1; if (strlen($value) >= 1) { $this->produtosEncontrados = Produto::where('empresa_id', Auth::user()->empresa_id)->where(function ($query) use ($value) { $query->where('id', $value)->orWhere('nome', 'like', '%' . $value . '%')->orWhere('codigo_barras', $value); })->limit(5)->get(); } else { $this->produtosEncontrados = []; } }
    public function updatedTransportadoraSearch($value){ if (strlen($value) >= 1) { $this->transportadorasEncontradas = Transportadora::where('empresa_id', Auth::user()->empresa_id)->where('ativo', true)->where(function ($query) use ($value) { $query->where('id', $value)->orWhere('razao_social', 'like', '%' . $value . '%')->orWhere('cnpj', 'like', '%' . $value . '%'); })->limit(5)->get(); } else { $this->transportadorasEncontradas = []; } }
    public function selecionarTransportadora($id) { $this->transportadoraSelecionada = Transportadora::find($id); $this->transportadoraSearch = ''; $this->transportadorasEncontradas = []; }
    public function removerTransportadora() { $this->transportadoraSelecionada = null; }
   
   
   
    public function adicionarProduto($produtoId)
    {
        $produto = Produto::find($produtoId);
        if (!$produto) return;
    
        // ======================= INÍCIO DA NOVA LÓGICA DE CFOP =======================
        $cfopFinal = null;
    
        // 1. Tenta pegar o CFOP da Natureza da Operação selecionada na tela
        if ($this->natureza_operacao_id) {
            $natureza = NaturezaOperacao::find($this->natureza_operacao_id);
            if ($natureza && !empty($natureza->cfop)) {
                $cfopFinal = $natureza->cfop;
            }
        }
    
        // 2. Se não encontrou na Natureza, tenta pegar o CFOP padrão do PRODUTO
        if (empty($cfopFinal) && isset($produto->dadosFiscais) && !empty($produto->dadosFiscais->cfop)) {
            $cfopFinal = $produto->dadosFiscais->cfop;
        }
    
        // 3. Se ainda assim não encontrou, usa um valor padrão de segurança
        if (empty($cfopFinal)) {
            $cfopFinal = '5102';
        }
        // ======================= FIM DA NOVA LÓGICA DE CFOP =======================
    
        $cartIndex = collect($this->cart)->search(fn ($item) => $item['id'] === $produto->id);
        
        if ($cartIndex !== false) {
            $this->aumentarQuantidade($cartIndex);
        } else {
            $this->cart[] = [
                'id' => $produto->id,
                'nome' => $produto->nome,
                'preco' => $produto->preco_venda,
                'quantidade' => 1,
                'unidade' => $produto->unidade,
                'cfop' => $cfopFinal, // <-- AQUI USAMOS O CFOP ENCONTRADO
                'peso_bruto' => $produto->peso_bruto ?? 0,
                'peso_liquido' => $produto->peso_liquido ?? 0,
                'impostos' => []
            ];
        }
        
        $this->produtoSearch = '';
        $this->produtosEncontrados = [];
        $this->calcularTotais();
    }


    
    public function removerProduto($cartIndex) { unset($this->cart[$cartIndex]); $this->cart = array_values($this->cart); $this->calcularTotais(); }
    public function aumentarQuantidade($cartIndex) { $this->cart[$cartIndex]['quantidade']++; $this->calcularTotais(); }
    public function diminuirQuantidade($cartIndex) { if ($this->cart[$cartIndex]['quantidade'] > 1) { $this->cart[$cartIndex]['quantidade']--; $this->calcularTotais(); } }
    public function updatedFreteValor() { $this->calcularTotais(); }
    public function updatedDesconto() { $this->calcularTotais(); }
    public function abrirModalImpostos($index) { $this->indexImpostos = $index; $this->itemImpostos = $this->cart[$index]['impostos'] ?? []; }
    public function salvarImpostos() { if ($this->indexImpostos !== null) { $this->cart[$this->indexImpostos]['impostos'] = $this->itemImpostos; $this->fecharModalImpostos(); $this->calcularTotais(); } }
    public function fecharModalImpostos() { $this->indexImpostos = null; $this->itemImpostos = []; }
    public function render(){ $naturezasOperacao = NaturezaOperacao::orderBy('descricao')->get(); return view('livewire.nfe-avulsa-create', ['naturezasOperacao' => $naturezasOperacao])->layout('layouts.app'); }
}