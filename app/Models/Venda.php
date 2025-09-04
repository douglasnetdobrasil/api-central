<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venda extends Model
{
    use HasFactory;

    protected $table = 'vendas';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'cliente_id',
        'orcamento_id',
        'subtotal',
        'desconto',
        'total',
        'status',
        'observacoes',
    ];

    /**
     * Relacionamento: Uma venda tem muitos itens.
     */
    public function items(): HasMany
    {
        return $this->hasMany(VendaItem::class);
    }

    /**
     * Relacionamento: Uma venda tem muitos pagamentos.
     */
    public function pagamentos(): HasMany
    {
        return $this->hasMany(Pagamento::class);
    }

    /**
     * Relacionamento: Uma venda pertence a um usuário (vendedor).
     */
    public function user(): BelongsTo
    {
        // Assumindo que o model User padrão do Laravel seja usado.
        return $this->belongsTo(User::class);
    }

    public function nfe()
{
    // Uma venda pode ter uma NFe.
    return $this->hasOne(Nfe::class);
}

    /**
     * Relacionamento: Uma venda pertence a um cliente.
     */
    public function cliente(): BelongsTo
    {
        // Certifique-se de que você tenha um model App\Models\Cliente
        return $this->belongsTo(Cliente::class);
    }
}