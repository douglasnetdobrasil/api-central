<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Orcamento extends Model
{
    protected $fillable = [ 'empresa_id', 'cliente_id', 'vendedor_id', 'status', 'data_emissao', 'data_validade', 'valor_total', 'observacoes' ];
    public function itens() { return $this->hasMany(OrcamentoItem::class); }
    public function cliente() { return $this->belongsTo(Cliente::class); }
    public function vendedor() { return $this->belongsTo(User::class, 'vendedor_id'); }
}
