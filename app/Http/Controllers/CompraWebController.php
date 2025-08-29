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
use App\Models\UnidadeMedida;
use App\Models\Categoria;
use Illuminate\Support\Facades\Session;
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

    public function create()
    {
        // 1. Busca todos os fornecedores no banco, ordenados por razão social.
        $fornecedores = Fornecedor::orderBy('razao_social')->get();

        // 2. Retorna a view 'create.blade.php', passando a lista de fornecedores.
        return view('compras.create', compact('fornecedores'));
    }

    public function importarXml(Request $request)
{
    $request->validate(['xml_file' => 'required|file|mimes:xml,txt']);

    try {
        $xmlContent = $request->file('xml_file')->getContent();
        $xml = simplexml_load_string($xmlContent);
        if ($xml === false) throw new Exception("Não foi possível carregar o conteúdo do XML.");
        
        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        $infNFe = $xml->xpath('//nfe:infNFe')[0];
        if (!$infNFe) throw new Exception("Estrutura do XML inválida.");

        $chaveAcesso = str_replace('NFe', '', (string)$infNFe->attributes()->Id);
        if (Compra::where('chave_acesso_nfe', $chaveAcesso)->exists()) {
            return back()->with('error', "Atenção: A Nota Fiscal (Chave: {$chaveAcesso}) já foi importada.");
        }

        $cnpjFornecedor = (string)$infNFe->emit->CNPJ;
        $fornecedor = Fornecedor::where('cpf_cnpj', $cnpjFornecedor)->first();

        // Carrega todas as configurações e margens necessárias de uma vez
        $configuracoes = Configuracao::pluck('valor', 'chave')->all();
        $margensCategorias = Categoria::pluck('margem_lucro', 'id');
        $margemPadrao = (float)($configuracoes['margem_lucro_padrao'] ?? 0);
        $categoriaPadraoId = $configuracoes['categoria_padrao_id'] ?? 1; // Usa ID 1 como fallback

        $itens = [];
        foreach ($xml->xpath('//nfe:det') as $itemXml) {
            $vinculo = null;
            if ($fornecedor) {
                $vinculo = ProdutoFornecedor::with('produto.categoria')
                    ->where('fornecedor_id', $fornecedor->id)
                    ->where('codigo_produto_fornecedor', (string)$itemXml->prod->cProd)
                    ->first();
            }

            $precoCustoNota = (float)$itemXml->prod->vUnCom;

            // ===== LÓGICA DAS CORES (Amarelo, Azul, Vermelho) =====
            $statusItem = 'normal';
            if ($vinculo && $vinculo->produto) {
                $statusItem = 'vinculo_encontrado';
                if (abs($vinculo->produto->preco_custo - $precoCustoNota) > 0.01) {
                    $statusItem = 'custo_alterado';
                }
            } else {
                $statusItem = 'novo';
            }
            // =======================================================

            // ===== HIERARQUIA DE MARGENS PARA SUGESTÃO DE PREÇO =====
            $margemAplicada = $margemPadrao; // 3º Padrão
            if ($vinculo && $vinculo->produto) {
                $produtoVinculado = $vinculo->produto;
                $categoriaDoProduto = $produtoVinculado->categoria;
                // 1º Margem do Produto
                if (isset($produtoVinculado->margem_lucro)) {
                    $margemAplicada = (float)$produtoVinculado->margem_lucro;
                } 
                // 2º Margem da Categoria
                elseif ($categoriaDoProduto && isset($categoriaDoProduto->margem_lucro)) {
                    $margemAplicada = (float)$categoriaDoProduto->margem_lucro;
                }
            } else {
                // Se o produto é NOVO, já busca a margem da categoria padrão
                $margemAplicada = $margensCategorias[$categoriaPadraoId] ?? $margemPadrao;
            }
            // ========================================================
            
            $precoVendaSugerido = $precoCustoNota * (1 + ($margemAplicada / 100));

            $itens[] = [
                'descricao_nota' => (string)$itemXml->prod->xProd,
                'codigo_fornecedor' => (string)$itemXml->prod->cProd,
                'ean' => (string)$itemXml->prod->cEAN,
                'ncm' => (string)$itemXml->prod->NCM,
                'cfop' => (string)$itemXml->prod->CFOP,
                'unidade' => (string)$itemXml->prod->uCom,
                'quantidade' => (float)$itemXml->prod->qCom,
                'preco_custo' => $precoCustoNota,
                'subtotal' => (float)$itemXml->prod->vProd,
                'vinculo_existente' => $vinculo ? $vinculo->produto->toArray() : null,
                'preco_venda_sugerido' => number_format($precoVendaSugerido, 2, '.', ''),
                'status' => $statusItem,
            ];
        }

        $dadosNFe = [
            'chave_acesso' => $chaveAcesso,
            'numero_nota' => (string)$infNFe->ide->nNF,
            'data_emissao' => new \DateTime((string)$infNFe->ide->dhEmi),
            'valor_total' => (float)$infNFe->total->ICMSTot->vNF,
            'fornecedor' => ['cnpj' => $cnpjFornecedor, 'razao_social' => (string)$infNFe->emit->xNome, 'existente' => !is_null($fornecedor)],
            'itens' => $itens,
        ];

        Session::put('importacao_nfe', $dadosNFe);
        return redirect()->route('compras.revisarImportacao');

    } catch (Exception $e) {
        return back()->with('error', 'Falha ao analisar o XML: ' . $e->getMessage());
    }
}

public function revisarImportacao()
    {
        // 1. Pega os dados da NFe que foram salvos na sessão pelo método importarXml
        $dadosNFe = Session::get('importacao_nfe');

        // 2. Se não houver dados na sessão (ex: usuário demorou muito), redireciona com um erro
        if (!$dadosNFe) {
            return redirect()->route('compras.index')->with('error', 'Nenhuma nota para revisar ou a sessão expirou.');
        }
        
        // 3. Busca dados adicionais que os menus <select> da tela de revisão precisam
        $produtosDoSistema = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        // 4. Retorna a view de revisão, passando todos os dados necessários
        return view('compras.revisar', compact('dadosNFe', 'produtosDoSistema', 'categorias'));
    }

    public function salvarImportacao(Request $request)
    {
        $dadosNFeDaSessao = Session::get('importacao_nfe');
        if (!$dadosNFeDaSessao) return redirect()->route('compras.index')->with('error', 'Sessão da importação expirada.');
    
        DB::beginTransaction();
        try {
            $configuracoes = Configuracao::pluck('valor', 'chave')->all();
            $margensCategorias = Categoria::pluck('margem_lucro', 'id');
            $margemPadrao = (float)($configuracoes['margem_lucro_padrao'] ?? 0);
            $categoriaPadraoId = $configuracoes['categoria_padrao_id'] ?? 1; // Usa ID 1 como fallback
    
            $fornecedor = Fornecedor::firstOrCreate(['cpf_cnpj' => $dadosNFeDaSessao['fornecedor']['cnpj']], ['razao_social' => $dadosNFeDaSessao['fornecedor']['razao_social']]);
            
            $compra = Compra::create([
                'fornecedor_id' => $fornecedor->id,
                'empresa_id' => Auth::user()->empresa_id,
                'numero_nota' => $dadosNFeDaSessao['numero_nota'],
                'data_emissao' => $dadosNFeDaSessao['data_emissao'],
                'valor_total_nota' => $dadosNFeDaSessao['valor_total'],
                'chave_acesso_nfe' => $dadosNFeDaSessao['chave_acesso'],
                'status' => 'concluida',
            ]);
    
            foreach ($dadosNFeDaSessao['itens'] as $index => $itemOriginal) {
                $dadosModificados = $request->input("itens.$index", []);
                $categoriaFinal = $dadosModificados['categoria_id'] ?? $itemOriginal['vinculo_existente']['categoria_id'] ?? $categoriaPadraoId;
                $precoCusto = (float)$itemOriginal['preco_custo'];
                $margemFinal = 0;
                $precoVendaFinal = (float)($dadosModificados['preco_venda'] ?? 0);
    
                if ($precoVendaFinal > $precoCusto) {
                    // Se o usuário digitou um preço válido, calcula a margem a partir dele
                    $margemFinal = (($precoVendaFinal / $precoCusto) - 1) * 100;
                } else {
                    // Se não, usa a hierarquia para recalcular o preço e a margem
                    $margemFinal = (float)($margensCategorias[$categoriaFinal] ?? $margemPadrao);
                    $precoVendaFinal = $precoCusto * (1 + ($margemFinal / 100));
                }
    
                $dadosFinaisProduto = [
                    'nome' => $dadosModificados['nome'] ?? $itemOriginal['descricao_nota'],
                    'categoria_id' => $categoriaFinal,
                    'preco_venda' => $precoVendaFinal,
                    'preco_custo' => $precoCusto,
                    'margem_lucro' => $margemFinal, // A mágica acontece aqui!
                    'codigo_barras' => $itemOriginal['ean'],
                    'ativo' => true,
                ];
    
                $produtoIdVinculado = $dadosModificados['produto_id'] ?? $itemOriginal['vinculo_existente']['id'] ?? null;
                $produtoFinal = null;
    
                if ($produtoIdVinculado) {
                    if ($produto = Produto::find($produtoIdVinculado)) {
                        $produto->update($dadosFinaisProduto);
                        $produtoFinal = $produto;
                    }
                } else {
                    $produtoFinal = Produto::updateOrCreate(
                        ['codigo_barras' => $dadosFinaisProduto['codigo_barras']],
                        $dadosFinaisProduto
                    );
                }
    
                if ($produtoFinal) {
                    $produtoFinal->increment('estoque_atual', (float)$itemOriginal['quantidade']);
                    ProdutoFornecedor::updateOrCreate(
                        ['fornecedor_id' => $fornecedor->id, 'codigo_produto_fornecedor' => $itemOriginal['codigo_fornecedor']],
                        ['produto_id' => $produtoFinal->id]
                    );
                    ItemCompra::create([
                        'compra_id' => $compra->id, 'produto_id' => $produtoFinal->id,
                        'descricao_item_nota' => $itemOriginal['descricao_nota'], 'quantidade' => $itemOriginal['quantidade'],
                        'preco_custo_nota' => $itemOriginal['preco_custo'], 'subtotal' => $itemOriginal['subtotal'],
                        'ncm' => $itemOriginal['ncm'], 'cfop' => $itemOriginal['cfop'],
                    ]);
                }
            }
    
            DB::commit();
            Session::forget('importacao_nfe');
            return redirect()->route('compras.index')->with('success', 'Nota importada e produtos salvos com sucesso!');
    
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro ao salvar a importação: ' . $e->getMessage());
        }
    }
    public function update(Request $request, string $id)
    {
        $request->validate([
            'itens' => 'required|array',
            // CORREÇÃO 3: Altere 'preco_entrada' para 'preco_venda' aqui
            'itens.*.preco_venda' => 'required|numeric', 
            'itens.*.categoria_id' => 'nullable|exists:categorias,id'
        ]);
    
        try {
            DB::transaction(function () use ($request, $id) {
                $compra = Compra::findOrFail($id);
    
                foreach ($request->itens as $itemId => $itemData) {
                    // ...
                    // Lembre-se de usar $itemData['preco_venda'] no resto deste método se necessário
                }
                // ...
            });
            // ...
        } catch (Exception $e) {
            // ...
        }
    }

    public function store(Request $request)
{
    // 1. Valida os dados do cabeçalho da nota e o array de itens
    $validatedData = $request->validate([
        'fornecedor_id' => 'required|exists:fornecedores,id',
        'numero_nota' => 'required|string|max:255',
        'data_emissao' => 'required|date',
        'items' => 'required|array|min:1',
        'items.*.produto_id' => 'required|exists:produtos,id',
        'items.*.quantidade' => 'required|numeric|min:0.01',
        'items.*.preco_custo' => 'required|numeric|min:0',
    ]);

    // Inicia uma transação para garantir a integridade dos dados
    DB::beginTransaction();
    try {
        // 2. Cria o cabeçalho da Compra
        $compra = Compra::create([
            'fornecedor_id' => $validatedData['fornecedor_id'],
            'numero_nota' => $validatedData['numero_nota'],
            'data_emissao' => $validatedData['data_emissao'],
            'empresa_id' => Auth::user()->empresa_id,
            'status' => 'concluida', // A nota já entra como concluída
        ]);

        $valorTotalNota = 0;

        // 3. Percorre e salva cada item da compra
        foreach ($validatedData['items'] as $itemData) {
            $subtotal = $itemData['quantidade'] * $itemData['preco_custo'];
            $valorTotalNota += $subtotal;

            ItemCompra::create([
                'compra_id' => $compra->id,
                'produto_id' => $itemData['produto_id'],
                'quantidade' => $itemData['quantidade'],
                'preco_custo_nota' => $itemData['preco_custo'],
                'subtotal' => $subtotal,
            ]);

            // 4. Atualiza o estoque e o preço de custo do produto principal
            $produto = Produto::find($itemData['produto_id']);
            if ($produto) {
                $produto->increment('estoque_atual', $itemData['quantidade']);
                // Atualiza o preço de custo do produto para o valor desta última compra
                $produto->update(['preco_custo' => $itemData['preco_custo']]);
            }
        }
        
        // 5. Atualiza o valor total na nota principal
        $compra->update(['valor_total_nota' => $valorTotalNota]);

        // Se tudo correu bem, confirma as operações no banco
        DB::commit();

        return redirect()->route('compras.index')->with('success', 'Compra manual lançada com sucesso!');

    } catch (\Exception $e) {
        // Se algo deu errado, desfaz todas as operações
        DB::rollBack();
        return back()->with('error', 'Ocorreu um erro ao salvar a compra: ' . $e->getMessage())->withInput();
    }
}
    private function vincularOuCriarProduto(Fornecedor $fornecedor, ItemCompra $itemCompra, string $unidadeComercialXml): void
    {
        // ... (código existente sem alterações)
    }

    public function destroy(Compra $compra)
    {
        DB::beginTransaction();
        try {
            // 1. Estorna o estoque de cada produto
            foreach ($compra->itens as $item) {
                if ($item->produto) {
                    $item->produto->decrement('estoque_atual', $item->quantidade);
                }
            }
    
            // 2. Apaga os registos dos itens da nota
            $compra->itens()->delete();
    
            // 3. Apaga a nota principal
            $compra->delete();
    
            DB::commit();
    
            return redirect()->route('compras.index')->with('success', 'Nota fiscal, itens e estoque estornado com sucesso.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocorreu um erro ao remover a nota: ' . $e->getMessage());
        }
    }
    
    /**
     * Mostra o formulário para editar uma Compra existente.
     *
     * @param  \App\Models\Compra  $compra
     * @return \Illuminate\Http\Response
     */
    public function edit(Compra $compra)
    {
        // CORREÇÃO 2: Carrega os itens da compra junto com a compra (Eager Loading)
        // Isso garante que $compra->itens terá os dados na view
        $compra->load('itens'); 

        $produtos = Produto::orderBy('nome')->get();

        return view('compras.edit', compact('compra', 'produtos'));
    }
}