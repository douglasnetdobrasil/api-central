<?php

namespace App\Http\Controllers;

use App\Models\Cliente; // <-- A CORREÇÃO ESTÁ AQUI
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ClienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Cliente::orderBy('nome', 'asc');

        $searchField = $request->input('search_field');
        $searchValue = $request->input('search_value');

        if ($searchField && $searchValue) {
            switch ($searchField) {
                case 'nome':
                    $query->where('nome', 'like', '%' . $searchValue . '%');
                    break;
                case 'cpf_cnpj':
                    $query->where('cpf_cnpj', 'like', '%' . $searchValue . '%');
                    break;
                case 'id':
                    $query->where('id', $searchValue);
                    break;
            }
        }

        $clientes = $query->paginate(20)->withQueryString();
        
        return view('clientes.index', compact('clientes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Precisamos passar um objeto vazio para o formulário não dar erro de variável indefinida
        $cliente = new Cliente();
        return view('clientes.form', compact('cliente')); // ou 'admin.clientes.form' dependendo da sua estrutura
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'ie' => 'nullable|string|max:20',
    'codigo_municipio' => 'nullable|string|max:7',
        ]);
    
        // ===== A LINHA DA CORREÇÃO ESTÁ AQUI =====
        $validatedData['empresa_id'] = Auth::user()->empresa_id;
        // ==========================================
    
        Cliente::create($validatedData);
    
        return redirect()->route('clientes.index')->with('success', 'Cliente cadastrado com sucesso!');
    }
    

    /**
     * Display the specified resource.
     */
    public function show(Cliente $cliente)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Cliente $cliente)
    {
        // Envia o cliente carregado do banco de dados para o formulário
        return view('clientes.form', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj,' . $cliente->id,
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'ie' => 'nullable|string|max:20',
    'codigo_municipio' => 'nullable|string|max:7',
        ]);
    
        $cliente->update($validatedData);
        $validatedData['empresa_id'] = Auth::user()->empresa_id;
    
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        // Adicione aqui uma verificação se o cliente tem vendas associadas antes de apagar
        // Exemplo: if ($cliente->vendas()->count() > 0) { ... }
        
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido com sucesso!');
    }
}