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
use App\Http\Controllers\EstoqueController;
use App\Http\Controllers\PdvCaixaController;
// ADICIONE OS NOVOS CONTROLLERS
use App\Http\Controllers\PerfilFiscalController;
use App\Http\Controllers\ConfiguracaoController;
use App\Livewire\Pdv;
use App\Livewire\NfeAvulsaCreate;
use App\Livewire\NfeRascunhos; // <-- IMPORTANTE: Importe o novo componente de rascunhos
use App\Models\Venda;          // <-- IMPORTANTE: Importe o Model de Venda
use App\Livewire\NfeAvulsaFormulario;
use App\Livewire\NfeAvulsaCriar;

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
    
    // Compras (Original - MANTIDO)
    Route::post('/compras/importar-xml', [CompraWebController::class, 'importarXml'])->name('compras.importarXml');
    Route::get('/compras/importacao/revisar', [CompraWebController::class, 'revisarImportacao'])->name('compras.revisarImportacao');
    Route::post('/compras/importacao/salvar', [CompraWebController::class, 'salvarImportacao'])->name('compras.salvarImportacao');
    Route::resource('compras', CompraWebController::class);

    // Cadastros (Original - MANTIDO)
    Route::resource('categorias', CategoriaController::class);
    Route::get('/produtos/search', [ProdutoController::class, 'search'])->name('produtos.search');
    Route::resource('produtos', ProdutoController::class);
    Route::resource('fornecedores', FornecedorController::class);
    Route::resource('clientes', ClienteController::class);
    Route::resource('transportadoras', TransportadoraController::class);
    Route::resource('formas-pagamento', FormaPagamentoController::class);

    Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index');
Route::get('/estoque/{produto}', [EstoqueController::class, 'show'])->name('estoque.show');

    // Admin (Original - MANTIDO)
    Route::middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis', RoleController::class)->except(['show']);
        Route::resource('usuarios', UserController::class);
        Route::resource('empresa', EmpresaController::class)->except(['show']);
       
    });

     // Rota para a busca de clientes via API para o Select2 (Original - MANTIDO)
     Route::get('/api/clientes/search', [App\Http\Controllers\ClienteController::class, 'search'])->name('api.clientes.search');

    // Vendas e Orçamentos (Original - MANTIDO)
    Route::resource('orcamentos', OrcamentoController::class);
    Route::post('/orcamentos/{orcamento}/converter-venda', [OrcamentoController::class, 'converterEmVenda'])->name('orcamentos.converterVenda');
    Route::resource('cotacoes', CotacaoController::class);
    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/novo', Pdv::class)->name('pedidos.create');
    Route::get('/pedidos/importar-orcamento', [PedidoController::class, 'importarOrcamento'])->name('pedidos.importarOrcamento');
    Route::get('/pedidos/{venda}/edit', [PedidoController::class, 'edit'])->name('pedidos.edit');
    Route::post('/pedidos/{venda}/emitir-nfe', [NFeController::class, 'emitir'])->name('pedidos.emitirNFe');
    Route::resource('contas_a_pagar', App\Http\Controllers\ContaAPagarController::class);
    Route::resource('contas_a_receber', App\Http\Controllers\ContaAReceberController::class); 
    // NFe (Original - MANTIDO)
    Route::prefix('nfe')->name('nfe.')->group(function () {
        Route::get('/', [NFeController::class, 'index'])->name('index');
        Route::get('/importar-pedidos', [NFeController::class, 'importarPedidosView'])->name('importarPedidos');
        Route::get('/{nfe}/danfe', [NFeController::class, 'downloadDanfe'])->name('danfe');
        Route::get('/{nfe}/xml', [NFeController::class, 'downloadXml'])->name('xml');
        Route::post('/{nfe}/cancelar', [NFeController::class, 'cancelar'])->name('cancelar');
        Route::post('/preparar-agrupada', [NFeController::class, 'prepararEmissaoAgrupada'])->name('prepararAgrupada');
        Route::post('/store', [NFeController::class, 'store'])->name('store');
        Route::post('/{nfe}/cce', [NFeController::class, 'enviarCCe'])->name('cce.enviar');
        Route::get('/cce/{cce}/pdf', [NFeController::class, 'downloadDacce'])->name('cce.pdf');
       
        // ===================================================================================
        // ||||||||||||||||||| INÍCIO DA CORREÇÃO E ADIÇÃO DAS ROTAS ||||||||||||||||||||||||
        // ===================================================================================
        
        // ADIÇÃO 1: Rota para a tela que LISTA os rascunhos de NFe Avulsa
      
        
        // ADIÇÃO 2: Rota para a tela de EDITAR um rascunho (Venda com status 'Em Digitação')
        // Esta rota é a que estava faltando e causando o erro 404
       // Route::get('/avulsa/iniciar-nova-nfe/{venda?}', NfeAvulsaCreate::class)->name('avulsa.criar');
      
        
       Route::get('/avulsa/editar/{venda}', NfeAvulsaFormulario::class)->name('avulsa.editar');
       Route::get('/avulsa/criar', NfeAvulsaCriar::class)->name('avulsa.criar');
       Route::get('/rascunhos', NfeRascunhos::class)->name('rascunhos');
        // ===================================================================================
        // ||||||||||||||||||||| FIM DA CORREÇÃO E ADIÇÃO DAS ROTAS |||||||||||||||||||||||||
        // ===================================================================================
    });

    // ROTA ORIGINAL PARA CRIAR NOTA AVULSA (MANTIDA EXATAMENTE COMO ESTAVA)
   
        
    // Rotas de rascunho antigas (MANTIDAS)
  //  Route::post('/rascunho', [NFeController::class, 'storeRascunho'])->name('rascunho.store');
   // Route::get('/rascunho/{nfe}/edit', [NFeController::class, 'editRascunho'])->name('rascunho.edit');
   // Route::put('/rascunho/{nfe}', [NFeController::class, 'updateRascunho'])->name('rascunho.update');
   // Route::post('/rascunho/{nfe}/emitir', [NFeController::class, 'emitirRascunho'])->name('rascunho.emitir');

    // Utilitários (Original - MANTIDO)
    Route::get('/consulta/cnpj/{cnpj}', [UtilController::class, 'consultarCnpj'])->name('consulta.cnpj');
    
    // Novas rotas de admin (Original - MANTIDO)
    Route::prefix('admin')->name('admin.')->middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis-fiscais', PerfilFiscalController::class);
        Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::post('configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
        Route::resource('regras-tributarias', App\Http\Controllers\RegraTributariaController::class);
    });


    Route::get('/pdv-caixa', PdvCaixaController::class)
    ->middleware('auth') // Protege a rota
    ->name('pdv-caixa.index');

    // Rota de teste (Original - MANTIDO)
    Route::get('/teste-nfe', function () {
        try {
            $config = new \NFePHP\Sped\Common\Config('{}');
            dd('SUCESSO! A classe Config foi encontrada e carregada.', $config);
        } catch (\Throwable $e) {
            dd('FALHA. A classe ainda não foi encontrada.', $e->getMessage());
        }
    });
});

require __DIR__.'/auth.php';