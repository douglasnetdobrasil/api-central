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
        'nome_fantasia',
        'cnpj',
        'ie',
        'im',
        'crt', // O campo que estava faltando
        'logradouro',
        'numero',
        'bairro',
        'complemento',
        'cep',
        'municipio',
        'uf',
        'codigo_municipio',
        'telefone',
        'certificado_a1_path',
        'certificado_a1_password',
        'ambiente_nfe',
        'nicho_negocio',
        'website', // Adicionado
        'logo_path', // Adicionado
        'codigo_uf',
        'csc_nfe',
        'csc_id_nfe',
    ];

    /**
     * Uma Empresa pode ter muitos Usuários.
     */
    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}