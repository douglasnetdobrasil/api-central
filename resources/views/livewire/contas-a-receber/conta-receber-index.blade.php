<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
    <div class="flex justify-end mb-4">
            <button wire:click="$dispatch('abrirModalFiltrosContasReceber')" class="flex items-center space-x-2 px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600" title="Filtros e Relatórios">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" /></svg>
                <span>Filtros</span>
            </button>
        </div>

        <div class="overflow-x-auto">
        <table class="min-w-full divide-y dark:divide-gray-700">
    <thead>
        <tr>
            <th class="px-4 py-2 text-left">Cliente</th>
            <th class="px-4 py-2 text-left">Origem</th>
            <th class="px-4 py-2 text-center">Vencimento</th>
            <th class="px-4 py-2 text-right">Valor Total</th>
            <th class="px-4 py-2 text-right">Valor Pendente</th>
            <th class="px-4 py-2 text-center">Status</th>
            <th class="px-4 py-2 text-center">Ações</th>
        </tr>
    </thead>
    <tbody class="dark:bg-gray-800 divide-y dark:divide-gray-700">
        @forelse($contas as $conta)
            <tr>
                {{-- << LÓGICA CORRIGIDA PARA EXIBIR O CLIENTE >> --}}
                <td class="px-4 py-3">
                    @if ($conta->venda && $conta->venda->cliente)
                        {{ $conta->venda->cliente->nome }}
                    @elseif ($conta->cliente)
                        {{ $conta->cliente->nome }}
                    @else
                        N/A
                    @endif
                </td>

                <td class="px-4 py-3">
                    @if($conta->venda_id)
                        Venda #{{ $conta->venda_id }}
                    @else
                        <span class="font-semibold text-gray-400">Manual</span>
                    @endif
                </td>

                <td class="px-4 py-3 text-center">{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-right">R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-right font-bold">R$ {{ number_format($conta->valor_pendente, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-center">
                    @php
                        $isVencida = \Carbon\Carbon::parse($conta->data_vencimento)->isPast() && $conta->status !== 'Recebido';
                        $statusClass = '';
                        $statusText = $conta->status;
                        if ($isVencida) {
                            $statusClass = 'bg-red-100 text-red-800';
                            $statusText = 'Vencida';
                        } else {
                            switch ($conta->status) {
                                case 'Recebido': $statusClass = 'bg-green-100 text-green-800'; break;
                                case 'Recebido Parcialmente': $statusClass = 'bg-blue-100 text-blue-800'; break;
                                default: $statusClass = 'bg-yellow-100 text-yellow-800'; break;
                            }
                        }
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
                        {{ $statusText }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center justify-center space-x-4">
                        <button wire:click="$dispatch('abrirModalReceber', { contaId: {{ $conta->id }} })" class="text-green-600 hover:text-green-900" title="Receber (Dar Baixa)">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M8.433 7.418c.158-.103.346-.196.567-.267v1.698a2.5 2.5 0 00-1.168-.217c-1.38 0-2.5 1.12-2.5 2.5s1.12 2.5 2.5 2.5c.62 0 1.167-.23 1.583-.604a3.003 3.003 0 00.167-1.895v-1.698c.22.07.408.163.567.267C11.433 9.418 13 11.042 13 13.5c0 2.485-2.015 4.5-4.5 4.5S4 15.985 4 13.5c0-2.458 1.567-4.082 3-4.996.433-.28.955-.536 1.433-.786zM12 4a1 1 0 011 1v1a1 1 0 11-2 0V5a1 1 0 011-1z" /><path fill-rule="evenodd" d="M14 2a2 2 0 012 2v8a2 2 0 01-2 2h-8a2 2 0 01-2-2V4a2 2 0 012-2h8zM8 4H6v1h2V4z" clip-rule="evenodd" /></svg>
                        </button>
                        <button wire:click="$dispatch('abrirModalHistoricoRecebimentos', { contaId: {{ $conta->id }} })" class="text-gray-500 hover:text-gray-800 dark:hover:text-white" title="Ver Histórico de Recebimentos">
                           <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 10a2 2 0 00-2 2v.5a.5.5 0 00.5.5h15a.5.5 0 00.5-.5V16a2 2 0 00-2-2H4z" clip-rule="evenodd" /></svg>
                        </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-6">Nenhuma conta a receber encontrada.</td></tr>
        @endforelse
    </tbody>
</table>
        </div>
         <div class="mt-4">{{ $contas->links() }}</div>
    </div>

    {{-- << NOVO >> Inclui os modais na página --}}
    @livewire('contas-a-receber.receber-modal')
    @livewire('contas-a-receber.historico-recebimentos-modal')
    @livewire('contas-a-receber.filtros-modal')
</div>