<div>
    @if ($mostrarModal && $conta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75" x-data>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 md:p-8 w-full max-w-3xl" @click.away="$wire.set('mostrarModal', false)">
                <h3 class="text-xl font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Extrato da Conta a Receber #{{ $conta->id }}
                </h3>
                
                {{-- Resumo Financeiro --}}
                <div class="grid grid-cols-3 gap-4 mb-6 text-center">
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                        <span class="text-sm dark:text-gray-400 block">Valor Original</span>
                        <span class="font-bold dark:text-gray-200">R$ {{ number_format($conta->valor, 2, ',', '.') }}</span>
                    </div>
                    <div class="bg-blue-100 dark:bg-blue-900 p-2 rounded">
                        <span class="text-sm text-blue-500 dark:text-blue-400 block">Total Recebido</span>
                        <span class="font-bold text-blue-800 dark:text-blue-200">R$ {{ number_format($conta->valor_recebido, 2, ',', '.') }}</span>
                    </div>
                    <div class="bg-red-100 dark:bg-red-900 p-2 rounded">
                        <span class="text-sm text-red-500 dark:text-red-400 block">Pendente</span>
                        <span class="font-bold text-red-800 dark:text-red-200">R$ {{ number_format($conta->valor_pendente, 2, ',', '.') }}</span>
                    </div>
                </div>

                <div class="space-y-6 overflow-y-auto max-h-[60vh]">
                    {{-- << SEÇÃO DE DETALHES DA ORIGEM (NOVA) >> --}}
                    <div>
                        <h4 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-3">Origem da Conta</h4>
                        @if($conta->venda)
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <p><strong>Origem:</strong> Venda #{{ $conta->venda->id }}</p>
                                <p><strong>Data da Venda:</strong> {{ $conta->venda->created_at->format('d/m/Y') }}</p>
                                <p><strong>Vendedor:</strong> {{ $conta->venda->user->name ?? 'N/A' }}</p>
                            </div>

                            <h5 class="font-semibold mt-4 mb-2">Produtos da Venda:</h5>
                            <table class="min-w-full text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900">
                                    <tr>
                                        <th class="px-3 py-2 text-left">Produto</th>
                                        <th class="px-3 py-2 text-center">Qtd</th>
                                        <th class="px-3 py-2 text-right">Vlr. Unit.</th>
                                        <th class="px-3 py-2 text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y dark:divide-gray-700">
                                    @foreach($conta->venda->items as $item)
                                        <tr>
                                            <td class="px-3 py-2">{{ $item->produto->nome ?? $item->descricao_produto }}</td>
                                            <td class="px-3 py-2 text-center">{{ $item->quantidade }}</td>
                                            <td class="px-3 py-2 text-right">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                                            <td class="px-3 py-2 text-right">R$ {{ number_format($item->subtotal_item, 2, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <p class="text-sm"><strong>Origem:</strong> Lançamento Manual</p>
                            <p class="text-sm"><strong>Descrição:</strong> {{ $conta->descricao }}</p>
                        @endif
                    </div>
                
                    {{-- Seção de Histórico de Pagamentos (Mantida) --}}
                    <div>
                        <h4 class="text-lg font-semibold border-b dark:border-gray-700 pb-2 mb-3">Histórico de Recebimentos</h4>
                        <table class="min-w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-900">
                                <tr>
                                    <th class="px-3 py-2 text-left">Data</th>
                                    <th class="px-3 py-2 text-left">Forma Pgto.</th>
                                    <th class="px-3 py-2 text-right">Valor Recebido</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y dark:divide-gray-700">
                                @forelse($recebimentos as $recebimento)
                                    <tr>
                                        <td class="px-3 py-2">{{ $recebimento->data_recebimento->format('d/m/Y') }}</td>
                                        <td class="px-3 py-2">{{ $recebimento->formaPagamento->nome ?? 'N/A' }}</td>
                                        <td class="px-3 py-2 text-right">R$ {{ number_format($recebimento->valor_recebido, 2, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-6 text-gray-500">Nenhum recebimento registrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                    <button type="button" @click="$wire.set('mostrarModal', false)" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Fechar</button>
                </div>
            </div>
        </div>
    @endif
</div>