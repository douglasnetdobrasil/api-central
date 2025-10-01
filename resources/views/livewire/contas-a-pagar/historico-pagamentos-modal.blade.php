<div>
    @if($mostrarModal && $conta)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center" 
         x-data="{ show: @entangle('mostrarModal') }" x-show="show" x-transition>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl p-6 m-4" @click.away="show = false">
            <div class="flex justify-between items-center pb-3 border-b dark:border-gray-700">
                <h3 class="text-xl font-bold dark:text-gray-100">Histórico de Pagamentos</h3>
                <button @click="show = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>

            <div class="my-4">
                <p class="dark:text-gray-300"><span class="font-semibold">Conta:</span> {{ $conta->descricao }}</p>
                <p class="dark:text-gray-300"><span class="font-semibold">Valor Total:</span> R$ {{ number_format($conta->valor_total, 2, ',', '.') }}</p>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y dark:divide-gray-700">
                    <thead>
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-medium dark:text-gray-200">Data</th>
                            <th class="px-4 py-2 text-left text-sm font-medium dark:text-gray-200">Forma Pagamento</th>
                            <th class="px-4 py-2 text-right text-sm font-medium dark:text-gray-200">Valor Pago</th>
                        </tr>
                    </thead>
                    <tbody class="dark:bg-gray-800 divide-y dark:divide-gray-700">
                        @forelse($pagamentos as $pagamento)
                            <tr>
                                <td class="px-4 py-3 dark:text-gray-300">{{ \Carbon\Carbon::parse($pagamento->data_pagamento)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 dark:text-gray-300">{{ $pagamento->formaPagamento->nome ?? 'N/D' }}</td>
                                <td class="px-4 py-3 text-right dark:text-gray-300">R$ {{ number_format($pagamento->valor, 2, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-gray-500">Nenhum pagamento registrado para esta conta.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-bold bg-gray-50 dark:bg-gray-700">
                            <td colspan="2" class="px-4 py-2 text-right dark:text-gray-100">Total Pago:</td>
                            <td class="px-4 py-2 text-right dark:text-gray-100">R$ {{ number_format($conta->valor_pago, 2, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="flex justify-end items-center mt-6 pt-4 border-t dark:border-gray-700">
                <button type="button" @click="show = false" class="bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded-md">Fechar</button>
            </div>
        </div>
    </div>
    @endif
</div>