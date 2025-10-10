<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Detalhes da Venda #{{ $venda->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Informações Gerais</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <p><strong>Cliente:</strong> {{ $venda->cliente->nome ?? 'Consumidor não identificado' }}</p>
                        <p><strong>Data:</strong> {{ $venda->created_at->format('d/m/Y H:i') }}</p>
                        <p><strong>Subtotal:</strong> R$ {{ number_format($venda->subtotal, 2, ',', '.') }}</p>
                        <p><strong>Desconto:</strong> R$ {{ number_format($venda->desconto, 2, ',', '.') }}</p>
                        <p class="font-bold"><strong>Total:</strong> R$ {{ number_format($venda->total, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>
            
            {{-- ========================================================== --}}
            {{-- |||||||||||||||||| NOVO CARD: ITENS DA VENDA |||||||||||||||| --}}
            {{-- ========================================================== --}}
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Itens da Venda</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Qtd.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Preço Unit.</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($venda->items as $item)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $item->descricao_produto }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-center">{{ $item->quantidade }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap text-right">R$ {{ number_format($item->subtotal_item, 2, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Documentos Fiscais Gerados</h3>
                    @forelse($venda->nfes as $nfe)
                        <div class="flex justify-between items-center border-b py-2 last:border-b-0">
                            <div>
                                <p class="font-semibold">
                                    {{ $nfe->modelo == 65 ? 'NFC-e (Cupom)' : 'NF-e' }} Nº {{ $nfe->numero_nfe }}
                                </p>
                                <p class="text-sm text-gray-600">Status: <span class="font-mono uppercase">{{ $nfe->status }}</span></p>
                            </div>
                            <a href="{{ route('nfe.danfe', $nfe) }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-semibold">
                                Reimprimir
                            </a>
                        </div>
                    @empty
                        <p class="text-gray-500">Nenhum documento fiscal foi gerado para esta venda.</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium mb-4">Auditoria de Estoque</h3>
                    @if($venda->movimentosEstoque->isEmpty())
                        <p class="text-yellow-600 bg-yellow-50 p-3 rounded-md">Atenção: Nenhuma movimentação de estoque foi registrada para esta venda.</p>
                    @else
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantidade</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($venda->movimentosEstoque as $movimento)
                                    <tr>
                                        <td class="px-4 py-2">{{ $movimento->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-2">{{ $movimento->produto->nome ?? 'Produto não encontrado' }}</td>
                                        <td class="px-4 py-2 font-bold text-red-600">{{ $movimento->quantidade }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>