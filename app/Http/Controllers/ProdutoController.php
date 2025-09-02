<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Produto;
use App\Models\Categoria;
use App\Models\DadoFiscalProduto; // <-- FALTAVA ESTA LINHA
use Illuminate\Support\Facades\DB;   // <-- FALTAVA ESTA LINHA
use Exception; 
use Illuminate\Support\Facades\Auth;                     // <-- FALTAVA ESTA LINHA

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
    
        // 1. Cria um "molde" de um novo produto em memória
        $produto = new Produto();
    
        // 2. Cria um "molde" dos dados fiscais e define o valor padrão para a origem
        $dadoFiscalPadrao = new DadoFiscalProduto(['origem' => '0']);
    
        // 3. Associa os dados fiscais padrão ao molde do produto
        $produto->setRelation('dadosFiscais', $dadoFiscalPadrao);
    
        // 4. Envia as variáveis para a view, incluindo o produto com o valor padrão
        return view('produtos.form', compact('produto', 'categorias'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // --- LÓGICA PARA DEFINIR 'ORIGEM' PADRÃO ---
        $fiscalData = $request->input('fiscal', []);
        if (empty($fiscalData['origem'])) {
            $fiscalData['origem'] = '0'; // Define '0' se estiver vazio
        }
        $request->merge(['fiscal' => $fiscalData]);
        // --- FIM DA LÓGICA ---
    
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
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
        ]);
       
        DB::beginTransaction();
        try {
            $produto = Produto::create($validatedData);
    
            if ($request->has('fiscal')) {
                $produto->dadosFiscais()->create($request->input('fiscal'));
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
        // Geralmente não usado em CRUDs como este, mas pode ser implementado se necessário
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Produto $produto)
    {
        // 1. Busca todas as categorias do banco de dados
        $categorias = Categoria::orderBy('nome')->get();
    
        // 2. Envia tanto o $produto a ser editado quanto a lista de $categorias para a view
        return view('produtos.form', compact('produto', 'categorias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Produto $produto)
    {
        // --- LÓGICA PARA DEFINIR 'ORIGEM' PADRÃO ---
        $fiscalData = $request->input('fiscal', []);
        if (empty($fiscalData['origem'])) {
            $fiscalData['origem'] = '0'; // Define '0' se estiver vazio
        }
        $request->merge(['fiscal' => $fiscalData]);
        // --- FIM DA LÓGICA ---
    
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'categoria_id' => 'required|exists:categorias,id',
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
        ]);
    
        DB::beginTransaction();
        try {
            $produto->update($validatedData);
    
            if ($request->has('fiscal')) {
                $produto->dadosFiscais()->updateOrCreate(
                    ['produto_id' => $produto->id],
                    $request->input('fiscal')
                );
            }
    
            DB::commit();
            return redirect()->route('produtos.index')->with('success', 'Produto atualizado com sucesso!');
            
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Ocorreu um erro: ' . $e->getMessage())->withInput();
        }
    }
  
    /*
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
    */
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