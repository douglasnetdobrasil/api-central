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
use App\Http\Controllers\NFeController;
use App\Http\Controllers\UtilController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Livewire\Pdv;

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
    
    // Compras
    Route::post('/compras/importar-xml', [CompraWebController::class, 'importarXml'])->name('compras.importarXml'); 
    Route::get('/compras/importacao/revisar', [CompraWebController::class, 'revisarImportacao'])->name('compras.revisarImportacao');
    Route::post('/compras/importacao/salvar', [CompraWebController::class, 'salvarImportacao'])->name('compras.salvarImportacao');
    Route::resource('compras', CompraWebController::class); 

    // Cadastros
    Route::resource('categorias', CategoriaController::class);
    Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('produtos.search');
    Route::resource('produtos', ProdutoController::class);
    Route::resource('fornecedores', FornecedorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('transportadoras', TransportadoraController::class);
    Route::resource('formas-pagamento', FormaPagamentoController::class);

    // Admin (Usuários, Perfis, Empresas)
    Route::middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis', RoleController::class)->except(['show']);
        Route::resource('usuarios', UserController::class);
        // Rota unificada para Empresas
        Route::resource('empresa', EmpresaController::class)->except(['show']);
    });

    // Vendas e Orçamentos
    Route::resource('orcamentos', OrcamentoController::class);
    Route::post('/orcamentos/{orcamento}/converter-venda', [OrcamentoController::class, 'converterEmVenda'])->name('orcamentos.converterVenda');
    Route::resource('cotacoes', CotacaoController::class);
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/novo', Pdv::class)->name('pedidos.create');
    Route::get('/pedidos/importar-orcamento', [PedidoController::class, 'importarOrcamento'])->name('pedidos.importarOrcamento');
    Route::get('/pedidos/{venda}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');

    // NFe
    Route::get('/nfe', [NFeController::class, 'index'])->name('nfe.index');
    Route::post('/pedidos/{venda}/emitir-nfe', [NFeController::class, 'emitir'])->name('pedidos.emitirNFe');
    
    // Utilitários
    Route::get('/consulta/cnpj/{cnpj}', [UtilController::class, 'consultarCnpj'])->name('consulta.cnpj');
});

require __DIR__.'/auth.php';