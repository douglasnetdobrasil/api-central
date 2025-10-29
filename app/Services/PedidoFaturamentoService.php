<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\VendaPagamento;
use App\Models\ContaAReceber;
use App\Models\FormaPagamento;
use App\Models\Produto; // Importar Produto
use App\Services\EstoqueService; // Importar EstoqueService
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class PedidoFaturamentoService
{
    // Injetar o EstoqueService (MUITO IMPORTANTE)
    protected $estoqueService;

    public function __construct(EstoqueService $estoqueService)
    {
        $this->estoqueService = $estoqueService;
    }

    /**
     * Converte um Pedido de Venda em uma Venda e gera o financeiro.
     * @param Pedido $pedido O Pedido a ser faturado.
     * @param FormaPagamento $formaPagamento A forma de pagamento.
     * @param array $dadosPagamento Informações de parcelamento (data_vencimento).
     * @return Venda
     */
    public function faturar(Pedido $pedido, FormaPagamento $formaPagamento, array $dadosPagamento): Venda
    {
        // REGRA 1: Checa se o Pedido está apto (Aprovado e não faturado)
        // (Ajuste 'Aprovado' para o status correto do seu fluxo)
        if ($pedido->status !== 'Aprovado' || $pedido->venda_id !== null) {
            throw new Exception("O Pedido #{$pedido->id} não pode ser faturado.");
        }

        return DB::transaction(function () use ($pedido, $formaPagamento, $dadosPagamento) {
            
            // 1. CRIAÇÃO DA VENDA
            $venda = Venda::create([
                'empresa_id' => $pedido->empresa_id,
                'user_id' => $pedido->vendedor_id, // Pega o vendedor do pedido
                'cliente_id' => $pedido->cliente_id,
                'subtotal' => $pedido->valor_total, // Assumindo que Pedido tem subtotal, ou recalcular
                'desconto' => 0.00, // Pegar do pedido se existir
                'total' => $pedido->valor_total,
                'status' => 'faturada_pedido', // Novo status
                'observacoes' => "Venda gerada a partir do Pedido #{$pedido->id}.",
            ]);

            // 2. ATUALIZA O PEDIDO
            $pedido->update(['venda_id' => $venda->id, 'status' => 'Faturado']);
            
            // 3. CRIA ITENS DA VENDA E DÁ BAIXA NO ESTOQUE
            foreach ($pedido->itens as $itemPedido) { //
                
                $produto = Produto::find($itemPedido->produto_id); //

                VendaItem::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $itemPedido->produto_id,
                    'descricao_produto' => $produto->nome ?? 'Produto do Pedido',
                    'quantidade' => $itemPedido->quantidade,
                    'preco_unitario' => $itemPedido->preco_unitario_venda, //
                    'subtotal_item' => $itemPedido->subtotal, //
                ]);

                // *** AQUI ESTÁ A LIGAÇÃO CRÍTICA ***
                // Dá baixa no estoque usando o serviço que já existe
                $this->estoqueService->registrarMovimento(
                    $produto, 
                    'saida_venda_pedido', 
                    $itemPedido->quantidade, 
                    $venda, // A origem é a Venda
                    "Venda #{$venda->id} (Origem: Pedido #{$pedido->id})"
                );
            }

            // 4. GERAÇÃO DO FINANCEIRO (Contas a Receber)
            // (Esta lógica é idêntica à do OrdemServicoFaturamentoService)
            $this->gerarContasAReceber($venda, $formaPagamento, $dadosPagamento);

            return $venda;
        });
    }

    /**
     * Lógica privada para gerar o financeiro.
     * (Esta função é uma cópia exata do OrdemServicoFaturamentoService.
     * No futuro, ela pode virar um Trait para não repetir o código)
     */
    private function gerarContasAReceber(Venda $venda, FormaPagamento $formaPagamento, array $dadosPagamento): void
    {
        $valorTotal = $venda->total;
        $numParcelas = $formaPagamento->numero_parcelas;
        $intervaloDias = $formaPagamento->dias_intervalo;
        $dataVencimento = Carbon::parse($dadosPagamento['data_vencimento']);

        // Se for à vista ou em 1x
        if ($numParcelas <= 1) {
             ContaAReceber::create([
                'empresa_id' => $venda->empresa_id,
                'cliente_id' => $venda->cliente_id,
                'venda_id' => $venda->id,
                'descricao' => "Venda Pedido #{$venda->id} - {$formaPagamento->nome}",
                'parcela_numero' => 1,
                'parcela_total' => 1,
                'valor' => $valorTotal,
                'data_vencimento' => $dataVencimento,
                'status' => 'A Receber'
            ]);
            VendaPagamento::create([
                'empresa_id' => $venda->empresa_id,
                'venda_id' => $venda->id,
                'forma_pagamento_id' => $formaPagamento->id,
                'valor' => $valorTotal,
            ]);

        } else {
            // Lógica para parcelamento
            $valorParcela = round($valorTotal / $numParcelas, 2);
            $valorRestante = $valorTotal;
            
            for ($i = 1; $i <= $numParcelas; $i++) {
                $valorAtual = ($i == $numParcelas) ? $valorRestante : $valorParcela;
                
                if ($i < $numParcelas) {
                    $valorRestante = bcsub((string)$valorRestante, (string)$valorParcela, 2);
                } else {
                    $valorAtual = $valorRestante; // Última parcela
                }

                $dataAtual = $dataVencimento->copy()->addDays(($i - 1) * $intervaloDias);

                ContaAReceber::create([
                    'empresa_id' => $venda->empresa_id,
                    'cliente_id' => $venda->cliente_id,
                    'venda_id' => $venda->id,
                    'descricao' => "Venda Pedido #{$venda->id} - Parcela {$i}/{$numParcelas}",
                    'parcela_numero' => $i,
                    'parcela_total' => $numParcelas,
                    'valor' => $valorAtual,
                    'data_vencimento' => $dataAtual,
                    'status' => 'A Receber'
                ]);

                if ($i === 1) {
                    VendaPagamento::create([
                        'empresa_id' => $venda->empresa_id,
                        'venda_id' => $venda->id,
                        'forma_pagamento_id' => $formaPagamento->id,
                        'valor' => $valorTotal,
                    ]);
                }
            }
        }
    }
}