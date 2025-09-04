<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Empresa;
use Illuminate\Validation\Rule;

class EmpresaController extends Controller
{
    public function index()
    {
        $empresas = Empresa::latest()->paginate(10);
        return view('admin.empresa.index', compact('empresas'));
    }

    public function create()
    {
        $empresa = new Empresa();
        return view('admin.empresa.create', compact('empresa'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:empresas,cnpj',
            'ie' => 'nullable|string|max:20',
            'crt' => 'required|integer',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cep' => 'nullable|string|max:9',
            'municipio' => 'nullable|string|max:100',
            'uf' => 'nullable|string|max:2',
            'codigo_municipio' => 'nullable|string|max:7',
            'telefone' => 'nullable|string|max:20',
            'nicho_negocio' => 'required|string',
          //  'certificado_a1_path' => 'nullable|file|mimes:pfx',
            'certificado_a1_password' => 'nullable|string',
            'ambiente_nfe' => 'required|in:1,2',
        ]);

        if ($request->hasFile('certificado_a1_path')) {
            $validatedData['certificado_a1_path'] = $request->file('certificado_a1_path')->store('certs', 'private');
        }

        Empresa::create($validatedData);
        return redirect()->route('empresa.index')->with('success', 'Empresa cadastrada com sucesso!');
    }

    public function edit(Empresa $empresa)
    {
        return view('admin.empresa.edit', compact('empresa'));
    }

    public function update(Request $request, Empresa $empresa)
    {
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => ['required', 'string', 'max:18', Rule::unique('empresas')->ignore($empresa->id)],
            'ie' => 'nullable|string|max:20',
            'crt' => 'required|integer',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'bairro' => 'nullable|string|max:100',
            'cep' => 'nullable|string|max:9',
            'municipio' => 'nullable|string|max:100',
            'uf' => 'nullable|string|max:2',
            'codigo_municipio' => 'nullable|string|max:7',
            'telefone' => 'nullable|string|max:20',
            'nicho_negocio' => 'required|string',
           // 'certificado_a1_path' => 'nullable|file|mimes:pfx',
            'certificado_a1_password' => 'nullable|string',
            'ambiente_nfe' => 'required|in:1,2',
        ]);
    
        if ($request->hasFile('certificado_a1_path')) {
            if ($empresa->certificado_a1_path) {
                Storage::disk('private')->delete($empresa->certificado_a1_path);
            }
            $validatedData['certificado_a1_path'] = $request->file('certificado_a1_path')->store('certs', 'private');
        }
    
        if (empty($validatedData['certificado_a1_password'])) {
            unset($validatedData['certificado_a1_password']);
        }
    
        $empresa->update($validatedData);
    
        return redirect()->route('empresa.index')->with('success', 'Empresa atualizada com sucesso!');
    }

    public function destroy(Empresa $empresa)
    {
        if ($empresa->usuarios()->exists()) {
            return back()->with('error', 'Não é possível excluir uma empresa que possui usuários vinculados.');
        }
        $empresa->delete();
        return redirect()->route('empresa.index')->with('success', 'Empresa excluída com sucesso!');
    }
}