<?php

namespace App\Http\Controllers\Relatorios;

use App\Http\Controllers\Controller;
use App\Models\CentroCusto;
use App\Models\LancamentoCentroCusto;
use Illuminate\Http\Request;

class CentroCustoRelatorioController extends Controller
{
    // Exibe o formulário de filtros para o extrato
    public function extratoForm()
    {
        $centrosCusto = CentroCusto::analiticos()->ativos()->get();
        return view('relatorios.centros_custo.extrato_form', compact('centrosCusto'));
    }

    public function fluxoCaixaForm()
{
    return view('relatorios.centros_custo.fluxo_caixa_form');
}

public function gerarFluxoCaixa(Request $request)
{
    // ... validação das datas ...

    // Pega todos os centros de custo e organiza em uma árvore
    $centrosCusto = CentroCusto::with('children')->whereNull('parent_id')->get();
    
    $dadosRelatorio = $this->calcularTotaisRecursivamente($centrosCusto, $request->data_inicio, $request->data_fim);

    return view('relatorios.centros_custo.fluxo_caixa_resultado', [
        'dadosRelatorio' => $dadosRelatorio,
        // ... outras vars ...
    ]);
}


private function calcularTotaisRecursivamente($centrosCusto, $inicio, $fim)
{
    $resultado = [];
    foreach ($centrosCusto as $cc) {
        $receitas = 0;
        $despesas = 0;

        // Se for analítico, calcula seus próprios totais
        if ($cc->tipo === 'ANALITICO') {
            $receitas = $this->getValorPorTipo($cc->id, 'App\Models\ContaReceber', $inicio, $fim);
            $despesas = $this->getValorPorTipo($cc->id, 'App\Models\ContaPagar', $inicio, $fim);
        }

        $filhosData = [];
        // Se tiver filhos, calcula os totais deles recursivamente
        if ($cc->children->isNotEmpty()) {
            $filhosData = $this->calcularTotaisRecursivamente($cc->children, $inicio, $fim);
            // Um centro sintético tem seus totais baseados na soma dos filhos
            $receitas += collect($filhosData)->sum('receitas');
            $despesas += collect($filhosData)->sum('despesas');
        }

        $resultado[] = [
            'id' => $cc->id,
            'nome' => $cc->nome,
            'tipo' => $cc->tipo,
            'receitas' => $receitas,
            'despesas' => $despesas,
            'saldo' => $receitas - $despesas,
            'filhos' => $filhosData
        ];
    }
    return $resultado;
}


private function getValorPorTipo($ccId, $modelType, $inicio, $fim)
{
    return LancamentoCentroCusto::where('centro_custo_id', $ccId)
        ->where('lancamento_type', $modelType)
        ->whereHas('lancamento', function ($query) use ($inicio, $fim) {
            $query->whereBetween('data_vencimento', [$inicio, $fim]);
        })
        ->sum('valor');
}

    // Processa os filtros e exibe o relatório
    public function gerarExtrato(Request $request)
    {
        $request->validate([
            'centro_custo_id' => 'required|exists:centros_custo,id',
            'data_inicio' => 'required|date',
            'data_fim' => 'required|date|after_or_equal:data_inicio',
        ]);

        $centroCusto = CentroCusto::findOrFail($request->centro_custo_id);

        $lancamentos = LancamentoCentroCusto::where('centro_custo_id', $request->centro_custo_id)
            // Filtra pela data do lançamento original (conta a pagar/receber)
            ->whereHas('lancamento', function ($query) use ($request) {
                // Supondo que seus models financeiros tenham um campo como 'data_vencimento' ou 'data_pagamento'
                $query->whereBetween('data_vencimento', [$request->data_inicio, $request->data_fim]);
            })
            ->with('lancamento') // Carrega o model relacionado (ContaPagar ou ContaReceber)
            ->get();
            
        return view('relatorios.centros_custo.extrato_resultado', [
            'lancamentos' => $lancamentos,
            'centroCusto' => $centroCusto,
            'periodo' => ['inicio' => $request->data_inicio, 'fim' => $request->data_fim]
        ]);
    }
    
    // ... métodos para outros relatórios ...
}