<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciamento de Empresas
            </h2>
            {{-- Botão para criar nova empresa (se você implementar a função 'create') --}}
            {{-- <a href="#" class="... btn ...">Nova Empresa</a> --}}
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Razão Social</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CNPJ</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nicho</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @forelse ($empresas as $empresa)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $empresa->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $empresa->razao_social }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $empresa->cnpj }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $empresa->nicho_negocio }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- Futuramente, um link para editar a empresa como admin --}}
                                            <a href="#" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center">Nenhuma empresa encontrada.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $empresas->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>