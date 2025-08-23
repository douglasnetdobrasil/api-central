<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'razao_social',
        'cnpj',
        'nicho_negocio',
    ];

    /**
     * Uma Empresa pode ter muitos Usuários.
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}