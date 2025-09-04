<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Importar Orçamento para Venda
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form method="GET" action="{{ route('pedidos.importarOrcamento') }}" class="mb-6">
                        <div class="flex items-center">
                            <x-text-input name="search" class="flex-grow" placeholder="Buscar por Nº do Orçamento ou Nome do Cliente..." value="{{ request('search') }}" />
                            <x-primary-button class="ml-2">Buscar</x-primary-button>
                        </div>
                    </form>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($orcamentos as $orcamento)
                                    <tr>
                                        <td class="px-4 py-4">#{{ $orcamento->id }}</td>
                                        <td class="px-4 py-4">{{ $orcamento->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-4">{{ $orcamento->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-4">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-center">
                                            <form action="{{ route('orcamentos.converterVenda', $orcamento) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja converter este orçamento em uma venda? O estoque será baixado.');">
                                                @csrf
                                                <button type="submit" class="text-sm font-medium text-green-600 hover:text-green-900">
                                                    Importar e Gerar Venda
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                            Nenhum orçamento pendente encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $orcamentos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>