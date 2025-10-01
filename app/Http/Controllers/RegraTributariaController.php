<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RegraTributaria;
use Illuminate\Http\Request;

class RegraTributariaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $regras = RegraTributaria::latest()->paginate(15);
        // Aponta para a NOVA view
        return view('admin.regras-tributarias.index', compact('regras'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $regra = new RegraTributaria(); // Objeto vazio para o formulário
        // Aponta para a NOVA view
        return view('admin.regras-tributarias.form', compact('regra'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Aqui você pode adicionar as regras de validação
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'cfop' => 'required|string|size:4',
            'crt_emitente' => 'nullable|integer|in:1,3',
            'uf_origem' => 'nullable|string|size:2',
            'uf_destino' => 'nullable|string|size:2',
            'icms_origem' => 'required|string|max:1',
            'icms_cst' => 'nullable|string|size:2',
            'csosn' => 'nullable|string|size:3',
            'icms_mod_bc' => 'nullable|integer',
            'icms_aliquota' => 'nullable|numeric|min:0',
            'icms_reducao_bc' => 'nullable|numeric|min:0',
            'icms_mod_bc_st' => 'nullable|integer',
            'mva_st' => 'nullable|numeric|min:0',
            'icms_aliquota_st' => 'nullable|numeric|min:0',
            'ipi_cst' => 'nullable|string|size:2',
            'ipi_aliquota' => 'nullable|numeric|min:0',
            'pis_cst' => 'nullable|string|size:2',
            'pis_aliquota' => 'nullable|numeric|min:0',
            'cofins_cst' => 'nullable|string|size:2',
            'cofins_aliquota' => 'nullable|numeric|min:0',
        ]);
        
        RegraTributaria::create($validated);
    
        return redirect()->route('admin.regras-tributarias.index')->with('success', 'Regra tributária atualizada com sucesso!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegraTributaria $regras_tributaria) // Laravel faz o find-or-fail
    {
        // Aponta para a NOVA view, passando a regra encontrada
        return view('admin.regras-tributarias.form', ['regra' => $regras_tributaria]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegraTributaria $regras_tributaria)
    {
        // REATIVE A VALIDAÇÃO
        $validated = $request->validate([
            'descricao' => 'required|string|max:255',
            'cfop' => 'required|string|size:4',
            'crt_emitente' => 'nullable|integer|in:1,3',
            'uf_origem' => 'nullable|string|size:2',
            'uf_destino' => 'nullable|string|size:2',
            'icms_origem' => 'required|string|max:1',
            'icms_cst' => 'nullable|string|size:2',
            'csosn' => 'nullable|string|size:3',
            'icms_mod_bc' => 'nullable|integer',
            'icms_aliquota' => 'nullable|numeric|min:0',
            'icms_reducao_bc' => 'nullable|numeric|min:0',
            'icms_mod_bc_st' => 'nullable|integer',
            'mva_st' => 'nullable|numeric|min:0',
            'icms_aliquota_st' => 'nullable|numeric|min:0',
            'ipi_cst' => 'nullable|string|size:2',
            'ipi_aliquota' => 'nullable|numeric|min:0',
            'pis_cst' => 'nullable|string|size:2',
            'pis_aliquota' => 'nullable|numeric|min:0',
            'cofins_cst' => 'nullable|string|size:2',
            'cofins_aliquota' => 'nullable|numeric|min:0',
        ]);
        
        // USE A VARIÁVEL $validated AQUI
        $regras_tributaria->update($validated);
    
        return redirect()->route('admin.regras-tributarias.index')->with('success', 'Regra tributária atualizada com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RegraTributaria $regras_tributaria)
    {
        $regras_tributaria->delete();
        return back()->with('success', 'Regra tributária excluída com sucesso!');
    }
}