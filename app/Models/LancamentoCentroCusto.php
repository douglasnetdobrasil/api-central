<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LancamentoCentroCusto extends Model
{
    use HasFactory;

    protected $table = 'lancamento_centro_custo';

    protected $fillable = [
        'centro_custo_id',
        'lancamento_type',
        'lancamento_id',
        'valor',
        'percentual',
    ];

    /**
     * Relacionamento: Retorna o centro de custo ao qual este lançamento pertence.
     */
    public function centroCusto(): BelongsTo
    {
        return $this->belongsTo(CentroCusto::class, 'centro_custo_id');
    }

    /**
     * Relacionamento Polimórfico: Retorna o model "dono" deste lançamento
     * (pode ser uma ContaPagar, ContaReceber, etc.).
     */
    public function lancamento(): MorphTo
    {
        return $this->morphTo();
    }
}