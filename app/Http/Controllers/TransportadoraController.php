<?php

namespace App\Http\Controllers;

use App\Models\Transportadora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransportadoraController extends Controller
{
    public function index(Request $request)
    {
        $query = Transportadora::where('empresa_id', Auth::user()->empresa_id);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('razao_social', 'like', '%' . $request->search . '%')
                  ->orWhere('nome_fantasia', 'like', '%' . $request->search . '%')
                  ->orWhere('cnpj', 'like', '%' . $request->search . '%');
            });
        }

        $transportadoras = $query->latest()->paginate(15);
        return view('transportadoras.index', compact('transportadoras'));
    }

    public function create()
    {
        return view('transportadoras.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:transportadoras,cnpj',
            // Adicione outras regras de validação conforme necessário
        ]);

        $validatedData['empresa_id'] = Auth::user()->empresa_id;
        Transportadora::create($validatedData);

        return redirect()->route('transportadoras.index')->with('success', 'Transportadora cadastrada com sucesso!');
    }

    public function edit(Transportadora $transportadora)
    {
        // Garante que o usuário só pode editar transportadoras da sua própria empresa
        if ($transportadora->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }
        return view('transportadoras.edit', compact('transportadora'));
    }

    public function update(Request $request, Transportadora $transportadora)
    {
        if ($transportadora->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }

        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            'cnpj' => 'required|string|max:18|unique:transportadoras,cnpj,' . $transportadora->id,
            // Adicione outras regras de validação
        ]);

        $transportadora->update($validatedData);

        return redirect()->route('transportadoras.index')->with('success', 'Transportadora atualizada com sucesso!');
    }

    public function destroy(Transportadora $transportadora)
    {
        if ($transportadora->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }
        
        // Adicionar verificação de segurança (ex: se a transportadora está em uso em alguma nota)
        // if ($transportadora->notasFiscais()->exists()) {
        //     return back()->with('error', 'Esta transportadora não pode ser excluída pois está em uso.');
        // }

        $transportadora->delete();
        return redirect()->route('transportadoras.index')->with('success', 'Transportadora excluída com sucesso!');
    }
}