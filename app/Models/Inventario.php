<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Inventario extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'empresa_id',
        'user_id',
        'data_inicio',
        'data_conclusao',
        'status',
        'observacoes',
    ];

    /**
     * Define o relacionamento: Um inventário TEM MUITOS itens.
     * Isso permite que você acesse todos os itens de um inventário com: $inventario->items
     */
    public function items(): HasMany
    {
        return $this->hasMany(InventarioItem::class);
    }

    /**
     * Define o relacionamento: Um inventário PERTENCE A UM usuário (o responsável).
     * Isso permite que você acesse o usuário que criou o inventário com: $inventario->responsavel
     */
    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Define o relacionamento: Um inventário PERTENCE A UMA empresa.
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }
}