<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Histórico de Vendas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- ==================================================================== --}}
            {{-- |||||||||||||| INÍCIO: NOVA ESTRUTURA DO CARD |||||||||||||||| --}}
            {{-- A classe "overflow-x-auto" agora está aqui, no card principal --}}
            {{-- ==================================================================== --}}
            <div class="bg-white shadow-sm sm:rounded-lg overflow-x-auto">
                
                {{-- O formulário de filtro agora tem seu próprio padding e uma borda inferior --}}
                <div class="p-6 border-b border-gray-200">
                    <form method="GET" action="{{ route('vendas.index') }}" class="flex items-center gap-4">
                        <div class="flex-grow">
                            <label for="search" class="sr-only">Buscar</label>
                            <x-text-input id="search" name="search" class="w-full" placeholder="Digite sua busca..." value="{{ request('search') }}" />
                        </div>
                        <div>
                            <label for="filter_by" class="sr-only">Filtrar por</label>
                            <select id="filter_by" name="filter_by" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="venda_id" @selected(request('filter_by', 'venda_id') == 'venda_id')>ID da Venda</option>
                                <option value="cliente" @selected(request('filter_by') == 'cliente')>Cliente</option>
                                <option value="nfce" @selected(request('filter_by') == 'nfce')>NFC-e</option>
                                <option value="nfe" @selected(request('filter_by') == 'nfe')>NF-e</option>
                            </select>
                        </div>
                        <div>
                            <x-primary-button>Buscar</x-primary-button>
                        </div>
                    </form>
                </div>

                {{-- A tabela agora fica diretamente dentro do container de rolagem --}}
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Documentos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            {{-- O cabeçalho da coluna fixa agora tem um fundo branco para combinar --}}
                            <th class="sticky right-0 bg-white border-l border-gray-200 px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">
                                Ações
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($vendas as $venda)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1 text-center">
                                        <span class="px-2 py-1 text-xs leading-4 font-semibold rounded-full bg-gray-100 text-gray-800" title="ID da Venda">
                                            Venda #{{ $venda->id }}
                                        </span>
                                        @forelse ($venda->nfes as $nfe)
                                            @if ($nfe->modelo == 65)
                                                <span class="px-2 py-1 text-xs leading-4 font-semibold rounded-full bg-green-100 text-green-800" title="Cupom Fiscal de Consumidor">
                                                    NFC-e #{{ $nfe->numero_nfe }}
                                                </span>
                                            @elseif ($nfe->modelo == 55)
                                                <span class="px-2 py-1 text-xs leading-4 font-semibold rounded-full bg-blue-100 text-blue-800" title="Nota Fiscal Eletrônica">
                                                    NF-e #{{ $nfe->numero_nfe }}
                                                </span>
                                            @endif
                                        @empty
                                            <span class="px-2 py-1 text-xs leading-4 font-semibold rounded-full bg-yellow-100 text-yellow-800" title="Nenhum documento fiscal emitido para esta venda">
                                                Fiscal Pendente
                                            </span>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $venda->cliente->nome ?? 'Consumidor Padrão' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">{{ $venda->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($venda->status == 'concluida')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Concluída</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">{{ ucfirst($venda->status) }}</span>
                                    @endif
                                </td>
                                {{-- O conteúdo da coluna fixa também tem uma borda para separação visual --}}
                                <td class="sticky right-0 bg-white border-l border-gray-200 px-6 py-4 whitespace-nowrap text-sm font-medium text-center">
                                    <a href="{{ route('vendas.show', $venda) }}" class="text-indigo-600 hover:text-indigo-900 font-semibold">
                                        Ver Detalhes
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">Nenhuma venda encontrada para os filtros aplicados.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                
                {{-- A paginação também fica numa área com padding separada --}}
                @if ($vendas->hasPages())
                    <div class="p-6 border-t border-gray-200">
                        {{ $vendas->links() }}
                    </div>
                @endif

            </div>
            {{-- ==================================================================== --}}
            {{-- |||||||||||||| FIM: NOVA ESTRUTURA DO CARD |||||||||||||||| --}}
            {{-- ==================================================================== --}}
        </div>
    </div>
</x-app-layout>