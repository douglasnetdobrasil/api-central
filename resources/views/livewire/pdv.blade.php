<div class="flex flex-col h-[calc(100vh-4.5rem)] bg-gray-100 dark:bg-gray-900">

    {{-- =============================================================== --}}
    {{-- Bloco Superior: Cliente                                       --}}
    {{-- =============================================================== --}}
    <div class="flex-shrink-0 bg-white dark:bg-gray-800 shadow-md p-4">
        {{-- CÓDIGO DO CLIENTE SEM ALTERAÇÕES --}}
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
    {{-- Bloco Central: Produtos                                       --}}
    {{-- =============================================================== --}}
    <div class="flex-grow p-4 overflow-y-auto">
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
                            {{ $produto->nome }} <span class="text-xs">(Estoque: {{ $produto->estoque_atual ?? 0 }})</span>
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
    {{-- Bloco Inferior: Totais e Finalização                          --}}
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
                    wire:target="finalizarVenda"
                    class="w-full justify-center text-lg py-3">
                    
                    <span wire:loading.remove wire:target="finalizarVenda">{{ $textoBotaoFinalizar }}</span>
                    <span wire:loading wire:target="finalizarVenda">Aguarde...</span>
                </x-primary-button>
            </div>
        </div>
    </div>

    {{-- =============================================================== --}}
{{-- Modal de Pagamento (VERSÃO MELHORADA)                         --}}
{{-- =============================================================== --}}
@if ($showPagamentoModal)
<div class="fixed inset-0 bg-gray-900 bg-opacity-60 z-40 flex items-center justify-center" x-data>
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl p-6 m-4" @click.away="$wire.set('showPagamentoModal', false)">
        
        <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">Recebimento / Pagamento</h3>

        {{-- Exibição de totais --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6 text-center">
            {{-- ... (seção de totais continua igual, sem alterações) ... --}}
            <div class="bg-gray-100 dark:bg-gray-700 p-3 rounded">
                <span class="text-sm text-gray-500 dark:text-gray-400 block">Total a Pagar</span>
                <span class="text-xl font-bold dark:text-gray-200">R$ {{ number_format($total, 2, ',', '.') }}</span>
            </div>
            <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded">
                <span class="text-sm text-blue-500 dark:text-blue-400 block">Valor Recebido</span>
                <span class="text-xl font-bold text-blue-800 dark:text-blue-200">R$ {{ number_format($valorRecebido, 2, ',', '.') }}</span>
            </div>
            <div class="bg-red-100 dark:bg-red-900 p-3 rounded">
                <span class="text-sm text-red-500 dark:text-red-400 block">Falta Pagar</span>
                <span class="text-xl font-bold text-red-800 dark:text-red-200">R$ {{ number_format($faltaPagar, 2, ',', '.') }}</span>
            </div>
             <div class="bg-green-100 dark:bg-green-900 p-3 rounded">
                <span class="text-sm text-green-500 dark:text-green-400 block">Troco</span>
                <span class="text-xl font-bold text-green-800 dark:text-green-200">R$ {{ number_format($troco, 2, ',', '.') }}</span>
            </div>
        </div>

        {{-- Formulário de Adição de Pagamento --}}
        <div class="flex flex-wrap items-end gap-3 border-t dark:border-gray-700 pt-4">
            <div class="flex-grow">
                <x-input-label for="forma_pagamento" value="Forma de Pagamento" />
                <select id="forma_pagamento" wire:model="formaPagamentoSelecionada" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                    <option value="dinheiro">Dinheiro</option>
                    <option value="pix">PIX</option>
                    <option value="cartao_debito">Cartão de Débito</option>
                    <option value="cartao_credito">Cartão de Crédito</option>
                </select>
            </div>
            <div class="flex-grow">
                <x-input-label for="valor_pagamento" value="Valor" />
                <div class="relative">
                    <x-text-input type="text" wire:model="valorPagamentoAtual" id="valor_pagamento" class="mt-1 w-full" placeholder="0,00"/>
                    
                    {{-- NOVIDADE: Botão para preencher o valor que falta --}}
                    @if ($faltaPagar > 0)
                    <button wire:click="$set('valorPagamentoAtual', '{{ number_format($faltaPagar, 2, ',', '.') }}')" 
                            class="absolute inset-y-0 right-0 px-3 text-sm text-blue-600 hover:underline">
                        Pagar Restante
                    </button>
                    @endif
                </div>
            </div>
            <x-secondary-button wire:click="adicionarPagamento" class="h-10">Adicionar</x-secondary-button>
        </div>

        {{-- Lista de Pagamentos Adicionados --}}
        <div class="mt-4 max-h-40 overflow-y-auto">
            {{-- ... (seção da lista de pagamentos continua igual, sem alterações) ... --}}
            <h4 class="text-sm font-medium text-gray-500 dark:text-gray-400 mb-2">Pagamentos Realizados:</h4>
            <ul class="space-y-2">
                @forelse($pagamentos as $index => $pag)
                    <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-2 rounded">
                        <span class="dark:text-gray-300">
                            {{ ucfirst(str_replace('_', ' ', $pag['forma'])) }}: 
                            <span class="font-bold">R$ {{ number_format($pag['valor'], 2, ',', '.') }}</span>
                        </span>
                        <button wire:click="removerPagamento({{ $index }})" class="text-red-500 text-xs hover:underline">Remover</button>
                    </li>
                @empty
                    <li class="text-center text-gray-500 text-sm py-4">Nenhum pagamento adicionado.</li>
                @endforelse
            </ul>
        </div>

        {{-- Ações do Modal --}}
        <div class="flex justify-between items-center mt-6 border-t dark:border-gray-700 pt-4">
            <x-danger-button wire:click="$set('showPagamentoModal', false)">Cancelar</x-danger-button>
            <div class="text-right">
                 @if (session()->has('error_modal'))
                    <span class="text-red-500 text-sm mr-4">{{ session('error_modal') }}</span>
                @endif
                <x-primary-button 
                    wire:click="confirmarVendaComPagamentos" 
                    wire:loading.attr="disabled"
                    wire:target="confirmarVendaComPagamentos"
                    {{-- MUDANÇA PRINCIPAL: Adicionamos uma tolerância de menos de 1 centavo --}}
                    :disabled="$faltaPagar > 0.009"
                    class="text-lg disabled:opacity-50 disabled:cursor-not-allowed">
                    
                    <span wire:loading.remove wire:target="confirmarVendaComPagamentos">Confirmar Venda</span>
                    <span wire:loading wire:target="confirmarVendaComPagamentos">Processando...</span>
                </x-primary-button>
                
                {{-- NOVIDADE: Mensagem que explica por que o botão está desabilitado --}}
                @if($faltaPagar > 0.009)
                <p class="text-xs text-gray-500 mt-1">O botão será liberado ao quitar o valor total.</p>
                @endif
            </div>
        </div>

    </div>
</div>
@endif

</div>