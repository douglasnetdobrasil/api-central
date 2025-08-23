<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    /**
     * Lista todos os clientes com paginação.
     */
    public function index()
    {
        return response()->json(Cliente::paginate(15));
    }

    /**
     * Armazena um novo cliente no banco de dados.
     */
    public function store(StoreClienteRequest $request)
    {
        // A validação é feita automaticamente pela classe StoreClienteRequest
        $cliente = Cliente::create($request->validated());

        return response()->json($cliente, 201); // 201 = Created
    }

    /**
     * Exibe um cliente específico.
     */
    public function show(Cliente $cliente)
    {
        // O Laravel busca o cliente pelo ID da URL automaticamente
        return response()->json($cliente);
    }

    /**
     * Atualiza um cliente existente.
     */
    public function update(UpdateClienteRequest $request, Cliente $cliente)
    {
        // A validação é feita automaticamente pela classe UpdateClienteRequest
        $cliente->update($request->validated());

        return response()->json($cliente->fresh());
    }

    /**
     * Remove um cliente do banco de dados.
     */
    public function destroy(Cliente $cliente)
    {
        $cliente->delete();

        return response()->json(null, 204); // 204 = No Content
    }
}