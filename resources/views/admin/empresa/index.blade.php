<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciamento de Empresas
            </h2>
            <a href="{{ route('empresa.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Nova Empresa
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Formulário de Busca --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <form action="{{ route('empresa.index') }}" method="GET">
                    <div class="flex items-center gap-4">
                        <div class="flex-grow">
                            <x-input-label for="search" value="Buscar Empresa" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" 
                                          :value="request('search')" 
                                          placeholder="Buscar por Razão Social ou CNPJ..."/>
                        </div>
                        <x-primary-button class="mt-6">Buscar</x-primary-button>
                        <a href="{{ route('empresa.index') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- Tabela de Dados --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razão Social</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CNPJ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nicho</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @forelse ($empresas as $empresa)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $empresa->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $empresa->razao_social }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $empresa->cnpj }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ Str::ucfirst($empresa->nicho_negocio) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        {{-- O link de Editar agora aponta para a rota de edição do admin --}}
                                        <a href="{{ route('empresa.edit', $empresa) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        <form action="{{ route('empresa.destroy', $empresa) }}" method="POST" class="inline-block" onsubmit="return confirm('Tem certeza que deseja excluir esta empresa?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">Nenhuma empresa encontrada.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{-- Adicionado o withQueryString() para manter a busca durante a paginação --}}
                    {{ $empresas->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>