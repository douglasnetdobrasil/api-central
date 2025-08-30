<?php

namespace App\Http\Controllers;

use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrcamentoController extends Controller
{
    public function index()
    {
        $orcamentos = Orcamento::where('empresa_id', Auth::user()->empresa_id)
            ->with('cliente')
            ->latest()->paginate(15);
        return view('orcamentos.index', compact('orcamentos'));
    }

    public function create()
    {
        $clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
        return view('orcamentos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_emissao' => 'required|date',
            'data_validade' => 'nullable|date|after_or_equal:data_emissao',
            'observacoes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produto_id' => 'required|exists:produtos,id',
            'items.*.quantidade' => 'required|numeric|min:0.01',
            'items.*.valor_unitario' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $valorTotal = 0;
            foreach($validated['items'] as $item) {
                $valorTotal += $item['quantidade'] * $item['valor_unitario'];
            }

            $orcamento = Orcamento::create([
                'empresa_id' => Auth::user()->empresa_id,
                'cliente_id' => $validated['cliente_id'],
                'vendedor_id' => Auth::id(),
                'data_emissao' => $validated['data_emissao'],
                'data_validade' => $validated['data_validade'],
                'observacoes' => $validated['observacoes'],
                'valor_total' => $valorTotal,
                'status' => 'Pendente',
            ]);

            foreach($validated['items'] as $item) {
                $produto = Produto::find($item['produto_id']);
                OrcamentoItem::create([
                    'orcamento_id' => $orcamento->id,
                    'produto_id' => $item['produto_id'],
                    'descricao_produto' => $produto->nome,
                    'quantidade' => $item['quantidade'],
                    'valor_unitario' => $item['valor_unitario'],
                    'subtotal' => $item['quantidade'] * $item['valor_unitario'],
                ]);
            }
            
            DB::commit();
            return redirect()->route('orcamentos.index')->with('success', 'Orçamento criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar orçamento: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Orcamento $orcamento)
    {
        $this->authorize('view', $orcamento); // Exemplo de autorização
        $orcamento->load('itens.produto');
        return view('orcamentos.show', compact('orcamento'));
    }

    // Os métodos edit(), update() e destroy() seguiriam o mesmo padrão...
}