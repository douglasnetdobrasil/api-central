<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompraWebController;
use App\Http\Controllers\CategoriaController; 
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\TransportadoraController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\OrcamentoController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\CotacaoController;
use App\Http\Controllers\PedidoController;
use App\Livewire\Pdv;
// use App\Livewire\DB; // <-- CORRIGIDO: Removido pois é desnecessário e incorreto aqui

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
    
    Route::post('/compras/importar-xml', [CompraWebController::class, 'importarXml'])->name('compras.importarXml'); 
    Route::get('/compras/importacao/revisar', [CompraWebController::class, 'revisarImportacao'])->name('compras.revisarImportacao');
    Route::post('/compras/importacao/salvar', [CompraWebController::class, 'salvarImportacao'])->name('compras.salvarImportacao');
    Route::resource('compras', CompraWebController::class); 

    Route::resource('categorias', CategoriaController::class);

    Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('produtos.search');
    Route::resource('produtos', App\Http\Controllers\ProdutoController::class);

    Route::resource('fornecedores', App\Http\Controllers\FornecedorController::class);
    Route::resource('clientes', App\Http\Controllers\ClienteController::class);

    Route::middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis', App\Http\Controllers\RoleController::class)->except(['show']);
        Route::resource('usuarios', App\Http\Controllers\UserController::class);
    });

    Route::resource('empresas', EmpresaController::class)->except(['show'])->names('empresa');
    Route::get('/empresas/{empresa}/edit', [EmpresaController::class, 'editAdmin'])->name('empresa.editAdmin');
    Route::put('/empresas/{empresa}', [EmpresaController::class, 'updateAdmin'])->name('empresa.updateAdmin');

    Route::get('/configuracoes/empresa', [EmpresaController::class, 'edit'])->name('configuracoes.empresa.edit');
    Route::patch('/configuracoes/empresa', [EmpresaController::class, 'update'])->name('configuracoes.empresa.update');

    Route::resource('transportadoras', TransportadoraController::class);

    Route::resource('formas-pagamento', FormaPagamentoController::class);

    Route::resource('orcamentos', OrcamentoController::class);

    Route::resource('cotacoes', CotacaoController::class);
    // Route::get('/cotacoes/{cotacao}', ...); // <-- CORRIGIDO: Removido por ser duplicado

    // ADICIONE as novas rotas de Pedidos:
    // <-- CORRIGIDO: Removido o ->middleware('auth') pois já estão dentro do grupo
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/novo', Pdv::class)->name('pedidos.create');
});

require __DIR__.'/auth.php';

// } // <-- CORRIGIDO: Removido o '}' extra