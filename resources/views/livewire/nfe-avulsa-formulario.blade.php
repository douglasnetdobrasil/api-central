<div>
    <div x-data="{ tab: 'info' }">
        {{-- CABEÇALHO COM AS ABAS --}}
        <div class="bg-white dark:bg-gray-800 shadow-md">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2"><a href="#" @click.prevent="tab = 'info'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'info'}" class="inline-block p-4 border-b-2 rounded-t-lg">1. Informações Gerais</a></li>
                        <li class="mr-2"><a href="#" @click.prevent="tab = 'produtos'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'produtos'}" class="inline-block p-4 border-b-2 rounded-t-lg">2. Produtos</a></li>
                        <li class="mr-2"><a href="#" @click.prevent="tab = 'transporte'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'transporte'}" class="inline-block p-4 border-b-2 rounded-t-lg">3. Transporte</a></li>
                        <li class="mr-2"><a href="#" @click.prevent="tab = 'totais'" :class="{'border-indigo-500 text-indigo-600 dark:text-indigo-400': tab === 'totais'}" class="inline-block p-4 border-b-2 rounded-t-lg">4. Totais e Finalização</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- CONTAINER COM O CONTEÚDO DAS ABAS --}}
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 space-y-6">

                    {{-- ... (Abas 1 e 2 permanecem iguais) ... --}}
                    <div x-show="tab === 'info'" class="space-y-6">
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Dados da Nota</h3>
                            <div class="mt-4 grid md:grid-cols-3 gap-4">
                                <div><x-input-label value="Natureza da Operação" /><select wire:model.live="natureza_operacao_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">@foreach($naturezasOperacao as $n)<option value="{{ $n->id }}">{{ $n->descricao }}</option>@endforeach</select></div>
                                <div><x-input-label value="Finalidade da Emissão" /><select wire:model.live="finalidade_emissao" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"><option value="1">NF-e Normal</option><option value="2">NF-e Complementar</option><option value="3">NF-e de Ajuste</option><option value="4">Devolução</option></select></div>
                                <div><x-input-label value="Tipo de Operação" /><select wire:model.live="tipo_operacao" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"><option value="1">Saída</option><option value="0">Entrada</option></select></div>
                                <div><x-input-label value="Consumidor Final" /><select wire:model.live="consumidor_final" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"><option value="1">Sim</option><option value="0">Não</option></select></div>
                                <div><x-input-label value="Série" /><x-text-input wire:model.live="serie" type="number" class="mt-1 block w-full" /></div>
                            </div>
                        </div>
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Destinatário (Cliente)</h3>
                            @if ($clienteSelecionado)<div class="mt-4 flex items-center justify-between p-3 bg-gray-100 dark:bg-gray-700 rounded-md"><p class="font-semibold">{{ $clienteSelecionado->nome }}</p><button wire:click="removerCliente" class="text-sm text-red-500">Remover</button></div>
                            @else<div class="mt-4 relative">
                                <x-text-input wire:model.live.debounce.300ms="clienteSearch" type="text" class="w-full" placeholder="Digite o ID, nome ou CPF/CNPJ..." x-on:keydown.arrow-down.prevent="$wire.set('highlightClienteIndex', Math.min($wire.get('highlightClienteIndex') + 1, $wire.get('clientesEncontrados').length - 1))" x-on:keydown.arrow-up.prevent="$wire.set('highlightClienteIndex', Math.max($wire.get('highlightClienteIndex') - 1, 0))" x-on:keydown.enter.prevent="$wire.selecionarClienteComEnter()" />
                                @error('clienteSelecionado')<span class="text-red-500">{{$message}}</span>@enderror @if(!empty($clientesEncontrados))<ul class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-900 border rounded-md shadow-lg">@foreach($clientesEncontrados as $index => $c)<li wire:click="selecionarCliente({{ $c->id }})" class="px-4 py-2 cursor-pointer {{ $highlightClienteIndex === $index ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">{{ $c->nome }}</li>@endforeach</ul>@endif</div>@endif
                        </div>
                    </div>
                    <div x-show="tab === 'produtos'">
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b pb-2">Itens da Nota</h3>
                            <div class="mt-4 relative">
                                <x-input-label value="Adicionar Produto" />
                                <x-text-input wire:model.live.debounce.300ms="produtoSearch" type="text" class="w-full mt-1" placeholder="Digite o ID, nome ou código..." x-on:keydown.arrow-down.prevent="$wire.set('highlightProdutoIndex', Math.min($wire.get('highlightProdutoIndex') + 1, $wire.get('produtosEncontrados').length - 1))" x-on:keydown.arrow-up.prevent="$wire.set('highlightProdutoIndex', Math.max($wire.get('highlightProdutoIndex') - 1, 0))" x-on:keydown.enter.prevent="$wire.selecionarProdutoComEnter()" />
                                @error('cart')<span class="text-red-500">{{$message}}</span>@enderror @if(!empty($produtosEncontrados))<ul class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-900 border rounded-md shadow-lg">@foreach($produtosEncontrados as $index => $p)<li wire:click="adicionarProduto({{$p->id}})" class="px-4 py-2 cursor-pointer {{ $highlightProdutoIndex === $index ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">{{$p->nome}}</li>@endforeach</ul>@endif</div>
                            <div class="mt-6 overflow-x-auto"><table class="min-w-full divide-y dark:divide-gray-700"><thead class="bg-gray-50 dark:bg-gray-700"><tr><th class="px-4 py-2 text-left">Produto</th><th class="px-4 py-2 text-center">UN</th><th class="px-4 py-2 text-center">CFOP</th><th class="px-4 py-2 text-center">Qtd</th><th class="px-4 py-2 text-right">Vlr. Unit.</th><th class="px-4 py-2 text-right">Subtotal</th><th class="px-4 py-2 text-center">Ação</th></tr></thead>
                            <tbody class="dark:bg-gray-800 divide-y dark:divide-gray-700">@forelse($cart as $i => $item)<tr wire:key="cart-{{$i}}">
                            <td class="px-4 py-3">{{$item['nome']}}</td><td class="px-4 py-3 text-center">{{$item['unidade']}}</td><td class="px-4 py-3 text-center"><input type="text" wire:model.live="cart.{{$i}}.cfop" class="w-20 text-center rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600"></td>
                            <td class="px-4 py-3"><div class="flex items-center justify-center"><button wire:click="diminuirQuantidade({{$i}})" class="px-2">-</button><span class="mx-2">{{$item['quantidade']}}</span><button wire:click="aumentarQuantidade({{$i}})" class="px-2">+</button></div></td>
                            <td class="px-4 py-3 text-right">R$ {{number_format($item['preco'],2,',','.')}}</td><td class="px-4 py-3 text-right font-semibold">R$ {{number_format($item['total_item'],2,',','.')}}</td>
                            <td class="px-4 py-3 text-center text-sm space-x-2"><button wire:click="abrirModalImpostos({{$i}})" type="button" class="text-blue-500 hover:underline">Impostos</button><button wire:click="removerProduto({{$i}})" class="text-red-500 hover:underline">Remover</button></td></tr>
                            @empty<tr><td colspan="7" class="text-center py-6">Nenhum produto.</td></tr>@endforelse</tbody></table></div>
                        </div>
                    </div>

                    {{-- ABA 3: TRANSPORTE --}}
                    <div x-show="tab === 'transporte'" class="space-y-6">
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                             <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Dados do Transporte</h3>
                             <div class="mt-4 grid md:grid-cols-4 gap-4">
                                <div>
                                    <x-input-label value="Modalidade do Frete" />
                                    <select wire:model.live="frete_modalidade" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                        <option value="0">0 - Contratação do Frete por conta do Remetente (CIF)</option>
                                        <option value="1">1 - Contratação do Frete por conta do Destinatário (FOB)</option>
                                        <option value="2">2 - Contratação do Frete por conta de Terceiros</option>
                                        <option value="3">3 - Transporte Próprio por conta do Remetente</option>
                                        <option value="4">4 - Transporte Próprio por conta do Destinatário</option>
                                        <option value="9">9 - Sem Ocorrência de Transporte</option>
                                    </select>
                                </div>
                             </div>
                        </div>
                        
                        {{-- Só mostra se houver frete --}}
                        <div x-show="$wire.get('frete_modalidade') != 9" class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Transportadora</h3>
                            @if ($transportadoraSelecionada)
                                <div class="mt-4 flex items-center justify-between p-3 bg-gray-100 dark:bg-gray-700 rounded-md">
                                    {{-- *** CORREÇÃO APLICADA AQUI *** --}}
                                    <p class="font-semibold">{{ $transportadoraSelecionada->razao_social }}</p>
                                    <button wire:click="removerTransportadora" class="text-sm text-red-500">Remover</button>
                                </div>
                            @else
                                <div class="mt-4 relative">
                                    <x-text-input wire:model.live.debounce.300ms="transportadoraSearch" type="text" class="w-full" placeholder="Digite o ID, Razão Social ou CNPJ..." />
                                    @if(!empty($transportadorasEncontradas))
                                        <ul class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-900 border rounded-md shadow-lg">
                                            @foreach($transportadorasEncontradas as $t)
                                                {{-- *** CORREÇÃO APLICADA AQUI *** --}}
                                                <li wire:click="selecionarTransportadora({{ $t->id }})" class="px-4 py-2 cursor-pointer hover:bg-gray-100">{{ $t->razao_social }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div x-show="$wire.get('frete_modalidade') != 9" class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium dark:text-gray-100 border-b dark:border-gray-700 pb-2">Volumes</h3>
                            <div class="mt-4 grid md:grid-cols-5 gap-4">
                                <div><x-input-label value="Quantidade" /><x-text-input wire:model.live="volume_quantidade" type="number" class="mt-1 block w-full" /></div>
                                <div><x-input-label value="Espécie" /><x-text-input wire:model.live="volume_especie" type="text" class="mt-1 block w-full" /></div>
                                <div><x-input-label value="Marca" /><x-text-input wire:model.live="volume_marca" type="text" class="mt-1 block w-full" /></div>
                                <div><x-input-label value="Peso Bruto (Kg)" /><x-text-input wire:model.live="peso_bruto" type="number" step="0.001" class="mt-1 block w-full" /></div>
                                <div><x-input-label value="Peso Líquido (Kg)" /><x-text-input wire:model.live="peso_liquido" type="number" step="0.001" class="mt-1 block w-full" /></div>
                            </div>
                        </div>
                    </div>

                    {{-- ABA 4: TOTAIS E FINALIZAÇÃO --}}
<div x-show="tab === 'totais'" class="space-y-6">
    
    {{-- RESUMO DA NOTA --}}
    <div class="p-6 border dark:border-gray-700 rounded-lg">
        <h3 class="text-xl font-bold dark:text-gray-100 border-b dark:border-gray-700 pb-3 mb-4">Resumo da Nota</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg"><span class="text-sm font-medium text-gray-500 dark:text-gray-400">Qtd. de Itens</span><p class="text-2xl font-semibold dark:text-white">{{ count($cart) }}</p></div>
            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg"><span class="text-sm font-medium text-gray-500 dark:text-gray-400">Qtd. de Produtos</span><p class="text-2xl font-semibold dark:text-white">{{ $totalQuantidadeProdutos }}</p></div>
            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded-lg"><span class="text-sm font-medium text-gray-500 dark:text-gray-400">Peso Total</span><p class="text-2xl font-semibold dark:text-white">{{ number_format($peso_bruto, 3, ',', '.') }} Kg</p></div>
            <div class="p-4 bg-blue-100 dark:bg-blue-900 rounded-lg"><span class="text-sm font-medium text-blue-500 dark:text-blue-300">Valor Total da Nota</span><p class="text-2xl font-bold text-blue-800 dark:text-blue-200">R$ {{ number_format($totalNota, 2, ',', '.') }}</p></div>
        </div>
    </div>
    
    {{-- SEÇÃO DE PAGAMENTOS (Lógica reutilizada do seu PDV) --}}
    <div class="p-6 border dark:border-gray-700 rounded-lg">
        <h3 class="text-xl font-bold dark:text-gray-100 pb-3 mb-4">Pagamento</h3>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center mb-6">
            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded"><span class="text-sm block">Total a Pagar</span><span class="text-xl font-bold text-blue-800 dark:text-blue-200">R$ {{ number_format($totalNota, 2, ',', '.') }}</span></div>
            <div class="p-3 bg-green-100 dark:bg-green-800 rounded"><span class="text-sm block">Total Pago</span><span class="text-xl font-bold">R$ {{ number_format($valorRecebido, 2, ',', '.') }}</span></div>
            <div class="p-3 bg-red-100 dark:bg-red-800 rounded"><span class="text-sm block">Faltante</span><span class="text-xl font-bold">R$ {{ number_format($faltaPagar, 2, ',', '.') }}</span></div>
            <div class="p-3 bg-yellow-100 dark:bg-yellow-700 rounded"><span class="text-sm block">Troco</span><span class="text-xl font-bold">R$ {{ number_format($troco, 2, ',', '.') }}</span></div>
        </div>

        <div class="flex flex-wrap items-end gap-3 border-t dark:border-gray-700 pt-4">
            <div class="flex-grow min-w-[200px]"><x-input-label value="Forma de Pagamento" /><select wire:model="formaPagamentoSelecionada" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">@foreach($formasPagamentoOpcoes as $forma)<option value="{{ $forma->id }}">{{ $forma->nome }}</option>@endforeach</select></div>
            <div class="flex-grow min-w-[150px]"><x-input-label value="Valor" /><x-text-input type="text" wire:model.lazy="valorPagamentoAtual" class="mt-1 w-full"/></div>
            <x-secondary-button wire:click.prevent="adicionarPagamento" class="h-10">Adicionar</x-secondary-button>
        </div>

        <div class="mt-4 max-h-40 overflow-y-auto">
            <h4 class="text-sm font-medium mb-2 dark:text-gray-300">Pagamentos adicionados:</h4>
            <ul class="space-y-2">
                @forelse($pagamentos as $index => $p)
                <li class="flex justify-between items-center bg-gray-50 dark:bg-gray-700 p-2 rounded text-sm">
                    <span>{{ $p['nome'] }}: <span class="font-bold">R$ {{ number_format($p['valor'], 2, ',', '.') }}</span></span>
                    <button wire:click.prevent="removerPagamento({{ $index }})" class="text-red-500 hover:text-red-700 text-xs font-semibold">REMOVER</button>
                </li>
                @empty
                <li class="text-center text-gray-500 text-sm py-4">Nenhum pagamento adicionado.</li>
                @endforelse
            </ul>
        </div>
    </div>

    {{-- INFORMAÇÕES ADICIONAIS E BOTÕES DE AÇÃO --}}
    <div class="p-6 border dark:border-gray-700 rounded-lg">
        <h4 class="text-lg font-medium dark:text-gray-100">Informações Adicionais</h4>
        <textarea wire:model.defer="observacoes" rows="4" class="mt-2 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600" placeholder="Digite aqui informações complementares, dados de interesse do contribuinte, etc."></textarea>
        
        <div class="mt-8 flex justify-end space-x-4">
            <x-secondary-button wire:click="salvarRascunho('Em Digitação')">Salvar Rascunho</x-secondary-button>
            <x-primary-button wire:click="emitirNFe" :disabled="!$clienteSelecionado || empty($cart) || $faltaPagar > 0" class="disabled:opacity-50 disabled:cursor-not-allowed">
                <div wire:loading wire:target="emitirNFe" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-3"></div>
                <span wire:loading.remove wire:target="emitirNFe">Emitir NF-e</span>
                <span wire:loading wire:target="emitirNFe">Emitindo...</span>
            </x-primary-button>
        </div>
        @if (session()->has('message'))<div class="mt-4 text-green-500">{{ session('message') }}</div>@endif
        @if (session()->has('error'))<div class="mt-4 text-red-500">{{ session('error') }}</div>@endif
    </div>
</div>
    {{-- MODAL DE IMPOSTOS --}}
    @if($indexImpostos !== null)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-60 z-50 flex items-center justify-center">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl w-full max-w-2xl p-6 m-4" @click.away="$wire.fecharModalImpostos()">
            <h3 class="text-xl font-bold dark:text-gray-100 mb-4">Impostos do Item: <span class="font-normal">{{ $cart[$indexImpostos]['nome'] }}</span></h3>
            <div class="space-y-4 max-h-[60vh] overflow-y-auto pr-2">
                <div class="p-4 border rounded dark:border-gray-600 space-y-2"><h4 class="font-semibold text-lg">ICMS</h4><div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div><x-input-label value="CST" /><x-text-input class="w-full" wire:model.defer="itemImpostos.icms_cst" /></div>
                <div><x-input-label value="BC (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.icms_base_calculo" /></div>
                <div><x-input-label value="Alíquota (%)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.icms_aliquota" /></div>
                <div><x-input-label value="Valor (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.icms_valor" /></div></div></div>
                <div class="p-4 border rounded dark:border-gray-600 space-y-2"><h4 class="font-semibold text-lg">IPI</h4><div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div><x-input-label value="CST" /><x-text-input class="w-full" wire:model.defer="itemImpostos.ipi_cst" /></div>
                <div><x-input-label value="BC (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.ipi_base_calculo" /></div>
                <div><x-input-label value="Alíquota (%)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.ipi_aliquota" /></div>
                <div><x-input-label value="Valor (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.ipi_valor" /></div></div></div>
                <div class="p-4 border rounded dark:border-gray-600 space-y-2"><h4 class="font-semibold text-lg">PIS</h4><div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div><x-input-label value="CST" /><x-text-input class="w-full" wire:model.defer="itemImpostos.pis_cst" /></div>
                <div><x-input-label value="BC (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.pis_base_calculo" /></div>
                <div><x-input-label value="Alíquota (%)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.pis_aliquota" /></div>
                <div><x-input-label value="Valor (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.pis_valor" /></div></div></div>
                <div class="p-4 border rounded dark:border-gray-600 space-y-2"><h4 class="font-semibold text-lg">COFINS</h4><div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div><x-input-label value="CST" /><x-text-input class="w-full" wire:model.defer="itemImpostos.cofins_cst" /></div>
                <div><x-input-label value="BC (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.cofins_base_calculo" /></div>
                <div><x-input-label value="Alíquota (%)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.cofins_aliquota" /></div>
                <div><x-input-label value="Valor (R$)" /><x-text-input type="number" step="0.01" class="w-full" wire:model.defer="itemImpostos.cofins_valor" /></div></div></div>
            </div>
            <div class="flex justify-end items-center mt-6 pt-4 border-t"><x-secondary-button wire:click="fecharModalImpostos">Cancelar</x-secondary-button><x-primary-button wire:click="salvarImpostos" class="ml-4">Salvar Impostos</x-primary-button></div>
        </div>
    </div>
    @endif

    {{-- MODAL DE PAGAMENTO --}}
  
</div>