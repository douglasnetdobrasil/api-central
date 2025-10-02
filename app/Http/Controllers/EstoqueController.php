<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use Illuminate\Http\Request;

class EstoqueController extends Controller
{
    // Exibe a tela de busca de produtos
    public function index()
    {
        return view('estoque.index');
    }

    // Exibe o extrato de um produto específico
    public function show(Produto $produto)
    {
        return view('estoque.show', ['produto' => $produto]);
    }
}