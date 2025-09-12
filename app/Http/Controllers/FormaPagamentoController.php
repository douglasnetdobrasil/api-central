<?php

namespace App\Http\Controllers;

use App\Models\FormaPagamento;
use Illuminate\Http\Request;

class FormaPagamentoController extends Controller
{
    public function index(Request $request)
    {
        $query = FormaPagamento::orderBy('nome', 'asc');

        if ($request->filled('search')) {
            $query->where('nome', 'like', '%' . $request->search . '%');
        }

        $formasPagamento = $query->paginate(15)->withQueryString();
        
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
            'codigo_sefaz' => 'required|string|max:2',
            'ativo' => 'nullable|boolean',
        ]);
        
        $validatedData['empresa_id'] = auth()->user()->empresa_id;
        $validatedData['ativo'] = $request->has('ativo');

        FormaPagamento::create($validatedData);

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento criada com sucesso!');
    }

    public function edit(FormaPagamento $formaPagamento)
    {
        // A variável $formaPagamento é injetada automaticamente pelo Laravel
        return view('formas-pagamento.edit', compact('formaPagamento'));
    }

    public function update(Request $request, FormaPagamento $formaPagamento)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'tipo' => 'required|in:a_vista,a_prazo',
            'numero_parcelas' => 'required|integer|min:1',
            'dias_intervalo' => 'required|integer|min:0',
            'codigo_sefaz' => 'required|string|max:2',
            'ativo' => 'nullable|boolean',
        ]);
        
        $validatedData['ativo'] = $request->has('ativo');

        $formaPagamento->update($validatedData);

        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento atualizada com sucesso!');
    }

    public function destroy(FormaPagamento $formaPagamento)
    {
        $formaPagamento->delete();
        return redirect()->route('formas-pagamento.index')->with('success', 'Forma de pagamento excluída com sucesso!');
    }
}