<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Pedidos de Venda
            </h2>
            <div class="space-x-2">
                <a href="{{ route('pedidos.importarOrcamento') }}">
                    <x-secondary-button>Importar Orçamento</x-secondary-button>
                </a>
                
                <a href="{{-- Rota para criar um novo pedido direto --}}">
                    <x-primary-button>Novo Pedido</x-primary-button>
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($pedidos as $pedido)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pedido->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pedido->cliente->nome ?? 'Consumidor Padrão' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">{{ $pedido->created_at->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">R$ {{ number_format($pedido->total, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($pedido->status == 'concluida')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Concluído</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pendente</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="#" class="text-indigo-600 hover:text-indigo-900">Ver</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhum pedido encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $pedidos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>