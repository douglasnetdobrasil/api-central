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

    public function importarXml(Request $request)
    {
        $request->validate(['xml_file' => 'required|file|mimes:xml,txt']);

        try {
            $xmlContent = $request->file('xml_file')->getContent();
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                throw new Exception("Não foi possível carregar o conteúdo do XML.");
            }
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

            $infNFe = $xml->xpath('//nfe:infNFe')[0];
            if (!$infNFe) {
                throw new Exception("Estrutura do XML inválida: tag <infNFe> não encontrada.");
            }

            $chaveAcesso = str_replace('NFe', '', (string)$infNFe->attributes()->Id);

            $notaExistente = Compra::where('chave_acesso_nfe', $chaveAcesso)->first();
            if ($notaExistente) {
                return back()->with('error', "Atenção: A Nota Fiscal (Chave: {$chaveAcesso}) já foi importada em " . $notaExistente->created_at->format('d/m/Y') . ".");
            }

            $cnpjFornecedor = (string)$infNFe->emit->CNPJ;
            $fornecedor = Fornecedor::where('cpf_cnpj', $cnpjFornecedor)->first();

            $margemPadrao = \App\Models\Configuracao::where('chave', 'margem_lucro_padrao')->value('valor') ?? 0;

            $itens = [];
            foreach ($xml->xpath('//nfe:det') as $itemXml) {
                $vinculo = null;
                if ($fornecedor) {
                    $vinculo = ProdutoFornecedor::with('produto.categoria')
                        ->where('fornecedor_id', $fornecedor->id)
                        ->where('codigo_produto_fornecedor', (string)$itemXml->prod->cProd)
                        ->first();
                }

                $precoCusto = (float)$itemXml->prod->vUnCom;
                $margemAplicada = $margemPadrao;

                if ($vinculo) {
                    $produtoVinculado = $vinculo->produto;
                    if (!empty($produtoVinculado->margem_lucro)) {
                        $margemAplicada = $produtoVinculado->margem_lucro;
                    }
                    elseif ($produtoVinculado->categoria && !empty($produtoVinculado->categoria->margem_lucro)) {
                        $margemAplicada = $produtoVinculado->categoria->margem_lucro;
                    }
                }

                $precoVendaSugerido = $precoCusto * (1 + ($margemAplicada / 100));

                $itens[] = [
                    'descricao_nota' => (string)$itemXml->prod->xProd,
                    'codigo_fornecedor' => (string)$itemXml->prod->cProd,
                    'ean' => (string)$itemXml->prod->cEAN,
                    'ncm' => (string)$itemXml->prod->NCM,
                    'cfop' => (string)$itemXml->prod->CFOP,
                    'unidade' => (string)$itemXml->prod->uCom,
                    'quantidade' => (float)$itemXml->prod->qCom,
                    'preco_custo' => $precoCusto,
                    'subtotal' => (float)$itemXml->prod->vProd,
                    'vinculo_existente' => $vinculo ? $vinculo->produto->toArray() : null,
                    'preco_venda_sugerido' => number_format($precoVendaSugerido, 2, '.', ''),
                ];
            }

            $dadosNFe = [
                'chave_acesso' => $chaveAcesso,
                'numero_nota' => (string)$infNFe->ide->nNF,
                'data_emissao' => new \DateTime((string)$infNFe->ide->dhEmi),
                'valor_total' => (float)$infNFe->total->ICMSTot->vNF,
                'fornecedor' => [
                    'cnpj' => $cnpjFornecedor,
                    'razao_social' => (string)$infNFe->emit->xNome,
                    'existente' => !is_null($fornecedor),
                ],
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
        $dadosNFe = Session::get('importacao_nfe');

        if (!$dadosNFe) {
            return redirect()->route('compras.index')->with('error', 'Nenhuma nota para revisar.');
        }

        $produtosDoSistema = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get();

        return view('compras.revisar', compact('dadosNFe', 'produtosDoSistema', 'categorias'));
    }


    public function salvarImportacao(Request $request)
    {
        $dadosNFeDaSessao = Session::get('importacao_nfe');

       
    
        if (!$dadosNFeDaSessao) {
            return redirect()->route('compras.index')->with('error', 'Sessão da importação expirada ou dados não encontrados.');
        }
    
        $itensOriginais = $dadosNFeDaSessao['itens'];
        $dadosDoFormulario = $request->input('itens', []);
        $configuracoes = Configuracao::pluck('valor', 'chave')->all();
        $margensCategorias = Categoria::pluck('margem_lucro', 'id');
    
        DB::beginTransaction();
        try {
            $fornecedor = Fornecedor::firstOrCreate(
                ['cpf_cnpj' => $dadosNFeDaSessao['fornecedor']['cnpj']],
                ['razao_social' => $dadosNFeDaSessao['fornecedor']['razao_social']]
            );
    
            $compra = Compra::create([
                'fornecedor_id' => $fornecedor->id,
                'empresa_id' => Auth::user()->empresa_id,
                'numero_nota' => $dadosNFeDaSessao['numero_nota'],
                'data_emissao' => $dadosNFeDaSessao['data_emissao'],
                'valor_total_nota' => $dadosNFeDaSessao['valor_total'],
                'chave_acesso_nfe' => $dadosNFeDaSessao['chave_acesso'],
                'status' => 'pendente',
            ]);
    
            foreach ($itensOriginais as $index => $itemOriginal) {
                $dadosModificados = $dadosDoFormulario[$index] ?? [];
                $nomeFinal = $dadosModificados['nome'] ?? $itemOriginal['vinculo_existente']['nome'] ?? $itemOriginal['descricao_nota'];
                $categoriaFinal = $dadosModificados['categoria_id'] ?? $itemOriginal['vinculo_existente']['categoria_id'] ?? $configuracoes['categoria_padrao_id'] ?? null;
                $produtoIdVinculado = $dadosModificados['produto_id'] ?? $itemOriginal['vinculo_existente']['id'] ?? null;
    
                $precoVendaFinal = 0;
                if (isset($dadosModificados['preco_venda']) && $dadosModificados['preco_venda'] !== $itemOriginal['preco_venda_sugerido']) {
                    $precoVendaFinal = $dadosModificados['preco_venda'];
                } else {
                    $precoCusto = $itemOriginal['preco_custo'];
                    $margem = $margensCategorias[$categoriaFinal] ?? null;
                    if (is_null($margem)) {
                        $margem = (float)($configuracoes['margem_lucro_padrao'] ?? 0);
                    }
                    $precoVendaFinal = $precoCusto * (1 + ($margem / 100));
                }
    
                $dadosFinaisProduto = [
                    'nome' => $nomeFinal,
                    'categoria_id' => $categoriaFinal,
                    'preco_venda' => $precoVendaFinal,
                    'preco_custo' => $itemOriginal['preco_custo'],
                    'codigo_barras' => $itemOriginal['ean'],
                    'ativo' => true,
                    'detalhe_id' => null,
                    'detalhe_type' => null,
                ];

                dd(
                    'VERIFICANDO O PRIMEIRO ITEM DO LOOP:',
                    'Nome Final Calculado:', $nomeFinal,
                    'Categoria Final Calculada:', $categoriaFinal,
                    'Array completo para salvar o produto:', $dadosFinaisProduto
                );
    
                if (empty($dadosFinaisProduto['nome']) || empty($dadosFinaisProduto['categoria_id'])) {
                    continue;
                }
    
                $produtoFinal = null;
                if ($produtoIdVinculado) {
                    $produto = Produto::find($produtoIdVinculado);
                    if ($produto) {
                        $produto->update($dadosFinaisProduto);
                        $produtoFinal = $produto;
                    }
                } else {
                    $produtoExistente = null;
                    if (!empty($dadosFinaisProduto['codigo_barras'])) {
                        $produtoExistente = Produto::where('codigo_barras', $dadosFinaisProduto['codigo_barras'])->first();
                    }
                    if ($produtoExistente) {
                        $produtoExistente->update($dadosFinaisProduto);
                        $produtoFinal = $produtoExistente;
                    } else {
                        $produtoFinal = Produto::create($dadosFinaisProduto);
                    }
                }


                   // ================== ADICIONE O DEBUG AQUI ==================

                if ($produtoFinal) {
                    $fatorConversao = (float)($dadosModificados['fator_conversao'] ?? 1);
                    $quantidadeDaNota = (float)$itemOriginal['quantidade'];
                    $quantidadeParaAdicionar = $quantidadeDaNota * $fatorConversao;
                    $produtoFinal->increment('estoque_atual', $quantidadeParaAdicionar);
    
                    // ================== INÍCIO DA LÓGICA DE VÍNCULO ADICIONADA ==================
                    // Cria ou atualiza o vínculo entre o produto do seu sistema e o código do fornecedor
                    ProdutoFornecedor::updateOrCreate(
                        [
                            'fornecedor_id' => $fornecedor->id,
                            'codigo_produto_fornecedor' => $itemOriginal['codigo_fornecedor'],
                        ],
                        [
                            'produto_id' => $produtoFinal->id,
                            'ean_fornecedor' => $itemOriginal['ean'],
                        ]
                    );
                    // =================== FIM DA LÓGICA DE VÍNCULO ADICIONADA ===================
    
                    ItemCompra::create([
                        'compra_id' => $compra->id,
                        'produto_id' => $produtoFinal->id,
                        'descricao_item_nota' => $itemOriginal['descricao_nota'],
                        'quantidade' => $itemOriginal['quantidade'],
                        'preco_custo_nota' => $itemOriginal['preco_custo'],
                        'subtotal' => $itemOriginal['subtotal'],
                        'codigo_produto_nota' => $itemOriginal['codigo_fornecedor'],
                        'ean_nota' => $itemOriginal['ean'],
                        'ncm' => $itemOriginal['ncm'],
                        'cfop' => $itemOriginal['cfop'],
                    ]);
                }
            }
    
            DB::commit();
            Session::forget('importacao_nfe');
            return redirect()->route('compras.index')->with('success', 'Nota importada e todos os produtos cadastrados/atualizados com sucesso!');
    
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