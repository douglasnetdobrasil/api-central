<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdemProducaoItem extends Model
{
    use HasFactory;

    protected $table = 'ordem_producao_itens';

    protected $fillable = [
        'ordem_producao_id',
        'materia_prima_id',
        'quantidade_necessaria',
        'quantidade_baixada',
        'custo_unitario_momento',
    ];

    /**
     * A qual Ordem de Produção este item pertence.
     */
    public function ordemProducao(): BelongsTo
    {
        return $this->belongsTo(OrdemProducao::class);
    }

    /**
     * Qual produto é a matéria-prima.
     */
    public function materiaPrima(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'materia_prima_id');
    }
}