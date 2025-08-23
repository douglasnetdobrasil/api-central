<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProdutoRequest;
use App\Http\Requests\UpdateProdutoRequest;
use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function index()
    {
        $produtos = Produto::with('categoria', 'unidadeMedida', 'fornecedor')->paginate(15);
        return response()->json($produtos);
    }

    public function store(StoreProdutoRequest $request)
    {
        $produto = Produto::create($request->validated());
        return response()->json($produto, 201);
    }

    public function show(Produto $produto)
    {
        return response()->json($produto->load('categoria', 'unidadeMedida', 'fornecedor'));
    }

    public function update(UpdateProdutoRequest $request, Produto $produto)
    {
        $produto->update($request->validated());
        return response()->json($produto->fresh()->load('categoria', 'unidadeMedida', 'fornecedor'));
    }

    public function destroy(Produto $produto)
    {
        $produto->delete();
        return response()->json(null, 204);
    }
}