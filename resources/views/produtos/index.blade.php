<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gestão de Produtos') }}
            </h2>
            <a href="{{ route('produtos.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Novo Produto
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Formulário de Pesquisa (sem alterações) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight mb-4">
                        Pesquisar Produtos
                    </h3>
                    <form action="{{ route('produtos.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="search_field" value="Pesquisar por" />
                                <select name="search_field" id="search_field" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="nome" @selected(request('search_field', 'nome') == 'nome')>Nome do Produto</option>
                                    <option value="id" @selected(request('search_field') == 'id')>Código</option>
                                    <option value="codigo_barras" @selected(request('search_field') == 'codigo_barras')>Código de Barras</option>
                                </select>
                            </div>
                            <div>
                                <x-input-label for="search_value" value="Valor a Pesquisar" />
                                <x-text-input id="search_value" name="search_value" type="text" class="mt-1 block w-full" :value="request('search_value')" placeholder="Digite aqui..." />
                            </div>
                            <div class="py-1.5 text-xs">
                                <x-primary-button>Pesquisar</x-primary-button>
                                <a href="{{ route('produtos.index') }}" class="text-sm text-gray-600 dark:text-gray-400">Limpar</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabela de Resultados --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Codigo</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th> {{-- <-- ADICIONADO --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estoque</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Preco Venda</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @forelse ($produtos as $produto)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produto->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $produto->nome }}</td>
                                        {{-- Célula da nova coluna com texto amigável --}}
                                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                                            @switch($produto->tipo)
                                                @case('materia_prima')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Matéria-Prima</span>
                                                    @break
                                                @case('produto_acabado')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Produção</span>
                                                    @break
                                                    @case('servico')
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Servico</span>
                                                    @break
                                                @default
                                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Venda Direta</span>
                                            @endswitch
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ number_format($produto->estoque_atual, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">R$ {{ number_format($produto->preco_venda, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('produtos.edit', $produto->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        {{-- Colspan atualizado para 6 colunas --}}
                                        <td colspan="6" class="px-6 py-4 text-center">Nenhum produto encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $produtos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>