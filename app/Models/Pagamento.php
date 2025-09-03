<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pagamento extends Model
{
    use HasFactory;

    protected $table = 'pagamentos';

    protected $fillable = [
        'venda_id',
        'forma_pagamento',
        'valor',
        'parcelas',
        'detalhes',
    ];

    /**
     * Converte automaticamente o campo JSON 'detalhes' para array e vice-versa.
     */
    protected $casts = [
        'detalhes' => 'array',
    ];

    /**
     * Relacionamento: O pagamento pertence a uma venda.
     */
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }
}