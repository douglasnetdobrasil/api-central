<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\DadoFiscalProduto;
use App\Models\EstoqueMovimento;
use Illuminate\Support\Facades\DB;
use Exception;
use Illuminate\Support\Facades\Auth;

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Produto::with('categoria')->orderBy('nome', 'asc');
    
        $searchField = $request->input('search_field');
        $searchValue = $request->input('search_value');
    
        if ($searchField && $searchValue) {
            switch ($searchField) {
                case 'id':
                    $query->where('id', $searchValue);
                    break;
                case 'nome':
                    $query->where('nome', 'like', '%' . $searchValue . '%');
                    break;
                case 'codigo_barras':
                    $query->where('codigo_barras', $searchValue);
                    break;
            }
        }
    
        $produtos = $query->paginate(200)->withQueryString(); 
        
        return view('produtos.index', compact('produtos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categorias = Categoria::orderBy('nome')->get();
        $produto = new Produto();
        $dadoFiscalPadrao = new DadoFiscalProduto(['origem' => '0']);
        $produto->setRelation('dadosFiscais', $dadoFiscalPadrao);
        return view('produtos.form', compact('produto', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $fiscalData = $request->input('fiscal', []);
        if (empty($fiscalData['origem'])) {
            $fiscalData['origem'] = '0';
        }
        $request->merge(['fiscal' => $fiscalData]);
    
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo' => 'required|in:venda,materia_prima,produto_acabado,servico', // <-- ADICIONADO
            'preco_venda' => 'required|numeric',
            'preco_custo' => 'nullable|numeric',
            'estoque_atual' => 'nullable|numeric',
            'codigo_barras' => 'nullable|string|max:30|unique:produtos,codigo_barras',
            'fiscal.origem' => 'required|string|digits:1',
            'fiscal.ncm' => 'nullable|string|max:8',
            'fiscal.cest' => 'nullable|string|max:7',
            'fiscal.cfop' => 'nullable|string|max:4',
            'fiscal.icms_cst' => 'nullable|string|max:3',
            'fiscal.pis_cst' => 'nullable|string|max:2',
            'fiscal.cofins_cst' => 'nullable|string|max:2',
            'fiscal.csosn' => 'nullable|string|max:4',
            'unidade' => 'required|string|max:10',
        ]);
       
        DB::beginTransaction();
        try {
            $produtoData = array_merge($validatedData, ['empresa_id' => Auth::user()->empresa_id]);
            $produto = Produto::create($produtoData);
    
            if ($request->has('fiscal')) {
                $produto->dadosFiscais()->create($request->input('fiscal'));
            }

            $estoqueInicial = floatval($validatedData['estoque_atual'] ?? 0);

            if ($estoqueInicial > 0) {
                DB::table('estoque_movimentos')->insert([
                    'empresa_id' => Auth::user()->empresa_id,
                    'produto_id' => $produto->id,
                    'user_id' => Auth::id(),
                    'tipo_movimento' => 'entrada_inicial',
                    'quantidade' => $estoqueInicial,
                    'saldo_anterior' => 0,
                    'saldo_novo' => $estoqueInicial,
                    'origem_id' => $produto->id,
                    'origem_type' => Produto::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            DB::commit();
            return redirect()->route('produtos.index')->with('success', 'Produto cadastrado com sucesso!');
    
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro: ' . $e->getMessage())->withInput();
        }
    }
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        // ...
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        $categorias = Categoria::orderBy('nome')->get();
        return view('produtos.form', compact('produto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produto $produto)
    {
        $fiscalData = $request->input('fiscal', []);
        if (empty($fiscalData['origem'])) {
            $fiscalData['origem'] = '0';
        }
        $request->merge(['fiscal' => $fiscalData]);
    
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
            'tipo' => 'required|in:venda,materia_prima,produto_acabado,servico', // <-- ADICIONADO
            'preco_venda' => 'required|numeric',
            'preco_custo' => 'nullable|numeric',
            'estoque_atual' => 'nullable|numeric',
            'codigo_barras' => 'nullable|string|max:30|unique:produtos,codigo_barras,' . $produto->id,
            'fiscal.origem' => 'required|string|digits:1',
            'fiscal.ncm' => 'nullable|string|max:8',
            'fiscal.cest' => 'nullable|string|max:7',
            'fiscal.cfop' => 'nullable|string|max:4',
            'fiscal.icms_cst' => 'nullable|string|max:3',
            'fiscal.pis_cst' => 'nullable|string|max:2',
            'fiscal.cofins_cst' => 'nullable|string|max:2',
            'fiscal.csosn' => 'nullable|string|max:4',
            'unidade' => 'required|string|max:10',
        ]);
    
        DB::beginTransaction();
        try {
            $saldoAnterior = $produto->estoque_atual;
            $produto->update($validatedData);
    
            if ($request->has('fiscal')) {
                $produto->dadosFiscais()->updateOrCreate(
                    ['produto_id' => $produto->id],
                    $request->input('fiscal')
                );
            }

            $saldoNovo = $produto->fresh()->estoque_atual;
            $quantidadeMovimentada = $saldoNovo - $saldoAnterior;

            if ($quantidadeMovimentada != 0) {
                DB::table('estoque_movimentos')->insert([
                    'empresa_id' => Auth::user()->empresa_id,
                    'produto_id' => $produto->id,
                    'user_id' => Auth::id(),
                    'tipo_movimento' => $quantidadeMovimentada > 0 ? 'ajuste_manual_entrada' : 'ajuste_manual_saida',
                    'quantidade' => abs($quantidadeMovimentada),
                    'saldo_anterior' => $saldoAnterior,
                    'saldo_novo' => $saldoNovo,
                    'origem_id' => $produto->id,
                    'origem_type' => Produto::class,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
    
            DB::commit();
            return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
            
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro: ' . $e->getMessage())->withInput();
        }
    }
  
    public function search(Request $request)
    {
        $term = $request->query('term');

        if (strlen($term) < 2) {
            return response()->json([]);
        }

        $produtos = Produto::where('empresa_id', Auth::user()->empresa_id)
                        ->where('ativo', true)
                        ->where(function ($query) use ($term) {
                            $query->where('nome', 'LIKE', "%{$term}%")
                                    ->orWhere('codigo_barras', 'LIKE', "%{$term}%");
                        })
                        ->select('id', 'nome', 'preco_venda')
                        ->limit(15)
                        ->get();

        return response()->json($produtos);
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Produto $produto)
    {
        // Adicione aqui lógica para verificar se o produto pode ser apagado (ex: se não tem vendas)
        $produto->delete();
        return redirect()->route('produtos.index')->with('success', 'Produto removido com sucesso!');
    }
}