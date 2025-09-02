<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Scopes\EmpresaScope;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'email',
        'empresa_id',
        'telefone',
        // Adicione os novos campos
        'cep',
        'logradouro',
        'numero',
        'complemento',
        'bairro',
        'cidade',
        'estado',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function pedidos()
    {
        // Assumindo que o seu modelo de pedido se chama Pedido
        // return $this->hasMany(Pedido::class);
    }

    // OS MÉTODOS create() E edit() FORAM REMOVIDOS DAQUI
}