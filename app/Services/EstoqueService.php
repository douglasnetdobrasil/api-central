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
        // Usamos o ID do produto para buscá-lo novamente dentro da transação
        $produtoId = $produto->id;
    
        DB::transaction(function () use ($produtoId, $tipoMovimento, $quantidade, $origem, $observacao) {
            
            // ================== ESTA É A PARTE MAIS IMPORTANTE ==================
            // 1. Busca novamente o produto DENTRO da transação e o "tranca" para atualização.
            // Isso garante que estamos trabalhando com os dados mais recentes e evita erros de concorrência.
            $produtoNaTransacao = Produto::where('id', $produtoId)->lockForUpdate()->first();
    
            if (!$produtoNaTransacao) {
                // Se o produto não for encontrado por algum motivo, interrompe a transação.
                return;
            }
    
            $saldoAnterior = $produtoNaTransacao->estoque_atual;
            $saldoNovo = $saldoAnterior;
    
            // Determina se é entrada ou saída pelo nome do tipo
            if (str_starts_with($tipoMovimento, 'entrada') || str_starts_with($tipoMovimento, 'ajuste_positivo')) {
                $saldoNovo += $quantidade;
            } elseif (str_starts_with($tipoMovimento, 'saida') || str_starts_with($tipoMovimento, 'estorno') || str_starts_with($tipoMovimento, 'ajuste_negativo')) {
                $saldoNovo -= $quantidade;
            }
    
            EstoqueMovimento::create([
                'empresa_id' => $produtoNaTransacao->empresa_id,
                'produto_id' => $produtoNaTransacao->id,
                'user_id' => Auth::id(),
                'tipo_movimento' => $tipoMovimento,
                'quantidade' => $quantidade,
                'saldo_anterior' => $saldoAnterior,
                'saldo_novo' => $saldoNovo,
                'origem_id' => $origem->id,
                'origem_type' => get_class($origem),
                'observacao' => $observacao,
            ]);

            DB::table('produtos')->where('id', $produtoId)->update([
                'estoque_atual' => $saldoNovo
            ]);
            /*
            // 2. Atualiza e salva o objeto do produto que foi buscado DENTRO da transação.
            $produtoNaTransacao->estoque_atual = $saldoNovo;
            $produtoNaTransacao->save();
            */
        });
    }
}