<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoricoPedido extends Model
{
    use HasFactory;

    protected $table = 'historico_pedidos';
    public $timestamps = false;

    protected $fillable = ['pedido_id', 'usuario_id', 'descricao_acao', 'data_acao'];
    
    protected $casts = ['data_acao' => 'datetime'];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(Usuario::class);
    }
}