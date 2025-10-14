<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório de Posição de Estoque
            </h2>
            <x-primary-button onclick="window.print()">
                Imprimir Relatório
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="filtros" class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 no-print">
                <form method="GET" action="{{ route('relatorios.estoque.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="categoria_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoria</label>
                            <select name="categoria_id" id="categoria_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                <option value="">Todas</option>
                                @foreach($categorias as $categoria)
                                <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        <x-primary-button type="submit">Filtrar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Itens em Estoque</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ number_format($totalItens, 0, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Valor do Estoque (Custo)</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($valorTotalCusto, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-gray-500 dark:text-gray-400">Potencial de Venda</h3>
                    <p class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($valorTotalVenda, 2, ',', '.') }}</p>
                </div>
            </div>

            <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                 <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Valor do Estoque por Categoria</h3>
                 <canvas id="valorPorCategoriaChart"></canvas>
            </div>
            
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100 overflow-x-auto">
                    <h3 class="font-semibold mb-4 text-lg">Posição Atual do Estoque</h3>
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-2 text-left">Produto</th>
                                <th class="px-4 py-2 text-left">Categoria</th>
                                <th class="px-4 py-2 text-center">Estoque Atual</th>
                                <th class="px-4 py-2 text-right">Preço Custo</th>
                                <th class="px-4 py-2 text-right">Valor Total (Custo)</th>
                                <th class="px-4 py-2 text-center no-print">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($produtos as $produto)
                                <tr class="border-b dark:border-gray-700">
                                    <td class="px-4 py-2">{{ $produto->nome }}</td>
                                    <td class="px-4 py-2">{{ $produto->categoria->nome ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-center font-bold">{{ $produto->estoque_atual }}</td>
                                    <td class="px-4 py-2 text-right">R$ {{ number_format($produto->preco_custo, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-right">R$ {{ number_format($produto->estoque_atual * $produto->preco_custo, 2, ',', '.') }}</td>
                                    <td class="px-4 py-2 text-center no-print">
                                        <a href="{{ route('relatorios.estoque.movimentacoes', $produto) }}" class="text-indigo-600 hover:text-indigo-900">
                                            Movimentações
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center py-4">Nenhum produto encontrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    <div class="mt-4">{{ $produtos->links() }}</div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('relatorios.estoque.charts-script')
</x-app-layout>