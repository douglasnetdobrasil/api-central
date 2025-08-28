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
        'nome_fantasia', // Adicionado
        'cnpj',
        'inscricao_estadual', // Adicionado
        'nicho_negocio',
        'endereco', // Adicionado
        'telefone', // Adicionado
        'email', // Adicionado
        'website', // Adicionado
        'logo_path', // Adicionado
    ];

    /**
     * Uma Empresa pode ter muitos Usuários.
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}