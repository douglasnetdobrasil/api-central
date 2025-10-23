<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
    Serviços Prestados
</h3>

<div class="p-4 border border-gray-200 dark:border-gray-700 rounded-md">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-start">
        <div class="md:col-span-2">
            <label for="servico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Serviço *</label>
            <select wire:model="servico_id" id="servico_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                <option value="">Buscar serviço...</option>
                @foreach ($servicosDisponiveis as $servico)
                    <option value="{{ $servico->id }}">{{ $servico->nome }}</option>
                @endforeach
            </select>
            @error('servico_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="servico_tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Executado por</label>
            <select wire:model="servico_tecnico_id" id="servico_tecnico_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                <option value="">(Não especificado)</option>
                @foreach ($tecnicos as $tecnico)
                    <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="servico_quantidade" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Qtd/Horas *</label>
            <input type="number" wire:model="servico_quantidade" id="servico_quantidade" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
            @error('servico_quantidade') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>
        <div class="pt-6">
            <button type="button" wire:click="addServico" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest">
                Adicionar Serviço
            </button>
        </div>
    </div>
</div>