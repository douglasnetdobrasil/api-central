<div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
    <form wire:submit.prevent="save">
        <div class="space-y-4">
            <div>
                <label for="descricao">Descrição</label>
                <input type="text" id="descricao" wire:model="descricao" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                @error('descricao') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="cliente_id">Cliente</label>
                <select wire:model="cliente_id" id="cliente_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    <option value="">Selecione um cliente</option>
                    @foreach($clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nome }}</option>
                    @endforeach
                </select>
                @error('cliente_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            
            {{-- << ALTERADO >>: A grade agora tem 4 colunas para acomodar o novo campo --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="valor_total">Valor Total (R$)</label>
                    <input type="number" step="0.01" id="valor_total" wire:model="valor_total" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    @error('valor_total') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="data_vencimento">Venc. da 1ª Parcela</label>
                    <input type="date" id="data_vencimento" wire:model="data_vencimento" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    @error('data_vencimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label for="numero_parcelas">Nº de Parcelas</label>
                    <input type="number" id="numero_parcelas" wire:model="numero_parcelas" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    @error('numero_parcelas') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                {{-- << NOVO CAMPO >> --}}
                <div>
                    <label for="dias_intervalo">Intervalo (dias)</label>
                    <input type="number" id="dias_intervalo" wire:model="dias_intervalo" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    @error('dias_intervalo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
        </div>
        <div class="flex justify-end mt-6">
            <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md">Salvar</button>
        </div>
    </form>
</div>