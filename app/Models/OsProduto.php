<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OsProduto extends Model
{
    use HasFactory;
    
    // Nome da tabela, se for diferente do plural do nome da classe
    protected $table = 'os_produtos';

    protected $fillable = [
        'ordem_servico_id',
        'produto_id',
        'quantidade',
        'preco_unitario', // Corrigido para 'preco_unitario' conforme seu banco
        'subtotal',
    ];

    // Relacionamento para buscar os dados completos do produto
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class, 'produto_id');
    }

    // Relacionamento para buscar a OS deste item
    public function ordemServico(): BelongsTo
    {
        return $this->belongsTo(OrdemServico::class, 'ordem_servico_id');
    }
}