<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fornecedor;

class FornecedorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Fornecedor::orderBy('razao_social', 'asc');
    
        // Pega os valores do novo formulário de pesquisa
        $searchField = $request->input('search_field');
        $searchValue = $request->input('search_value');
    
        // Aplica o filtro se ambos os campos forem preenchidos
        if ($searchField && $searchValue) {
            switch ($searchField) {
                case 'razao_social':
                    $query->where('razao_social', 'like', '%' . $searchValue . '%');
                    break;
                case 'cpf_cnpj':
                    $query->where('cpf_cnpj', 'like', '%' . $searchValue . '%');
                    break;
                // Futuramente, pode adicionar 'nome_fantasia', 'id', etc.
            }
        }
    
        $fornecedores = $query->paginate(20)->withQueryString();
        
        return view('fornecedores.index', compact('fornecedores'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $fornecedor = new Fornecedor();
        return view('fornecedores.form', compact('fornecedor'));
    }
    
    public function edit(Fornecedor $fornecedor)
    {
        return view('fornecedores.form', compact('fornecedor'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validação dos dados
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            // Garante que o CNPJ seja único na tabela
            'cpf_cnpj' => 'required|string|max:20|unique:fornecedores,cpf_cnpj',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);
       
        Fornecedor::create($validatedData);
    
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor cadastrado com sucesso!');
    }
    
    public function update(Request $request, Fornecedor $fornecedor)
    {
        $validatedData = $request->validate([
            'razao_social' => 'required|string|max:255',
            'nome_fantasia' => 'nullable|string|max:255',
            // Garante que o CNPJ seja único, ignorando o próprio fornecedor
            'cpf_cnpj' => 'required|string|max:20|unique:fornecedores,cpf_cnpj,' . $fornecedor->id,
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
        ]);
    
        $fornecedor->update($validatedData);
    
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor atualizado com sucesso!');
    }
    
    public function destroy(Fornecedor $fornecedor)
    {
        // Adicionar verificação se o fornecedor tem compras associadas antes de apagar
        if ($fornecedor->compras()->count() > 0) {
            return back()->with('error', 'Este fornecedor não pode ser removido pois possui compras vinculadas.');
        }
        
        $fornecedor->delete();
        return redirect()->route('fornecedores.index')->with('success', 'Fornecedor removido com sucesso!');
    }

    /**
     * Remove the specified resource from storage.
     */
   
}
