<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Gerenciar Regras Tributárias
            </h2>
            {{-- A rota de criação agora é 'regras-tributarias.create' --}}
            <a href="{{ route('admin.regras-tributarias.create') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-500 active:bg-indigo-700">
                Nova Regra
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
                                    {{-- Novas colunas para refletir a regra --}}
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Descrição</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">CFOP</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Origem</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">Destino</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500">CRT</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                {{-- O loop virá da variável $regras --}}
                                @forelse ($regras as $regra)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $regra->descricao }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $regra->cfop }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $regra->uf_origem ?? 'Todas' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $regra->uf_destino ?? 'Todas' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $regra->crt_emitente ?? 'Todos' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            {{-- A rota de edição agora é 'regras-tributarias.edit' --}}
                                            <a href="{{ route('admin.regras-tributarias.edit', $regra->id) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-center text-gray-500">
                                            Nenhuma regra tributária encontrada.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{-- Paginação --}}
                    <div class="mt-4">
                        {{ $regras->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>