<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\HasCentroCusto;

class ContaPagamento extends Model
{
    use HasFactory;

    // Aponte para a tabela correta se o nome não seguir a convenção exata
    protected $table = 'conta_pagamentos';

    protected $fillable = [
        'conta_a_pagar_id',
        'forma_pagamento_id',
        'empresa_id',
        'valor',
        'data_pagamento',
    ];

    public function contaAPagar()
    {
        return $this->belongsTo(ContaAPagar::class, 'conta_a_pagar_id');
    }

    public function formaPagamento()
    {
        return $this->belongsTo(FormaPagamento::class, 'forma_pagamento_id');
    }
}