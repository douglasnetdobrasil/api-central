<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrcamentoItem extends Model
{
    use HasFactory;

    // Usando 'orcamento_itens' como nome da tabela
    protected $table = 'orcamento_itens';

    // Campos baseados na sua migração
    protected $fillable = [
        'orcamento_id',
        'produto_id',
        'descricao_produto',
        'quantidade',
        'valor_unitario',
        'subtotal', // Alterado de 'valor_total' para 'subtotal'
    ];

    // --- RELACIONAMENTOS ---

    public function orcamento()
    {
        return $this->belongsTo(Orcamento::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}