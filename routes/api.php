<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProdutoController;
use App\Http\Controllers\Api\ProdutoMercadoController; // GARANTA QUE ESTA LINHA EXISTA
use App\Http\Controllers\Api\FornecedorController;
use App\Http\Controllers\Api\ClienteController;
use App\Http\Controllers\Api\PedidoController;
use App\Http\Controllers\Api\CompraController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// --- NOSSAS ROTAS DO ERP ---

// Rotas para o nicho de Mercado
Route::get('/produtos/mercado', [ProdutoMercadoController::class, 'index']);
Route::get('/produtos/mercado/{produto}', [ProdutoMercadoController::class, 'show']);
Route::post('/produtos/mercado', [ProdutoMercadoController::class, 'store']);
Route::put('/produtos/mercado/{produto}', [ProdutoMercadoController::class, 'update']); // <-- ROTA ADICIONADA
Route::delete('/produtos/mercado/{produto}', [ProdutoMercadoController::class, 'destroy']); // <-- ROTA ADICIONADA

// Rota genérica de produtos
Route::apiResource('produtos', ProdutoController::class);

// Rota para o CRUD de Fornecedores
Route::apiResource('fornecedores', FornecedorController::class)
     ->parameters(['fornecedores' => 'fornecedor']);


     // Rota para o CRUD de Clientes
Route::apiResource('clientes', ClienteController::class)
->parameters(['clientes' => 'cliente']);


// Rota para registrar um novo Pedido de Venda
Route::post('/pedidos', [PedidoController::class, 'store']);


// Rota para registrar uma nova Pré-Nota de Compra
Route::post('/compras', [CompraController::class, 'store']);