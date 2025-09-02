<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Criar Novo Orçamento
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div x-data="orcamentoForm()">
                
                {{-- Seção para exibir erros de validação e de sessão --}}
                <div class="mb-6">
                    @if ($errors->any())
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Opa! Algo deu errado.</strong>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-4" role="alert">
                            <strong class="font-bold">Erro!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif
                </div>

                <form action="{{ route('orcamentos.store') }}" method="POST" @submit.prevent="$el.submit()">
                    @csrf
                    {{-- DADOS DO ORÇAMENTO --}}
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="cliente_id" value="Cliente" />
                                <select name="cliente_id" id="cliente_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>{{ $cliente->nome }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="data_emissao" value="Data de Emissão" />
                                <x-text-input type="date" name="data_emissao" id="data_emissao" class="block mt-1 w-full" :value="old('data_emissao', now()->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-input-label for="data_validade" value="Validade" />
                                <x-text-input type="date" name="data_validade" id="data_validade" class="block mt-1 w-full" :value="old('data_validade')" />
                            </div>
                        </div>
                    </div>

                    {{-- ITENS DO ORÇAMENTO --}}
                    <div class="relative mt-8 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
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
                                            <td class="py-2 pr-2">
                                                <input type="hidden" x-bind:name="`items[${index}][produto_id]`" x-model="item.produto_id">
                                                <x-text-input type="text"
                                                    x-model="item.produto_nome"
                                                    x-bind:name="`items[${index}][produto_nome]`"
                                                    x-bind:x-ref="'produto_search_' + index"
                                                    @keydown.arrow-down.prevent="navigateDown(index)"
                                                    @keydown.arrow-up.prevent="navigateUp(index)"
                                                    @keydown.enter.prevent="selectHighlighted(index)"
                                                    @input.debounce.300ms="searchProducts(index, $event.target.value)"
                                                    @focus="searchProducts(index, $event.target.value)"
                                                    class="w-full" placeholder="Digite para buscar..." required
                                                />
                                                <div x-show="item.searchResults.length > 0 || item.loading" @click.away="closeDropdown(index)" class="absolute z-10 w-full bg-white dark:bg-gray-900 border rounded-md shadow-lg mt-1 max-h-60 overflow-y-auto">
                                                    <div x-show="item.loading" class="px-4 py-2 text-sm text-gray-500">Buscando...</div>
                                                    <div x-show="!item.loading && item.searchResults.length === 0 && item.produto_nome.length >= 2" class="px-4 py-2 text-sm text-gray-500">Nenhum resultado encontrado.</div>
                                                    <template x-for="(produto, resultIndex) in item.searchResults" :key="produto.id">
                                                        <a @click.prevent="selectProduct(index, produto)" href="#"
                                                           :class="{ 'bg-gray-200 dark:bg-gray-700': resultIndex === item.highlightedIndex }"
                                                           class="block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                                                           x-text="produto.nome"></a>
                                                    </template>
                                                </div>
                                            </td>
                                            <td class="py-2 px-2">
                                                <x-text-input type="number" step="1" x-bind:name="`items[${index}][quantidade]`"
                                                    x-model.number="item.quantidade"
                                                    x-bind:x-ref="'quantidade_' + index"
                                                    @keydown.enter.prevent="$refs['valor_unitario_' + index].focus()"
                                                    @input="calculateTotals" class="w-full" required />
                                            </td>
                                            <td class="py-2 px-2">
                                                <x-text-input type="number" step="0.01" x-bind:name="`items[${index}][valor_unitario]`"
                                                    x-model.number="item.valor_unitario"
                                                    x-bind:x-ref="'valor_unitario_' + index"
                                                    @keydown.enter.prevent="addNewItemAndFocus(index)"
                                                    @input="calculateTotals" class="w-full" required />
                                            </td>
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
                                <textarea name="observacoes" id="observacoes" rows="4" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">{{ old('observacoes') }}</textarea>
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
                        <x-primary-button>Salvar Orçamento</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @php
        $defaultItem = [['produto_id' => null, 'produto_nome' => '', 'quantidade' => 1, 'valor_unitario' => 0, 'searchResults' => [], 'loading' => false, 'highlightedIndex' => -1]];
    @endphp
    <script>
        function orcamentoForm() {
            return {
                items: @json(old('items', $defaultItem)),
                total: 0,
                init() { this.calculateTotals(); },
                async searchProducts(index, term) {
                    this.items[index].highlightedIndex = -1; 
                    if (term.length < 2) { this.items[index].searchResults = []; return; }
                    this.items[index].loading = true;
                    try {
                        const response = await fetch(`{{ route('produtos.search') }}?term=${encodeURIComponent(term)}`);
                        if (response.redirected) { window.location.reload(); return; }
                        if (!response.ok) { throw new Error('Erro na resposta da rede.'); }
                        this.items[index].searchResults = await response.json();
                    } catch (error) {
                        console.error('Erro ao buscar produtos:', error);
                        this.items[index].searchResults = [];
                    } finally { this.items[index].loading = false; }
                },
                selectProduct(index, produto) {
                    this.items[index].produto_id = produto.id;
                    this.items[index].produto_nome = produto.nome;
                    this.items[index].valor_unitario = parseFloat(produto.preco_venda);
                    this.closeDropdown(index);
                    this.calculateTotals();
                    this.$nextTick(() => { this.$refs['quantidade_' + index].focus(); this.$refs['quantidade_' + index].select(); });
                },
                closeDropdown(index) {
                    this.items[index].searchResults = [];
                    this.items[index].loading = false;
                    this.items[index].highlightedIndex = -1;
                },
                selectHighlighted(index) {
                    if (this.items[index].highlightedIndex >= 0) {
                        const highlightedProduct = this.items[index].searchResults[this.items[index].highlightedIndex];
                        this.selectProduct(index, highlightedProduct);
                    }
                },
                navigateDown(index) {
                    if (this.items[index].searchResults.length > 0) { this.items[index].highlightedIndex = (this.items[index].highlightedIndex + 1) % this.items[index].searchResults.length; }
                },
                navigateUp(index) {
                    if (this.items[index].searchResults.length > 0) { this.items[index].highlightedIndex = (this.items[index].highlightedIndex - 1 + this.items[index].searchResults.length) % this.items[index].searchResults.length; }
                },
                addItem() { this.items.push({ produto_id: null, produto_nome: '', quantidade: 1, valor_unitario: 0, searchResults: [], loading: false, highlightedIndex: -1 }); },
                addNewItemAndFocus(currentIndex) {
                    this.addItem();
                    this.$nextTick(() => { this.$refs['produto_search_' + (currentIndex + 1)].focus(); });
                },
                removeItem(index) {
                    if (this.items.length > 1) { this.items.splice(index, 1); }
                    this.calculateTotals();
                },
                calculateTotals() { this.total = this.items.reduce((acc, item) => acc + (item.quantidade || 0) * (item.valor_unitario || 0), 0); },
                formatCurrency(value) { return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value || 0); },
            }
        }
    </script>
</x-app-layout>