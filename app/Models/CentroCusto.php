<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CentroCusto extends Model
{
    use HasFactory;

    protected $table = 'centros_custo';

    protected $fillable = [
        'empresa_id',
        'parent_id',
        'nome',
        'codigo',
        'tipo',
        'aceita_despesas',
        'aceita_receitas',
        'ativo',
    ];

    /**
     * Relacionamento: Retorna o centro de custo pai (se houver).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(CentroCusto::class, 'parent_id');
    }

    /**
     * Relacionamento: Retorna todos os centros de custo filhos.
     */
    public function children(): HasMany
    {
        return $this->hasMany(CentroCusto::class, 'parent_id');
    }

    /**
     * Escopo para retornar apenas centros de custo ANALÍTICOS.
     */
    public function scopeAnaliticos($query)
    {
        return $query->where('tipo', 'ANALITICO');
    }

    /**
     * Escopo para retornar apenas centros de custo SINTÉTICOS.
     */
    public function scopeSinteticos($query)
    {
        return $query->where('tipo', 'SINTETICO');
    }

    /**
     * Escopo para retornar apenas centros de custo ATIVOS.
     */
    public function scopeAtivos($query)
    {
        return $query->where('ativo', true);
    }
}