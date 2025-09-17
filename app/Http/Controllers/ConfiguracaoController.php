<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Lógica para mostrar a página de configurações
        // Por enquanto, pode retornar uma view simples ou até um texto.
        // O importante é o método existir.
        // return view('admin.configuracoes.index');
        return "Página de Configurações";
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // Lógica para salvar as configurações
        return back()->with('success', 'Configurações salvas com sucesso!');
    }
}