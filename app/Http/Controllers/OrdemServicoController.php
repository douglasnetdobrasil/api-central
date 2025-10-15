<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\OsProduto; // <-- ADICIONADO: Importar para usar nos novos métodos
use App\Models\OsServico;
use App\Models\OsHistorico;
use Illuminate\Support\Facades\DB; // <-- ADICIONADO: Essencial para transações seguras

class OrdemServicoController extends Controller
{
    // ... (Seus métodos index, create, store e show continuam aqui, sem alterações) ...
    public function index(Request $request)
    {
        $query = OrdemServico::with(['cliente', 'tecnico'])->latest();
        if ($request->filled('search_os')) { $query->where('id', $request->search_os); }
        if ($request->filled('search_cliente')) { $query->whereHas('cliente', function ($q) use ($request) { $q->where('nome', 'like', '%' . $request->search_cliente . '%'); }); }
        if ($request->filled('status') && $request->status !== 'Todos') { $query->where('status', $request->status); }
        $ordensServico = $query->paginate(15)->withQueryString();
        return view('ordens_servico.index', compact('ordensServico'));
    }
    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        $tecnicos = User::orderBy('name')->get();
        return view('ordens_servico.create', compact('clientes', 'tecnicos'));
    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'equipamento' => 'required|string|max:255',
            'defeito_relatado' => 'required|string',
        ]);
        $dadosParaCriar = $validatedData;
        $dadosParaCriar['empresa_id'] = auth()->user()->empresa_id;
        $dadosParaCriar['data_entrada'] = now();
        $ordemServico = OrdemServico::create($dadosParaCriar);
        return redirect()->route('ordens-servico.index')->with('success', "Ordem de Serviço #{$ordemServico->id} criada com sucesso!");
    }
    public function show(OrdemServico $ordemServico)
    {
        $ordemServico->load(['cliente', 'tecnico', 'produtos.produto', 'servicos.servico', 'servicos.tecnico']); // <-- LINHA CORRIGIDA
        return view('ordens_servico.show', compact('ordemServico'));
    }


    /**
     * Mostra o formulário para editar uma Ordem de Serviço existente.
     */
    public function edit(OrdemServico $ordemServico)
    {
        // Carrega os dados para os selects principais
        $clientes = Cliente::orderBy('nome')->get();
        $tecnicos = User::orderBy('name')->get(); // Você pode adicionar ->where('tipo', 'tecnico') se tiver
    
        // Busca as PEÇAS disponíveis para adicionar
        $pecas = Produto::where('ativo', 1)
                        ->whereIn('tipo', ['venda', 'produto_acabado', 'materia_prima'])
                        ->orderBy('nome')
                        ->get();
    
        // Busca os SERVIÇOS disponíveis para adicionar
        // Garanta que você tem produtos cadastrados com tipo='servico' no seu banco
        $servicos = Produto::where('ativo', 1)
                           ->where('tipo', 'servico')
                           ->orderBy('nome')
                           ->get();
    
        // Carrega os itens que JÁ ESTÃO na Ordem de Serviço para listá-los
        $ordemServico->load(['produtos.produto', 'servicos.servico', 'servicos.tecnico']);
    
        // Envia todas as variáveis necessárias para a view
        return view('ordens_servico.edit', compact(
            'ordemServico',
            'clientes',
            'tecnicos',
            'pecas',     // <<-- Esta variável vai popular o dropdown de peças
            'servicos'   // <<-- Esta variável vai popular o dropdown de serviços
        ));
    }

    /**
     * Atualiza uma Ordem de Serviço no banco de dados.
     */
    public function update(Request $request, OrdemServico $ordemServico)
    {
        // Validação completa para TODOS os campos do formulário principal
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'equipamento' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:100',
            'defeito_relatado' => 'required|string',
            'laudo_tecnico' => 'nullable|string',
            'data_previsao_conclusao' => 'nullable|date',
        ]);
    
        // Lógica para registrar o histórico (que você já tinha)
        $mudancas = [];
        if ($ordemServico->status !== $validatedData['status']) {
            $mudancas[] = "Status alterado de '{$ordemServico->status}' para '{$validatedData['status']}'";
        }
        
        // Salva TODAS as alterações no banco de dados
        $ordemServico->update($validatedData);
    
        if (!empty($mudancas)) {
            $ordemServico->historico()->create([
                'user_id' => auth()->id(), // <-- CORREÇÃO AQUI
                'descricao' => implode('. ', $mudancas) . '.',
            ]);
        }
    
        // Redireciona de volta com mensagem de sucesso
        return redirect()->route('ordens-servico.edit', $ordemServico->id)
                         ->with('success', 'Dados principais da Ordem de Serviço atualizados com sucesso!');
    }


    // ===================================================================
    // ||||||||||||||||||||| MÉTODOS NOVOS ADICIONADOS |||||||||||||||||||||
    // ===================================================================

    /**
     * Adiciona um produto a uma Ordem de Serviço.
     */

    public function imprimir(OrdemServico $ordemServico)
    {
        // Carrega todos os relacionamentos necessários para a impressão
        $ordemServico->load(['cliente', 'tecnico', 'produtos.produto', 'servicos.servico', 'servicos.tecnico']);
    
        // Busca os dados da empresa para o cabeçalho
        $empresa = auth()->user()->empresa;
    
        // Retorna a nova view 'imprimir.blade.php' com os dados
        return view('ordens_servico.imprimir', compact('ordemServico', 'empresa'));
    }
    public function storeProduto(Request $request, OrdemServico $ordemServico)
    {
        $request->validate([
            'produto_id' => 'required|exists:produtos,id',
            'quantidade' => 'required|numeric|min:0.01',
        ]);

        $produto = Produto::find($request->produto_id);
        $quantidade = $request->quantidade;

        DB::transaction(function () use ($ordemServico, $produto, $quantidade) {
            $ordemServico->produtos()->create([
                'produto_id'     => $produto->id,
                'quantidade'     => $quantidade,
                'preco_unitario' => $produto->preco_venda,
                'subtotal'       => $quantidade * $produto->preco_venda,
            ]);
            $ordemServico->atualizarValores();
        });

        return redirect()->back()->with('success', 'Produto adicionado com sucesso!');
    }

    /**
     * Remove um produto de uma Ordem de Serviço.
     */
    public function destroyProduto(OsProduto $osProduto)
    {
        $ordemServico = $osProduto->ordemServico;
        DB::transaction(function () use ($osProduto, $ordemServico) {
            $osProduto->delete();
            $ordemServico->atualizarValores();
        });
        return redirect()->back()->with('success', 'Produto removido com sucesso!');
    }

    /**
     * Adiciona um serviço a uma Ordem de Serviço.
     */
    public function storeServico(Request $request, OrdemServico $ordemServico)
    {
        $request->validate([
            'servico_id' => 'required|exists:produtos,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'quantidade' => 'required|numeric|min:0.01',
        ]);

        $servico = Produto::find($request->servico_id);
        
        DB::transaction(function () use ($ordemServico, $servico, $request) {
            $ordemServico->servicos()->create([
                'servico_id'     => $servico->id,
                'tecnico_id'     => $request->tecnico_id,
                'quantidade'     => $request->quantidade,
                'preco_unitario' => $servico->preco_venda,
                'subtotal'       => $request->quantidade * $servico->preco_venda,
            ]);
            $ordemServico->atualizarValores();
        });

        return redirect()->back()->with('success', 'Serviço adicionado com sucesso!');
    }

    /**
     * Remove um serviço de uma Ordem de Serviço.
     */
    public function destroyServico(OsServico $osServico)
    {
        $ordemServico = $osServico->ordemServico;
        DB::transaction(function () use ($osServico, $ordemServico) {
            $osServico->delete();
            $ordemServico->atualizarValores();
        });
        return redirect()->back()->with('success', 'Serviço removido com sucesso!');
    }

    /**
     * Remove uma Ordem de Serviço do banco de dados.
     */
    public function destroy(OrdemServico $ordemServico)
    {
        $ordemServico->delete();
        return redirect()->route('ordens-servico.index')
                         ->with('success', 'Ordem de Serviço excluída com sucesso!');
    }
}