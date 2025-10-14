<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\EstoqueMovimento;
use Illuminate\Support\Facades\DB;

class RelatorioEstoqueController extends Controller
{
    /**
     * Exibe a tela principal do relatório de estoque.
     */
    public function index(Request $request)
    {
        // --- 1. Query Base de Produtos ---
        $query = Produto::with('categoria')->where('ativo', true);

        // Aplica filtro de categoria, se houver
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        // --- 2. Dados para a Tabela de Posição de Estoque ---
        $produtos = $query->orderBy('nome')->paginate(20)->withQueryString();

        // --- 3. Dados para os KPIs ---
        $kpiQuery = Produto::where('ativo', true); // KPIs sobre todo o estoque ativo
        $totalItens = $kpiQuery->sum('estoque_atual');
        $valorTotalCusto = $kpiQuery->sum(DB::raw('estoque_atual * preco_custo'));
        $valorTotalVenda = $kpiQuery->sum(DB::raw('estoque_atual * preco_venda'));

        // --- 4. Dados para o Gráfico (Valor de Estoque por Categoria) ---
        $valorPorCategoria = Produto::where('ativo', true)
            ->join('categorias', 'produtos.categoria_id', '=', 'categorias.id')
            ->select('categorias.nome', DB::raw('SUM(produtos.estoque_atual * produtos.preco_custo) as total_custo'))
            ->groupBy('categorias.nome')
            ->orderBy('total_custo', 'desc')
            ->get();

        // --- 5. Dados para popular o filtro ---
        $categorias = Categoria::orderBy('nome')->get();

        return view('relatorios.estoque.index', compact(
            'produtos',
            'totalItens',
            'valorTotalCusto',
            'valorTotalVenda',
            'valorPorCategoria',
            'categorias'
        ));
    }

    /**
     * Exibe o histórico de movimentações de um produto específico.
     */
    public function movimentacoes(Produto $produto, Request $request)
    {
        $query = $produto->movimentosEstoque()->with('origem')->latest(); // O model Produto precisa ter o relacionamento 'movimentosEstoque'

        // Filtro opcional por período
        if ($request->filled('data_inicio')) {
            $query->whereDate('created_at', '>=', $request->data_inicio);
        }
        if ($request->filled('data_fim')) {
            $query->whereDate('created_at', '<=', $request->data_fim);
        }

        $movimentacoes = $query->paginate(30)->withQueryString();

        return view('relatorios.estoque.movimentacoes', compact('produto', 'movimentacoes'));
    }
}