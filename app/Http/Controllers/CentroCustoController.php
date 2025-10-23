<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\CentroCusto;

class CentroCustoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Vamos retornar a view mais simples possível, sem usar o Livewire por enquanto.
        return view('centros_de_custo.index'); 
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Passa todos os centros de custo para serem usados no <select> de "Centro de Custo Pai"
        $paisDisponiveis = CentroCusto::sinteticos()->ativos()->get();
        return view('centros_custo.create', compact('paisDisponiveis'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'codigo' => 'nullable|string|max:50', // Adicionar regra de 'unique' depois
            'parent_id' => 'nullable|exists:centros_custo,id',
            'tipo' => 'required|in:SINTETICO,ANALITICO',
            'aceita_despesas' => 'boolean',
            'aceita_receitas' => 'boolean',
            'ativo' => 'boolean',
        ]);
    
        // Simplesmente para garantir que o valor seja salvo corretamente
        $validatedData['empresa_id'] = auth()->user()->empresa_id; // Supondo que o usuário logado tem o id da empresa
    
        CentroCusto::create($validatedData);
    
        return redirect()->route('centros-custo.index')->with('success', 'Centro de Custo criado com sucesso!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CentroCusto $centroCusto)
    {
        // Evita que um centro de custo seja filho de si mesmo ou de seus próprios descendentes
        $paisDisponiveis = CentroCusto::sinteticos()
                                        ->ativos()
                                        ->where('id', '!=', $centroCusto->id)
                                        ->get();
    
        return view('centros_custo.edit', compact('centroCusto', 'paisDisponiveis'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
