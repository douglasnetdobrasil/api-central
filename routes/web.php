<?php
// Lembre-se de remover o 'dd' que estava aqui

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
use App\Livewire\FechamentoCaixa;
use App\Http\Livewire\Fiscal\ContingenciaMonitor;
use App\Http\Controllers\Fiscal\ContingenciaController;
use App\Http\Controllers\VendaController;
use App\Http\Controllers\RelatorioVendaController; 
use App\Http\Controllers\RelatorioFinanceiroController;
use App\Http\Controllers\RelatorioEstoqueController;
use App\Http\Controllers\RelatorioComprasController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\InventarioItemController;
use App\Http\Controllers\FichaTecnicaController;
use App\Http\Controllers\OrdemServicoController;
use App\Http\Controllers\ClienteEquipamentoController;
use App\Http\Controllers\CentroCustoController;
use App\Http\Controllers\PortalClienteController;
use App\Http\Controllers\Admin\SuporteChamadoController;
use App\Http\Controllers\Auth\ClienteLoginController;


use App\Services\NFCeService;
use Illuminate\Support\Facades\Auth;
use App\Models\Empresa;

// ADICIONE OS NOVOS CONTROLLERS
use App\Http\Controllers\PerfilFiscalController;
use App\Http\Controllers\ConfiguracaoController;
use App\Livewire\Pdv;
use App\Livewire\NfeAvulsaCreate;
use App\Livewire\NfeRascunhos;
use App\Models\Venda;
use App\Livewire\NfeAvulsaFormulario;
use App\Livewire\NfeAvulsaCriar;
use App\Http\Controllers\Admin\RelatorioSuporteController;
use App\Http\Controllers\Admin\RelatorioOSController;

// (Dependendo da sua versão do Laravel, você pode precisar de 'use App\Http\Controllers\CentroCustoRelatorioController;')


Route::get('/', function () {
    // Altere 'return view('welcome');' para 'return redirect()->route('login');'
    return redirect()->route('login'); 
});

// =====================================================================
// ||||||||||||||| BLOCO DO PORTAL DO CLIENTE ||||||||||||||||
// =====================================================================
Route::prefix('portal')->name('portal.')->group(function () {

    // Rotas de login/logout do CLIENTE (Acessíveis como 'guest')
    Route::middleware('guest:cliente')->group(function () {
        Route::get('/login', [ClienteLoginController::class, 'showLoginForm'])->name('login');
        Route::post('/login', [ClienteLoginController::class, 'login']);
    });

    // Rotas Protegidas do Portal (Exigem login de cliente)
    Route::middleware(['auth:cliente'])->group(function () {
        Route::post('/logout', [ClienteLoginController::class, 'logout'])->name('logout');
        
        Route::get('/dashboard', [PortalClienteController::class, 'index'])->name('dashboard');
        Route::get('/chamados/novo', [PortalClienteController::class, 'create'])->name('chamados.create');
        Route::post('/chamados', [PortalClienteController::class, 'store'])->name('chamados.store');
        Route::get('/chamados/{chamado}', [PortalClienteController::class, 'show'])->name('chamados.show');
        Route::post('/chamados/{chamado}/responder', [PortalClienteController::class, 'responder'])->name('chamados.responder');
        
        // Rotas do Portal para Equipamentos (Incluindo o modal)
        Route::post('/equipamentos/store-modal', [PortalClienteController::class, 'storeEquipamentoModal'])
            ->name('equipamentos.storeModal');
           
    });
});
// =================== FIM DO BLOCO PORTAL ===================


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

    // Estoque
    Route::get('/estoque', [EstoqueController::class, 'index'])->name('estoque.index');
    Route::get('/estoque/{produto}', [EstoqueController::class, 'show'])->name('estoque.show');

    // Admin
    Route::middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis', RoleController::class)->except(['show']);
        Route::resource('usuarios', UserController::class);
        Route::resource('empresa', EmpresaController::class)->except(['show']);
    });

    // API
    Route::get('/api/clientes/search', [ClienteController::class, 'search'])->name('api.clientes.search');

    // Vendas e Orçamentos
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
    Route::get('/vendas', [VendaController::class, 'index'])->name('vendas.index');
    Route::get('/vendas/{venda}', [VendaController::class, 'show'])->name('vendas.show');

    // NFe
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
        Route::get('/avulsa/editar/{venda}', NfeAvulsaFormulario::class)->name('avulsa.editar');
        Route::get('/avulsa/criar', NfeAvulsaCriar::class)->name('avulsa.criar');
        Route::get('/rascunhos', NfeRascunhos::class)->name('rascunhos');
    });

    // --- MÓDULO DE ORDEM DE SERVIÇO ---
    Route::resource('ordens-servico', OrdemServicoController::class)->parameters([
        'ordens-servico' => 'ordemServico'
    ]);
    Route::resource('cliente-equipamentos', ClienteEquipamentoController::class);

  // Rotas para adicionar/remover PRODUTOS da OS
Route::post('/ordens-servico/{ordemServico}/produtos', [OrdemServicoController::class, 'storeProduto'])->name('os.produtos.store');
Route::delete('/os-produtos/{osProduto}', [OrdemServicoController::class, 'destroyProduto'])->name('os.produtos.destroy');

Route::get('/ordens-servico/{ordemServico}/imprimir', [OrdemServicoController::class, 'imprimir'])->name('ordens-servico.imprimir');

// Rotas para adicionar/remover SERVIÇOS da OS
Route::post('/ordens-servico/{ordemServico}/servicos', [OrdemServicoController::class, 'storeServico'])->name('os.servicos.store');
Route::delete('/os-servicos/{osServico}', [OrdemServicoController::class, 'destroyServico'])->name('os.servicos.destroy');

    // Utilitários
    Route::get('/consulta/cnpj/{cnpj}', [UtilController::class, 'consultarCnpj'])->name('consulta.cnpj');
    
    // Admin (Configurações e Fiscal)
    Route::prefix('admin')->name('admin.')->middleware(['can:acessar-admin'])->group(function () {
        Route::resource('perfis-fiscais', PerfilFiscalController::class);
        Route::get('configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
        Route::post('configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');
        Route::resource('regras-tributarias', App\Http\Controllers\RegraTributariaController::class);
    });

    // PDV
    Route::get('/pdv-caixa', PdvCaixaController::class)->middleware(['can:operar-caixa'])->name('pdv-caixa.index');
    Route::get('/pdv/fechamento', FechamentoCaixa::class)->name('pdv.fechamento');

    // RELATORIOS 
    Route::get('/relatorios/vendas', [RelatorioVendaController::class, 'index'])->name('relatorios.vendas.index');
    Route::get('/relatorios/financeiro', [RelatorioFinanceiroController::class, 'index'])->name('relatorios.financeiro.index');
    Route::get('/relatorios/estoque', [RelatorioEstoqueController::class, 'index'])->name('relatorios.estoque.index');
    Route::get('/relatorios/estoque/movimentacoes/{produto}', [RelatorioEstoqueController::class, 'movimentacoes'])->name('relatorios.estoque.movimentacoes');
    Route::get('/relatorios/compras', [RelatorioComprasController::class, 'index'])->name('relatorios.compras.index');
    Route::get('/admin/relatorios/suporte', [RelatorioSuporteController::class, 'dashboard'])
    ->middleware('can:acessar-admin') // Garante que só supervisor acesse
    ->name('relatorios.suporte.dashboard');
   // ROTA PRINCIPAL: /admin/relatorios/suporte (URL) -> admin.relatorios.suporte.dashboard (NOME)
Route::get('/relatorios/suporte/dashboard', [RelatorioSuporteController::class, 'dashboard'])
->name('admin.relatorios.suporte.dashboard') // <--- CORRIGIDO: Nome completo esperado pelo formulário
->middleware('can:acessar-admin'); 

// ROTA DETALHE:
Route::get('/relatorios/suporte/detalhe', [RelatorioSuporteController::class, 'detalhe'])
->name('admin.relatorios.suporte.detalhe')
->middleware('can:acessar-admin');

Route::get('/relatorios/os/dashboard', [RelatorioOsController::class, 'dashboard'])
    ->name('admin.relatorios.os.dashboard') // Nome com prefixo admin.
    ->middleware('can:acessar-admin'); 
    
Route::get('/relatorios/os/detalhe', [RelatorioOsController::class, 'detalhe'])
    ->name('admin.relatorios.os.detalhe')
    ->middleware('can:acessar-admin');



    // --- MÓDULO DE INVENTÁRIO ---
    Route::prefix('inventarios')->name('inventarios.')->group(function () {
        Route::get('/', [InventarioController::class, 'index'])->name('index');
        Route::get('/create', [InventarioController::class, 'create'])->name('create');
        Route::post('/', [InventarioController::class, 'store'])->name('store');
        Route::get('/{inventario}/contagem', [InventarioController::class, 'showContagem'])->name('contagem');
        Route::get('/{inventario}/reconciliacao', [InventarioController::class, 'showReconciliacao'])->name('reconciliacao');
        Route::post('/{inventario}/finalizar', [InventarioController::class, 'finalizar'])->name('finalizar');
        Route::patch('/item/{inventarioItem}', [InventarioItemController::class, 'update'])->name('item.update');
        Route::get('/{inventario}/visualizar', [InventarioController::class, 'showVisualizacao'])->name('visualizar');
        Route::post('/{inventario}/marcar-contado', [InventarioController::class, 'marcarComoContado'])->name('marcarContado');
    });

    // --- MÓDULO DE PRODUÇÃO ---
    Route::post('ficha-tecnica/{produto}/store-item', [FichaTecnicaController::class, 'storeItem'])->name('ficha-tecnica.storeItem');
    Route::resource('ficha-tecnica', FichaTecnicaController::class)->except(['show']);
   
    // ORDEM DE PRODUÇÃO
    Route::post('ordem-producao/{ordemProducao}/iniciar', [App\Http\Controllers\OrdemProducaoController::class, 'iniciarProducao'])->name('ordem-producao.iniciar');
    Route::post('ordem-producao/{ordemProducao}/finalizar', [App\Http\Controllers\OrdemProducaoController::class, 'finalizarProducao'])->name('ordem-producao.finalizar');
    Route::resource('ordem-producao', App\Http\Controllers\OrdemProducaoController::class)->except(['edit', 'update']);
    Route::get('/producao', App\Http\Controllers\ProducaoDashboardController::class)->name('producao.dashboard');   
   
    // FISCAL
    Route::get('/fiscal', [ContingenciaController::class, 'index'])->name('fiscal.index');
    
    // CENTRO DE CUSTO 
    Route::resource('centros-custo', CentroCustoController::class)->middleware('auth');

    // ... (Bloco de CentroCustoRelatorioController comentado) ...
  
    // =====================================================================
    // ||||||||||||||||| GESTÃO DE CHAMADOS (PAINEL INTERNO) - CORRIGIDO ||||||||||||||||
    // =====================================================================
    Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
        
        // 1. ROTAS AJAX (PRIORIDADE MÁXIMA PARA EVITAR CONFLITO COM {chamado})
        Route::get('chamados/equipamentosPorCliente', [SuporteChamadoController::class, 'equipamentosPorCliente'])
            ->name('chamados.equipamentosPorCliente'); 

        Route::post('equipamentos/storeModal', [ClienteEquipamentoController::class, 'storeModal']) 
            ->name('equipamentos.storeModal');
        
        // 2. ROTAS CRUD COM NOME ESPECÍFICO (PRIORIDADE ALTA)
        Route::get('/chamados/novo', [SuporteChamadoController::class, 'create'])->name('chamados.create');
        Route::post('/chamados', [SuporteChamadoController::class, 'store'])->name('chamados.store');
        Route::get('/chamados', [SuporteChamadoController::class, 'index'])->name('chamados.index');

        // 3. ROTAS DE AÇÃO E {chamado} (PRIORIDADE BAIXA)
        Route::post('/chamados/{chamado}/converter-os', [SuporteChamadoController::class, 'converterOS'])->name('chamados.converterOS');
        Route::post('/chamados/{chamado}/responder', [SuporteChamadoController::class, 'responder'])->name('chamados.responder');
        Route::post('/chamados/{chamado}/atribuir', [SuporteChamadoController::class, 'atribuir'])->name('chamados.atribuir');

        Route::patch('/chamados/{chamado}/mudar-status', [SuporteChamadoController::class, 'mudarStatus'])
        ->name('chamados.mudarStatus');
        
        Route::patch('/chamados/{chamado}/mudar-prioridade', [SuporteChamadoController::class, 'mudarPrioridade'])
        ->name('chamados.mudarPrioridade');

        Route::patch('/chamados/{chamado}/salvar-solucao', [SuporteChamadoController::class, 'salvarSolucao'])
                     ->name('chamados.salvarSolucao');
                     
        Route::patch('/chamados/{chamado}/reatribuir', [SuporteChamadoController::class, 'reatribuir'])
                     ->name('chamados.reatribuir'); 
                     
        // ROTA SHOW - DEVE SER A ÚLTIMA A USAR O PARÂMETRO {chamado}
        Route::get('/chamados/{chamado}', [SuporteChamadoController::class, 'show'])->name('chamados.show');
    });

    // --- ROTAS DA OS QUE TAMBÉM PODEM CAUSAR CONFLITO ---
    Route::get('/os/clientes/{clienteId}/equipamentos', [App\Http\Controllers\OrdemServicoController::class, 'getEquipamentosByCliente'])->name('os.equipamentos.by.cliente');
    Route::post('/os/equipamentos/store-modal', [OrdemServicoController::class, 'storeEquipamentoModal'])
         ->name('os.equipamentos.storeModal');
    
    // ... (o restante das suas rotas de teste e fiscal) ...

    Route::get('/teste-status-sefaz', function () {
        if (!Auth::check()) {
            return 'Você precisa estar logado para fazer este teste.';
        }
    
        try {
            $empresa = Auth::user()->empresa;
            if (!$empresa) {
                return "Usuário não tem uma empresa associada.";
            }
    
            $nfceService = new NFCeService();
    
            $reflection = new \ReflectionClass($nfceService);
            $method = $reflection->getMethod('bootstrap');
            $method->setAccessible(true);
            $method->invoke($nfceService, $empresa);
    
            $toolsProperty = $reflection->getProperty('tools');
            $toolsProperty->setAccessible(true);
            $tools = $toolsProperty->getValue($nfceService);
    
            $response = $tools->sefazStatus();
    
            $std = new \NFePHP\NFe\Common\Standardize($response);
            dd($std->toStd());
    
        } catch (\Throwable $e) {
            dd($e->getMessage());
        }
    });

    Route::get('/fiscal', [ContingenciaController::class, 'index'])
    ->middleware('auth')
    ->name('fiscal.index');
    

    Route::get('/teste-nfe', function () {
        try {
            $config = new \NFePHP\Sped\Common\Config('{}');
            dd('SUCESSO! A classe Config foi encontrada e carregada.', $config);
        } catch (\Throwable $e) {
            dd('FALHA. A classe ainda não foi encontrada.', $e->getMessage());
        }
    });

}); // FIM DO GRUPO GERAL DE MIDDLEWARE 'auth'

require __DIR__.'/auth.php';