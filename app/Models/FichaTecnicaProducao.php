<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FichaTecnicaProducao extends Model
{
    use HasFactory;

    protected $table = 'ficha_tecnica_producao';

    protected $fillable = [
        'empresa_id',
        'produto_acabado_id',
        'materia_prima_id',
        'quantidade',
        'observacoes',
    ];

    protected $casts = [
        'quantidade' => 'float',
    ];

    /**
     * A qual produto acabado este item da receita pertence.
     */
    public function produtoAcabado(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_acabado_id');
    }

    /**
     * Qual produto é a matéria-prima deste item da receita.
     */
    public function materiaPrima(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'materia_prima_id');
    }
}