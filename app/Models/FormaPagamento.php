<?php

namespace App\Models;

use App\Models\Scopes\EmpresaScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormaPagamento extends Model
{
    use HasFactory;

    protected $table = 'forma_pagamentos';

    protected $fillable = [
        'empresa_id',
        'nome',
        'tipo',
        'numero_parcelas',
        'dias_intervalo',
        'ativo',
    ];

    protected $casts = [
        'ativo' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new EmpresaScope);
    }
}