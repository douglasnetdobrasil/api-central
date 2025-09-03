<?php

namespace App\Http\Controllers;

use App\Models\Cotacao;
use App\Models\Produto;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CotacaoController extends Controller
{
    public function index(Request $request)
    {
        // Carrega as cotações com os relacionamentos para evitar múltiplas queries
        $query = Cotacao::withCount(['fornecedores', 'produtos'])->latest();
    
        // Adiciona a lógica de busca se necessário
        if ($request->filled('search')) {
            $query->where('descricao', 'like', '%' . $request->search . '%');
        }
    
        $cotacoes = $query->paginate(15)->withQueryString();
    
        return view('cotacoes.index', compact('cotacoes'));
    }

    public function create()
{
    // Futuramente, podemos adicionar uma Policy para 'create'
    // $this->authorize('create', Cotacao::class);

    // Busca todos os produtos e fornecedores ativos
    $produtos = Produto::where('ativo', true)->orderBy('nome')->get();
    $fornecedores = Fornecedor::where('ativo', true)->orderBy('razao_social')->get();

    return view('cotacoes.create', compact('produtos', 'fornecedores'));
}

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'descricao' => 'nullable|string|max:255',
            'data_cotacao' => 'required|date',
            'produtos' => 'required|array|min:1',
            'produtos.*.id' => 'required|exists:produtos,id',
            'produtos.*.quantidade' => 'required|numeric|min:0.01',
            'fornecedores' => 'required|array|min:1',
            'fornecedores.*' => 'required|exists:fornecedores,id',
        ]);

        DB::beginTransaction();
        try {
            $cotacao = Cotacao::create([
                'empresa_id' => auth()->user()->empresa_id,
                'user_id' => auth()->id(),
                'descricao' => $validatedData['descricao'],
                'data_cotacao' => $validatedData['data_cotacao'],
            ]);

            // Formata o array de produtos para o método attach
            $produtosParaSincronizar = [];
            foreach ($validatedData['produtos'] as $produto) {
                $produtosParaSincronizar[$produto['id']] = ['quantidade' => $produto['quantidade']];
            }

            $cotacao->produtos()->attach($produtosParaSincronizar);
            $cotacao->fornecedores()->attach($validatedData['fornecedores']);

            DB::commit();

            // return redirect()->route('cotacoes.index')->with('success', 'Cotação criada com sucesso!');
            return "Cotação salva com sucesso! (Redirecionamento a ser criado)";

        } catch (\Exception $e) {
            DB::rollBack();
            // return back()->with('error', 'Erro ao criar cotação: ' . $e->getMessage())->withInput();
            return "Erro ao salvar: " . $e->getMessage();
        }
    }

    public function show(Cotacao $cotacao)
    {
        // Carrega os relacionamentos necessários de forma otimizada (Eager Loading)
        $cotacao->load(['produtos', 'fornecedores', 'respostas']);

        // Para facilitar a exibição na view, vamos criar uma matriz/grid com as respostas
        // que pode ser acessada por [produto_id][fornecedor_id]
        $respostasGrid = [];
        foreach ($cotacao->respostas as $resposta) {
            $respostasGrid[$resposta->produto_id][$resposta->fornecedor_id] = $resposta;
        }

        return view('cotacoes.show', compact('cotacao', 'respostasGrid'));
    }

    // Os outros métodos (show, edit, update, destroy) serão implementados depois.
}