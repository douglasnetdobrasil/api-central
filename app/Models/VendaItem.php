<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendaItem extends Model
{
    use HasFactory;

    protected $table = 'venda_items';

    protected $fillable = [
        'venda_id',
        'produto_id',
        'descricao_produto',
        'quantidade',
        'preco_unitario',
        'subtotal_item',
        'cfop',
    ];

    /**
     * Relacionamento: O item pertence a uma venda.
     */
    public function venda(): BelongsTo
    {
        return $this->belongsTo(Venda::class);
    }

    /**
     * Relacionamento: O item refere-se a um produto.
     */
    public function produto(): BelongsTo
    {
        // Certifique-se de que você tenha um model App\Models\Produto
        return $this->belongsTo(Produto::class);
    }
}