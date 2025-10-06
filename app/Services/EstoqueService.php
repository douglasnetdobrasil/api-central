<?php

namespace App\Services;

use App\Models\Produto;
use App\Models\EstoqueMovimento;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EstoqueService
{
    /**
     * Registra uma movimentação de estoque e atualiza o saldo do produto.
     *
     * @param Produto $produto O produto a ser movimentado.
     * @param string $tipoMovimento 'entrada_compra', 'saida_venda', etc.
     * @param float $quantidade A quantidade a ser movimentada.
     * @param Model $origem O modelo que originou a movimentação (ex: Compra, Venda).
     * @param string|null $observacao Notas adicionais.
     * @return void
     */
    public static function registrarMovimento(Produto $produto, string $tipoMovimento, float $quantidade, Model $origem, ?string $observacao = null): void
    {
        DB::transaction(function () use ($produto, $tipoMovimento, $quantidade, $origem, $observacao) {
            
            $saldoAnterior = $produto->estoque_atual;
            $saldoNovo = $saldoAnterior;

            // Determina se é entrada ou saída pelo nome do tipo
            if (str_starts_with($tipoMovimento, 'entrada') || str_starts_with($tipoMovimento, 'ajuste_positivo')) {
                $saldoNovo += $quantidade;
            } elseif (str_starts_with($tipoMovimento, 'saida') || str_starts_with($tipoMovimento, 'estorno') || str_starts_with($tipoMovimento, 'ajuste_negativo')) {
                $saldoNovo -= $quantidade;
            }

            EstoqueMovimento::create([
                'empresa_id' => Auth::user()->empresa_id,
                'produto_id' => $produto->id,
                'user_id' => Auth::id(),
                'tipo_movimento' => $tipoMovimento,
                'quantidade' => $quantidade,
                'saldo_anterior' => $saldoAnterior,
                'saldo_novo' => $saldoNovo,
                'origem_id' => $origem->id,
                'origem_type' => get_class($origem),
                'observacao' => $observacao,
            ]);

            $produto->estoque_atual = $saldoNovo;
            $produto->save();
        });
    }
}