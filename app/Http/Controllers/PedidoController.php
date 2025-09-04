<?php

namespace App\Http\Controllers;

use App\Models\Venda; // Importe o model Venda
use Illuminate\Http\Request;
use App\Models\Orcamento;

class PedidoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Busca os pedidos, ordenando pelos mais recentes
        // O 'with('cliente')' carrega os dados do cliente para evitar múltiplas queries
        $pedidos = Venda::with('cliente')->latest()->paginate(15);

        return view('pedidos.index', compact('pedidos'));
    }

    public function importarOrcamento(Request $request)
{
    $query = Orcamento::where('status', 'Pendente')
                ->where('empresa_id', auth()->user()->empresa_id)
                ->with('cliente')
                ->latest();

    if ($request->filled('search')) {
        $search = $request->search;
        $query->where(function($q) use ($search) {
            $q->where('id', $search)
              ->orWhereHas('cliente', fn($clienteQuery) => $clienteQuery->where('nome', 'like', "%{$search}%"));
        });
    }

    $orcamentos = $query->paginate(15);

    return view('pedidos.importar-orcamento', compact('orcamentos'));
}

public function edit(Venda $venda)
{
    // Validação de segurança (opcional, mas recomendado)
    if ($venda->empresa_id !== auth()->user()->empresa_id) {
        abort(403);
    }

    return view('pedidos.edit', compact('venda'));
}
}