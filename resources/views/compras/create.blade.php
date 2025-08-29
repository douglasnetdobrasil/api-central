<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Lançar Nova Compra Manual
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="compraForm()">
                <form action="{{ route('compras.store') }}" method="POST">
                    @csrf
                    {{-- DADOS DA NOTA --}}
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Dados da Nota</h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <x-input-label for="fornecedor_id" :value="__('Fornecedor')" />
                                    <select name="fornecedor_id" id="fornecedor_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                        <option value="">Selecione um fornecedor</option>
                                        @foreach ($fornecedores as $fornecedor)
                                            <option value="{{ $fornecedor->id }}">{{ $fornecedor->razao_social }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="numero_nota" :value="__('Número do Documento')" />
                                    <x-text-input type="text" name="numero_nota" id="numero_nota" class="block mt-1 w-full" required />
                                </div>
                                <div>
                                    <x-input-label for="data_emissao" :value="__('Data de Emissão')" />
                                    <x-text-input type="date" name="data_emissao" id="data_emissao" class="block mt-1 w-full" :value="old('data_emissao', now()->format('Y-m-d'))" required />
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ITENS DA NOTA --}}
                    <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-semibold mb-4">Itens da Compra</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full">
                                    <thead>
                                        <tr>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500">Produto</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 w-28">Qtd.</th>
                                            <th class="px-4 py-2 text-left text-sm font-medium text-gray-500 w-36">Custo Unit.</th>
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
                                                        @input.debounce.300ms="searchProducts(index, $event.target.value)"
                                                        @focus="searchProducts(index, $event.target.value)"
                                                        class="w-full"
                                                        placeholder="Digite para buscar..." required
                                                    />
                                                    <div x-show="item.searchResults.length > 0" @click.away="item.searchResults = []" class="absolute z-10 w-full bg-white dark:bg-gray-900 border rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                                                        <template x-for="produto in item.searchResults" :key="produto.id">
                                                            <a @click.prevent="selectProduct(index, produto)" href="#" class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700" x-text="produto.nome"></a>
                                                        </template>
                                                    </div>
                                                </td>
                                                <td class="py-2 px-2"><x-text-input type="number" step="1" x-bind:name="`items[${index}][quantidade]`" x-model.number="item.quantidade" @input="calculateTotals" class="w-full" required /></td>
                                                <td class="py-2 px-2"><x-text-input type="number" step="0.01" x-bind:name="`items[${index}][preco_custo]`" x-model.number="item.preco_custo" @input="calculateTotals" class="w-full" required /></td>
                                                <td class="py-2 px-2"><span class="font-mono text-lg" x-text="formatCurrency((item.quantidade || 0) * (item.preco_custo || 0))"></span></td>
                                                <td class="py-2 pl-2"><x-danger-button type="button" @click.prevent="removeItem(index)">X</x-danger-button></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                            
                            <div class="mt-4">
                                <x-secondary-button type="button" @click.prevent="addItem">Adicionar Item</x-secondary-button>
                            </div>

                            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
                                <div class="text-right">
                                    <p class="text-gray-500">Valor Total da Nota:</p>
                                    <p class="text-2xl font-bold font-mono" x-text="formatCurrency(total)"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <x-primary-button type="submit">
                            Salvar Compra
                        </x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function compraForm() {
            return {
                items: [{ produto_id: null, produto_nome: '', quantidade: 1, preco_custo: 0, searchResults: [] }],
                total: 0,
                addItem() { this.items.push({ produto_id: null, produto_nome: '', quantidade: 1, preco_custo: 0, searchResults: [] }); },
                removeItem(index) { this.items.splice(index, 1); this.calculateTotals(); },
                calculateTotals() {
                    this.total = this.items.reduce((acc, item) => acc + (item.quantidade || 0) * (item.preco_custo || 0), 0);
                },
                async searchProducts(index, term) {
                    if (term.length < 2) { this.items[index].searchResults = []; return; }
                    try {
                        const response = await fetch(`{{ route('produtos.search') }}?term=${encodeURIComponent(term)}`);
                        this.items[index].searchResults = await response.json();
                    } catch (error) { console.error('Erro ao buscar produtos:', error); }
                },
                selectProduct(index, produto) {
                    this.items[index].produto_id = produto.id;
                    this.items[index].produto_nome = produto.nome;
                    this.items[index].preco_custo = produto.preco_custo;
                    this.items[index].searchResults = [];
                    this.calculateTotals();
                    this.$nextTick(() => { document.querySelector(`input[name='items[${index}][quantidade]']`).focus(); });
                },
                formatCurrency(value) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0); },
            }
        }
    </script>
</x-app-layout>