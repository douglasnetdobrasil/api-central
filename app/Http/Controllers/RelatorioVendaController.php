<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RelatorioVendaController extends Controller
{
    public function index(Request $request)
    {
        // --- 1. Query Base ---
        $query = Venda::with(['cliente', 'user'])->where('status', 'concluida');

        // --- 2. Aplicação dos Filtros ---
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }
        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // --- 3. Buscar Dados para a Tabela Detalhada ---
        $vendas = $query->latest()->paginate(20)->withQueryString();

        // --- 4. Buscar Dados para os KPIs (Resumo) ---
        // Clonamos a query para não afetar a paginação
        $kpiQuery = clone $query; 
        $valorTotal = $kpiQuery->sum('total');
        $numeroVendas = $kpiQuery->count();
        $ticketMedio = ($numeroVendas > 0) ? $valorTotal / $numeroVendas : 0;
        
        // --- 5. Buscar Dados para os Gráficos ---
        // Vendas por Dia
        $vendasPorDia = (clone $query)
            ->reorder()
            ->select(DB::raw('DATE(created_at) as data'), DB::raw('SUM(total) as total'))
            ->groupBy('data')
            ->orderBy('data')
            ->get();
            
        // Produtos mais vendidos
        $produtosMaisVendidos = DB::table('venda_items')
            ->join('produtos', 'venda_items.produto_id', '=', 'produtos.id')
            ->join('vendas', 'venda_items.venda_id', '=', 'vendas.id')
            ->whereIn('vendas.id', (clone $query)->pluck('id')) // Filtra pelos IDs da query principal
            ->select('produtos.nome', DB::raw('SUM(venda_items.quantidade) as total_vendido'))
            ->groupBy('produtos.nome')
            ->orderBy('total_vendido', 'desc')
            ->limit(5)
            ->get();

        // --- 6. Buscar Dados para popular os filtros <select> ---
        $clientes = Cliente::orderBy('nome')->get();
        $vendedores = User::orderBy('name')->get(); // Assumindo que vendedores estão na tabela Users

        // --- 7. Enviar tudo para a View ---
        return view('relatorios.vendas.index', compact(
            'vendas',
            'valorTotal',
            'numeroVendas',
            'ticketMedio',
            'vendasPorDia',
            'produtosMaisVendidos',
            'clientes',
            'vendedores'
        ));
    }
}