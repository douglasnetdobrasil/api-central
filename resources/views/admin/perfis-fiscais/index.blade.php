<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Perfis Fiscais Padrão
            </h2>
            <a href="{{ route('admin.perfis-fiscais.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700">
                Novo Perfil
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Perfil</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">CFOP Padrão</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">CSOSN Padrão</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($perfis as $perfil)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $perfil->nome_perfil }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $perfil->cfop_padrao }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $perfil->csosn_padrao }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.perfis-fiscais.edit', $perfil->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            Nenhum perfil fiscal encontrado.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $perfis->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>