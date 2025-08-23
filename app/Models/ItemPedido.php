<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    use HasFactory;

    protected $table = 'itens_pedido';
    public $timestamps = false;

    protected $fillable = [
        'pedido_id', 'produto_id', 'quantidade', 'unidade_medida_id',
        'preco_unitario_venda', 'subtotal'
    ];

    protected $casts = [
        'quantidade' => 'decimal:3',
        'preco_unitario_venda' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}