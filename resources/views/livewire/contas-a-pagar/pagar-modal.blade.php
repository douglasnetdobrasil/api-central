<div>
    @if($mostrarModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6 m-4" @click.away="$wire.mostrarModal = false">
            <h3 class="text-xl font-bold dark:text-gray-100 mb-4">Registar Pagamento</h3>

            @if($conta)
                <div class="mb-4 p-3 bg-gray-100 dark:bg-gray-700 rounded">
                    <p>{{ $conta->descricao }}</p>
                    <p class="font-semibold">Valor Restante: R$ {{ number_format($valorRestante, 2, ',', '.') }}</p>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="valorAPagar" class="block text-sm">Valor a Pagar</label>
                        <input type="text" wire:model="valorAPagar" id="valorAPagar" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        @error('valorAPagar') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label for="dataPagamento" class="block text-sm">Data do Pagamento</label>
                        <input type="date" wire:model="dataPagamento" id="dataPagamento" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        @error('dataPagamento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                     <div>
                        <label for="formaPagamentoId" class="block text-sm">Forma de Pagamento</label>
                        <select wire:model="formaPagamentoId" id="formaPagamentoId" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            @foreach($formasPagamento as $forma)
                            <option value="{{ $forma->id }}">{{ $forma->nome }}</option>
                            @endforeach
                        </select>
                         @error('formaPagamentoId') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>
            @endif

            <div class="flex justify-end items-center mt-6 pt-4 border-t dark:border-gray-700">
                <button type="button" @click="$wire.mostrarModal = false" class="text-gray-600 dark:text-gray-400">Cancelar</button>
                <button type="button" wire:click="salvarPagamento" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md">Salvar Pagamento</button>
            </div>
        </div>
    </div>
    @endif
</div>