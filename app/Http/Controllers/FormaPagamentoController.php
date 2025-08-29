<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FormaPagamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = FormaPagamento::where('empresa_id', Auth::user()->empresa_id);

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        $formasPagamento = $query->latest()->paginate(15);
        return view('formas-pagamento.index', compact('formasPagamento'));
    }

    public function create()
    {
        return view('formas-pagamento.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:a_vista,a_prazo',
            'numero_parcelas' => 'required|integer|min:1',
            'dias_intervalo' => 'required|integer|min:0',
        ]);

        $validatedData['empresa_id'] = Auth::user()->empresa_id;
        $validatedData['ativo'] = $request->has('ativo');
        
        FormaPagamento::create($validatedData);

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento cadastrada com sucesso!');
    }

    public function edit(FormaPagamento $formaPagamento)
    {
        if ($formaPagamento->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }
        return view('formas-pagamento.edit', compact('formaPagamento'));
    }

    public function update(Request $request, FormaPagamento $formaPagamento)
    {
        if ($formaPagamento->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }

        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:a_vista,a_prazo',
            'numero_parcelas' => 'required|integer|min:1',
            'dias_intervalo' => 'required|integer|min:0',
        ]);
        
        $validatedData['ativo'] = $request->has('ativo');
        $formaPagamento->update($validatedData);

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento atualizada com sucesso!');
    }

    public function destroy(FormaPagamento $formaPagamento)
    {
        if ($formaPagamento->empresa_id !== Auth::user()->empresa_id) {
            abort(403);
        }
        
        $formaPagamento->delete();
        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento excluída com sucesso!');
    }
}