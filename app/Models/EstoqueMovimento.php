<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EstoqueMovimento extends Model
{
    use HasFactory;

    protected $table = 'estoque_movimentos';
    protected $guarded = [];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Relacionamento polimórfico para a origem (Venda, Compra, etc.)
    public function origem()
    {
        return $this->morphTo();
    }
}