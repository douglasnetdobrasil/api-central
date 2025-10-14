<?php

namespace App\Http\Controllers;

use App\Models\OrdemProducao;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProducaoDashboardController extends Controller
{
    /**
     * Exibe o painel principal do módulo de produção.
     * Usamos __invoke porque este controller tem apenas uma ação.
     */
    public function __invoke(Request $request)
    {
        // Coleta estatísticas rápidas para o painel
        $stats = [
            'ops_planejadas' => OrdemProducao::where('status', 'Planejada')->count(),
            'ops_em_producao' => OrdemProducao::where('status', 'Em Produção')->count(),
            'produtos_sem_ficha' => Produto::where('tipo', 'produto_acabado')->whereDoesntHave('fichaTecnica')->count(),
        ];

        // Busca as 5 ordens de produção mais recentes
        $ordensRecentes = OrdemProducao::with('produtoAcabado')->latest()->take(5)->get();

        return view('producao.dashboard', compact('stats', 'ordensRecentes'));
    }
}