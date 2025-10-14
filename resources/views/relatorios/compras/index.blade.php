<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Compras
            </h2>
            <x-primary-button onclick="window.print()">
                Imprimir Relatório
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="filtros" class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 no-print">
                <form method="GET" action="{{ route('relatorios.compras.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium">Data Início</label>
                            <input type="date" name="data_inicio" value="{{ $dataInicio }}" class="mt-1 block w-full rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_fim" class="block text-sm font-medium">Data Fim</label>
                            <input type="date" name="data_fim" value="{{ $dataFim }}" class="mt-1 block w-full rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="fornecedor_id" class="block text-sm font-medium">Fornecedor</label>
                            <select name="fornecedor_id" id="fornecedor_id" class="mt-1 block w-full rounded-md shadow-sm">
                                <option value="">Todos</option>
                                @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}" @selected(request('fornecedor_id') == $fornecedor->id)>{{ $fornecedor->razao_social }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button type="submit">Filtrar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Valor Total em Compras</h3>
                    <p class="mt-1 text-3xl font-semibold">R$ {{ number_format($valorTotal, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Nº de Notas</h3>
                    <p class="mt-1 text-3xl font-semibold">{{ $numeroNotas }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Ticket Médio</h3>
                    <p class="mt-1 text-3xl font-semibold">R$ {{ number_format($ticketMedio, 2, ',', '.') }}</p>
                </div>
            </div>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold mb-4">Top Fornecedores (por Valor)</h3>
                    <canvas id="comprasPorFornecedorChart"></canvas>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold mb-4">Top Produtos Comprados (por Valor)</h3>
                    <canvas id="produtosCompradosChart"></canvas>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    <h3 class="font-semibold mb-4 text-lg">Lista de Notas de Compra</h3>
                    <table class="min-w-full divide-y">
                        <thead>
                            <tr>
                                <th class="px-4 py-2 text-left">Data Emissão</th>
                                <th class="px-4 py-2 text-left">Nº Nota</th>
                                <th class="px-4 py-2 text-left">Fornecedor</th>
                                <th class="px-4 py-2 text-right">Valor Total</th>
                                <th class="px-4 py-2 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($compras as $compra)
                                <tr class="border-b">
                                    <td class="px-4 py-2">{{ \Carbon\Carbon::parse($compra->data_emissao)->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2">{{ $compra->numero_nota }}</td>
                                    <td class="px-4 py-2">{{ $compra->fornecedor->razao_social ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-right">R$ {{ number_format($compra->valor_total_nota, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-center">{{ $compra->status }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-4">Nenhuma compra encontrada para o período.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $compras->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('relatorios.compras.charts-script')
</x-app-layout>