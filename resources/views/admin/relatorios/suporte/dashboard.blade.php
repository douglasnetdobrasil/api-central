<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            📊 Dashboard de Gestão de Suporte (BI)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- SEÇÃO DE FILTROS --}}
            <div id="filtros" class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <form method="GET" action="{{ route('admin.relatorios.suporte.dashboard') }}">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
                        {{-- Filtro de Período --}}
                        <div>
                            <x-input-label for="data_inicio" value="Data Início" />
                            <x-text-input type="date" name="data_inicio" value="{{ $dataInicio }}" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <x-input-label for="data_fim" value="Data Fim" />
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

                {{-- GRID PRINCIPAL DE KPIS --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">

                    {{-- KPI 1: Tempo Médio de Atendimento --}}
                    <div class="bg-blue-50 dark:bg-blue-900/50 p-4 rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Tempo Médio de Atendimento (TMA)</p>
                        <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">{{ $tma }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">Tempo médio total da criação à resolução.</p>
                    </div>

                    {{-- KPI 2: Taxa de Resolução Online (Atualizado) --}}
                    <div class="bg-green-50 dark:bg-green-900/50 p-4 rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-green-700 dark:text-green-300">Taxa de Resolução Online</p>
                        <p class="text-3xl font-bold text-green-900 dark:text-green-100 mt-1">{{ $taxaResolucaoOnline }}%</p>
                        <p class="text-xs text-green-600 dark:text-green-400">Chamados resolvidos sem criar OS.</p>
                    </div>

                    {{-- KPI 3: Total de Chamados Abertos (Atualizado) --}}
                     <div class="bg-yellow-50 dark:bg-yellow-900/50 p-4 rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300">Total Chamados Abertos</p>
                        <p class="text-3xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">{{ $totalChamadosAbertos }}</p>
                        <p class="text-xs text-yellow-600 dark:text-yellow-400">Chamados criados no período.</p>
                    </div>

                     {{-- KPI 4: Chamados Pendentes (Atualizado) --}}
                     <div class="bg-red-50 dark:bg-red-900/50 p-4 rounded-lg shadow-sm">
                        <p class="text-sm font-medium text-red-700 dark:text-red-300">Chamados Pendentes</p>
                        <p class="text-3xl font-bold text-red-900 dark:text-red-100 mt-1">{{ $totalPendentes }}</p>
                        <p class="text-xs text-red-600 dark:text-red-400">Abertos, Em Atendimento ou Aguardando.</p>
                    </div>
                </div>

                {{-- GRÁFICOS E LISTAS DE RANKING --}}
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    {{-- Ranking 1: Técnico Mais Ativo (Com Link de Detalhe) --}}
                    <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">🏅 Top 5 Técnicos Mais Ativos</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($tecnicosAtivos as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    {{-- Link para a página de detalhe --}}
                                    <a href="{{ route('admin.relatorios.suporte.detalhe', [
                                        'tipo' => 'tecnico',
                                        'id' => $item->tecnico_atribuido_id,
                                        'data_inicio' => $dataInicio,
                                        'data_fim' => $dataFim
                                    ]) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                                        {{ $item->tecnico->name ?? 'Não Atribuído' }}
                                    </a>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-300">
                                        {{ $item->total_chamados }} Chamados
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado para o período e filtros.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Ranking 2: Top Clientes com Mais Chamados (Com Link de Detalhe) --}}
                    <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">💔 Top 5 Clientes com Mais Chamados</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($topClientes as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    <a href="{{ route('admin.relatorios.suporte.detalhe', [
                                        'tipo' => 'cliente',
                                        'id' => $item->cliente_id,
                                        'data_inicio' => $dataInicio,
                                        'data_fim' => $dataFim
                                    ]) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 truncate">
                                        {{ $item->cliente->nome ?? 'Cliente Excluído' }}
                                    </a>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                                        {{ $item->total_chamados }} Chamados
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado para o período e filtros.</li>
                            @endforelse
                        </ul>
                    </div>

                    {{-- Ranking 3: Equipamentos Mais Problemáticos --}}
                    <div class="lg:col-span-1 bg-gray-50 dark:bg-gray-700 p-4 rounded-lg shadow">
                        <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-3">⚠️ Top 5 Equipamentos Problemáticos</h4>
                        <ul class="divide-y divide-gray-200 dark:divide-gray-600">
                            @forelse($equipamentosProblematicos as $item)
                                <li class="py-2 flex justify-between items-center text-sm">
                                    <span class="font-medium text-gray-700 dark:text-gray-300 truncate" title="{{ $item->equipamento->descricao ?? '' }}">
                                        {{ $item->equipamento->descricao ?? 'Equipamento não listado' }}
                                    </span>
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                        {{ $item->total_chamados }} Chamados
                                    </span>
                                </li>
                            @empty
                                <li class="py-2 text-sm text-gray-500 dark:text-gray-400">Nenhum dado para o período e filtros.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>

                <hr class="my-8 dark:border-gray-600">

                {{-- GRÁFICOS VISUAIS --}}
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Top 5 Técnicos (Por Chamados Atribuídos)</h3>
                        {{-- Canvas para o gráfico de Técnicos --}}
                        <canvas id="tecnicosAtivosChart"></canvas>
                    </div>
                    <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <h3 class="font-semibold mb-4 text-gray-900 dark:text-gray-100">Top 5 Equipamentos Problemáticos</h3>
                         {{-- Canvas para o gráfico de Equipamentos --}}
                        <canvas id="equipamentosProblematicosChart"></canvas>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- INCLUSÃO DO CHART.JS E DO SCRIPT DE DADOS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    {{-- Inclui o script que renderiza os gráficos --}}
    @include('admin.relatorios.suporte.charts-script') {{-- <-- CORREÇÃO AQUI --}}
</x-app-layout>