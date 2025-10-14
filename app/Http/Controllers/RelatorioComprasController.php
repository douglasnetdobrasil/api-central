<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Compra;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioComprasController extends Controller
{
    public function index(Request $request)
    {
        // Define o período padrão como o mês atual
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->toDateString());

        // --- 1. Query Base ---
        $query = Compra::with('fornecedor')
            ->whereBetween('data_emissao', [$dataInicio, $dataFim]);

        // Aplica filtro de fornecedor
        if ($request->filled('fornecedor_id')) {
            $query->where('fornecedor_id', $request->fornecedor_id);
        }

        // --- 2. Dados para a Tabela Principal ---
        $compras = $query->latest('data_emissao')->paginate(20)->withQueryString();

        // --- 3. Dados para os KPIs ---
        $kpiQuery = Compra::whereBetween('data_emissao', [$dataInicio, $dataFim]);
        if ($request->filled('fornecedor_id')) {
            $kpiQuery->where('fornecedor_id', $request->fornecedor_id);
        }

        $valorTotal = $kpiQuery->sum('valor_total_nota');
        $numeroNotas = $kpiQuery->count();
        $ticketMedio = ($numeroNotas > 0) ? $valorTotal / $numeroNotas : 0;
        
        // --- 4. Dados para os Gráficos ---
        // Gráfico de Compras por Fornecedor
        $comprasPorFornecedor = Compra::whereBetween('data_emissao', [$dataInicio, $dataFim])
            ->join('fornecedores', 'compras.fornecedor_id', '=', 'fornecedores.id')
            ->select('fornecedores.razao_social', DB::raw('SUM(compras.valor_total_nota) as total_comprado'))
            ->groupBy('fornecedores.razao_social')
            ->orderBy('total_comprado', 'desc')
            ->limit(7)
            ->get(); //

        // Gráfico de Produtos Mais Comprados (por valor)
        $produtosMaisComprados = DB::table('itens_compra')
            ->join('produtos', 'itens_compra.produto_id', '=', 'produtos.id')
            ->join('compras', 'itens_compra.compra_id', '=', 'compras.id')
            ->whereBetween('compras.data_emissao', [$dataInicio, $dataFim])
            ->select('produtos.nome', DB::raw('SUM(itens_compra.subtotal) as total_valor'))
            ->groupBy('produtos.nome')
            ->orderBy('total_valor', 'desc')
            ->limit(7)
            ->get(); //

        // --- 5. Dados para popular filtros ---
        $fornecedores = Fornecedor::orderBy('razao_social')->get();

        return view('relatorios.compras.index', compact(
            'compras',
            'valorTotal',
            'numeroNotas',
            'ticketMedio',
            'comprasPorFornecedor',
            'produtosMaisComprados',
            'fornecedores',
            'dataInicio',
            'dataFim'
        ));
    }
}