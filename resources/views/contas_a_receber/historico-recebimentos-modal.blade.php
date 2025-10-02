<div>
    @if ($mostrarModal && $conta)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75" x-data>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 md:p-8 w-full max-w-2xl" @click.away="$wire.set('mostrarModal', false)">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Histórico de Recebimentos (Conta #{{ $conta->id }})
                </h3>
                
                {{-- Resumo da Conta --}}
                <div class="grid grid-cols-3 gap-4 mb-4 text-center">
                    <div class="bg-gray-100 dark:bg-gray-700 p-2 rounded">
                        <span class="text-sm dark:text-gray-400 block">Valor Total</span>
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
                
                {{-- Tabela do Histórico --}}
                <div class="overflow-y-auto max-h-80">
                    <table class="min-w-full">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-2 text-left">Data</th>
                                <th class="px-4 py-2 text-left">Forma Pgto.</th>
                                <th class="px-4 py-2 text-right">Valor Recebido</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y dark:divide-gray-700">
                            @forelse($recebimentos as $recebimento)
                                <tr>
                                    <td class="px-4 py-3">{{ $recebimento->data_recebimento->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3">{{ $recebimento->formaPagamento->nome ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-right">R$ {{ number_format($recebimento->valor_recebido, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center py-6">Nenhum recebimento registrado.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                    <button type="button" @click="$wire.set('mostrarModal', false)" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Fechar</button>
                </div>
            </div>
        </div>
    @endif
</div>