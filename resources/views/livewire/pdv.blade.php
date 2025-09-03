<div class="flex flex-col h-[calc(100vh-4.5rem)] bg-gray-100 dark:bg-gray-900">

    {{-- =============================================================== --}}
    {{-- Bloco Superior: Cliente (Sem alterações)                       --}}
    {{-- =============================================================== --}}
    <div class="flex-shrink-0 bg-white dark:bg-gray-800 shadow-md p-4">
        {{-- O CÓDIGO DO BLOCO DO CLIENTE CONTINUA IGUAL --}}
        <div class="max-w-7xl mx-auto">
            @if ($clienteSelecionado)
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Cliente Selecionado:</h2>
                        <p class="text-gray-800 dark:text-gray-200"><span class="font-bold">{{ $clienteSelecionado->nome ?? $clienteSelecionado->cpf_cnpj }}</span></p>
                    </div>
                    <x-secondary-button wire:click="removerCliente">Trocar Cliente</x-secondary-button>
                </div>
            @else
                <div class="relative">
                    <x-input-label for="cliente_search" value="Buscar Cliente" />
                    <x-text-input 
                        id="cliente_search" type="text" class="mt-1 block w-full" 
                        placeholder="Digite o ID, nome ou CPF/CNPJ..." 
                        wire:model.live.debounce.300ms="clienteSearch"
                        x-on:keydown.arrow-down.prevent="$wire.set('highlightClienteIndex', Math.min($wire.get('highlightClienteIndex') + 1, {{ count($clientesEncontrados) - 1 }}))"
                        x-on:keydown.arrow-up.prevent="$wire.set('highlightClienteIndex', Math.max($wire.get('highlightClienteIndex') - 1, 0))"
                        x-on:keydown.enter.prevent="$wire.selecionarClienteComEnter()"
                        />
                    @if(!empty($clientesEncontrados) && strlen($clienteSearch) > 0)
                        <ul class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto">
                            @foreach($clientesEncontrados as $index => $cliente)
                                <li wire:key="cliente-{{ $cliente->id }}" 
                                    class="px-4 py-2 cursor-pointer {{ $highlightClienteIndex === $index ? 'bg-blue-500 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-600' }}" 
                                    wire:click="selecionarCliente({{ $cliente->id }})">
                                    {{ $cliente->nome }} ({{$cliente->cpf_cnpj}})
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- Bloco Central: Produtos (Com botões de qtd menores)            --}}
    {{-- =============================================================== --}}
    <div class="flex-grow p-4 overflow-y-auto">
        {{-- O CÓDIGO DA BUSCA DE PRODUTOS CONTINUA IGUAL --}}
        <div class="max-w-7xl mx-auto">
            <div class="relative mb-4">
                <x-input-label for="produto_search" value="Adicionar Produto" />
                <x-text-input 
                    id="produto_search" type="text" class="mt-1 block w-full" 
                    placeholder="Digite o ID, nome ou código de barras..." 
                    wire:model.live.debounce.300ms="produtoSearch"
                    x-on:keydown.arrow-down.prevent="$wire.set('highlightProdutoIndex', Math.min($wire.get('highlightProdutoIndex') + 1, {{ count($produtosEncontrados) - 1 }}))"
                    x-on:keydown.arrow-up.prevent="$wire.set('highlightProdutoIndex', Math.max($wire.get('highlightProdutoIndex') - 1, 0))"
                    x-on:keydown.enter.prevent="$wire.selecionarProdutoComEnter()"
                    />
                @if(!empty($produtosEncontrados) && strlen($produtoSearch) > 0)
                    <ul class="absolute z-20 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg max-h-60 overflow-y-auto">
                        @foreach($produtosEncontrados as $index => $produto)
                        <li wire:key="produto-{{ $produto->id }}"
    class="px-4 py-2 cursor-pointer {{ $highlightProdutoIndex === $index ? 'bg-blue-500 text-white' : 'hover:bg-gray-100 dark:hover:bg-gray-600' }}" 
    wire:click="adicionarProduto({{ $produto->id }})">
    {{ $produto->nome }} <span class="text-xs">(Estoque: {{ $produto->estoque_atual ?? 0 }})</span> {{-- <-- CORRIGIDO --}}
</li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase w-32">Qtd</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preço Un.</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($cart as $index => $item)
                            <tr wire:key="cart-item-{{ $index }}">
                                <td class="px-4 py-4 whitespace-nowrap">{{ $item['nome'] }} <span class="text-xs text-gray-500">(Estoque: {{ $item['estoque_atual'] }})</span></td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    {{-- BOTÕES DE QUANTIDADE MENORES --}}
                                    <div class="flex items-center">
                                        <button wire:click="diminuirQuantidade({{ $index }})" class="p-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-l">-</button>
                                        <span class="px-3 py-1 text-sm bg-white dark:bg-gray-800 border-t border-b">{{ $item['quantidade'] }}</span>
                                        <button wire:click="aumentarQuantidade({{ $index }})" class="p-1 text-sm bg-gray-200 dark:bg-gray-700 rounded-r">+</button>
                                    </div>
                                </td>
                                <td class="px-4 py-4 whitespace-nowrap">R$ {{ number_format($item['preco'], 2, ',', '.') }}</td>
                                <td class="px-4 py-4 whitespace-nowrap font-bold">R$ {{ number_format($item['total_item'], 2, ',', '.') }}</td>
                                <td class="px-4 py-4 whitespace-nowrap">
                                    <button wire:click="removerProduto({{ $index }})" class="text-red-500 hover:text-red-700 text-sm">Remover</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">Nenhum produto adicionado à venda.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
    {{-- Bloco Inferior: Com botão para mostrar o desconto              --}}
    {{-- =============================================================== --}}
    <div class="flex-shrink-0 bg-white dark:bg-gray-800 shadow-lg p-4">
         <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6 items-center">
            <div class="md:col-span-1">
                <x-input-label for="observacoes" value="Observações (Opcional)" />
                <textarea id="observacoes" wire:model.live="observacoes" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm"></textarea>
            </div>
            
            <div class="md:col-span-1 text-right space-y-2">
                 <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400">Subtotal:</span>
                    <span class="font-semibold text-gray-800 dark:text-gray-200">R$ {{ number_format($subtotal, 2, ',', '.') }}</span>
                </div>

                {{-- LÓGICA DO BOTÃO DE DESCONTO --}}
                @if ($showDesconto)
                    <div class="flex justify-between items-center">
                        <div class="flex items-center">
                            <span class="text-gray-500 dark:text-gray-400 mr-2">Desconto:</span>
                            <div class="flex rounded-md shadow-sm" role="group">
                                <button type="button" wire:click="definirTipoDesconto('valor')" class="px-3 py-1 text-sm font-medium {{ $tipoDesconto == 'valor' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700' }} border border-gray-200 dark:border-gray-600 rounded-l-lg">R$</button>
                                <button type="button" wire:click="definirTipoDesconto('percentual')" class="px-3 py-1 text-sm font-medium {{ $tipoDesconto == 'percentual' ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-700' }} border border-gray-200 dark:border-gray-600 rounded-r-lg">%</button>
                            </div>
                        </div>
                        <x-text-input id="desconto" type="number" step="0.01" class="w-32 text-right" wire:model.live.debounce.500ms="desconto" />
                    </div>
                @else
                    <div class="flex justify-end">
                        <button wire:click="toggleDesconto" class="text-sm text-blue-500 hover:underline">Aplicar Desconto</button>
                    </div>
                @endif
                
                @if ($descontoCalculado > 0)
                    <div class="flex justify-between items-center text-sm">
                        <span class="text-red-500">Valor do Desconto:</span>
                        <span class="font-semibold text-red-500">- R$ {{ number_format($descontoCalculado, 2, ',', '.') }}</span>
                    </div>
                @endif
                
                <p class="text-2xl font-bold text-gray-900 dark:text-gray-100 flex justify-between items-center border-t pt-2 mt-2">
                    <span>Total:</span>
                    <span>R$ {{ number_format($total, 2, ',', '.') }}</span>
                </p>
            </div>

            <div class="md:col-span-1">
    <x-primary-button 
        wire:click="finalizarVenda" 
        wire:loading.attr="disabled"
        wire:target="finalizarVenda" {{-- Adicionado aqui também para desativar o botão --}}
        class="w-full justify-center text-lg py-3">
        
        {{-- CORRIGIDO: Adicionamos o wire:target para especificar a ação --}}
        <span wire:loading.remove wire:target="finalizarVenda">{{ $textoBotaoFinalizar }}</span>
        <span wire:loading wire:target="finalizarVenda">Aguarde...</span>

    </x-primary-button>
</div>
        </div>
    </div>

</div>