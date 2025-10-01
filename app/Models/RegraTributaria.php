<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegraTributaria extends Model
{
    use HasFactory;

    // Se você for usar o método create() com um array de dados,
    // é uma boa prática definir os campos preenchíveis.
    protected $table = 'regras_tributarias';
    protected $guarded = []; 
}