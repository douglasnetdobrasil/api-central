<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraWebController;
use App\Http\Controllers\CategoriaController; 

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('compras', CompraWebController::class); 
    Route::post('/compras/importar-xml', [CompraWebController::class, 'importarXml'])->name('compras.importarXml'); 
   

// NOVA ROTA: para exibir a tela de conferência
Route::get('/compras/importacao/revisar', [CompraWebController::class, 'revisarImportacao'])->name('compras.revisarImportacao');

// NOVA ROTA: para o salvamento final
Route::post('/compras/importacao/salvar', [CompraWebController::class, 'salvarImportacao'])->name('compras.salvarImportacao');

//NOVA ROTA: PARA DELETAR
Route::delete('/compras/{compra}', [CompraWebController::class, 'destroy'])->name('compras.destroy');
  

// Rota para o CRUD de Categorias
    Route::resource('categorias', CategoriaController::class);

    Route::resource('produtos', App\Http\Controllers\ProdutoController::class);

    Route::resource('fornecedores', App\Http\Controllers\FornecedorController::class);

    Route::resource('clientes', App\Http\Controllers\ClienteController::class);
});

require __DIR__.'/auth.php';
