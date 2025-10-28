<?php

namespace App\Http\Controllers;

use App\Models\ClienteEquipamento;
use App\Models\Cliente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class ClienteEquipamentoController extends Controller
{
    public function index()
    {
        $equipamentos = ClienteEquipamento::with('cliente')->latest()->paginate(15);
        return view('cliente_equipamentos.index', compact('equipamentos'));
    }

    public function create()
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('cliente_equipamentos.create', compact('clientes'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string|max:255',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            'numero_serie' => 'nullable|string|max:100|unique:cliente_equipamentos,numero_serie',
            'observacoes' => 'nullable|string',
        ]);
        
        $validatedData['empresa_id'] = auth()->user()->empresa_id;

        ClienteEquipamento::create($validatedData);

        return redirect()->route('cliente-equipamentos.index')
                         ->with('success', 'Equipamento cadastrado com sucesso!');
    }

    public function show(ClienteEquipamento $clienteEquipamento)
    {
        // Geralmente não há necessidade de uma página "show" para equipamentos,
        // mas a rota existe. Podemos redirecionar para a edição.
        return redirect()->route('cliente-equipamentos.edit', $clienteEquipamento);
    }

    public function edit(ClienteEquipamento $clienteEquipamento)
    {
        $clientes = Cliente::orderBy('nome')->get();
        return view('cliente_equipamentos.edit', compact('clienteEquipamento', 'clientes'));
    }

    public function storeModal(Request $request)
    {
        // 1. Determina a origem e o Cliente ID
        $isAdmin = $request->routeIs('admin.*'); // Verifica se a rota atual pertence ao grupo 'admin.'
        
        // Se for Admin, o cliente_id vem no Request. Se for Portal, pegamos do usuário logado.
        $clienteId = $isAdmin ? $request->cliente_id : Auth::user()->cliente_id;
        
       // 2. Validação Específica para o Modal (SIMPLIFICADA PARA DEBUG)
       $validated = $request->validate([
        'descricao' => 'required|string|max:255',
        'numero_serie' => [
            'nullable', 
            'string', 
            'max:100',
            // Removida a regra Rule::unique temporariamente para isolar o erro
        ],
        'marca' => 'nullable|string|max:50',
        'modelo' => 'nullable|string|max:50',
        // Esta regra é crucial para o Admin
        'cliente_id' => Rule::requiredIf($isAdmin) . '|exists:clientes,id', 
    ]);

        // 3. Criação do Equipamento
        $equipamento = ClienteEquipamento::create([
            'cliente_id' => $clienteId, 
            'empresa_id' => Auth::user()->empresa_id, // Pega a empresa do usuário logado (Admin ou Cliente)
            'descricao' => $validated['descricao'],
            'numero_serie' => $validated['numero_serie'] ?? null,
            'marca' => $validated['marca'] ?? null,
            'modelo' => $validated['modelo'] ?? null,
        ]);
        
        // 4. Retorno JSON (para o AJAX)
        $descricaoCompleta = $equipamento->descricao . ($equipamento->numero_serie ? ' (SN: ' . $equipamento->numero_serie . ')' : '');

        return response()->json([
            'id' => $equipamento->id,
            'texto' => $descricaoCompleta, // Retorna o formato que o <select> espera
        ]);
    }


    public function update(Request $request, ClienteEquipamento $clienteEquipamento)
    {
        $validatedData = $request->validate([
            'cliente_id' => 'required|exists:clientes,id',
            'descricao' => 'required|string|max:255',
            'marca' => 'nullable|string|max:100',
            'modelo' => 'nullable|string|max:100',
            // Validação de "unique" ignorando o registro atual
            'numero_serie' => 'nullable|string|max:100|unique:cliente_equipamentos,numero_serie,' . $clienteEquipamento->id,
            'observacoes' => 'nullable|string',
        ]);

        $clienteEquipamento->update($validatedData);

        return redirect()->route('cliente-equipamentos.index')
                         ->with('success', 'Equipamento atualizado com sucesso!');
    }

    public function destroy(ClienteEquipamento $clienteEquipamento)
    {
        try {
            $clienteEquipamento->delete();
            return redirect()->route('cliente-equipamentos.index')
                             ->with('success', 'Equipamento excluído com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Não foi possível excluir o equipamento.');
        }
    }
}