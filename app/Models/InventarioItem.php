<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventarioItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'inventario_id',
        'produto_id',
        'estoque_esperado',
        'quantidade_contada',
        'diferenca',
    ];

    /**
     * The attributes that should be cast.
     * Isso garante que os campos decimais sejam tratados como números (float) no Laravel.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'estoque_esperado' => 'float',
        'quantidade_contada' => 'float',
        'diferenca' => 'float',
    ];

    /**
     * Define o relacionamento: Um item de inventário PERTENCE A UM inventário.
     */
    public function inventario(): BelongsTo
    {
        return $this->belongsTo(Inventario::class);
    }

    /**
     * Define o relacionamento: Um item de inventário PERTENCE A UM produto.
     * Isso permite que você acesse os dados do produto (como o nome) com: $item->produto->nome
     */
    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }
}