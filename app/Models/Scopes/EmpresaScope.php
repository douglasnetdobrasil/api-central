<?php
// app/Models/Scopes/EmpresaScope.php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class EmpresaScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        // Se houver um usuário logado, aplica o filtro
        if (Auth::check()) {
            $builder->where($model->getTable() . '.empresa_id', Auth::user()->empresa_id);
        }
    }
}
