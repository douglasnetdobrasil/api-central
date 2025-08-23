<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContaAReceber extends Model
{
    use HasFactory;

    protected $table = 'contas_a_receber';

    protected $fillable = [
        'pedido_id', 'descricao', 'parcela_numero', 'parcela_total',
        'valor', 'data_vencimento', 'data_recebimento', 'status'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'data_vencimento' => 'date',
        'data_recebimento' => 'date',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}