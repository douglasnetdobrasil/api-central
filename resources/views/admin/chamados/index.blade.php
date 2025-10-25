<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gestão de Chamados
        </h2>
        
        {{-- ADICIONADO: BOTÃO ABRIR NOVO CHAMADO --}}
        <div class="mt-4 sm:mt-0 sm:ml-4 flex justify-end items-center">
            <a href="{{ route('admin.chamados.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                Abrir Novo Chamado
            </a>
        </div>
             
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- FORMULÁRIO DE FILTROS GERAIS --}}
            {{-- ... (Formulário de filtros, mantido inalterado) ... --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm p-4">
                <form action="{{ route('admin.chamados.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4 items-end">
                    
                    {{-- Coluna 1: Cliente --}}
                    <div>
                        <label for="cliente_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente</label>
                        <select name="cliente_id" id="cliente_id_filtro" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach ($clientes as $id => $nome)
                                <option value="{{ $id }}" {{ $request->cliente_id == $id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Coluna 2: Equipamento (Carregado via JS) --}}
                    <div>
                        <label for="cliente_equipamento_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Equipamento Específico</label>
                        <select name="cliente_equipamento_id" id="cliente_equipamento_id_filtro" 
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                                {{ $request->filled('cliente_id') ? '' : 'disabled' }}> {{-- Mantém desabilitado se não houver cliente selecionado --}}
                            <option value="">Todos</option>
                            @if ($equipamentosDoCliente->count())
                                @foreach ($equipamentosDoCliente as $equipamento)
                                    <option value="{{ $equipamento->id }}" {{ $request->cliente_equipamento_id == $equipamento->id ? 'selected' : '' }}>
                                        {{ $equipamento->descricao }} (S/N: {{ $equipamento->numero_serie }})
                                    </option>
                                @endforeach
                            @elseif ($request->filled('cliente_id'))
                                <option value="" disabled>Nenhum equipamento para este cliente</option>
                            @endif
                            
                        </select>
                    </div>

                    {{-- Coluna 3: Protocolo --}}
                    <div>
                        <label for="protocolo" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Protocolo</label>
                        <input type="text" name="protocolo" id="protocolo" value="{{ $request->protocolo }}" placeholder="Ex: 202410-0001"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    </div>

                    {{-- Coluna 4: Prioridade --}}
                    <div>
                        <label for="prioridade" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Prioridade</label>
                        <select name="prioridade" id="prioridade" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todas</option>
                            @foreach ($prioridades as $p)
                                <option value="{{ $p }}" {{ $request->prioridade == $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Coluna 5: Técnico --}}
                    <div>
                         <label for="tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Técnico</label>
                         <select name="tecnico_id" id="tecnico_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            <option value="0" {{ $request->tecnico_id === '0' ? 'selected' : '' }}>Não Atribuído</option>
                            @foreach ($tecnicos as $id => $nome)
                                <option value="{{ $id }}" {{ $request->tecnico_id == $id ? 'selected' : '' }}>{{ $nome }}</option>
                            @endforeach
                        </select>
                    </div>

                     {{-- Coluna 6: Status --}}
                    <div>
                         <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status</label>
                         <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                            <option value="">Todos</option>
                            @foreach ($statusOptions as $status)
                                <option value="{{ $status }}" {{ $request->status == $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- Coluna de Busca Geral (Ocupa toda a largura) --}}
                    <div class="lg:col-span-6 md:col-span-2">
                        <label for="busca_texto" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Busca Rápida (Título, Descrição, Equipamento ou N/S)</label>
                        <div class="flex gap-2">
                             <input type="text" name="busca_texto" id="busca_texto" value="{{ $request->busca_texto }}" placeholder="Buscar por texto no chamado ou equipamento..."
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        </div>
                    </div>
                    
                    {{-- Coluna dos Botões (Posicionada na linha de baixo para melhor layout) --}}
                    <div class="lg:col-span-6 flex gap-2 justify-end mt-4">
                         <button type="submit"
                                class="inline-flex justify-center items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 focus:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Filtrar
                        </button>
                        {{-- Botão Limpar --}}
                        <a href="{{ route('admin.chamados.index') }}"
                                class="inline-flex justify-center items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                            Limpar Filtros
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

                        {{-- ABA: Fechados --}}
                        <button @click="activeTab = 'fechados'"
                                :class="{ 'border-indigo-500 text-indigo-600 dark:text-indigo-400': activeTab === 'fechados', 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 hover:border-gray-300 dark:hover:text-gray-300': activeTab !== 'fechados' }"
                                class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                            Fechados
                             @php $countFechados = count($chamadosPorAba['fechados']); @endphp
                             @if($countFechados > 0)
                                <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-400 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                                    {{ $countFechados }}
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
                         @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['meus_resolvidos']])
                    </div>
                    
                    {{-- CONTEÚDO NOVA ABA: Fechados --}}
                    <div x-show="activeTab === 'fechados'" style="display: none;">
                         @include('admin.chamados.partials.tabela-chamados', ['chamados' => $chamadosPorAba['fechados']])
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