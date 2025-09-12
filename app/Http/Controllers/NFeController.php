<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Nfe; // Importe o Model Nfe
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use NFePHP\NFe\Config;

class NFeController extends Controller
{
    /**
     * Exibe a tela principal do gerenciador de NF-e.
     */
    public function index(Request $request)
    {
        $empresaId = Auth::user()->empresa_id;
        $termoPesquisa = $request->query('search');

        // Inicia a query base, buscando apenas as notas da empresa do usuário logado
        $query = Nfe::where('empresa_id', $empresaId);

        // Se um termo de pesquisa foi enviado, aplica o filtro
        if ($termoPesquisa) {
            $query->where(function ($q) use ($termoPesquisa) {
                // Pesquisa no número da NF-e
                $q->where('numero_nfe', 'like', '%' . $termoPesquisa . '%')
                  // Pesquisa no nome do cliente (através do relacionamento com Venda e Cliente)
                  ->orWhereHas('venda.cliente', function ($subQ) use ($termoPesquisa) {
                      $subQ->where('nome', 'like', '%' . $termoPesquisa . '%');
                  });
            });
        }

        // Adiciona o eager loading para otimizar a consulta e ordena pelos mais recentes
        $notasEmitidas = $query->with('venda.cliente')
                               ->latest()
                               ->paginate(15);

        // Retorna a view, passando a coleção de notas e também o termo de pesquisa (para manter no campo do formulário)
        return view('nfe.index', [
            'notasEmitidas' => $notasEmitidas,
            'search' => $termoPesquisa,
        ]);
    }
    /**
     * Aciona o serviço para emitir uma NF-e para uma determinada venda.
     */
    public function emitir(Venda $venda, NFeService $nfeService)
    {
        // Validações de segurança e regras de negócio
        if ($venda->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }
        if ($venda->nfe_chave_acesso) {
            return back()->with('error', 'Esta venda já possui uma NF-e emitida.');
        }

        // Aciona o serviço de emissão (que já criamos)
        $resultado = $nfeService->emitirDeVendas([$venda->id]);

        // Trata o retorno
        if ($resultado['success']) {
            return redirect()->route('nfe.index')->with('success', $resultado['message']);
        } else {
            return back()->with('error', 'Falha ao emitir NF-e: ' . $resultado['message']);
        }
    }


    public function downloadDanfe(Nfe $nfe)
    {
        if ($nfe->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($nfe->caminho_danfe && Storage::disk('private')->exists($nfe->caminho_danfe)) {
            return Storage::disk('private')->response($nfe->caminho_danfe);
        }

        return back()->with('error', 'Arquivo DANFE não encontrado.');
    }

    public function downloadXml(Nfe $nfe)
    {
        if ($nfe->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }

        if ($nfe->caminho_xml && Storage::disk('private')->exists($nfe->caminho_xml)) {
            return Storage::disk('private')->download($nfe->caminho_xml);
        }

        return back()->with('error', 'Arquivo XML não encontrado.');
    }

    public function prepararEmissaoAgrupada(Request $request)
    {
        $vendaIds = $request->validate([
            'venda_ids' => 'required|array|min:1',
            'venda_ids.*' => 'exists:vendas,id',
        ])['venda_ids'];

        $vendas = Venda::with('cliente', 'itens.produto')->whereIn('id', $vendaIds)->get();

        // Validação: todas as vendas devem ser do mesmo cliente (se houver mais de uma)
        if (count($vendas) > 1) {
            $primeiroClienteId = $vendas->first()->cliente_id;
            foreach ($vendas as $venda) {
                if ($venda->cliente_id !== $primeiroClienteId) {
                    return back()->with('error', 'Todos os pedidos selecionados devem pertencer ao mesmo cliente.');
                }
            }
        }
        
        // Validação de segurança
        foreach ($vendas as $venda) {
            if ($venda->empresa_id !== Auth::user()->empresa_id) {
                abort(403, 'Acesso não autorizado.');
            }
        }

        // Agrupa todos os itens de todas as vendas em uma única coleção
        $itensAgrupados = $vendas->pluck('itens')->flatten()->groupBy('produto_id')->map(function ($items) {
            $primeiroItem = clone $items->first(); // Usamos clone para não modificar o objeto original
            $primeiroItem->quantidade = $items->sum('quantidade');
            $primeiroItem->total = $items->sum('total'); // Recalcula o total do item agrupado
            return $primeiroItem;
        });

        $cliente = $vendas->first()->cliente;
        $valorTotal = $itensAgrupados->sum('subtotal_item');

        // Retorna a nova view de "confirmação" com os dados compilados
        return view('nfe.confirmar_agrupada', compact('cliente', 'itensAgrupados', 'valorTotal', 'vendaIds'));
    }

    // ...


    public function cancelar(Nfe $nfe, Request $request, NFeService $nfeService)
    {
        if ($nfe->empresa_id !== Auth::user()->empresa_id) {
            abort(403, 'Acesso não autorizado.');
        }
        if ($nfe->status !== 'autorizada') {
            return back()->with('error', 'Apenas NF-e autorizadas podem ser canceladas.');
        }
    
        $validated = $request->validate([
            'justificativa' => 'required|string|min:15|max:255',
        ]);
    
        // Aciona o serviço real que se comunica com a SEFAZ
        $resultado = $nfeService->cancelar($nfe, $validated['justificativa']);
    
        if ($resultado['success']) {
            return redirect()->route('nfe.index')->with('success', $resultado['message']);
        } else {
            return back()->with('error', 'Falha ao cancelar NF-e: ' . $resultado['message']);
        }
    }

    public function enviarCCe(Nfe $nfe, Request $request, NFeService $nfeService)
{
    if ($nfe->empresa_id !== Auth::user()->empresa_id) {
        abort(403, 'Acesso não autorizado.');
    }
    if ($nfe->status !== 'autorizada') {
        return back()->with('error', 'Apenas NF-e autorizadas podem receber Carta de Correção.');
    }

    $validated = $request->validate([
        'correcao' => 'required|string|min:15|max:1000',
    ]);

    $resultado = $nfeService->cartaCorrecao($nfe, $validated['correcao']);

    if ($resultado['success']) {
        return redirect()->route('nfe.index')->with('success', $resultado['message']);
    } else {
        return back()->with('error', 'Falha ao emitir CC-e: ' . $resultado['message']);
    }
}

public function downloadDacce(Cce $cce)
{
    // Validação de segurança para garantir que a CCe pertence à empresa do usuário
    if ($cce->nfe->empresa_id !== Auth::user()->empresa_id) {
        abort(403, 'Acesso não autorizado.');
    }

    if (Storage::disk('private')->exists($cce->caminho_pdf)) {
        return Storage::disk('private')->response($cce->caminho_pdf);
    }

    return back()->with('error', 'Arquivo DACCE (PDF da CC-e) não encontrado.');
}

    public function importarPedidosView()
    {
        $empresaId = Auth::user()->empresa_id;

        // Busca Vendas concluídas que AINDA NÃO possuem NF-e emitida
        $vendasParaEmitir = Venda::where('empresa_id', $empresaId)
            ->where('status', 'concluida') // Ajuste o status conforme sua regra de negócio
            ->whereNull('nfe_chave_acesso')
            ->with('cliente')
            ->latest()
            ->paginate(20);

        return view('nfe.importar-pedidos', compact('vendasParaEmitir'));
    }

    public function store(Request $request, NFeService $nfeService)
    {
        // Valida se recebemos um array de IDs de vendas
        $validated = $request->validate([
            'venda_ids' => 'required|array|min:1',
            'venda_ids.*' => 'exists:vendas,id',
        ]);

        // Delega a lógica complexa de emissão para o NFeService
        $resultado = $nfeService->emitirDeVendas($validated['venda_ids']);

        // Trata o retorno do serviço
        if ($resultado['success']) {
            return redirect()->route('nfe.index')->with('success', $resultado['message']);
        } else {
            return back()->with('error', 'Falha ao emitir NF-e: ' . $resultado['message']);
        }
    }
}


