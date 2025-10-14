<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Histórico de Movimentação: {{ $produto->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <a href="{{ route('relatorios.estoque.index') }}" class="text-indigo-600 hover:text-indigo-900 mb-4 inline-block no-print">
                        &larr; Voltar para o relatório de estoque
                    </a>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-2 text-left">Data</th>
                                    <th class="px-4 py-2 text-left">Tipo</th>
                                    <th class="px-4 py-2 text-center">Quantidade</th>
                                    <th class="px-4 py-2 text-center">Saldo Anterior</th>
                                    <th class="px-4 py-2 text-center">Saldo Novo</th>
                                    <th class="px-4 py-2 text-left">Origem</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($movimentacoes as $movimento)
                                    <tr class="border-b dark:border-gray-700">
                                        <td class="px-4 py-2">{{ $movimento->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2">{{ $movimento->tipo_movimento }}</td>
                                        <td class="px-4 py-2 text-center font-bold {{ $movimento->quantidade > 0 ? 'text-green-500' : 'text-red-500' }}">
                                            {{ $movimento->quantidade > 0 ? '+' : '' }}{{ $movimento->quantidade }}
                                        </td>
                                        <td class="px-4 py-2 text-center">{{ $movimento->saldo_anterior }}</td>
                                        <td class="px-4 py-2 text-center">{{ $movimento->saldo_novo }}</td>
                                        <td class="px-4 py-2">
                                            @if($movimento->origem_type === 'App\Models\Venda')
                                                Venda #{{ $movimento->origem_id }}
                                            @elseif($movimento->origem_type === 'App\Models\Compra')
                                                Compra #{{ $movimento->origem_id }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">Nenhuma movimentação encontrada.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $movimentacoes->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>