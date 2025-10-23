<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Factories\HasFactory; // (Estes 'use' não são necessários, mas não causam mal)
use Illuminate\Database\Eloquent\Model; // (Estes 'use' não são necessários)
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash; // <-- IMPORTANTE: Importa o 'Hash'

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
        $cliente = new Cliente();
        return view('clientes.form', compact('cliente'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // ==========================================================
        // ||||||||||||||||||| ATUALIZAÇÃO DE VALIDAÇÃO |||||||||||||||||||
        // ==========================================================
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj',
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clientes,email', // <-- REGRA DE 'UNIQUE' ADICIONADA
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'ie' => 'nullable|string|max:20',
            'codigo_municipio' => 'nullable|string|max:7',
            'password' => 'nullable|string|min:6|confirmed', // <-- REGRAS DE SENHA ADICIONADAS
        ]);
    
        $validatedData['empresa_id'] = Auth::user()->empresa_id;
    
        // ==========================================================
        // ||||||||||||||||| LÓGICA DE HASH ADICIONADA ||||||||||||||||||
        // ==========================================================
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        } else {
            // Garante que não salve uma senha nula se o campo estiver vazio
            unset($validatedData['password']); 
        }

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
        return view('clientes.form', compact('cliente'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Cliente $cliente)
    {
        // ==========================================================
        // ||||||||||||||||||| ATUALIZAÇÃO DE VALIDAÇÃO |||||||||||||||||||
        // ==========================================================
        $validatedData = $request->validate([
            'nome' => 'required|string|max:255',
            'cpf_cnpj' => 'nullable|string|max:20|unique:clientes,cpf_cnpj,' . $cliente->id, // Ignora o próprio ID
            'telefone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255|unique:clientes,email,' . $cliente->id, // Ignora o próprio ID
            'cep' => 'nullable|string|max:10',
            'logradouro' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:20',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'ie' => 'nullable|string|max:20',
            'codigo_municipio' => 'nullable|string|max:7',
            'password' => 'nullable|string|min:6|confirmed', // <-- REGRAS DE SENHA ADICIONADAS
        ]);
    
        // ==========================================================
        // ||||||||||||||||| LÓGICA DE HASH ADICIONADA ||||||||||||||||||
        // ==========================================================
        if ($request->filled('password')) {
            $validatedData['password'] = Hash::make($request->password);
        } else {
            // Remove a chave 'password' para não sobrescrever a senha existente com 'null'
            unset($validatedData['password']);
        }

        // A linha $validatedData['empresa_id'] = Auth::user()->empresa_id; foi removida daqui
        // pois não é seguro ou necessário alterar o 'empresa_id' de um cliente existente.
        
        $cliente->update($validatedData);
    
        return redirect()->route('clientes.index')->with('success', 'Cliente atualizado com sucesso!');
    }
    
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();
        return redirect()->route('clientes.index')->with('success', 'Cliente removido com sucesso!');
    }
}