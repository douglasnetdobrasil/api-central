<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gerenciador de Notas Fiscais (NF-e)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Pendentes de Emissão</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ação</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($vendasParaEmitir as $venda)
                                    <tr>
                                        <td class="px-4 py-4">#{{ $venda->id }}</td>
                                        <td class="px-4 py-4">{{ $venda->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-4">{{ $venda->created_at->format('d/m/Y') }}</td>
                                        <td class="px-4 py-4">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-center">
                                            {{-- O formulário/botão que já criamos --}}
                                            <form action="{{ route('pedidos.emitirNFe', $venda) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja emitir a NF-e para este pedido?');">
                                                @csrf
                                                <button type="submit" class="font-medium text-blue-600 hover:text-blue-900">
                                                    Emitir NF-e
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum pedido pendente de emissão.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $vendasParaEmitir->appends(['emitidas' => $notasEmitidas->currentPage()])->links() }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-medium mb-4">Notas Emitidas</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nº NFe</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data Emissão</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($notasEmitidas as $nfe)
                                    <tr>
                                        <td class="px-4 py-4">{{ $nfe->numero_nfe }}</td>
                                        <td class="px-4 py-4">{{ $nfe->venda->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-4">{{ $nfe->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($nfe->status == 'autorizada') bg-green-100 text-green-800 @endif
                                                @if($nfe->status == 'erro') bg-red-100 text-red-800 @endif
                                                @if($nfe->status == 'cancelada') bg-yellow-100 text-yellow-800 @endif
                                                @if($nfe->status == 'processando') bg-blue-100 text-blue-800 @endif
                                            ">
                                                {{ ucfirst($nfe->status) }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center text-sm font-medium space-x-2">
                                            {{-- Links para futuras funcionalidades --}}
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">DANFE</a>
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">XML</a>
                                            <a href="#" class="text-red-600 hover:text-red-900">Cancelar</a>
                                        </td>
                                    </tr>
                                @empty
                                     <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhuma NF-e emitida ainda.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">{{ $notasEmitidas->appends(['pendentes' => $vendasParaEmitir->currentPage()])->links() }}</div>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>