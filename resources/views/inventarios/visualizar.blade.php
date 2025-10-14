<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{-- Título alterado para "Visualização" --}}
            Visualização do Inventário #{{ $inventario->id }} (Finalizado)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- Cards de Resumo (KPIs) - Idênticos ao da reconciliação --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="p-4 bg-red-100 dark:bg-red-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Valor Total das Perdas</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-900 dark:text-red-100">R$ {{ number_format(abs($stats['total_perdas']), 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-green-100 dark:bg-green-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-green-800 dark:text-green-200">Valor Total dos Ganhos</h3>
                    <p class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-100">R$ {{ number_format($stats['total_ganhos'], 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-blue-100 dark:bg-blue-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200">SKUs com Divergência</h3>
                    <p class="mt-1 text-3xl font-semibold text-blue-900 dark:text-blue-100">{{ $stats['skus_com_diferenca'] }}</p>
                </div>
            </div>
            
            {{-- Tabela de Divergências - Idêntica à da reconciliação --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Itens com Divergência na Contagem</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Produto</th>
                                    <th class="px-4 py-2 text-center">Est. Esperado</th>
                                    <th class="px-4 py-2 text-center">Qtd. Contada</th>
                                    <th class="px-4 py-2 text-center">Diferença (Qtd)</th>
                                    <th class="px-4 py-2 text-right">Diferença (R$)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($itensComDiferenca as $item)
                                    <tr class="border-b">
                                        <td class="px-4 py-2">{{ $item->produto->nome }}</td>
                                        <td class="px-4 py-2 text-center">{{ $item->estoque_esperado }}</td>
                                        <td class="px-4 py-2 text-center">{{ $item->quantidade_contada }}</td>
                                        <td class="px-4 py-2 text-center font-bold {{ $item->diferenca > 0 ? 'text-green-500' : 'text-red-500' }}">
                                            {{ ($item->diferenca > 0 ? '+' : '') . $item->diferenca }}
                                        </td>
                                        <td class="px-4 py-2 text-right font-bold {{ $item->diferenca > 0 ? 'text-green-500' : 'text-red-500' }}">
                                            R$ {{ number_format($item->diferenca * ($item->produto->preco_custo ?? 0), 2, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Nenhuma divergência foi encontrada neste inventário.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- O formulário de finalização foi removido daqui --}}
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700 text-right">
                        <a href="{{ route('inventarios.index') }}">
                            <x-secondary-button>
                                Voltar para a Lista
                            </x-secondary-button>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>