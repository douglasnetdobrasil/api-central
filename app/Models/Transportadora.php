<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transportadora extends Model
{
    use HasFactory;

    protected $fillable = [
        'empresa_id',
        'razao_social',
        'nome_fantasia',
        'cnpj',
        'inscricao_estadual',
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'uf',
        'telefone',
        'email',
        'rntc',
        'ativo',
    ];

    // Relação: Uma transportadora pertence a uma empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}