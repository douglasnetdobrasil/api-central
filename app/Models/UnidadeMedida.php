<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnidadeMedida extends Model
{
    use HasFactory;
    
    protected $table = 'unidades_medida';
    public $timestamps = false; // A tabela não tem `criado_em` e `atualizado_em`

    protected $fillable = ['nome', 'sigla'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }
}