<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoMercadoRequest;
use App\Models\DetalheItemMercado;
use App\Models\Produto; // Adicionado para a função index
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UpdateProdutoMercadoRequest; // Adicione este 'use' no topo do arquivo



class ProdutoMercadoController extends Controller
{
    /**
     * Lista todos os produtos do nicho de mercado.
     */
    public function index()
    {
        // Esta consulta busca na tabela 'produtos', mas filtra ONDE o relacionamento
        // polimórfico 'detalhe' é do tipo 'DetalheItemMercado'.
        $produtosDeMercado = Produto::whereHasMorph(
            'detalhe',
            [DetalheItemMercado::class]
        )
        ->with('detalhe', 'dadoFiscal') // Carrega os relacionamentos para evitar múltiplas queries (problema N+1)
        ->paginate(15); // Pagina o resultado para não sobrecarregar a API

        return response()->json($produtosDeMercado);
    }

    /**
     * Armazena um novo produto do nicho de mercado.
     */
    public function store(StoreProdutoMercadoRequest $request)
    {
        $dadosValidados = $request->validated();

        try {
            $produto = DB::transaction(function () use ($dadosValidados) {
                $detalhes = DetalheItemMercado::create($dadosValidados['detalhes']);

                $produto = $detalhes->produto()->create([
                    'nome' => $dadosValidados['nome'],
                    'preco_venda' => $dadosValidados['preco_venda'],
                ]);

                if (isset($dadosValidados['dados_fiscais'])) {
                    $produto->dadoFiscal()->create($dadosValidados['dados_fiscais']);
                }

                return $produto;
            });

            return response()->json($produto->load('detalhe', 'dadoFiscal'), 201);

        } catch (\Exception $e) {
            Log::error('Erro ao registrar produto de mercado: ', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ocorreu um erro inesperado ao registrar o produto.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Produto $produto)
    {
        // Nós só precisamos verificar se o produto encontrado é realmente do tipo 'mercado'
        // e carregar os relacionamentos antes de retorná-lo.
        if (!($produto->detalhe instanceof DetalheItemMercado)) {
            // Se não for um produto de mercado, retorna um erro 404
            return response()->json(['message' => 'Produto não encontrado neste nicho.'], 404);
        }
    
        return response()->json($produto->load('detalhe', 'dadoFiscal'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProdutoMercadoRequest $request, Produto $produto)
{
    // Garante que estamos lidando com um produto do nicho correto
    if (!($produto->detalhe instanceof \App\Models\DetalheItemMercado)) {
        return response()->json(['message' => 'Este produto não pertence ao nicho de Mercado.'], 404);
    }

    $dadosValidados = $request->validated();

    try {
        DB::transaction(function () use ($produto, $dadosValidados) {
            // 1. Atualiza a tabela principal 'produtos' (se houver dados para ela)
            $produto->update($dadosValidados);

            // 2. Atualiza a tabela de detalhes (se houver dados para ela)
            if (isset($dadosValidados['detalhes'])) {
                $produto->detalhe->update($dadosValidados['detalhes']);
            }

            // 3. Atualiza a tabela de dados fiscais (se houver dados para ela)
            if (isset($dadosValidados['dados_fiscais'])) {
                $produto->dadoFiscal->update($dadosValidados['dados_fiscais']);
            }
        });

        // Retorna o produto com os dados atualizados e todos os relacionamentos
        return response()->json($produto->fresh()->load('detalhe', 'dadoFiscal'));

    } catch (\Exception $e) {
        Log::error('Erro ao atualizar produto de mercado: ', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Ocorreu um erro inesperado ao atualizar o produto.'], 500);
    }
}
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        // Garante que estamos lidando com um produto do nicho correto
        if (!($produto->detalhe instanceof \App\Models\DetalheItemMercado)) {
            return response()->json(['message' => 'Produto não encontrado neste nicho.'], 404);
        }
    
        try {
            DB::transaction(function () use ($produto) {
                // Deleta os registros relacionados primeiro para manter a integridade
                optional($produto->dadoFiscal)->delete(); // optional() previne erro se não houver dado fiscal
                $produto->detalhe->delete();
    
                // Por fim, deleta o produto principal
                $produto->delete();
            });
    
            // O status 204 (No Content) é a resposta padrão para uma exclusão bem-sucedida
            return response()->json(null, 204);
    
        } catch (\Exception $e) {
            Log::error('Erro ao deletar produto de mercado: ', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ocorreu um erro inesperado ao deletar o produto.'], 500);
        }
    }
}