<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'numero_parcelas',
        'dias_intervalo',
        'ativo',
    ];

    // Relação: Uma forma de pagamento pertence a uma empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}