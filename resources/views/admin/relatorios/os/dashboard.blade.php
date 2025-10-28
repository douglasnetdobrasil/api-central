<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            🛠️ Dashboard de Gestão de Ordens de Serviço (BI)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- SEÇÃO DE FILTROS --}}
            <div id="filtros" class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                 <form method="GET" action="{{ route('admin.relatorios.os.dashboard') }}">
                    {{-- ... (Formulário de filtros permanece o mesmo) ... --}}
                     <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        {{-- Filtro de Período --}}
                        <div>
                            <x-input-label for="data_inicio" value="Data Início (Criação OS)" />
                            <x-text-input type="date" name="data_inicio" value="{{ $dataInicio }}" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="data_fim" value="Data Fim (Criação OS)" />
                            <x-text-input type="date" name="data_fim" value="{{ $dataFim }}" class="mt-1 block w-full" />
                        </div>
                        {{-- Filtro de Cliente --}}
                        <div>
                            <x-input-label for="cliente_id" value="Cliente Específico" />
                            <select name="cliente_id" id="cliente_id" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos os Clientes</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected($clienteId == $cliente->id)>{{ $cliente->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Filtro de Técnico --}}
                        <div>
                            <x-input-label for="tecnico_id" value="Técnico Específico" />
                            <select name="tecnico_id" id="tecnico_id" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Todos os Técnicos</option>
                                @foreach($tecnicos as $tecnico)
                                    <option value="{{ $tecnico->id }}" @selected($tecnicoId == $tecnico->id)>{{ $tecnico->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Botão Aplicar Filtros --}}
                        <x-primary-button type="submit">Aplicar Filtros</x-primary-button>
                    </div>
                </form>
            </div>

            {{-- CONTEÚDO PRINCIPAL DO DASHBOARD --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg p-6">

                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">Resultados do Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}</h3>

                {{-- GRID PRINCIPAL DE KPIS (Atualizado) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">

                    {{-- KPI 1: TMR --}}
                    <div class="bg-purple-50 dark:bg-purple-900/50 p-4 rounded-lg shadow-sm border border-purple-200 dark:border-purple-800">
                        <p class="text-sm font-medium text-purple-700 dark:text-purple-300">Tempo Médio de Reparo (TMR)</p>
                        <p class="text-3xl font-bold text-purple-900 dark:text-purple-100 mt-1">{{ $tmr }}</p>
                        <p class="text-xs text-purple-600 dark:text-purple-400">Tempo médio da entrada à conclusão.</p>
                    </div>

                    {{-- KPI 2: OS Concluídas --}}
                    <div class="bg-green-50 dark:bg-green-900/50 p-4 rounded-lg shadow-sm border border-green-200 dark:border-green-800">
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">OS Concluídas no Período</p>
                        <p class="text-3xl font-bold text-green-900 dark:text-green-100 mt-1">{{ $volumeOSConcluidas }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400">Total de OS finalizadas.</p>
                    </div>

                    {{-- KPI 3: Valor Médio por OS --}}
                    <div class="bg-blue-50 dark:bg-blue-900/50 p-4 rounded-lg shadow-sm border border-blue-200 dark:border-blue-800">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Valor Médio por OS</p>
                        <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">R$ {{ $valorMedioOS }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">Ticket médio das OS concluídas.</p>
                    </div>
                    
                    {{-- KPI 4: OS Pendentes (Atualizado) --}}
                    <div class="bg-red-50 dark:bg-red-900/50 p-4 rounded-lg shadow-sm border border-red-200 dark:border-red-800">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">OS Pendentes</p>
                        <p class="text-3xl font-bold text-red-900 dark:text-red-100 mt-1">{{ $totalOSPendentes }}</p> 
                        <p class="text-xs text-red-600 dark:text-red-400">OS Abertas ou Em Andamento.</p>
                    </div>
                </div>

                {{-- GRÁFICOS E LISTAS DE RANKING (Atualizado com Novos Rankings) --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-4 gap-6 mb-8"> {{-- Alterado para 4 colunas --}}

                    {{-- Ranking 1: Top Técnicos (Produtividade) --}}
                    <div class="xl:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">🛠️ Top Técnicos (OS Concluídas)</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($topTecnicosProdutividade as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    <a href="{{ route('admin.relatorios.os.detalhe', ['tipo' => 'tecnico', 'id' => $item->tecnico_id, 'data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                        {{ $item->tecnico->name ?? 'N/A' }}
                                    </a>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                        {{ $item->total_concluidas }} OS
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Ranking 2: Top Clientes com Mais OS (Novo) --}}
                    <div class="xl:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">👥 Top Clientes (Volume OS)</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                             @forelse($topClientesOS as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    <a href="{{ route('admin.relatorios.os.detalhe', ['tipo' => 'cliente', 'id' => $item->cliente_id, 'data_inicio' => $dataInicio, 'data_fim' => $dataFim]) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 truncate">
                                        {{ $item->cliente->nome ?? 'N/A' }}
                                    </a>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        {{ $item->total_os }} OS
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado.</li>
                            @endforelse
                        </ul>
                    </div>
                    
                    {{-- Ranking 3: Produtos Mais Usados (Novo) --}}
                    <div class="xl:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">🔩 Top Produtos Usados</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($topProdutosOS as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $item->nome_produto }}">
                                        {{ Str::limit($item->nome_produto, 25) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                                        {{ number_format($item->quantidade_total, 0) }} un
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado.</li>
                            @endforelse
                        </ul>
                    </div>
                    
                     {{-- Ranking 4: Serviços Mais Realizados (Novo) --}}
                    <div class="xl:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">⚙️ Top Serviços Realizados</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($topServicosOS as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $item->nome_servico }}">
                                         {{ Str::limit($item->nome_servico, 25) }}
                                    </span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-300">
                                        {{ $item->total_vezes }} vezes
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                {{-- GRÁFICOS VISUAIS (Estrutura) --}}
                {{-- (A implementação dos gráficos será o próximo passo) --}}
                <hr class="my-8 dark:border-gray-600">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Produtividade por Técnico (OS Concluídas)</h3>
                        <canvas id="produtividadeTecnicoChart"></canvas> 
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Faturamento por Técnico (R$)</h3>
                         <canvas id="faturamentoTecnicoChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>
    
    {{-- INCLUSÃO DO CHART.JS E DO SCRIPT DE DADOS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- @include('admin.relatorios.os.charts-script') --}} {{-- Script dos gráficos virá depois --}}
    @include('admin.relatorios.os.charts-script')
</x-app-layout>