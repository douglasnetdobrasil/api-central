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
            // --- LINHAS ESSENCIAIS RESTAURADAS ---
            $xmlContent = $request->file('xml_file')->getContent();
            $xml = simplexml_load_string($xmlContent);
            if ($xml === false) {
                throw new Exception("Não foi possível carregar o conteúdo do XML.");
            }
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
            // ------------------------------------
    
            $infNFe = $xml->xpath('//nfe:infNFe')[0];
            if (!$infNFe) {
                throw new Exception("Estrutura do XML inválida: tag <infNFe> não encontrada.");
            }
            
            $chaveAcesso = str_replace('NFe', '', (string)$infNFe->attributes()->Id);
    
            // REGRA 2: VERIFICA SE A NOTA JÁ FOI LANÇADA
            $notaExistente = Compra::where('chave_acesso_nfe', $chaveAcesso)->first();
            if ($notaExistente) {
                return back()->with('error', "Atenção: A Nota Fiscal (Chave: {$chaveAcesso}) já foi importada em " . $notaExistente->created_at->format('d/m/Y') . ".");
            }
    
            // REGRA 3: VERIFICA O FORNECEDOR
            $cnpjFornecedor = (string)$infNFe->emit->CNPJ;
            $fornecedor = Fornecedor::where('cpf_cnpj', $cnpjFornecedor)->first();
    
            // Pega a margem padrão da sua tabela `configuracoes`
            $margemPadrao = \App\Models\Configuracao::where('chave', 'margem_lucro_padrao')->value('valor') ?? 0;
    
            $itens = [];
            foreach ($xml->xpath('//nfe:det') as $itemXml) {
                $vinculo = null;
                // REGRA 4: VERIFICA VÍNCULOS DE PRODUTOS
                if ($fornecedor) {
                    $vinculo = ProdutoFornecedor::with('produto.categoria')
                        ->where('fornecedor_id', $fornecedor->id)
                        ->where('codigo_produto_fornecedor', (string)$itemXml->prod->cProd)
                        ->first();
                }
                
                $precoCusto = (float)$itemXml->prod->vUnCom;
                $margemAplicada = $margemPadrao;
    
                // REGRA 5: APLICA HIERARQUIA DE MARGENS
                if ($vinculo) {
                    $produtoVinculado = $vinculo->produto;
                    // 1ª Prioridade: Margem do próprio produto
                    if (!empty($produtoVinculado->margem_lucro)) {
                        $margemAplicada = $produtoVinculado->margem_lucro;
                    } 
                    // 2ª Prioridade: Margem da categoria do produto
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

    /**
     * ETAPA 2: Exibe a tela de conferência com os dados da sessão.
     */
    public function revisarImportacao()
    {
        $dadosNFe = Session::get('importacao_nfe');

        if (!$dadosNFe) {
            return redirect()->route('compras.index')->with('error', 'Nenhuma nota para revisar.');
        }

        // Carrega os dados necessários para os dropdowns da view
        $produtosDoSistema = Produto::orderBy('nome')->get();
        $categorias = Categoria::orderBy('nome')->get(); // <-- ESTA É A LINHA QUE ESTAVA FALTANDO

        // Passe a variável $categorias para a view
        return view('compras.revisar', compact('dadosNFe', 'produtosDoSistema', 'categorias')); // <-- AQUI TAMBÉM
    }


    /**
     * ETAPA 3: Recebe os dados da tela de conferência e salva TUDO no banco.
     */
    public function salvarImportacao(Request $request)
    {
        // Definição das regras de validação
        $regrasDeValidacao = [
            'itens' => 'required|array',
            'itens.*.nome' => 'required|string|max:255',
            'itens.*.categoria_id' => 'nullable|exists:categorias,id',
            'itens.*.preco_venda' => 'required|numeric|min:0',
            'itens.*.unidade_venda_id' => 'nullable|exists:unidades_medida,id', // Nome da tabela corrigido
        ];
    
        // AQUI ESTÁ A MUDANÇA: A validação é executada e seu resultado (os dados validados)
        // é diretamente atribuído à variável $dadosValidados.
        $dadosValidados = $request->validate($regrasDeValidacao);
    
        // Carrega as configurações padrões do banco de dados
        $configuracoes = Configuracao::pluck('valor', 'chave')->all();
    
        // Processa cada item, aplicando os padrões quando necessário
        foreach ($dadosValidados['itens'] as $itemData) {
            // Se o usuário não escolheu uma categoria, usa o padrão do banco
            if (empty($itemData['categoria_id'])) {
                $itemData['categoria_id'] = $configuracoes['categoria_padrao_id'] ?? null;
            }
    
            // Se o usuário não escolheu uma unidade, usa o padrão do banco
            if (empty($itemData['unidade_venda_id'])) {
                $itemData['unidade_venda_id'] = $configuracoes['unidade_padrao_id'] ?? null;
            }
    
            // Garante que não tentaremos criar um produto sem categoria
            if (empty($itemData['categoria_id'])) {
                continue; // Pula para o próximo item
            }
    
            // Lógica para Criar ou Atualizar o produto
            if (!empty($itemData['produto_id'])) {
                // Seu código para ATUALIZAR um produto existente...
            } else {
                // Cria um novo produto
                Produto::create($itemData);
            }
        }
    
        // Sua lógica para salvar o fornecedor...
    
        return redirect()->route('compras.index')->with('success', 'Nota importada com sucesso!');
    }

    public function update(Request $request, string $id)
    {
        // Este método parece correto, mantido como estava.
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
                    if(method_exists($produto, 'calcularPrecoVenda')) {
                        $produto->preco_venda = $produto->calcularPrecoVenda((float)$itemData['preco_entrada']);
                    }
                    $produto->save();

                    ProdutoFornecedor::updateOrCreate(
                        ['fornecedor_id' => $compra->fornecedor_id, 'codigo_produto_fornecedor' => $item->codigo_produto_nota],
                        ['produto_id' => $produto->id, 'ean_fornecedor' => $item->ean_nota]
                    );

                    $item->update(['produto_id' => $produto->id, 'preco_entrada' => $itemData['preco_entrada']]);
                }

                $compra->status = 'finalizada';
                $compra->save();
            });

            return redirect()->route('compras.index')->with('success', 'Nota de compra #' . $id . ' finalizada com sucesso!');
        } catch (Exception $e) {
            return back()->with('error', 'Ocorreu um erro: ' . $e->getMessage());
        }
    }

    // CORREÇÃO 3: Assinatura do método e lógica interna totalmente revisadas.
    private function vincularOuCriarProduto(Fornecedor $fornecedor, ItemCompra $itemCompra, string $unidadeComercialXml): void
    {
        $vinculo = ProdutoFornecedor::where('fornecedor_id', $fornecedor->id)
                                    ->where('codigo_produto_fornecedor', $itemCompra->codigo_produto_nota)
                                    ->first();
    
        $produtoIdFinal = null;
    
        if ($vinculo) {
            // Se o vínculo já existe, apenas atualizamos o produto existente.
            $produto = Produto::find($vinculo->produto_id);
            if ($produto) {
                $produto->preco_custo = $itemCompra->preco_custo_nota;
                $produto->save();
                $produtoIdFinal = $produto->id;
            }
        } else {
            // Se não há vínculo, é um produto novo.
            $detalheId = null;
            $detalheType = null; // <-- CORREÇÃO 1: Inicia a variável do TIPO como nula.
            $usuario = Auth::user();
    
            if ($usuario->empresa && $usuario->empresa->nicho_negocio === 'mercado') {
                
                $unidadeMedida = UnidadeMedida::firstOrCreate(
                    ['sigla' => strtoupper($unidadeComercialXml)],
                    ['nome' => $unidadeComercialXml]
                );
    
                $detalheItem = DetalhesItemMercado::create([
                    'codigo_barras'     => $itemCompra->ean_nota,
                    'fornecedor_id'     => $fornecedor->id,
                    'preco_custo'       => $itemCompra->preco_custo_nota,
                    'estoque_atual'     => $itemCompra->quantidade,
                    'marca'             => null,
                    'categoria_id'      => null,
                    'estoque_minimo'    => 1,
                    'unidade_medida_id' => $unidadeMedida->id,
                    'controla_validade' => false,
                    'vendido_por_peso'  => false,
                ]);
    
                $detalheId = $detalheItem->id;
                $detalheType = DetalhesItemMercado::class; // <-- CORREÇÃO 2: Define o TIPO quando o detalhe é criado.
            }
    
            // Cria o registro principal do produto
            $novoProduto = Produto::create([
                'nome'         => $itemCompra->descricao_item_nota,
                'ativo'        => true,
                'categoria_id' => null,
                'detalhe_id'   => $detalheId,
                'detalhe_type' => $detalheType, // <-- CORREÇÃO 3: Passa o TIPO ao criar o produto.
                'preco_custo'  => $itemCompra->preco_custo_nota,
                'preco_venda'  => 0
            ]);
            
            if(method_exists($novoProduto, 'calcularPrecoVenda')) {
                $novoProduto->preco_venda = $novoProduto->calcularPrecoVenda($novoProduto->preco_custo);
                $novoProduto->save();
            }
    
            $produtoIdFinal = $novoProduto->id;
    
            ProdutoFornecedor::create([
                'produto_id'                => $produtoIdFinal,
                'fornecedor_id'             => $fornecedor->id,
                'codigo_produto_fornecedor' => $itemCompra->codigo_produto_nota,
                'ean_fornecedor'            => $itemCompra->ean_nota,
            ]);
        }
    
        if ($produtoIdFinal) {
            $itemCompra->produto_id = $produtoIdFinal;
            $itemCompra->save();
        }
    }



    public function destroy(Compra $compra)
    {
        // Usamos uma transação para garantir que ou tudo funciona, ou nada é alterado.
        DB::beginTransaction();
        try {
            // 1. Itera sobre cada item da nota que será excluída
            foreach ($compra->itens as $item) {
                // Verifica se há um produto vinculado
                if ($item->produto) {
                    // 2. Estorna o estoque: subtrai a quantidade que entrou
                    $item->produto->decrement('estoque_atual', $item->quantidade);
                }
            }

            // 3. Depois de estornar o estoque de todos os itens, apaga a nota
            $compra->delete();

            // 4. Confirma as alterações no banco de dados
            DB::commit();

            return redirect()->route('compras.index')->with('success', 'Nota fiscal e estoque estornado com sucesso.');

        } catch (\Exception $e) {
            // 5. Se qualquer passo falhar, desfaz todas as operações
            DB::rollBack();
            return redirect()->back()->with('error', 'Ocorreu um erro ao remover a nota: ' . $e->getMessage());
        }
    }
}



