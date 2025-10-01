<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaContaAPagar extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categoria_contas_a_pagar'; // Especifica o nome da tabela
    protected $guarded = [];

    public function parent(): BelongsTo
    {
        // Auto-relacionamento
        return $this->belongsTo(CategoriaContaAPagar::class, 'parent_id');
    }

    public function children(): HasMany
    {
        // Auto-relacionamento
        return $this->hasMany(CategoriaContaAPagar::class, 'parent_id');
    }
}