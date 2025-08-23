<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Produto;
use App\Models\Fornecedor;
use App\Models\ItemCompra;
use App\Models\ProdutoFornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Configuracao;
use Illuminate\Support\Facades\Auth;
use App\Models\DetalhesItemMercado;
use Exception;

class CompraWebController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with('fornecedor')->latest();
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }
        if ($request->filled('numero_nota')) {
            $query->where('numero_nota', 'like', '%' . $request->numero_nota . '%');
        }
        if ($request->filled('data_inicio')) {
            $query->whereDate('data_emissao', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('data_emissao', '<=', $request->data_fim);
        }
        $comprasRecentes = $query->paginate(15);
        $fornecedores = Fornecedor::orderBy('razao_social')->get();
        return view('compras.index', compact('comprasRecentes', 'fornecedores'));
    }

    public function importarXml(Request $request)
    {
        $request->validate(['xml_file' => 'required|file|mimes:xml,txt']);
    
        try {
            $xmlContent = $request->file('xml_file')->getContent();
            $xml = @simplexml_load_string($xmlContent);
    
            if ($xml === false) throw new Exception("O arquivo XML parece estar mal formatado.");
    
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
            $infNFeNode = $xml->xpath('//nfe:infNFe');
            if (empty($infNFeNode)) throw new Exception("Não foi possível encontrar a tag <infNFe>. O arquivo é uma NF-e válida?");
            
            $infNFe = $infNFeNode[0];
            $chaveAcesso = str_replace('NFe', '', (string)$infNFe->attributes()->Id);
            
            if (Compra::where('chave_acesso_nfe', $chaveAcesso)->exists()) {
                return redirect()->route('compras.index')->with('error', 'Atenção! A Nota Fiscal (Chave: ' . $chaveAcesso . ') já foi cadastrada.');
            }
    
            $fornecedor = Fornecedor::firstOrCreate(
                ['cpf_cnpj' => (string)$infNFe->emit->CNPJ],
                ['razao_social' => (string)$infNFe->emit->xNome, 'tipo_pessoa' => 'juridica']
            );
            
            // --- BLOCO ATUALIZADO ---
            // Preenchendo os dados da compra com as informações do XML
            $compra = Compra::create([
                'empresa_id' => auth()->user()->empresa_id, // Resolvendo o erro anterior
                'fornecedor_id' => $fornecedor->id,
                'chave_acesso_nfe' => $chaveAcesso,
                'numero_nota' => (string) $infNFe->ide->nNF,
                'serie_nota' => (string) $infNFe->ide->serie,
                'data_emissao' => new \DateTime((string) $infNFe->ide->dhEmi),
                'valor_total_nota' => (float) $infNFe->total->ICMSTot->vNF,
                'status' => 'conferencia', // Status inicial
                // Adicione a lógica da forma de pagamento se necessário
                // 'forma_pagamento' => $formaPagamento,
            ]);
    
            // --- LÓGICA DE VÍNCULO AUTOMÁTICO ---
            foreach ($xml->xpath('//nfe:det') as $itemXml) {
                $itemCompra = $compra->itens()->create([
                    'descricao_item_nota' => (string) $itemXml->prod->xProd,
                    'codigo_produto_nota' => (string) $itemXml->prod->cProd,
                    'ean_nota' => (string) $itemXml->prod->cEAN,
                    'ncm' => (string) $itemXml->prod->NCM,
                    'cfop' => (string) $itemXml->prod->CFOP,
                    'quantidade' => (float) $itemXml->prod->qCom,
                    'preco_custo_nota' => (float) $itemXml->prod->vUnCom,
                    'subtotal' => (float) $itemXml->prod->vProd,
                ]);
                
                $this->vincularOuCriarProduto($fornecedor, $itemCompra);
            }
            
            return redirect()->route('compras.edit', $compra->id)
                             ->with('success', 'Nota importada! Confira os itens vinculados e categorize os novos produtos.');
    
        } catch (Exception $e) {
            return back()->with('error', 'Falha ao processar XML: ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'itens' => 'required|array',
            'itens.*.preco_entrada' => 'required|numeric',
            'itens.*.categoria_id' => 'nullable|exists:categorias,id'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $compra = Compra::findOrFail($id);

                foreach ($request->itens as $itemId => $itemData) {
                    $item = ItemCompra::findOrFail($itemId);
                    $produtoId = $itemData['produto_id'] ?? null;
                    
                    if (!$produtoId) continue;
                    
                    $produto = Produto::findOrFail($produtoId);

                    $produto->preco_custo = $itemData['preco_entrada'];
                    if (!$produto->categoria_id && isset($itemData['categoria_id'])) {
                        $produto->categoria_id = $itemData['categoria_id'];
                    }
                    $produto->preco_venda = $produto->calcularPrecoVenda((float)$itemData['preco_entrada']);
                    $produto->save();

                    ProdutoFornecedor::updateOrCreate(
                        [
                            'fornecedor_id' => $compra->fornecedor_id,
                            'codigo_produto_fornecedor' => $item->codigo_produto_nota,
                        ],
                        [
                            'produto_id' => $produto->id,
                            'ean_fornecedor' => $item->ean_nota,
                        ]
                    );

                    $item->update([
                        'produto_id' => $produto->id,
                        'preco_entrada' => $itemData['preco_entrada'],
                    ]);
                }

                $compra->status = 'finalizada';
                $compra->save();
            });

            return redirect()->route('compras.index')->with('success', 'Nota de compra #' . $id . ' finalizada com sucesso!');
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro: ' . $e->getMessage());
        }
    }

    public function edit(string $id)
    {
        $compra = Compra::with('fornecedor', 'itens.produto.categoria')->findOrFail($id);
        $produtos = Produto::orderBy('nome')->get();
        $categorias = \App\Models\Categoria::orderBy('nome')->get();
    
        return view('compras.edit', compact('compra', 'produtos', 'categorias'));
    }

    private function vincularOuCriarProduto(Fornecedor $fornecedor, ItemCompra $itemCompra): void
    {  


       
        $produtoIdFinal = null;
        $produtoParaCalcularPreco = null;
    
        $vinculo = ProdutoFornecedor::where('fornecedor_id', $fornecedor->id)
                                    ->where('codigo_produto_fornecedor', $itemCompra->codigo_produto_nota)
                                    ->first();
    
        if ($vinculo) {
            $produto = Produto::find($vinculo->produto_id);
            if ($produto) {
                $produto->preco_custo = $itemCompra->preco_custo_nota;
                $produtoParaCalcularPreco = $produto;
                $produtoIdFinal = $produto->id;
            }
        } else {
            $detalheId = null;
            $usuario = Auth::user();

/*
            dd([
                '1. Objeto Usuario' => $usuario,
                '2. Objeto Empresa (do Usuario)' => $usuario->empresa,
                '3. Nicho da Empresa' => $usuario->empresa ? $usuario->empresa->nicho : 'Empresa é nula, não foi possível ler o nicho.',
                '4. A condição ($usuario->empresa) é verdadeira?' => !is_null($usuario->empresa),
                '5. A condição ($usuario->empresa->nicho === \'mercado\') é verdadeira?' => $usuario->empresa ? ($usuario->empresa->nicho === 'mercado') : 'Empresa é nula, não foi possível comparar o nicho.',
            ]);
            */
    
            if ($usuario->empresa && $usuario->empresa->nicho_negocio === 'mercado') {
                // --- BLOCO ATUALIZADO ---
                // 1. Cria o registro de detalhes com dados extraídos da nota e valores padrão
                $detalheItem = DetalhesItemMercado::create([
                    // --- Dados que podemos pegar da Nota Fiscal (XML) ---
                    'codigo_barras' => $itemCompra->ean_nota,
                    'fornecedor_id' => $fornecedor->id,
                    'preco_custo' => $itemCompra->preco_custo_nota,
                    'estoque_atual' => $itemCompra->quantidade, // Estoque inicial é a quantidade da nota
    
                    // --- Dados que precisam de um valor padrão ---
                    // !!! ATENÇÃO: Verifique e ajuste estes valores padrão conforme sua necessidade !!!
                    'marca' => null, // Pode ser nulo ou um valor como 'A Definir'
                    'categoria_id' => null, // Será vinculado depois na tela de edição
                    'estoque_minimo' => 1, // Defina um padrão
                    'unidade_medida_id' => 1, // Ex: ID 1 = 'UNIDADE'. AJUSTE CONFORME SUA TABELA 'unidades_medida'
                    'controla_validade' => false,
                    'vendido_por_peso' => false,
                    // Adicione aqui qualquer outro campo que seja obrigatório na sua tabela
                ]);
                
                // 2. Pega o ID do detalhe que acabamos de criar
                $detalheId = $detalheItem->id;
            }
            
            $novoProduto = new Produto();
            $novoProduto->nome = $itemCompra->descricao_item_nota;
            $novoProduto->ativo = true;
            $novoProduto->categoria_id = null;
            $novoProduto->detalhe_id = $detalheId;
            $novoProduto->preco_custo = $itemCompra->preco_custo_nota;
            
            $produtoParaCalcularPreco = $novoProduto;
            $produtoIdFinal = null;
        }
    
        if ($produtoParaCalcularPreco) {
            // Lógica de cálculo de preço de venda (mantida como antes)
            $margemPadrao = Configuracao::where('chave', 'margem_lucro_padrao')->value('valor') ?? 25.0;
            $margemAplicar = $margemPadrao;
            if ($produtoParaCalcularPreco->categoria && $produtoParaCalcularPreco->categoria->margem_lucro > 0) {
                $margemAplicar = $produtoParaCalcularPreco->categoria->margem_lucro;
            }
            $precoCusto = (float) $itemCompra->preco_custo_nota;
            $produtoParaCalcularPreco->preco_venda = $precoCusto * (1 + ($margemAplicar / 100));
            $produtoParaCalcularPreco->save();
    
            if (!$produtoIdFinal) {
                $produtoIdFinal = $produtoParaCalcularPreco->id;
                ProdutoFornecedor::create([
                    'produto_id' => $produtoIdFinal,
                    'fornecedor_id' => $fornecedor->id,
                    'codigo_produto_fornecedor' => $itemCompra->codigo_produto_nota,
                    'ean_fornecedor' => $itemCompra->ean_nota,
                ]);
            }
        }
        
        if ($produtoIdFinal) {
            $itemCompra->produto_id = $produtoIdFinal;
            $itemCompra->save();
        }
    }
    
    // Seus outros métodos (create, store, destroy) devem ser mantidos como estavam
    public function create() { /* ... */ }
    public function store(Request $request) { /* ... */ }
    public function destroy(string $id)
{
    // Usamos uma transaction para garantir que ou tudo dá certo, ou nada é feito.
    DB::beginTransaction();

    try {
        $compra = Compra::findOrFail($id);

        // 1. Deleta todos os itens associados a esta compra.
        $compra->itens()->delete();

        // 2. Agora que não há mais itens, podemos deletar a compra.
        $compra->delete();

        // 3. Se tudo correu bem, confirma a operação.
        DB::commit();

        return redirect()->route('compras.index')
                         ->with('success', 'Nota de compra #' . $id . ' e seus itens foram removidos com sucesso.');

    } catch (Exception $e) {
        // 4. Se algo deu errado, desfaz a operação.
        DB::rollBack();

        return redirect()->route('compras.edit', $id)
                         ->with('error', 'Falha ao remover a nota: ' . $e->getMessage());
    }
}
}