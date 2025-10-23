<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">

        <form wire:submit.prevent="save">
            <div class="space-y-6">
                {{-- Nome --}}
                <div>
                    <label for="nome" class="block text-sm font-medium">Nome</label>
                    <input type="text" id="nome" wire:model="nome" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    @error('nome') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    {{-- Código --}}
                    <div>
                        <label for="codigo" class="block text-sm font-medium">Código</label>
                        <input type="text" id="codigo" wire:model="codigo" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                         @error('codigo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    {{-- Centro de Custo Pai --}}
                    <div>
                        <label for="parent_id" class="block text-sm font-medium">Centro de Custo Pai (Agrupador)</label>
                        <select id="parent_id" wire:model="parent_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            <option value="">Nenhum</option>
                            @foreach($paisDisponiveis as $pai)
                                <option value="{{ $pai->id }}">{{ $pai->nome }}</option>
                            @endforeach
                        </select>
                        @error('parent_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                </div>

                 {{-- Tipo --}}
                <div>
                    <label for="tipo" class="block text-sm font-medium">Tipo</label>
                    <select id="tipo" wire:model="tipo" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        <option value="ANALITICO">Analítico (Aceita Lançamentos)</option>
                        <option value="SINTETICO">Sintético (Apenas Agrupa)</option>
                    </select>
                </div>
                
                {{-- Checkboxes --}}
                <div class="flex space-x-6 items-center pt-4">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="aceita_despesas" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        <span class="ml-2 text-sm">Aceita Despesas</span>
                    </label>
                     <label class="flex items-center">
                        <input type="checkbox" wire:model="aceita_receitas" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        <span class="ml-2 text-sm">Aceita Receitas</span>
                    </label>
                     <label class="flex items-center">
                        <input type="checkbox" wire:model="ativo" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        <span class="ml-2 text-sm">Ativo</span>
                    </label>
                </div>

            </div>

            <div class="flex items-center justify-end mt-8 pt-6 border-t dark:border-gray-700">
                <a href="{{ route('centros-de-custo.index') }}" class="text-sm hover:underline">Cancelar</a>
                <button type="submit" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md">
                    Salvar
                </button>
            </div>
        </form>

    </div>
</div>