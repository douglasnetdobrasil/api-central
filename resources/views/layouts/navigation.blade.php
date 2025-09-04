<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Dashboard') }}
    </x-nav-link>

    @canany(['ver-produtos', 'ver-fornecedores', 'ver-clientes', 'ver-entrada-notas', 'ver-categorias', 'ver-usuarios', 'ver-perfis', 'ver-transportadoras', 'ver-servicos', 'ver-formas-pagamento'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Cadastros</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-produtos')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Produtos') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-fornecedores')
                    <x-dropdown-link :href="route('fornecedores.index')">
                        {{ __('Fornecedores') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-clientes')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Clientes') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-entrada-notas')
                    <x-dropdown-link :href="route('compras.index')">
                        {{ __('Entrada de Notas') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-categorias')
                    <x-dropdown-link :href="route('categorias.index')">
                        {{ __('Categorias') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-usuarios')
                    <x-dropdown-link :href="route('usuarios.index')">
                        {{ __('Usuarios') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-perfis')
                    <x-dropdown-link :href="route('perfis.index')">
                        {{ __('Perfil de Usuarios') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-transportadoras')
                    <x-dropdown-link :href="route('transportadoras.index')">
                        {{ __('Transportadoras') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-formas-pagamento')
                    <x-dropdown-link :href="route('formas-pagamento.index')">
                        {{ __('Formas de Pagamento') }}
                    </x-dropdown-link>
                @endcan
             
            </x-slot>
        </x-dropdown>
    </div>
@endcanany


@canany(['ver-leads', 'ver-orcamentos', 'ver-pedidos-venda', 'ver-notas-fiscais', 'ver-comissoes'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Vendas</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-leads')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Leads / Oportunidades') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-orcamentos')
                    <x-dropdown-link :href="route('orcamentos.index')">
                        {{ __('Orçamentos') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-pedidos-venda')
                    <x-dropdown-link :href="route('pedidos.index')">
                        {{ __('Pedidos de Venda') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-notas-fiscais')
                    <x-dropdown-link :href="route('nfe.index')">
                        {{ __('Notas Fiscais (NF-e / NFC-e)') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-comissoes')
                    <x-dropdown-link :href="route('compras.index')">
                        {{ __('Comissões') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany

    @canany(['ver-solicitacoes-compra', 'ver-cotacoes', 'ver-pedidos-compra', 'ver-entrada-mercadorias'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Compras</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-solicitacoes-compra')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Solicitações de Compra') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-solicitacoes-compra')
                    <x-dropdown-link :href="route('compras.index')">
                        {{ __('Entrada de Nota') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-cotacoes')
                    <x-dropdown-link :href="route('cotacoes.index')">
                        {{ __('Cotações') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-pedidos-compra')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Pedidos de Compra') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-entrada-mercadorias')
                    <x-dropdown-link :href="route('compras.index')">
                        {{ __('Entrada de Mercadorias') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany



@canany(['ver-contas-receber', 'ver-contas-pagar', 'ver-fluxo-caixa', 'ver-conciliacao-bancaria', 'ver-centro-custo', 'ver-usuarios'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Financeiro</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-contas-receber')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Contas a Receber') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-contas-pagar')
                    <x-dropdown-link :href="route('fornecedores.index')">
                        {{ __('Contas a Pagar') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-fluxo-caixa')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Fluxo de Caixa') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-conciliacao-bancaria')
                    <x-dropdown-link :href="route('compras.index')">
                        {{ __('Conciliacao Bancaria') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-centro-custo')
                    <x-dropdown-link :href="route('categorias.index')">
                        {{ __('Centro de custo') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-usuarios')
                    <x-dropdown-link :href="route('usuarios.index')">
                        {{ __('Usuarios') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany

@canany(['ver-movimentacoes-estoque', 'ver-transferencias-estoque', 'ver-inventario', 'ver-posicao-estoque'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Estoque</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-movimentacoes-estoque')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Movimentações') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-transferencias-estoque')
                    <x-dropdown-link :href="route('fornecedores.index')">
                        {{ __('Transferências') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-inventario')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Inventário') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-posicao-estoque')
                    <x-dropdown-link :href="route('compras.index')">
                        {{ __('Posição de Estoque') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany

@canany(['ver-ordem-producao', 'ver-estrutura-produto', 'ver-ordem-servico'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Produção</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-ordem-producao')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Ordem de Produção (OP)') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-estrutura-produto')
                    <x-dropdown-link :href="route('fornecedores.index')">
                        {{ __('Estrutura de Produto (BOM - Bill of Materials)') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-ordem-servico')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Ordem de Serviço (OS)') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany
@canany(['ver-relatorio-vendas', 'ver-relatorio-financeiro', 'ver-relatorio-estoque', 'ver-relatorio-compras'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Relatórios</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-relatorio-vendas')
                    <x-dropdown-link :href="route('produtos.index')">
                        {{ __('Relatórios de Vendas') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-relatorio-financeiro')
                    <x-dropdown-link :href="route('fornecedores.index')">
                        {{ __('Relatórios Financeiros') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-relatorio-estoque')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Relatórios de Estoque') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-relatorio-compras')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Relatórios de Compras') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany

@canany(['ver-config-empresa', 'ver-config-fiscal', 'ver-logs-sistema'])
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>Configurações</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                @can('ver-config-empresa')
                    <x-dropdown-link :href="route('empresa.index')">
                        {{ __('Minha Empresa') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-config-fiscal')
                    <x-dropdown-link :href="route('empresa.index')">
                        {{ __('Configurações Fiscais') }}
                    </x-dropdown-link>
                @endcan
                @can('ver-logs-sistema')
                    <x-dropdown-link :href="route('clientes.index')">
                        {{ __('Logs do Sistema') }}
                    </x-dropdown-link>
                @endcan
            </x-slot>
        </x-dropdown>
    </div>
@endcanany




    </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>

                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Profile') }}
                        </x-dropdown-link>

                        <!-- Authentication -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf

                            <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault();
                                                this.closest('form').submit();">
                                {{ __('Log Out') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
