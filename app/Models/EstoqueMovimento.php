<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueMovimento extends Model
{
    use HasFactory;

    protected $table = 'estoque_movimentos';
    protected $guarded = [];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public static function registrarMovimento(Produto $produto, string $tipoMovimento, float $quantidade, Model $origem, ?string $observacao = null): void
    {
        $produtoId = $produto->id;
    
        DB::transaction(function () use ($produtoId, $tipoMovimento, $quantidade, $origem, $observacao) {
            
            $produtoNaTransacao = Produto::where('id', $produtoId)->lockForUpdate()->first();
    
            if (!$produtoNaTransacao) {
                return;
            }
    
            $saldoAnterior = $produtoNaTransacao->estoque_atual;
            $saldoNovo = $saldoAnterior;
    
            // ==========================================================
            // ||||||||||||||||||| A CORREÇÃO ESTÁ AQUI |||||||||||||||||||
            // ==========================================================
            // A verificação de 'estorno' foi MOVIDA para o bloco de ADIÇÃO (+)
            if (str_starts_with($tipoMovimento, 'entrada') || str_starts_with($tipoMovimento, 'ajuste_positivo') || str_starts_with($tipoMovimento, 'estorno')) {
                $saldoNovo += $quantidade;
            } 
            // O bloco de SUBTRAÇÃO (-) agora NÃO contém mais 'estorno'
            elseif (str_starts_with($tipoMovimento, 'saida') || str_starts_with($tipoMovimento, 'ajuste_negativo')) {
                $saldoNovo -= $quantidade;
            }
            // ==========================================================
            // ||||||||||||||||||| FIM DA CORREÇÃO |||||||||||||||||||
            // ==========================================================
    
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

            // Atualiza o estoque usando um Query Builder para segurança
            DB::table('produtos')->where('id', $produtoId)->update([
                'estoque_atual' => $saldoNovo
            ]);
        });
    }



    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento polimórfico para a origem (Venda, Compra, etc.)
    public function origem()
    {
        return $this->morphTo();
    }
}