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
// ADICIONE OS NOVOS CONTROLLERS
use App\Http\Controllers\PerfilFiscalController;
use App\Http\Controllers\ConfiguracaoController;
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
    
    // Compras (Original)
    Route::post('/compras/importar-xml', [CompraWebController::class, 'importarXml'])->name('compras.importarXml'); 
    Route::get('/compras/importacao/revisar', [CompraWebController::class, 'revisarImportacao'])->name('compras.revisarImportacao');
    Route::post('/compras/importacao/salvar', [CompraWebController::class, 'salvarImportacao'])->name('compras.salvarImportacao');
    Route::resource('compras', CompraWebController::class); 

    // Cadastros (Original)
    Route::resource('categorias', CategoriaController::class);
    Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('produtos.search');
    Route::resource('produtos', ProdutoController::class);
    Route::resource('fornecedores', FornecedorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('transportadoras', TransportadoraController::class);
    Route::resource('formas-pagamento', FormaPagamentoController::class);

    // Admin (Original)
    Route::middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis', RoleController::class)->except(['show']);
        Route::resource('usuarios', UserController::class);
        Route::resource('empresa', EmpresaController::class)->except(['show']);
    });

    // Vendas e Orçamentos (Original)
    Route::resource('orcamentos', OrcamentoController::class);
    Route::post('/orcamentos/{orcamento}/converter-venda', [OrcamentoController::class, 'converterEmVenda'])->name('orcamentos.converterVenda');
    Route::resource('cotacoes', CotacaoController::class);
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/novo', Pdv::class)->name('pedidos.create');
    Route::get('/pedidos/importar-orcamento', [PedidoController::class, 'importarOrcamento'])->name('pedidos.importarOrcamento');
    Route::get('/pedidos/{venda}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');
    Route::post('/pedidos/{venda}/emitir-nfe', [NFeController::class, 'emitir'])->name('pedidos.emitirNFe');

    // NFe (Original)
    Route::prefix('nfe')->name('nfe.')->group(function () {
        Route::get('/', [NFeController::class, 'index'])->name('index');
        Route::get('/importar-pedidos', [NFeController::class, 'importarPedidosView'])->name('importarPedidos');
        Route::get('/{nfe}/danfe', [NFeController::class, 'downloadDanfe'])->name('danfe');
        Route::get('/{nfe}/xml', [NFeController::class, 'downloadXml'])->name('xml');
        Route::post('/{nfe}/cancelar', [NFeController::class, 'cancelar'])->name('cancelar');
        Route::post('/preparar-agrupada', [NFeController::class, 'prepararEmissaoAgrupada'])->name('prepararAgrupada');
        Route::post('/store', [NFeController::class, 'store'])->name('store');
        Route::post('/nfe/{nfe}/cancelar', [NFeController::class, 'cancelar'])->name('nfe.cancelar');
        Route::post('/{nfe}/cce', [NFeController::class, 'enviarCCe'])->name('nfe.cce.enviar');
        Route::get('/cce/{cce}/pdf', [NFeController::class, 'downloadDacce'])->name('cce.pdf');
    });

    // Utilitários (Original)
    Route::get('/consulta/cnpj/{cnpj}', [UtilController::class, 'consultarCnpj'])->name('consulta.cnpj');
    
    // ===== INÍCIO DAS NOVAS ROTAS (ADICIONADAS DE FORMA SEGURA) =====
    // Grupo para as novas funcionalidades de configuração fiscal
    Route::prefix('admin')->name('admin.')->middleware(['can:acessar-admin'])->group(function () {
        // CRUD para gerenciar os Perfis Fiscais
        Route::resource('perfis-fiscais', PerfilFiscalController::class);

        // Tela principal para selecionar o perfil ativo e outras configurações
        Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::post('configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
    });
    // ===== FIM DAS NOVAS ROTAS =====

    Route::get('/teste-nfe', function () {
        try {
            // Tenta criar um objeto da classe que está dando erro
            $config = new \NFePHP\Sped\Common\Config('{}');
            
            // Se conseguir, a instalação está CORRETA!
            dd('SUCESSO! A classe Config foi encontrada e carregada.', $config);
    
        } catch (\Throwable $e) {
            // Se falhar, mostrará o erro novamente
            dd('FALHA. A classe ainda não foi encontrada.', $e->getMessage());
        }
    });
});

require __DIR__.'/auth.php';