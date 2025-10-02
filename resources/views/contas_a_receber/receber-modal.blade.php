<div>
    @if ($mostrarModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-800 bg-opacity-75" x-data>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 md:p-8 w-full max-w-lg" @click.away="$wire.set('mostrarModal', false)">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Registrar Recebimento (Conta #{{ $conta->id }})
                </h3>
                <p class="text-sm dark:text-gray-400 mb-2">Valor Pendente: <span class="font-bold">R$ {{ number_format($valorPendente, 2, ',', '.') }}</span></p>

                <form wire:submit.prevent="salvarRecebimento">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="dataRecebimento" class="block text-sm font-medium">Data do Recebimento</label>
                                <input type="date" wire:model="dataRecebimento" id="dataRecebimento" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                @error('dataRecebimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                            <div>
                                <label for="formaPagamentoId" class="block text-sm font-medium">Forma de Pagamento</label>
                                <select wire:model="formaPagamentoId" id="formaPagamentoId" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                    @foreach($formasPagamento as $forma)
                                        <option value="{{ $forma->id }}">{{ $forma->nome }}</option>
                                    @endforeach
                                </select>
                                @error('formaPagamentoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div>
                            <label for="valorAReceber" class="block text-sm font-medium">Valor a Receber (R$)</label>
                            <input type="text" wire:model="valorAReceber" id="valorAReceber" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="0,00">
                            @error('valorAReceber') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                             <div>
                                <label for="juros" class="block text-sm font-medium">Juros (R$)</label>
                                <input type="text" wire:model="juros" id="juros" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="0,00">
                            </div>
                             <div>
                                <label for="multa" class="block text-sm font-medium">Multa (R$)</label>
                                <input type="text" wire:model="multa" id="multa" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="0,00">
                            </div>
                             <div>
                                <label for="desconto" class="block text-sm font-medium">Desconto (R$)</label>
                                <input type="text" wire:model="desconto" id="desconto" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="0,00">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-end mt-6 pt-4 border-t dark:border-gray-700 space-x-4">
                        <button type="button" @click="$wire.set('mostrarModal', false)" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</button>
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">Salvar Recebimento</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>