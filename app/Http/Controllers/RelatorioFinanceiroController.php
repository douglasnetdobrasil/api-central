<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RelatorioFinanceiroController extends Controller
{
    public function index(Request $request)
    {
        // Define o período padrão como o mês atual se não for informado
        $dataInicio = $request->input('data_inicio', Carbon::now()->startOfMonth()->toDateString());
        $dataFim = $request->input('data_fim', Carbon::now()->endOfMonth()->toDateString());

        // --- 1. Buscar RECEITAS REALIZADAS (Dinheiro que entrou) ---
        $totalReceitas = DB::table('recebimentos')
            ->whereBetween('data_recebimento', [$dataInicio, $dataFim])
            ->sum('valor_recebido'); //

        // --- 2. Buscar DESPESAS REALIZADAS (Dinheiro que saiu) ---
        $totalDespesas = DB::table('conta_pagamentos')
            ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
            ->sum('valor'); //

        // --- 3. Calcular o SALDO ---
        $saldo = $totalReceitas - $totalDespesas;

        // --- 4. Dados para o GRÁFICO DE FLUXO DE CAIXA ---
        $entradasPorDia = DB::table('recebimentos')
            ->whereBetween('data_recebimento', [$dataInicio, $dataFim])
            ->select(DB::raw('DATE(data_recebimento) as data'), DB::raw('SUM(valor_recebido) as total'))
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        $saidasPorDia = DB::table('conta_pagamentos')
            ->whereBetween('data_pagamento', [$dataInicio, $dataFim])
            ->select(DB::raw('DATE(data_pagamento) as data'), DB::raw('SUM(valor) as total'))
            ->groupBy('data')
            ->orderBy('data')
            ->get();

        // --- 5. Contas Pendentes ---
        $hoje = Carbon::now()->toDateString();
        $contasAReceber = DB::table('contas_a_receber')
            ->join('clientes', 'contas_a_receber.cliente_id', '=', 'clientes.id')
            ->select('contas_a_receber.*', 'clientes.nome as cliente_nome')
            ->whereIn('status', ['A Receber', 'Recebido Parcialmente'])
            ->orderBy('data_vencimento')
            ->get(); //

        $contasAPagar = DB::table('contas_a_pagar')
            ->leftJoin('fornecedores', 'contas_a_pagar.fornecedor_id', '=', 'fornecedores.id')
            ->select('contas_a_pagar.*', 'fornecedores.razao_social as fornecedor_nome')
            ->whereIn('status', ['A Pagar'])
            ->orderBy('data_vencimento')
            ->get(); //

        return view('relatorios.financeiro.index', compact(
            'dataInicio',
            'dataFim',
            'totalReceitas',
            'totalDespesas',
            'saldo',
            'entradasPorDia',
            'saidasPorDia',
            'contasAReceber',
            'contasAPagar',
            'hoje'
        ));
    }
}