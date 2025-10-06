<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PdvCaixaController extends Controller
{
    /**
     * Exibe a interface do PDV Caixa.
     */
    public function __invoke(): View
    {
        // Esta view conterá a tag do seu componente Livewire
        return view('pdv-caixa.index'); 
    }
}