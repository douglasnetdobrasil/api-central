<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fornecedor extends Model
{
    use HasFactory;

    protected $table = 'fornecedores';

    /**
     * A lista de campos que podem ser preenchidos em massa.
     * ESTA ERA A PEÇA FALTANTE.
     */
    protected $fillable = [
        'razao_social',
        'nome_fantasia',
        'tipo_pessoa',
        'cpf_cnpj',
        'email',
        'telefone',
        'endereco',
    ];

    // Seus relacionamentos futuros virão aqui...
    // public function produtos() { ... }
}