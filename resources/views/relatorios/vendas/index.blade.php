<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Vendas
            </h2>
            <x-primary-button onclick="window.print()">
                Imprimir Relatório
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="filtros" class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <form method="GET" action="{{ route('relatorios.vendas.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" value="{{ request('data_inicio') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="cliente_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
                            <select name="cliente_id" id="cliente_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                @foreach($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(request('cliente_id') == $cliente->id)>{{ $cliente->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="user_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Vendedor</label>
                            <select name="user_id" id="user_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">Todos</option>
                                @foreach($vendedores as $vendedor)
                                    <option value="{{ $vendedor->id }}" @selected(request('user_id') == $vendedor->id)>{{ $vendedor->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-4">
                        <x-primary-button type="submit">Filtrar</x-primary-button>
                    </div>
                </form>
            </div>

            <div id="kpis" class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Valor Total Vendido</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($valorTotal, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Nº de Vendas</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $numeroVendas }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Ticket Médio</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                </div>
            </div>
            
            <div id="graficos" class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                 <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold mb-4">Vendas por Dia</h3>
                    <canvas id="vendasPorDiaChart"></canvas>
                 </div>
                 <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold mb-4">Top 5 Produtos Mais Vendidos</h3>
                    <canvas id="produtosChart"></canvas>
                 </div>
            </div>

            <div id="tabela" class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Vendedor</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($vendas as $venda)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $venda->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $venda->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $venda->cliente->nome ?? 'N/A' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $venda->user->name }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">Nenhuma venda encontrada para os filtros selecionados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $vendas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('relatorios.vendas.charts-script')
</x-app-layout>