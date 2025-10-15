<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OS #</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipamento</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Entrada</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($ordensServico as $os)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $os->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->cliente->nome }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->equipamento }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->data_entrada->format('d/m/Y H:i') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        {{-- **MELHORIA: STATUS COM CORES** --}}
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                            {{-- Lógica de cores baseada no status (exemplo) --}}
                            @if($os->status == 'Concluída') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                            @elseif($os->status == 'Cancelada') bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                            @elseif(in_array($os->status, ['Aguardando Aprovação do Cliente', 'Aguardando Peças'])) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                            @else bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 @endif">
                            {{ $os->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('ordens-servico.show', $os) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900">Detalhes</a>
                        <a href="{{ route('ordens-servico.edit', $os) }}" class="ml-4 text-gray-600 dark:text-gray-400 hover:text-gray-900">Editar</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-300">
                        Nenhuma Ordem de Serviço encontrada.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $ordensServico->links() }}
</div>