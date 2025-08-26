<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gestão de Fornecedores') }}
            </h2>
            <a href="{{ route('fornecedores.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Novo Fornecedor
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Formulário de Pesquisa com ComboBox --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="font-semibold text-lg text-gray-800 dark:text-gray-200 leading-tight mb-4">
                        Pesquisar Fornecedores
                    </h3>
                    <form action="{{ route('fornecedores.index') }}" method="GET">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            
                            {{-- Campo 1: ComboBox para escolher o tipo de pesquisa --}}
                            <div>
                                <x-input-label for="search_field" value="Pesquisar por" />
                                <select name="search_field" id="search_field" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="razao_social" @selected(request('search_field', 'razao_social') == 'razao_social')>Razão Social</option>
                                    <option value="cpf_cnpj" @selected(request('search_field') == 'cpf_cnpj')>CPF/CNPJ</option>
                                </select>
                            </div>

                            {{-- Campo 2: Input para o valor da pesquisa --}}
                            <div>
                                <x-input-label for="search_value" value="Valor a Pesquisar" />
                                <x-text-input id="search_value" name="search_value" type="text" class="mt-1 block w-full" :value="request('search_value')" placeholder="Digite aqui..." />
                            </div>

                            {{-- Campo 3: Botões (com alinhamento corrigido) --}}
                            <div>
                                <x-input-label value="&nbsp;" /> {{-- Rótulo invisível para alinhamento --}}
                                <div class="flex items-center gap-4">
                                    <x-primary-button class="py-1.5 text-xs">
                                        Pesquisar
                                    </x-primary-button>
                                    <a href="{{ route('fornecedores.index') }}" class="text-sm text-gray-600 dark:text-gray-400">Limpar</a>
                                </div>
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
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razão Social</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome Fantasia</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CPF/CNPJ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefone</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @forelse ($fornecedores as $fornecedor)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $fornecedor->razao_social }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $fornecedor->nome_fantasia }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $fornecedor->cpf_cnpj }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $fornecedor->telefone }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('fornecedores.edit', $fornecedor->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">Nenhum fornecedor encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $fornecedores->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>