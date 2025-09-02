<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CotacaoResposta extends Model
{
    use HasFactory;

    protected $table = 'cotacao_respostas';

    protected $fillable = [
        'cotacao_id',
        'produto_id',
        'fornecedor_id',
        'preco_ofertado',
        'prazo_entrega_dias',
    ];
}