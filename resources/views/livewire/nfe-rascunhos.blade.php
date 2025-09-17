<div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Rascunhos de NF-e (Em Digitação)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID da Venda</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($rascunhos as $rascunho)
                                    <tr>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $rascunho->id }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $rascunho->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-4 py-4 whitespace-nowrap">{{ $rascunho->updated_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-4 text-right whitespace-nowrap">R$ {{ number_format($rascunho->total, 2, ',', '.') }}</td>
                                        <td class="px-4 py-4 text-center text-sm font-medium">
                                            <a href="{{ route('avulsa.editar', $rascunho) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                Continuar Digitação
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                     <tr><td colspan="5" class="px-6 py-8 text-center text-gray-500">Nenhum rascunho encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $rascunhos->links() }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>