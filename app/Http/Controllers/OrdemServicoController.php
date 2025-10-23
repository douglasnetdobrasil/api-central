<?php

namespace App\Http\Controllers;

use App\Models\OrdemServico;
use App\Models\Cliente;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\OsProduto;
use App\Models\OsServico;
use App\Models\OsHistorico;
use App\Models\ClienteEquipamento;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OrdemServicoController extends Controller
{
    // ... (index e create - sem alterações) ...
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

    // ... (store - atualizado anteriormente) ...
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'defeito_relatado' => 'required|string',
            'cliente_equipamento_id' => 'nullable|exists:cliente_equipamentos,id', // <-- CORRIGIDO: Agora é opcional
            'data_previsao_conclusao' => 'nullable|date',
            
            // Campos de texto agora são a fonte principal se um equipamento existente não for selecionado
            'equipamento' => 'required|string|max:255', // <-- CORRIGIDO: Agora é obrigatório
            'numero_serie' => 'nullable|string|max:100',
        ]);

        $dadosParaCriar = $validatedData;
        $dadosParaCriar['empresa_id'] = auth()->user()->empresa_id;
        $dadosParaCriar['data_entrada'] = now();

        // Esta lógica permanece a mesma e está correta:
        // Se um equipamento existente FOI selecionado, ele usa os dados do BD.
        // Se não, ele usa os dados digitados (que acabaram de ser validados).
        $equipamento = ClienteEquipamento::find($request->cliente_equipamento_id);
        if ($equipamento) {
            $dadosParaCriar['equipamento'] = $equipamento->descricao;
            $dadosParaCriar['numero_serie'] = $equipamento->numero_serie;
        }

        $ordemServico = OrdemServico::create($dadosParaCriar);

        $ordemServico->historico()->create([
            'user_id' => auth()->id(),
            'descricao' => "OS Criada com status '{$ordemServico->status}'.",
        ]);

        return redirect()->route('ordens-servico.edit', $ordemServico->id)
                         ->with('success', "Ordem de Serviço #{$ordemServico->id} criada! Adicione peças e serviços.");
    }
    
    // ... (show - sem alterações) ...
    public function show(OrdemServico $ordemServico)
    {
        $ordemServico->load(['cliente', 'tecnico', 'produtos.produto', 'servicos.servico', 'servicos.tecnico']);
        return view('ordens_servico.show', compact('ordemServico'));
    }

    // ... (edit - atualizado anteriormente) ...
    public function edit(OrdemServico $ordemServico)
    {
        $clientes = Cliente::orderBy('nome')->get();
        $tecnicos = User::orderBy('name')->get();
        $pecas = Produto::where('ativo', 1)
                        ->whereIn('tipo', ['venda', 'produto_acabado', 'materia_prima'])
                        ->orderBy('nome')
                        ->get();
        $servicos = Produto::where('ativo', 1)
                           ->where('tipo', 'servico')
                           ->orderBy('nome')
                           ->get();
        
        $equipamentosDoCliente = ClienteEquipamento::where('cliente_id', $ordemServico->cliente_id)
                                                  ->orderBy('descricao')
                                                  ->get();

        $ordemServico->load(['produtos.produto', 'servicos.servico', 'servicos.tecnico']);
    
        return view('ordens_servico.edit', compact(
            'ordemServico', 'clientes', 'tecnicos', 'pecas', 'servicos', 'equipamentosDoCliente'
        ));
    }

    /**
     * ==========================================================
     * ||||||||||||||||||| ESTE É O BUG PRINCIPAL |||||||||||||||||||
     * ==========================================================
     * Corrigido para usar 'descricao' e formatar o 'texto'
     */
    public function getEquipamentosByCliente(int $clienteId): JsonResponse
    {
        $cliente = Cliente::findOrFail($clienteId);

        $equipamentos = $cliente->equipamentos()
            ->select('id', 'descricao', 'numero_serie') // <-- CORRIGIDO (era 'nome')
            ->get()
            ->map(function ($equip) {
                // Formata o texto para exibição no dropdown
                $equip->texto = $equip->descricao . ($equip->numero_serie ? " (SN: {$equip->numero_serie})" : "");
                return $equip;
            });

        return response()->json($equipamentos);
    }

    // ... (update - atualizado anteriormente) ...
    public function update(Request $request, OrdemServico $ordemServico)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'tecnico_id' => 'nullable|exists:users,id',
            'status' => 'required|string|max:50',
            'defeito_relatado' => 'required|string',
            'laudo_tecnico' => 'nullable|string',
            'data_previsao_conclusao' => 'nullable|date',
            'cliente_equipamento_id' => 'required|exists:cliente_equipamentos,id',
            'equipamento' => 'nullable|string|max:255',
            'numero_serie' => 'nullable|string|max:100',
        ]);
    
        $mudancas = [];
        if ($ordemServico->status !== $validatedData['status']) {
            $mudancas[] = "Status alterado de '{$ordemServico->status}' para '{$validatedData['status']}'";
        }

        $equipamento = ClienteEquipamento::find($request->cliente_equipamento_id);
        if ($equipamento) {
            $validatedData['equipamento'] = $equipamento->descricao;
            $validatedData['numero_serie'] = $equipamento->numero_serie;
        }
        
        $ordemServico->update($validatedData);
    
        if (!empty($mudancas)) {
            $ordemServico->historico()->create([
                'user_id' => auth()->id(),
                'descricao' => implode('. ', $mudancas) . '.',
            ]);
        }
    
        return redirect()->route('ordens-servico.edit', $ordemServico->id)
                         ->with('success', 'Dados principais da Ordem de Serviço atualizados com sucesso!');
    }

    // ... (imprimir, storeProduto, destroyProduto, storeServico, destroyServico, destroy - sem alterações) ...
    public function imprimir(OrdemServico $ordemServico)
    {
        $ordemServico->load(['cliente', 'tecnico', 'produtos.produto', 'servicos.servico', 'servicos.tecnico']);
        $empresa = auth()->user()->empresa;
        return view('ordens_servico.imprimir', compact('ordemServico', 'empresa'));
    }
    public function storeProduto(Request $request, OrdemServico $ordemServico)
    {
        $request->validate(['produto_id' => 'required|exists:produtos,id', 'quantidade' => 'required|numeric|min:0.01']);
        $produto = Produto::find($request->produto_id);
        $quantidade = $request->quantidade;
        DB::transaction(function () use ($ordemServico, $produto, $quantidade) {
            $ordemServico->produtos()->create(['produto_id' => $produto->id, 'quantidade' => $quantidade, 'preco_unitario' => $produto->preco_venda, 'subtotal' => $quantidade * $produto->preco_venda]);
            $ordemServico->atualizarValores();
        });
        return redirect()->back()->with('success', 'Produto adicionado com sucesso!');
    }
    public function destroyProduto(OsProduto $osProduto)
    {
        $ordemServico = $osProduto->ordemServico;
        DB::transaction(function () use ($osProduto, $ordemServico) {
            $osProduto->delete();
            $ordemServico->atualizarValores();
        });
        return redirect()->back()->with('success', 'Produto removido com sucesso!');
    }
    public function storeServico(Request $request, OrdemServico $ordemServico)
    {
        $request->validate(['servico_id' => 'required|exists:produtos,id', 'tecnico_id' => 'nullable|exists:users,id', 'quantidade' => 'required|numeric|min:0.01']);
        $servico = Produto::find($request->servico_id);
        DB::transaction(function () use ($ordemServico, $servico, $request) {
            $ordemServico->servicos()->create(['servico_id' => $servico->id, 'tecnico_id' => $request->tecnico_id, 'quantidade' => $request->quantidade, 'preco_unitario' => $servico->preco_venda, 'subtotal' => $request->quantidade * $servico->preco_venda]);
            $ordemServico->atualizarValores();
        });
        return redirect()->back()->with('success', 'Serviço adicionado com sucesso!');
    }
    public function destroyServico(OsServico $osServico)
    {
        $ordemServico = $osServico->ordemServico;
        DB::transaction(function () use ($osServico, $ordemServico) {
            $osServico->delete();
            $ordemServico->atualizarValores();
        });
        return redirect()->back()->with('success', 'Serviço removido com sucesso!');
    }
    public function destroy(OrdemServico $ordemServico)
    {
        $ordemServico->delete();
        return redirect()->route('ordens-servico.index')->with('success', 'Ordem de Serviço excluída com sucesso!');
    }

    // ... (storeEquipamentoModal - o método do modal que adicionamos) ...
    public function storeEquipamentoModal(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string|max:255',
            'numero_serie' => 'nullable|string|max:100',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
        ]);
        $validatedData['empresa_id'] = auth()->user()->empresa_id;
        $equipamento = ClienteEquipamento::create($validatedData);
        $equipamento->texto = $equipamento->descricao . ($equipamento->numero_serie ? " (SN: {$equipamento->numero_serie})" : "");
        return response()->json($equipamento);
    }
}