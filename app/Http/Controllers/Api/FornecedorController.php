<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFornecedorRequest;
use App\Http\Requests\UpdateFornecedorRequest;
use App\Models\Fornecedor;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;

class FornecedorController extends Controller
{
    /**
     * Lista todos os fornecedores.
     */
    public function index()
    {
        // Retorna uma lista paginada de fornecedores
        return response()->json(Fornecedor::paginate(15));
    }

    /**
     * Armazena um novo fornecedor.
     */
    public function store(StoreFornecedorRequest $request)
    {
        // A validação é feita automaticamente pela classe StoreFornecedorRequest
        $fornecedor = Fornecedor::create($request->validated());

        return response()->json($fornecedor, 201); // 201 = Created
    }

    /**
     * Exibe um fornecedor específico.
     */
    public function show(Fornecedor $fornecedor)
    {
        // Graças ao "Route Model Binding", o Laravel já busca o fornecedor
        // e retorna um erro 404 se não encontrar.
        return response()->json($fornecedor);
    }

    /**
     * Atualiza um fornecedor existente.
     */
    public function update(UpdateFornecedorRequest $request, Fornecedor $fornecedor)
    {
        // A validação é feita pela UpdateFornecedorRequest
        $fornecedor->update($request->validated());

        return response()->json($fornecedor->fresh());
    }

    /**
     * Remove um fornecedor.
     */
    public function destroy(Fornecedor $fornecedor)
    {
        try {
            $fornecedor->delete();
    
            return response()->json(null, 204); // 204 = No Content
    
        } catch (QueryException $e) {
            // Verifica se o erro é uma violação de chave estrangeira (código 23000)
            if ($e->getCode() === '23000') {
                return response()->json([
                    'message' => 'Não é possível deletar o fornecedor pois ele está associado a produtos ou contas.'
                ], 409); // 409 = Conflict
            }
    
            // Para outros erros de banco de dados
            return response()->json(['message' => 'Ocorreu um erro no servidor.'], 500);
        }
    }
}