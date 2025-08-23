<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // ADICIONE ESTE BLOCO DE CÓDIGO
    protected $fillable = [
        'nome',
        'margem_lucro',
    ];

    /**
     * Define a relação com Produtos (opcional mas recomendado)
     */
    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}