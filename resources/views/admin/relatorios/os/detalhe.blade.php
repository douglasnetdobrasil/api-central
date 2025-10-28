<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
             <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $titulo }} ({{ $ordensServico->total() }} Ordens de Serviço) {{-- Ajustado para OS --}}
            </h2>
            {{-- Link para voltar ao Dashboard OS --}}
            <a href="{{ route('admin.relatorios.os.dashboard', request()->only(['data_inicio', 'data_fim', 'cliente_id', 'tecnico_id'])) }}" 
               class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-800">
                &larr; Voltar ao Dashboard OS
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 overflow-x-auto">
                    
                    {{-- Tabela de Ordens de Serviço Detalhada --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OS #</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Equipamento</th>
                                <th scope="col" class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Técnico</th>
                                <th scope="col" class="px-4 py-2 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Data Entrada</th>
                                <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($ordensServico as $os) {{-- Ajustado para $ordensServico --}}
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition duration-150">
                                    <td class="px-4 py-2 whitespace-nowrap">
                                        {{-- Link para a edição da OS individual --}}
                                        <a href="{{ route('ordens-servico.edit', $os) }}" 
                                           class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                           {{ $os->id }} {{-- Ou $os->numero_os se existir --}}
                                        </a>
                                    </td>
                                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $os->cliente->nome ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $os->equipamento ?? ($os->clienteEquipamento->descricao ?? '-') }}</td>
                                    <td class="px-4 py-2 text-center text-sm text-gray-600 dark:text-gray-300">{{ $os->status }}</td>
                                    <td class="px-4 py-2 text-sm text-gray-600 dark:text-gray-300">{{ $os->tecnico->name ?? 'N/A' }}</td>
                                    <td class="px-4 py-2 text-center text-sm text-gray-600 dark:text-gray-300">{{ $os->data_entrada ? \Carbon\Carbon::parse($os->data_entrada)->format('d/m/Y') : '-'}}</td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-600 dark:text-gray-300">R$ {{ number_format($os->valor_total, 2, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-8 text-gray-500 dark:text-gray-400">Nenhuma Ordem de Serviço encontrada para este filtro.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Paginação --}}
                    <div class="mt-4">
                        {{-- Mantém os parâmetros da URL (tipo, id, datas) na paginação --}}
                        {{ $ordensServico->withQueryString()->links() }} 
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>