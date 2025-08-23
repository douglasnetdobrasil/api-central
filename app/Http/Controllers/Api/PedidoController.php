<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePedidoRequest;
use App\Models\ContaAReceber;
use App\Models\HistoricoPedido;
use App\Models\ItemPedido;
use App\Models\Pedido;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PedidoController extends Controller
{
    /**
     * Armazena um novo pedido de venda no sistema.
     */
    public function store(StorePedidoRequest $request)
    {
        // Os dados já chegam validados (cliente/vendedor existem, estoque é suficiente, etc.)
        $dadosValidados = $request->validated();

        try {
            // A transação garante que todas as operações sejam bem-sucedidas.
            // Se qualquer uma falhar, todas são desfeitas (rollback).
            $pedido = DB::transaction(function () use ($dadosValidados) {

                // 1. Calcula o valor total do pedido a partir dos itens.
                $valorTotalPedido = 0;
                foreach ($dadosValidados['itens'] as $item) {
                    $valorTotalPedido += $item['quantidade'] * $item['preco_unitario_venda'];
                }

                // 2. Cria o registro na tabela 'pedidos'.
                $novoPedido = Pedido::create([
                    'cliente_id' => $dadosValidados['cliente_id'],
                    'vendedor_id' => $dadosValidados['vendedor_id'],
                    'valor_total' => $valorTotalPedido,
                    'status' => 'Pendente', // Status inicial padrão
                    'observacao' => $dadosValidados['observacao'] ?? null,
                ]);

                // 3. Itera sobre os itens do pedido para salvá-los e dar baixa no estoque.
                foreach ($dadosValidados['itens'] as $item) {
                    $produto = Produto::find($item['produto_id']);

                    ItemPedido::create([
                        'pedido_id' => $novoPedido->id,
                        'produto_id' => $item['produto_id'],
                        'quantidade' => $item['quantidade'],
                        'unidade_medida_id' => $produto->detalhe->unidade_medida_id,
                        'preco_unitario_venda' => $item['preco_unitario_venda'],
                        'subtotal' => $item['quantidade'] * $item['preco_unitario_venda'],
                    ]);

                    // 4. Dá baixa no estoque do produto (na tabela de detalhes).
                    $produto->detalhe->decrement('estoque_atual', $item['quantidade']);
                }

                // 5. Gera a pendência financeira em 'contas_a_receber'.
                ContaAReceber::create([
                    'pedido_id' => $novoPedido->id,
                    'descricao' => "Recebimento referente ao Pedido de Venda #" . $novoPedido->id,
                    'valor' => $valorTotalPedido,
                    'data_vencimento' => now()->addDays(30), // Vencimento padrão para 30 dias
                ]);

                // 6. Cria o primeiro evento na timeline (histórico) do pedido.
                HistoricoPedido::create([
                    'pedido_id' => $novoPedido->id,
                    'usuario_id' => $dadosValidados['vendedor_id'],
                    'descricao_acao' => 'Pedido realizado pelo sistema.',
                ]);

                return $novoPedido;
            });

            // Se a transação foi um sucesso, retorna o pedido criado com seus itens
            return response()->json($pedido->load('itens.produto', 'cliente'), 201);

        } catch (\Exception $e) {
            // Se qualquer erro ocorrer, a transação é desfeita e um log é gerado.
            Log::error('Erro ao registrar pedido: ', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Ocorreu um erro inesperado ao registrar o pedido.'], 500);
        }
    }

    // Os outros métodos do CRUD (index, show, update, destroy) podem ser implementados depois.
}