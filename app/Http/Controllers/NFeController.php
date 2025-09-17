<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Nfe; // Importe o Model Nfe
use App\Services\NFeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use NFePHP\NFe\Config;
use NFePHP\NFe\Evento;
use App\Models\VendaItem; // Model para os itens da venda
use App\Models\Pagamento; // Model para os pagamentos
use App\Models\FormaPagamento; // Model para as formas de pagamento
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Support\Collection;

class NFeController extends Controller
{
    /**
     * Exibe a tela principal do gerenciador de NF-e.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
    
        // Busca pelas Notas Fiscais já Emitidas (sua lógica original, mantida)
        $notasEmitidasQuery = Nfe::with('venda.cliente')
            ->orderBy('created_at', 'desc');
    
        // Busca pelas Vendas que são Rascunhos de NF-e Avulsa
        $rascunhosQuery = Venda::where('status', 'Em Digitação')
            ->with('cliente')
            ->orderBy('updated_at', 'desc');
    
        // Aplica o filtro de busca em ambas as consultas, se houver
        if ($search) {
            $notasEmitidasQuery->where(function ($query) use ($search) {
                $query->where('numero_nfe', 'like', "%{$search}%")
                    ->orWhereHas('venda.cliente', function ($q) use ($search) {
                        $q->where('nome', 'like', "%{$search}%");
                    });
            });
    
            $rascunhosQuery->whereHas('cliente', function ($q) use ($search) {
                $q->where('nome', 'like', "%{$search}%");
            });
        }
    
        // Pagina os resultados separadamente para cada aba
        $notasEmitidas = $notasEmitidasQuery->paginate(10, ['*'], 'emitidas_page');
        $rascunhos = $rascunhosQuery->paginate(10, ['*'], 'rascunhos_page');
    
        return view('nfe.index', compact('notasEmitidas', 'rascunhos', 'search'));
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

    public function criarAvulsa()
    {
        $empresaId = Auth::user()->empresa_id;

        // Buscamos dados para preencher os selects do formulário
        $clientes = Cliente::where('empresa_id', $empresaId)->orderBy('nome')->get();
        $produtos = Produto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        // Adicione outras buscas se necessário (ex: transportadoras)

        return view('nfe.criar-avulsa', compact('clientes', 'produtos'));
    }



    public function storeAvulsa(Request $request, NFeService $nfeService)
    {
        // Validação dos dados recebidos do formulário (essencial!)
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'produtos.*.preco' => 'required|numeric|min:0.01',
            // Adicione aqui validações para frete, pagamento, etc.
        ]);

        try {
            // 1. Montar um objeto "Venda" virtual em memória
            $vendaVirtual = new Venda();
            
            // 2. Anexar as Relações Essenciais
            $vendaVirtual->setRelation('empresa', Auth::user()->empresa);
            $vendaVirtual->setRelation('cliente', Cliente::find($validated['cliente_id']));

            // 3. Montar a coleção de Itens
            $itens = new Collection();
            $totalVenda = 0;
            foreach ($validated['produtos'] as $produtoData) {
                $produto = Produto::find($produtoData['id']);
                $item = new VendaItem([
                    'produto_id' => $produto->id,
                    'quantidade' => $produtoData['quantidade'],
                    'preco_unitario' => $produtoData['preco'],
                    'subtotal_item' => $produtoData['quantidade'] * $produtoData['preco'],
                ]);
                $item->setRelation('produto', $produto); // Anexa o produto ao item
                $itens->push($item);
                $totalVenda += $item->subtotal_item;
            }
            $vendaVirtual->setRelation('items', $itens); // Note o 's' em 'items' para corresponder à relação
            $vendaVirtual->total = $totalVenda;
            
            // 4. Montar a coleção de Pagamentos (exemplo simples)
            $pagamentos = new Collection();
            $formaPagamento = FormaPagamento::first(); // Exemplo: pegando a primeira forma de pagamento
            $pagamento = new Pagamento([
                'valor' => $totalVenda,
            ]);
            $pagamento->setRelation('forma', $formaPagamento);
            $pagamentos->push($pagamento);
            $vendaVirtual->setRelation('pagamentos', $pagamentos);

            // 5. Chamar o NFeService com o objeto virtual
            // O service não saberá a diferença, pois o objeto tem a estrutura que ele espera.
            // Passamos null para os IDs da venda original, pois não existem.
            $resultado = $nfeService->emitir($vendaVirtual, null);

            // 6. Tratar o retorno
            if ($resultado['success']) {
                return redirect()->route('nfe.index')->with('success', $resultado['message']);
            } else {
                return back()->with('error', 'Falha ao emitir NF-e Avulsa: ' . $resultado['message'])->withInput();
            }

        } catch (\Exception $e) {
            return back()->with('error', 'Ocorreu um erro inesperado: ' . $e->getMessage())->withInput();
        }
    }

    public function storeOrUpdateRascunho(Request $request, Nfe $nfe = null)
    {
        // Validação dos dados (combine a validação aqui)
        // ...

        DB::beginTransaction();
        try {
            $data = $request->except(['_token', '_method', 'action', 'produtos']);
            $data['empresa_id'] = Auth::user()->empresa_id;
            $data['status'] = 'rascunho';
            
            // Se for um novo rascunho, cria. Se não, atualiza.
            $rascunho = Nfe::updateOrCreate(['id' => $nfe->id ?? null], $data);

            // Deleta os itens antigos para reinserir os novos
            $rascunho->items()->delete();

            // Insere os novos itens
            foreach ($request->produtos as $index => $produtoData) {
                NfeItem::create([
                    'nfe_id' => $rascunho->id,
                    'produto_id' => $produtoData['id'],
                    'numero_item' => $index + 1,
                    'quantidade' => $produtoData['quantidade'],
                    'valor_unitario' => $produtoData['preco'],
                    'valor_total' => $produtoData['quantidade'] * $produtoData['preco'],
                ]);
            }

            DB::commit();

            // Verifica a ação do botão
            if ($request->input('action') === 'issue_now') {
                // Se o usuário clicou em "Emitir", chama o método de emissão
                return $this->emitirRascunho($rascunho, app(NFeService::class));
            }

            // Se clicou em "Salvar Rascunho", redireciona para a edição
            return redirect()->route('nfe.rascunho.edit', $rascunho)->with('success', 'Rascunho salvo com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao salvar rascunho: ' . $e->getMessage())->withInput();
        }
    }

    public function emitirRascunho(Nfe $nfe, NFeService $nfeService)
    {
        // Lógica de autorização
        // ...
        
        // Constrói um objeto "Venda" virtual a partir do Rascunho salvo
        $vendaVirtual = new Venda();
        $vendaVirtual->setRelation('empresa', $nfe->empresa);
        $vendaVirtual->setRelation('cliente', $nfe->cliente); // Assumindo que você adicionará a relação no Model Nfe
        $vendaVirtual->total = $nfe->items->sum('valor_total');
        
        // Converte NfeItem para VendaItem virtualmente
        $vendaItems = new Collection();
        foreach($nfe->items as $nfeItem) {
            $item = new VendaItem($nfeItem->toArray());
            $item->setRelation('produto', $nfeItem->produto);
            $vendaItems->push($item);
        }
        $vendaVirtual->setRelation('items', $vendaItems);

        // ... (lógica para pagamentos e transporte) ...

        // Passa o ID do rascunho para o service poder atualizá-lo
        $resultado = $nfeService->emitir($vendaVirtual, null, $nfe->id);

        if ($resultado['success']) {
            return redirect()->route('nfe.index')->with('success', $resultado['message']);
        } else {
            // Se falhar, redireciona de volta para a edição do rascunho
            return redirect()->route('nfe.rascunho.edit', $nfe)->with('error', $resultado['message']);
        }
    }


    public function editRascunho(Nfe $nfe)
    {
        // Lógica de autorização
        if ($nfe->empresa_id !== Auth::user()->empresa_id || $nfe->status !== 'rascunho') {
            abort(403);
        }
        
        // Carrega os mesmos dados da view de criação
        $clientes = Cliente::where('empresa_id', $nfe->empresa_id)->get();
        $produtos = Produto::where('empresa_id', $nfe->empresa_id)->get();

        return view('nfe.criar-avulsa', compact('nfe', 'clientes', 'produtos'));
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


