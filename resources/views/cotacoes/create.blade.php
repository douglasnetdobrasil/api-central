<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Gerar Nova Cotação de Preços
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8">

                    {{-- Seção de Erros --}}
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Opa! Algo deu errado.</strong>
                            <ul class="mt-3 list-disc list-inside text-sm">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('cotacoes.store') }}" method="POST" x-data="cotacaoForm()">
                        @csrf

                        {{-- DADOS GERAIS DA COTAÇÃO --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="data_cotacao" value="Data da Cotação" />
                                <x-text-input type="date" name="data_cotacao" id="data_cotacao" class="mt-1 block w-full" :value="old('data_cotacao', now()->format('Y-m-d'))" required />
                            </div>
                            <div>
                                <x-input-label for="descricao" value="Descrição (Opcional)" />
                                <x-text-input type="text" name="descricao" id="descricao" class="mt-1 block w-full" :value="old('descricao')" placeholder="Ex: Cotação para reposição de estoque" />
                            </div>
                        </div>

                        {{-- SELEÇÃO DE PRODUTOS --}}
                        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold mb-4">Produtos para Cotar</h3>
                            <div class="space-y-4">
                                <template x-for="(produto, index) in produtosSelecionados" :key="index">
                                    <div class="flex items-center gap-4">
                                        <div class="flex-grow">
                                            <select x-bind:name="`produtos[${index}][id]`" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                <option value="">Selecione um produto</option>
                                                @foreach ($produtos as $p)
                                                    <option value="{{ $p->id }}">{{ $p->nome }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="w-40">
                                            <x-text-input type="number" x-bind:name="`produtos[${index}][quantidade]`" placeholder="Quantidade" class="block w-full" step="0.01" min="0.01" required />
                                        </div>
                                        <x-danger-button type="button" @click.prevent="removerProduto(index)">Remover</x-danger-button>
                                    </div>
                                </template>
                            </div>
                            <x-secondary-button type="button" @click.prevent="adicionarProduto" class="mt-4">Adicionar Produto</x-secondary-button>
                        </div>

                        {{-- SELEÇÃO DE FORNECEDORES --}}
                        <div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                            <h3 class="text-lg font-semibold mb-2">Enviar para os Fornecedores</h3>
                            <p class="text-sm text-gray-500 mb-4">Selecione todos os fornecedores que devem receber esta cotação.</p>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 max-h-60 overflow-y-auto p-4 border rounded-md">
                                @foreach ($fornecedores as $fornecedor)
                                    <label class="flex items-center space-x-3">
                                        <input type="checkbox" name="fornecedores[]" value="{{ $fornecedor->id }}" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm">
                                        <span>{{ $fornecedor->razao_social }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('cotacoes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Salvar e Gerar Cotação
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function cotacaoForm() {
            return {
                produtosSelecionados: [{}], // Começa com uma linha de produto
                adicionarProduto() {
                    this.produtosSelecionados.push({});
                },
                removerProduto(index) {
                    if (this.produtosSelecionados.length > 1) {
                        this.produtosSelecionados.splice(index, 1);
                    }
                }
            }
        }
    </script>
</x-app-layout>