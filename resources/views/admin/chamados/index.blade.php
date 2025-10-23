<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestão de Chamados
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- FORMULÁRIO DE FILTROS GERAIS --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form action="{{ route('admin.chamados.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">
                    <div>
                        <label for="cliente_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente</label>
                        <select name="cliente_id" id="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach ($clientes as $id => $nome)
                                <option value="{{ $id }}" {{ $request->cliente_id == $id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="prioridade" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Prioridade</label>
                        <select name="prioridade" id="prioridade" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todas</option>
                            @foreach ($prioridades as $p)
                                <option value="{{ $p }}" {{ $request->prioridade == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    {{-- Adicionar mais filtros (Data, Técnico, etc.) aqui --}}
                    <div class="md:col-span-1 flex gap-2">
                         <button type="submit"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Filtrar
                        </button>
                        {{-- Botão Limpar (opcional) --}}
                        <a href="{{ route('admin.chamados.index') }}"
                                class="w-full inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                            Limpar
                        </a>
                    </div>
                </form>
            </div>

            {{-- SISTEMA DE ABAS COM ALPINE.JS --}}
            <div x-data="{ activeTab: 'novos' }" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                {{-- Navegação das Abas --}}
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="-mb-px flex space-x-6 px-6" aria-label="Tabs">
                        {{-- Aba Novos --}}
                        <button @click="activeTab = 'novos'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'novos', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'novos' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Novos
                            @php $countNovos = count($chamadosPorAba['novos']); @endphp
                            @if($countNovos > 0)
                                <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $countNovos > 0 ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300' }}">
                                    {{ $countNovos }}
                                </span>
                            @endif
                        </button>

                        {{-- Aba Meus Atendimentos --}}
                        <button @click="activeTab = 'meus'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'meus', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'meus' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Meus Atendimentos
                             @php $countMeus = count($chamadosPorAba['meus']); @endphp
                             @if($countMeus > 0)
                                <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                    {{ $countMeus }}
                                </span>
                             @endif
                        </button>

                         {{-- Aba Aguardando Resposta (Cliente respondeu) --}}
                        <button @click="activeTab = 'aguardando_atendimento'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'aguardando_atendimento', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'aguardando_atendimento' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Aguardando Resposta
                             @php $countAguardandoAtendimento = count($chamadosPorAba['aguardando_atendimento']); @endphp
                             @if($countAguardandoAtendimento > 0)
                                <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                    {{ $countAguardandoAtendimento }}
                                </span>
                             @endif
                        </button>
                        
                        {{-- Aba Aguardando Cliente --}}
                        <button @click="activeTab = 'aguardando_cliente'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'aguardando_cliente', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'aguardando_cliente' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Aguardando Cliente
                            {{-- Pode adicionar contador se quiser --}}
                        </button>

                        <button @click="activeTab = 'meus_resolvidos'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'meus_resolvidos', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'meus_resolvidos' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Meus Resolvidos
                             @php $countMeusResolvidos = count($chamadosPorAba['meus_resolvidos']); @endphp
                             @if($countMeusResolvidos > 0)
                                <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                    {{ $countMeusResolvidos }}
                                </span>
                             @endif
                        </button>
                        
                        {{-- Aba Todos --}}
                        <button @click="activeTab = 'todos'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'todos', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'todos' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Todos
                        </button>
                    </nav>
                </div>

                {{-- Conteúdo das Abas --}}
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Conteúdo Aba Novos --}}
                    <div x-show="activeTab === 'novos'">
                        @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['novos']])
                    </div>
                    {{-- Conteúdo Aba Meus --}}
                    <div x-show="activeTab === 'meus'" style="display: none;">
                        @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['meus']])
                    </div>
                    {{-- Conteúdo Aba Aguardando Resposta --}}
                    <div x-show="activeTab === 'aguardando_atendimento'" style="display: none;">
                        @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['aguardando_atendimento']])
                    </div>
                     {{-- Conteúdo Aba Aguardando Cliente --}}
                    <div x-show="activeTab === 'aguardando_cliente'" style="display: none;">
                        @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['aguardando_cliente']])
                    </div>

                    {{-- Conteúdo Aba Meus Resolvidos --}}
                    <div x-show="activeTab === 'meus_resolvidos'" style="display: none;">
                         {{-- Adicione o dump para teste --}}
                         @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['meus_resolvidos']])
                    </div>
                    {{-- Conteúdo Aba Todos (Usa a variável com paginação) --}}
                    <div x-show="activeTab === 'todos'" style="display: none;">
                        @include('admin.chamados.partials.tabela-chamados', ['chamados' => $todosChamados])
                        <div class="mt-4">
                            {{ $todosChamados->links() }} {{-- Paginação SÓ na aba Todos --}}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>