<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    use HasFactory;

    protected $table = 'pedidos';
    // A migration de pedidos não usou timestamps padrão, então desativamos
    public $timestamps = false; 

    protected $fillable = [
        'cliente_id', 'vendedor_id', 'valor_total', 
        'status', 'observacao', 'data_pedido'
    ];

    protected $casts = [
        'valor_total' => 'decimal:2',
        'data_pedido' => 'datetime',
    ];

    // RELACIONAMENTOS
    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function vendedor()
    {
        return $this->belongsTo(Usuario::class, 'vendedor_id');
    }

    public function itens()
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function historico()
    {
        return $this->hasMany(HistoricoPedido::class);
    }

    public function contasAReceber()
    {
        return $this->hasMany(ContaAReceber::class);
    }
}