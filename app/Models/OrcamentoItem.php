<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrcamentoItem extends Model
{
    protected $fillable = [ 'orcamento_id', 'produto_id', 'descricao_produto', 'quantidade', 'valor_unitario', 'subtotal' ];
    public function produto() { return $this->belongsTo(Produto::class); }
}
