<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Orçamento #{{ $orcamento->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Passamos os itens do orçamento para o AlpineJS --}}
            <div x-data="orcamentoForm({
                orcamentoItems: {{ $orcamento->itens->map(function($item) {
                    return [
                        'produto_id' => $item->produto_id,
                        'produto_nome' => $item->descricao_produto,
                        'quantidade' => $item->quantidade,
                        'valor_unitario' => $item->valor_unitario,
                        'searchResults' => []
                    ];
                })->toJson() }}
            })">
                
                <form action="{{ route('orcamentos.update', $orcamento) }}" method="POST" @submit.prevent="$el.submit()">
                    @csrf
                    @method('PUT') {{-- Importante para o método update --}}

                    {{-- DADOS DO ORÇAMENTO --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="cliente_id" value="Cliente" />
                                <select name="cliente_id" id="cliente_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" @selected($orcamento->cliente_id == $cliente->id)>
                                            {{ $cliente->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="data_emissao" value="Data de Emissão" />
                                <x-text-input type="date" name="data_emissao" id="data_emissao" class="block mt-1 w-full" :value="old('data_emissao', $orcamento->data_emissao->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-input-label for="data_validade" value="Validade" />
                                <x-text-input type="date" name="data_validade" id="data_validade" class="block mt-1 w-full" :value="old('data_validade', $orcamento->data_validade ? $orcamento->data_validade->format('Y-m-d') : '')" />
                            </div>
                        </div>
                    </div>

                    {{-- ITENS DO ORÇAMENTO (O HTML é idêntico ao de create.blade.php) --}}
                    <div class="mt-8 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold mb-4">Itens do Orçamento</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Produto</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 w-28">Qtd.</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 w-36">Valor Unit.</th>
                                        <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 w-36">Subtotal</th>
                                        <th class="w-12"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(item, index) in items" :key="index">
                                        <tr class="border-t border-gray-200 dark:border-gray-700">
                                            <td class="py-2 pr-2 relative">
                                                <input type="hidden" x-bind:name="`items[${index}][produto_id]`" x-model="item.produto_id">
                                                <x-text-input type="text"
                                                    x-model="item.produto_nome"
                                                    @keydown.enter.prevent
                                                    @input.debounce.300ms="searchProducts(index, $event.target.value)"
                                                    @focus="searchProducts(index, $event.target.value)"
                                                    class="w-full" placeholder="Digite para buscar..." required
                                                />
                                                <div x-show="item.searchResults.length > 0" @click.away="item.searchResults = []" class="absolute z-10 w-full bg-white dark:bg-gray-900 border rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                                                    <template x-for="produto in item.searchResults" :key="produto.id">
                                                        <a @click.prevent="selectProduct(index, produto)" href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" x-text="produto.nome"></a>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="py-2 px-2"><x-text-input type="number" step="1" x-bind:name="`items[${index}][quantidade]`" x-model.number="item.quantidade" @input="calculateTotals" class="w-full" required /></td>
                                            <td class="py-2 px-2"><x-text-input type="number" step="0.01" x-bind:name="`items[${index}][valor_unitario]`" x-model.number="item.valor_unitario" @input="calculateTotals" class="w-full" required /></td>
                                            <td class="py-2 px-2"><span class="font-mono text-lg" x-text="formatCurrency((item.quantidade || 0) * (item.valor_unitario || 0))"></span></td>
                                            <td class="py-2 pl-2"><x-danger-button type="button" @click.prevent="removeItem(index)">X</x-danger-button></td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="mt-4">
                            <x-secondary-button type="button" @click.prevent="addItem">Adicionar Item</x-secondary-button>
                        </div>

                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="observacoes" value="Observações" />
                                <textarea name="observacoes" id="observacoes" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('observacoes', $orcamento->observacoes) }}</textarea>
                            </div>

                            <div class="flex items-end justify-end">
                                <div class="text-right">
                                    <p class="text-gray-500">Valor Total do Orçamento:</p>
                                    <p class="text-3xl font-bold font-mono" x-text="formatCurrency(total)"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button>Atualizar Orçamento</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- O SCRIPT é o mesmo do create.blade.php, mas adaptado para receber os itens iniciais --}}
    <script>
        function orcamentoForm(initialData = {}) {
            return {
                items: initialData.orcamentoItems || [{ produto_id: null, produto_nome: '', quantidade: 1, valor_unitario: 0, searchResults: [] }],
                total: 0,
                
                init() {
                    this.calculateTotals();
                },

                async searchProducts(index, term) {
                    if (term.length < 2) {
                        this.items[index].searchResults = [];
                        return;
                    }
                    try {
                        // A rota de busca deve existir em seu arquivo de rotas (web.php)
                        const response = await fetch(`{{ route('produtos.search') }}?term=${encodeURIComponent(term)}`);
                        if (response.redirected) {
                            window.location.reload(); 
                            return; 
                        }
                        if (!response.ok) throw new Error('Erro na resposta da rede.');
                        this.items[index].searchResults = await response.json();
                    } catch (error) {
                        console.error('Erro ao buscar produtos:', error);
                        this.items[index].searchResults = [];
                    }
                },

                selectProduct(index, produto) {
                    this.items[index].produto_id = produto.id;
                    this.items[index].produto_nome = produto.nome;
                    this.items[index].valor_unitario = produto.preco_venda;
                    this.items[index].searchResults = [];
                    this.calculateTotals();
                    this.$nextTick(() => {
                        const qtyInput = document.querySelector(`input[name='items[${index}][quantidade]']`);
                        if(qtyInput) {
                            qtyInput.focus();
                            qtyInput.select();
                        }
                    });
                },
                
                addItem() {
                    this.items.push({ produto_id: null, produto_nome: '', quantidade: 1, valor_unitario: 0, searchResults: [] });
                },
                removeItem(index) {
                    if (this.items.length > 1) {
                        this.items.splice(index, 1);
                    }
                    this.calculateTotals();
                },
                calculateTotals() {
                    this.total = this.items.reduce((acc, item) => acc + (item.quantidade || 0) * (item.valor_unitario || 0), 0);
                },
                formatCurrency(value) {
                    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0);
                },
            }
        }
    </script>
</x-app-layout>