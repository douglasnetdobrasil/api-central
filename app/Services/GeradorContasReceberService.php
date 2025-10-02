<?php

namespace App\Services;

use App\Models\Venda;
use App\Models\ContaAReceber;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log; // Importe a classe Log

class GeradorContasReceberService
{
    /**
     * Gera as contas a receber para uma venda recém-criada.
     */
    public function gerarPelaVenda(Venda $venda): void
    {
        $venda->load('pagamentos.formaPagamento');

        Log::info("Iniciando geração de contas para Venda #{$venda->id}. Total de pagamentos: " . $venda->pagamentos->count());

        foreach ($venda->pagamentos as $pagamento) {
            $formaPagamento = $pagamento->formaPagamento;

            if (!$formaPagamento) {
                Log::warning("Venda #{$venda->id}: Forma de pagamento para o pagamento #{$pagamento->id} não encontrada. Pulando...");
                continue;
            }

            // << MELHORIA AQUI >>: Logando a informação crucial para debug
            Log::info("Processando pagamento #{$pagamento->id} com Forma '{$formaPagamento->nome}' (Tipo: {$formaPagamento->tipo}, Parcelas: {$formaPagamento->numero_parcelas})");

            if ($formaPagamento->tipo === 'a_prazo' && $formaPagamento->numero_parcelas > 0) {
                Log::info("Tipo 'a_prazo' detectado. Gerando parcelas...");
                $this->gerarParcelas($venda, $pagamento);
            } 
            else {
                Log::info("Tipo 'a_vista' ou não especificado detectado. Gerando recebimento à vista...");
                $this->gerarRecebimentoAVista($venda, $pagamento);
            }
        }
        Log::info("Finalizada geração de contas para Venda #{$venda->id}.");
    }

    /**
     * Gera as parcelas para um pagamento a prazo.
     */
    private function gerarParcelas(Venda $venda, $pagamento): void
    {
        // (O restante do código dos métodos gerarParcelas e gerarRecebimentoAVista continua exatamente o mesmo da versão anterior)
        $formaPagamento = $pagamento->formaPagamento;
        $totalParcelas = $formaPagamento->numero_parcelas;
        $valorTotal = $pagamento->valor;

        $valorParcela = bcdiv((string)$valorTotal, (string)$totalParcelas, 2);
        $valorAcumulado = 0;

        for ($i = 1; $i <= $totalParcelas; $i++) {
            $valorCorrente = $valorParcela;
            if ($i === $totalParcelas) {
                $valorCorrente = bcsub((string)$valorTotal, (string)$valorAcumulado, 2);
            }
            $valorAcumulado = bcadd((string)$valorAcumulado, (string)$valorCorrente, 2);

            $diasVencimento = $i * $formaPagamento->dias_intervalo;
            $dataVencimento = Carbon::parse($venda->created_at)->addDays($diasVencimento)->toDateString();

            ContaAReceber::create([
                'empresa_id' => $venda->empresa_id,
                'venda_id' => $venda->id, 
                'descricao' => "Recebimento ref. Venda #{$venda->id}, Parcela {$i}/{$totalParcelas}",
                'parcela_numero' => $i,
                'parcela_total' => $totalParcelas,
                'valor' => $valorCorrente,
                'data_vencimento' => $dataVencimento,
                'status' => 'A Receber',
            ]);
        }
    }

    /**
     * Gera uma conta a receber para pagamento à vista e já realiza a baixa.
     */
    private function gerarRecebimentoAVista(Venda $venda, $pagamento): void
    {
        $conta = ContaAReceber::create([
            'empresa_id' => $venda->empresa_id,
            'venda_id' => $venda->id,
            'descricao' => "Recebimento ref. Venda #{$venda->id} ({$pagamento->formaPagamento->nome})",
            'parcela_numero' => 1,
            'parcela_total' => 1,
            'valor' => $pagamento->valor,
            'valor_recebido' => $pagamento->valor,
            'data_vencimento' => Carbon::parse($venda->created_at)->toDateString(),
            'status' => 'Recebido',
        ]);

        $conta->recebimentos()->create([
            'empresa_id' => $venda->empresa_id,
            'forma_pagamento_id' => $pagamento->forma_pagamento_id,
            'valor_recebido' => $pagamento->valor,
            'data_recebimento' => Carbon::parse($venda->created_at)->toDateString(),
        ]);
    }
}