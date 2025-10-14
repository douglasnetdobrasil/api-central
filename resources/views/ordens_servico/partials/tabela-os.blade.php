<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-700">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipamento</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Entrada</th>
                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($ordensServico as $os)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">{{ $os->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->cliente->nome ?? 'N/A' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->equipamento }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        {{-- Badge de Status com cor condicional --}}
                        @php
                            $statusClasses = [
                                'Aberta' => 'bg-blue-100 text-blue-800',
                                'Aprovada' => 'bg-green-100 text-green-800',
                                'Em Execução' => 'bg-yellow-100 text-yellow-800',
                                'Concluída' => 'bg-purple-100 text-purple-800',
                                'Faturada' => 'bg-gray-100 text-gray-800',
                                'Cancelada' => 'bg-red-100 text-red-800',
                                'Aguardando Aprovação' => 'bg-orange-100 text-orange-800',
                                'Aguardando Peças' => 'bg-pink-100 text-pink-800',
                            ];
                            $class = $statusClasses[$os->status] ?? 'bg-gray-200 text-gray-900';
                        @endphp
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $class }}">
                            {{ $os->status }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ $os->data_entrada->format('d/m/Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('ordens-servico.show', $os) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-200 mr-3">Ver</a>
                        <a href="{{ route('ordens-servico.edit', $os) }}" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-200 mr-3">Editar</a>
                        <form action="{{ route('ordens-servico.destroy', $os) }}" method="POST" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir esta OS?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-200">Excluir</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">Nenhuma Ordem de Serviço encontrada.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-6">
    {{ $ordensServico->links() }}
</div>