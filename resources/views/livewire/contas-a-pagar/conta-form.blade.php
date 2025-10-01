<div>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
        <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
            
            <form wire:submit.prevent="save">
                @csrf
                <div class="space-y-6">
                    {{-- Descrição --}}
                    <div>
                        <label for="descricao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                        <input type="text" wire:model.defer="descricao" id="descricao" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        @error('descricao') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Fornecedor --}}
                        <div>
                            <label for="fornecedor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fornecedor (Opcional)</label>
                            <select wire:model.defer="fornecedor_id" id="fornecedor_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                <option value="">Nenhum fornecedor</option>
                                @foreach($fornecedores as $fornecedor)
                                    <option value="{{ $fornecedor->id }}">{{ $fornecedor->razao_social }}</option>
                                @endforeach
                            </select>
                            @error('fornecedor_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Categoria com Botão + --}}
                        <div>
                            <label for="categoria_conta_a_pagar_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoria (Opcional)</label>
                            <div class="flex items-center space-x-2 mt-1">
                            <select wire:model.defer="categoria_conta_a_pagar_id" id="categoria_conta_a_pagar_id" class="block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                    <option value="">Nenhuma categoria</option>
                                    @foreach($formattedCategorias as $categoria)
                                        <option value="{{ $categoria['id'] }}">{{ $categoria['nome'] }}</option>
                                    @endforeach
                                </select>
                                <button type="button" wire:click="$dispatch('abrirModalCriarCategoria')" class="p-2 bg-indigo-600 text-white rounded-md flex-shrink-0" title="Criar Nova Categoria">
                                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" /></svg>
                                </button>
                            </div>
                            @error('categoria_conta_a_pagar_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        {{-- Número do Documento --}}
                        <div>
                            <label for="numero_documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nº do Documento</label>
                            <input type="text" wire:model.defer="numero_documento" id="numero_documento" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            @error('numero_documento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        {{-- Data de Emissão --}}
                        <div>
                            <label for="data_emissao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Emissão</label>
                            <input type="date" wire:model.defer="data_emissao" id="data_emissao" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                             @error('data_emissao') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        {{-- Data de Vencimento --}}
                        <div>
                            <label for="data_vencimento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Vencimento</label>
                            <input type="date" wire:model.defer="data_vencimento" id="data_vencimento" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            @error('data_vencimento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                    </div>
                    
                     {{-- Valor Total --}}
                    <div>
                        <label for="valor_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor Total (R$)</label>
                        <input type="number" step="0.01" wire:model.defer="valor_total" id="valor_total" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        @error('valor_total') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>

                    {{-- Checkbox "Já foi paga?" --}}
<div class="mt-6 border-t dark:border-gray-700 pt-6">
    <label class="flex items-center">
        <input type="checkbox" wire:model.live="foiPaga" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500">
        <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Esta conta já foi paga?</span>
    </label>
</div>

{{-- Campos de Pagamento (aparecem se o checkbox for marcado) --}}
@if($foiPaga)
<div class="mt-6 space-y-6 animate-fade-in">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
            <label for="data_pagamento" class="block text-sm font-medium">Data do Pagamento</label>
            <input type="date" wire:model.defer="data_pagamento" id="data_pagamento" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
            @error('data_pagamento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
        <div>
            <label for="forma_pagamento_id" class="block text-sm font-medium">Forma de Pagamento</label>
            <select wire:model.defer="forma_pagamento_id" id="forma_pagamento_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                @foreach($formasPagamento as $forma)
                <option value="{{ $forma->id }}">{{ $forma->nome }}</option>
                @endforeach
            </select>
            @error('forma_pagamento_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>
    </div>
</div>
@endif
                    
                    {{-- Observações --}}
                    <div>
                        <label for="observacoes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</label>
                        <textarea wire:model.defer="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"></textarea>
                        @error('observacoes') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end mt-8 pt-6 border-t dark:border-gray-700">
                    <a href="{{ route('contas_a_pagar.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                    <button type="submit" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md">
                        Salvar
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    {{-- Inclui o componente do modal, que ficará escondido até ser chamado --}}
 @livewire('contas-a-pagar.categoria-quick-create')
</div>