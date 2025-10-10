<?php

namespace App\Http\Controllers\Fiscal;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ContingenciaController extends Controller
{
    /**
     * Mostra a tela do monitor de contingência.
     */
    public function index()
    {
        // Apenas retorna a view que irá carregar o componente Livewire
        return view('fiscal.index');
    }
}