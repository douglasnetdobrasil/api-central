<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
    Peças e Produtos Utilizados
</h3>

<div class="p-4 border border-gray-200 dark:border-gray-700 rounded-md">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
        {{-- Dropdown de Produtos --}}
        <div class="md:col-span-2">
            <label for="peca_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Produto/Peça *</label>
            {{-- O wire:model="peca_id" conecta este campo à propriedade $peca_id no componente Livewire --}}
            <select wire:model="peca_id" id="peca_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                <option value="">Buscar peça ou produto...</option>
                @foreach ($pecasDisponiveis as $peca)
                    <option value="{{ $peca->id }}">
                        {{ $peca->nome }} (Estoque: {{ (float)$peca->estoque_atual }})
                    </option>
                @endforeach
            </select>
            @error('peca_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>
        
        {{-- Campo de Quantidade --}}
        <div>
            <label for="peca_quantidade" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Quantidade *</label>
            <input type="number" wire:model="peca_quantidade" id="peca_quantidade" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
            @error('peca_quantidade') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
        </div>
        
        {{-- Botão de Adicionar --}}
        <div class="pt-6">
             {{-- O wire:click="addPeca" chama o método addPeca() no componente, sem recarregar a página --}}
            <button type="button" wire:click="addPeca" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest">
                Adicionar Peça
            </button>
        </div>
    </div>
</div>