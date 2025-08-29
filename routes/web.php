<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraWebController;
use App\Http\Controllers\CategoriaController; 
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\TransportadoraController;
use App\Http\Controllers\FormaPagamentoController;

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

    Route::middleware(['can:acessar-admin'])->group(function () {

        // Sua rota de perfis, agora dentro do grupo de segurança
        Route::resource('perfis', App\Http\Controllers\RoleController::class)->except(['show']);

        // A nova rota para o CRUD de usuários
        Route::resource('usuarios', App\Http\Controllers\UserController::class);

        // Se quisermos uma tela única para todas as permissões, como no plano original,
        // podemos adicionar uma rota customizada aqui, mas vamos focar no CRUD primeiro.
    });

    Route::get('/empresas', [EmpresaController::class, 'index'])->name('empresa.index'); // <-- ROTA ADICIONADA
    Route::get('/empresa/configuracoes', [EmpresaController::class, 'edit'])->name('empresa.edit');
    Route::patch('/empresa/configuracoes', [EmpresaController::class, 'update'])->name('empresa.update');

    Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('produtos.search');

    Route::resource('transportadoras', TransportadoraController::class);

    Route::resource('formas-pagamento', FormaPagamentoController::class);
});

require __DIR__.'/auth.php';
