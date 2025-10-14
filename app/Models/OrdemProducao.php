<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrdemProducao extends Model
{
    use HasFactory;

    protected $table = 'ordens_producao';

    protected $fillable = [
        'empresa_id',
        'produto_acabado_id',
        'user_id',
        'status',
        'quantidade_planejada',
        'quantidade_produzida',
        'data_inicio_prevista',
        'data_fim_prevista',
        'data_inicio_real',
        'data_fim_real',
        'custo_total_estimado',
        'custo_total_real',
        'observacoes',
    ];

    protected $casts = [
        'quantidade_planejada' => 'float',
        'quantidade_produzida' => 'float',
    ];

    /**
     * As matérias-primas necessárias para esta ordem de produção.
     * Uma OP TEM MUITOS itens.
     */
    public function itens(): HasMany
    {
        return $this->hasMany(OrdemProducaoItem::class);
    }

    /**
     * O produto que está sendo fabricado.
     */
    public function produtoAcabado(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_acabado_id');
    }

    /**
     * O usuário responsável pela OP.
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}