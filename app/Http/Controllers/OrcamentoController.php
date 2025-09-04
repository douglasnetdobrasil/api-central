<?php

namespace App\Http\Controllers;

use App\Models\Venda;
use App\Models\Orcamento;
use App\Models\OrcamentoItem;
use App\Models\Cliente;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrcamentoController extends Controller
{
    public function index(Request $request) // Adicione Request $request
    {
        // Inicia a query base
        $query = Orcamento::where('empresa_id', Auth::user()->empresa_id)
            ->with('cliente')
            ->latest();

        // Verifica se há um termo de busca
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                // Busca pelo número do orçamento (removendo o '#')
                if (is_numeric($searchTerm)) {
                    $q->where('id', $searchTerm);
                }
                // Ou busca pelo nome do cliente
                $q->orWhereHas('cliente', function ($clienteQuery) use ($searchTerm) {
                    $clienteQuery->where('nome', 'like', "%{$searchTerm}%");
                });
            });
        }

        $orcamentos = $query->paginate(15);

        return view('orcamentos.index', compact('orcamentos'));
    }


    public function create()
    {
        $clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
        return view('orcamentos.create', compact('clientes'));
    }

    public function edit(Orcamento $orcamento)
    {
        $this->authorize('update', $orcamento); // Requer policy de autorização (opcional, mas recomendado)
        
        // Carrega o orçamento com seus itens e os produtos relacionados
        $orcamento->load('itens.produto');
        
        $clientes = Cliente::where('empresa_id', Auth::user()->empresa_id)->orderBy('nome')->get();
        
        return view('orcamentos.edit', compact('orcamento', 'clientes'));
    }

    public function store(Request $request)
    {
        // 1. Validação dos dados
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'data_emissao' => 'required|date',
            'data_validade' => 'nullable|date|after_or_equal:data_emissao',
            'observacoes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.produto_id' => 'required|exists:produtos,id',
            'items.*.produto_nome' => 'required|string',
            'items.*.quantidade' => 'required|numeric|min:0.01',
            'items.*.valor_unitario' => 'required|numeric|min:0',
        ]);
    
        DB::beginTransaction();
        try {
            // 2. Calcula o valor total
            $valorTotal = 0;
            foreach ($validatedData['items'] as $item) {
                $valorTotal += $item['quantidade'] * $item['valor_unitario'];
            }
    
            // 3. Cria o Orçamento principal (cabeçalho)
            $orcamento = Orcamento::create([
                'empresa_id' => auth()->user()->empresa_id,
                'cliente_id' => $validatedData['cliente_id'],
                'vendedor_id' => auth()->id(), // Pega o ID do usuário logado como vendedor
                'data_emissao' => $validatedData['data_emissao'],
                'data_validade' => $validatedData['data_validade'] ?? null,
                'valor_total' => $valorTotal,
                'status' => 'Pendente',
                'observacoes' => $validatedData['observacoes'] ?? null,
            ]);
    
            // 4. Cria os Itens do Orçamento
            foreach ($validatedData['items'] as $itemData) {
                $orcamento->items()->create([
                    'produto_id' => $itemData['produto_id'],
                    'descricao_produto' => $itemData['produto_nome'],
                    'quantidade' => $itemData['quantidade'],
                    'valor_unitario' => $itemData['valor_unitario'],
                    'subtotal' => $itemData['quantidade'] * $itemData['valor_unitario'], // Usa a coluna 'subtotal'
                ]);
            }
    
            DB::commit();
    
            return redirect()->route('orcamentos.index')->with('success', 'Orçamento salvo com sucesso!');
    
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro ao salvar o orçamento: ' . $e->getMessage())->withInput();
        }
    }

    public function converterEmVenda(Orcamento $orcamento)
    {
        if ($orcamento->status !== 'Pendente') {
            return redirect()->back()->with('error', 'Apenas orçamentos pendentes podem ser convertidos.');
        }
        // ... (outras validações que você já tem) ...
    
        DB::beginTransaction();
        try {
            // 1. Cria a Venda com status 'pendente', SEM baixar o estoque
            $venda = Venda::create([
                'empresa_id' => $orcamento->empresa_id,
                'cliente_id' => $orcamento->cliente_id,
                'user_id' => auth()->id(),
                'orcamento_id' => $orcamento->id,
                'subtotal' => $orcamento->valor_total,
                'desconto' => 0,
                'total' => $orcamento->valor_total,
                'observacoes' => $orcamento->observacoes,
                'status' => 'pendente', // Status inicial como pendente/rascunho
            ]);
    
            // 2. Apenas copia os itens, sem tocar no estoque
            foreach ($orcamento->itens as $itemOrcamento) {
                $venda->items()->create([
                    'produto_id' => $itemOrcamento->produto_id,
                    'descricao_produto' => $itemOrcamento->descricao_produto,
                    'quantidade' => $itemOrcamento->quantidade,
                    'preco_unitario' => $itemOrcamento->valor_unitario,
                    'subtotal_item' => $itemOrcamento->subtotal,
                ]);
            }
    
            // 3. Atualiza o status do Orçamento
            $orcamento->update(['status' => 'Aprovado']);
    
            DB::commit();
    
            // 4. Redireciona para a TELA DE EDIÇÃO do novo pedido
            return redirect()->route('pedidos.edit', $venda)->with('success', 'Orçamento importado. Finalize o pedido abaixo.');
    
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erro ao importar orçamento: '. $e->getMessage());
        }
    }

    public function show(Orcamento $orcamento)
    {
        $this->authorize('view', $orcamento); // Exemplo de autorização
        $orcamento->load('itens.produto');
        return view('orcamentos.show', compact('orcamento'));
    }

    public function update(Request $request, Orcamento $orcamento)
    {
        $this->authorize('update', $orcamento);

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
            // Recalcula o valor total
            $valorTotal = 0;
            foreach($validated['items'] as $item) {
                $valorTotal += $item['quantidade'] * $item['valor_unitario'];
            }

            // Atualiza o cabeçalho do orçamento
            $orcamento->update([
                'cliente_id' => $validated['cliente_id'],
                'data_emissao' => $validated['data_emissao'],
                'data_validade' => $validated['data_validade'],
                'observacoes' => $validated['observacoes'],
                'valor_total' => $valorTotal,
                // Não altera o status aqui, isso seria outra ação (ex: aprovar)
            ]);

            // Remove os itens antigos
            $orcamento->itens()->delete();

            // Adiciona os novos itens (ou os itens atualizados)
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
            return redirect()->route('orcamentos.index')->with('success', 'Orçamento atualizado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao atualizar orçamento: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(Orcamento $orcamento)
    {
        $this->authorize('delete', $orcamento);

        DB::beginTransaction();
        try {
            // Exclui os itens primeiro para manter a integridade referencial
            $orcamento->itens()->delete();
            // Exclui o orçamento
            $orcamento->delete();
            
            DB::commit();
            return redirect()->route('orcamentos.index')->with('success', 'Orçamento excluído com sucesso!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao excluir orçamento: ' . $e->getMessage());
        }
    }

    // Os métodos edit(), update() e destroy() seguiriam o mesmo padrão...
}