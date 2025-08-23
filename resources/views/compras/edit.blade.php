<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Conferência e Entrada da Nota de Compra #{{ $compra->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Sucesso!</strong>
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                    Dados Gerais e Financeiros da Nota
                </h3>
                <div class="flex flex-wrap gap-x-12 gap-y-6">
                    <div class="flex-1 min-w-[250px]"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Fornecedor</span><p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $compra->fornecedor->razao_social }}</p></div>
                    <div class="flex-1 min-w-[150px]"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nº da Nota</span><p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $compra->numero_nota }}</p></div>
                    <div class="flex-1 min-w-[150px]"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Valor Total</span><p class="mt-1 text-lg text-gray-900 dark:text-gray-100">R$ {{ number_format($compra->valor_total_nota, 2, ',', '.') }}</p></div>
                    <div class="flex-1 min-w-[150px]"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Forma de Pagamento</span><p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $compra->forma_pagamento ?? 'N/A' }}</p></div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                     <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Chave de Acesso</span>
                     <p class="mt-1 text-sm font-mono text-gray-800 dark:text-gray-200 tracking-tight">{{ $compra->chave_acesso_nfe }}</p>
                </div>
            </div>


            <form action="{{ route('compras.update', $compra->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                        Itens da Nota para Conferência
                    </h3>
                    <div class="space-y-6">
                        @foreach ($compra->itens as $item)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">

                            {{-- Container Flex para Nome e Valores --}}
                            <div class="flex justify-between items-start gap-4">
                                {{-- Nome do Produto (ocupa a maior parte do espaço) --}}
                                <div class="flex-grow">
                                    <span class="block text-xs font-bold text-indigo-600 dark:text-indigo-400">
                                        ITEM #{{ $loop->iteration }}
                                    </span>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $item->descricao_item_nota }}</p>
                                </div>

                                {{-- Container para Preço, Qtd e Subtotal --}}
                                {{-- AJUSTADO: Aumentei o espaçamento de 'space-x-6 md:space-x-8' para 'space-x-8 md:space-x-12' --}}
                                <div class="flex items-center space-x-8 md:space-x-12 text-right flex-shrink-0">
                                    <div>
                                        <span class="block text-xs font-medium text-gray-500">PREÇO UNIT. (NOTA)</span>
                                        <p class="text-md font-semibold">R$ {{ number_format($item->preco_custo_nota, 2, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-medium text-gray-500">QTD</span>
                                        <p class="text-md font-semibold">{{ number_format($item->quantidade, 2, ',', '.') }}</p>
                                    </div>
                                    <div>
                                        <span class="block text-xs font-medium text-gray-500">SUBTOTAL</span>
                                        <p class="text-md font-semibold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</p>
                                    </div>
                                </div>
                            </div>

                            {{-- Dados Fiscais --}}
                            {{-- AJUSTADO: Diminuí o espaçamento de 'gap-4' para 'gap-x-6' (horizontal) e 'gap-y-4' (vertical) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 py-2">
                                <div>
                                    <x-input-label value="NCM" />
                                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $item->ncm ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <x-input-label value="CFOP" />
                                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $item->cfop ?? 'N/A' }}</p>
                                </div>
                                <div>
                                    <x-input-label value="EAN" />
                                    <p class="mt-1 text-sm text-gray-800 dark:text-gray-200">{{ $item->ean ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Campos Editáveis --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div>
                                    <x-input-label for="preco_entrada_{{ $item->id }}" value="Preço de Venda (Consumidor Final)" />
                                    <x-text-input type="number" step="0.01" name="itens[{{ $item->id }}][preco_entrada]" id="preco_entrada_{{ $item->id }}" class="block mt-1 w-full" value="{{ old('itens.'.$item->id.'.preco_entrada', number_format($item->preco_custo_nota, 2, '.', '')) }}" />
                                </div>
                                <div>
                                    <x-input-label for="produto_{{ $item->id }}" value="Vincular ao Produto do Sistema" />
                                    <select name="itens[{{ $item->id }}][produto_id]" id="produto_{{ $item->id }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                        <option value="">Selecione um produto...</option>
                                        @foreach ($produtos as $produto)
                                            <option value="{{ $produto->id }}" @selected(old('itens.'.$item->id.'.produto_id', $item->produto_id) == $produto->id)>
                                                {{ $produto->nome }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-end gap-4">
                        {{-- O botão de remover foi movido para fora do formulário principal para evitar submissões aninhadas --}}
                        <div id="delete-button-container">
                            <form action="{{ route('compras.destroy', $compra->id) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta nota da digitação? Esta ação não pode ser desfeita.');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button type="submit">
                                    Remover Nota
                                </x-danger-button>
                            </form>
                        </div>

                        <x-primary-button>
                            Salvar Alterações e Finalizar
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>