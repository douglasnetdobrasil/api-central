<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendaPagamento extends Model
{
    use HasFactory;

    protected $table = 'venda_pagamentos';

    protected $fillable = [
        'empresa_id',
        'venda_id',
        'forma_pagamento_id',
        'valor',
    ];

    // Relacionamento para buscar os detalhes da forma de pagamento
    public function forma()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}