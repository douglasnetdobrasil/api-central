<div>
    @if($mostrarModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center" 
         x-data="{ show: @entangle('mostrarModal') }" 
         x-show="show" 
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-md p-6 m-4" @click.away="show = false">
            <h3 class="text-xl font-bold dark:text-gray-100 mb-4">Criar Nova Categoria</h3>
            
            <form wire:submit.prevent="salvarCategoria">
                <div class="space-y-4">
                    {{-- Nome da Categoria --}}
                    <div>
                        <label for="modal-nome" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome</label>
                        <input type="text" wire:model.defer="nome" id="modal-nome" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        @error('nome') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Grupo (Categoria Pai) --}}
                    <div>
                        <label for="modal-parent_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Grupo (Opcional)</label>
                        <select wire:model.defer="parent_id" id="modal-parent_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            <option value="">Nenhum</option>
                            @if($categoriasPai)
                                @foreach($categoriasPai as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->nome }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Cor --}}
                    <div>
                        <label for="modal-cor" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cor</label>
                        <input type="color" wire:model.defer="cor" id="modal-cor" class="mt-1 block w-full rounded-md h-10">
                        @error('cor') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end items-center mt-6 pt-4 border-t dark:border-gray-700">
                    <button type="button" @click="show = false" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</button>
                    <button type="submit" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md">
                        Salvar Categoria
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>