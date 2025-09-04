<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Nfe; // Importe o Model Nfe
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NFeController extends Controller
{
    /**
     * Exibe a tela principal do gerenciador de NF-e.
     */
    public function index()
    {
        $empresaId = Auth::user()->empresa_id;

        // Busca Vendas concluídas que AINDA NÃO possuem NF-e emitida
        $vendasParaEmitir = Venda::where('empresa_id', $empresaId)
            ->where('status', 'concluida')
            ->whereNull('nfe_chave_acesso')
            ->with('cliente')
            ->latest()
            ->paginate(10, ['*'], 'pendentes');

        // Busca as NF-e que JÁ FORAM emitidas
        $notasEmitidas = Nfe::where('empresa_id', $empresaId)
            ->with('venda.cliente')
            ->latest()
            ->paginate(10, ['*'], 'emitidas');

        return view('nfe.index', compact('vendasParaEmitir', 'notasEmitidas'));
    }

    /**
     * Aciona o serviço para emitir uma NF-e para uma determinada venda.
     */
    public function emitir(Venda $venda, NFeService $nfeService)
    {
        // Validações de segurança
        if ($venda->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }
        if ($venda->status !== 'concluida') {
            return back()->with('error', 'Apenas pedidos concluídos podem gerar NF-e.');
        }
        if ($venda->nfe_chave_acesso) {
            return back()->with('error', 'Esta venda já possui uma NF-e emitida.');
        }

        // Aciona o serviço de emissão
        $resultado = $nfeService->emitir($venda);

        // Trata o retorno
        if ($resultado['success']) {
            return back()->with('success', $resultado['message']);
        } else {
            return back()->with('error', 'Falha ao emitir NF-e: ' . $resultado['message']);
        }
    }
}