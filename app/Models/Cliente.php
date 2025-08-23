<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'nome',
        'cpf_cnpj',
        'email',
        'telefone',
        'data_nascimento',
        'endereco_completo',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
    ];

    public function pedidos()
    {
        return $this->hasMany(Pedido::class);
    }
}