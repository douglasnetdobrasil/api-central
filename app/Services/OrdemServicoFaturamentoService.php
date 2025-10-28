<?php

namespace App\Services;

use App\Models\OrdemServico;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\VendaPagamento;
use App\Models\ContaAReceber;
use App\Models\FormaPagamento;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Exception;

class OrdemServicoFaturamentoService
{
    /**
     * Converte uma Ordem de Serviço em uma Venda e gera as Contas a Receber.
     * @param OrdemServico $os A Ordem de Serviço a ser faturada.
     * @param FormaPagamento $formaPagamento A forma de pagamento escolhida.
     * @param array $dadosPagamento Informações de parcelamento ou à vista.
     * @return Venda
     */
    public function faturar(OrdemServico $os, FormaPagamento $formaPagamento, array $dadosPagamento): Venda
    {
        // REGRA 1: Checa se a OS está apta a ser faturada (Concluída e não faturada)
        if ($os->status !== 'Concluida' || $os->venda_id !== null) {
            throw new Exception("A OS #{$os->id} não pode ser faturada.");
        }

        // REGRA 2: A Forma de Pagamento deve existir
        if (!$formaPagamento) {
            throw new Exception("Forma de pagamento inválida.");
        }

        return DB::transaction(function () use ($os, $formaPagamento, $dadosPagamento) {
            $user = $os->tecnico ?? $os->user; // Usa o técnico da OS, ou quem abriu

            // 1. CRIAÇÃO DA VENDA
            $venda = Venda::create([
                'empresa_id' => $os->empresa_id,
                'user_id' => $user->id,
                'cliente_id' => $os->cliente_id,
                'subtotal' => bcadd($os->valor_servicos, $os->valor_produtos, 2),
                'desconto' => $os->valor_desconto,
                'total' => $os->valor_total,
                'status' => 'faturada_os',
                'observacoes' => "Venda gerada a partir da OS #{$os->id}. Equipamento: {$os->equipamento}",
            ]);

            // 2. ATUALIZA OS
            $os->update(['venda_id' => $venda->id]);
            $os->osHistorico()->create(['user_id' => $user->id, 'descricao' => "OS Faturada e convertida na Venda #{$venda->id}."]);
            
            // 3. CRIA ITENS DA VENDA (Produtos e Serviços da OS)
            // Produtos (Peças)
            foreach ($os->produtos as $osProduto) {
                VendaItem::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $osProduto->produto_id,
                    'descricao_produto' => $osProduto->produto->nome ?? 'Peça/Produto',
                    'quantidade' => $osProduto->quantidade,
                    'preco_unitario' => $osProduto->preco_unitario,
                    'subtotal_item' => $osProduto->subtotal,
                    // CFOP e outros dados fiscais seriam incluídos aqui, se necessário
                ]);
            }
            // Serviços
            foreach ($os->servicos as $osServico) {
                 VendaItem::create([
                    'venda_id' => $venda->id,
                    'produto_id' => $osServico->servico_id, // Usamos o ID do Produto/Serviço
                    'descricao_produto' => $osServico->servico->nome ?? 'Serviço',
                    'quantidade' => $osServico->quantidade,
                    'preco_unitario' => $osServico->preco_unitario,
                    'subtotal_item' => $osServico->subtotal,
                ]);
            }

            // 4. GERAÇÃO DO FINANCEIRO (Contas a Receber)
            $this->gerarContasAReceber($venda, $formaPagamento, $dadosPagamento);

            return $venda;
        });
    }

    private function gerarContasAReceber(Venda $venda, FormaPagamento $formaPagamento, array $dadosPagamento): void
    {
        $valorTotal = $venda->total;
        $numParcelas = $formaPagamento->numero_parcelas;
        $intervaloDias = $formaPagamento->dias_intervalo;
        $dataVencimento = Carbon::parse($dadosPagamento['data_vencimento']);

        // Se for à vista ou em 1x, é uma única conta
        if ($numParcelas <= 1) {
             ContaAReceber::create([
                'empresa_id' => $venda->empresa_id,
                'cliente_id' => $venda->cliente_id,
                'venda_id' => $venda->id,
                'descricao' => "Venda OS #{$venda->id} - {$formaPagamento->nome}",
                'parcela_numero' => 1,
                'parcela_total' => 1,
                'valor' => $valorTotal,
                'valor_recebido' => 0.00,
                'data_vencimento' => $dataVencimento,
                'status' => 'A Receber'
            ]);

            // Também cria um registro em venda_pagamentos
            VendaPagamento::create([
                'empresa_id' => $venda->empresa_id,
                'venda_id' => $venda->id,
                'forma_pagamento_id' => $formaPagamento->id,
                'valor' => $valorTotal,
            ]);

        } else {
            // Lógica para parcelamento (Tipo 'a_prazo')
            $valorParcela = round($valorTotal / $numParcelas, 2);
            $valorRestante = $valorTotal;
            
            for ($i = 1; $i <= $numParcelas; $i++) {
                $valorAtual = ($i == $numParcelas) ? $valorRestante : $valorParcela;
                
                // Ajuste para garantir que o total das parcelas seja exatamente o valor total
                if ($i < $numParcelas) {
                    $valorRestante = bcsub((string)$valorRestante, (string)$valorParcela, 2);
                } else {
                    $valorAtual = $valorRestante; // A última parcela cobre a diferença
                }

                $dataAtual = $dataVencimento->copy()->addDays(($i - 1) * $intervaloDias);

                ContaAReceber::create([
                    'empresa_id' => $venda->empresa_id,
                    'cliente_id' => $venda->cliente_id,
                    'venda_id' => $venda->id,
                    'descricao' => "Venda OS #{$venda->id} - Parcela {$i}/{$numParcelas}",
                    'parcela_numero' => $i,
                    'parcela_total' => $numParcelas,
                    'valor' => $valorAtual,
                    'valor_recebido' => 0.00,
                    'data_vencimento' => $dataAtual,
                    'status' => 'A Receber'
                ]);

                // Registro para venda_pagamentos (apenas o principal)
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