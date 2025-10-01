<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- Adicionar filtros aqui no futuro --}}
                    <div class="overflow-x-auto">
                    <table class="min-w-full divide-y dark:divide-gray-700">
    <thead>
        <tr>
            <th class="px-4 py-2 text-left">Descrição</th>
            <th class="px-4 py-2 text-left">Categoria</th> {{-- <-- COLUNA ADICIONADA --}}
            <th class="px-4 py-2 text-left">Fornecedor</th>
            <th class="px-4 py-2 text-center">Vencimento</th>
            <th class="px-4 py-2 text-right">Valor Total</th>
            <th class="px-4 py-2 text-center">Status</th>
            <th class="px-4 py-2 text-center">Ações</th>
        </tr>
    </thead>
    <tbody class="dark:bg-gray-800 divide-y dark:divide-gray-700">
        @forelse($contas as $conta)
            {{-- A estilização da cor agora está na linha principal --}}
            <tr style="border-left: 5px solid {{ $conta->categoriaContaAPagar->cor ?? 'transparent' }};">
                <td class="px-4 py-3">{{ $conta->descricao }}</td>
                
                {{-- A CÉLULA DA CATEGORIA FOI MOVIDA PARA CÁ --}}
                <td class="px-4 py-3">
                    @if($conta->categoriaContaAPagar)
                        @if($conta->categoriaContaAPagar->parent)
                            <span class="text-xs text-gray-500">{{ $conta->categoriaContaAPagar->parent->nome }} ></span><br>
                        @endif
                        <span class="font-semibold">{{ $conta->categoriaContaAPagar->nome }}</span>
                    @else
                        <span class="text-gray-500">N/A</span>
                    @endif
                </td>

                <td class="px-4 py-3">{{ $conta->fornecedor->razao_social ?? 'N/A' }}</td>
                <td class="px-4 py-3 text-center">{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                <td class="px-4 py-3 text-right">R$ {{ number_format($conta->valor_total, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                        @if($conta->status == 'Paga') bg-green-100 text-green-800 @endif
                        @if($conta->status == 'A Pagar') bg-yellow-100 text-yellow-800 @endif
                        @if(\Carbon\Carbon::parse($conta->data_vencimento)->isPast() && $conta->status != 'Paga') bg-red-100 text-red-800 @endif
                    ">
                        {{ (\Carbon\Carbon::parse($conta->data_vencimento)->isPast() && $conta->status != 'Paga') ? 'Vencida' : $conta->status }}
                    </span>
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium">
                    <div class="flex items-center justify-center space-x-4">
                        <a href="{{ route('contas_a_pagar.edit', $conta) }}" class="text-indigo-600 hover:text-indigo-900" title="Editar">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M17.414 2.586a2 2 0 00-2.828 0L7 10.172V13h2.828l7.586-7.586a2 2 0 000-2.828z" /><path fill-rule="evenodd" d="M2 6a2 2 0 012-2h4a1 1 0 010 2H4v10h10v-4a1 1 0 112 0v4a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" clip-rule="evenodd" /></svg>
                        </a>
                        <button wire:click="$dispatch('abrirModalPagar', { contaId: {{ $conta->id }} })" class="text-blue-600 hover:text-blue-900" title="Pagar (Dar Baixa)">
                             <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M8.433 7.418c.158-.103.346-.196.567-.267v1.698a2.5 2.5 0 00-1.168-.217c-1.38 0-2.5 1.12-2.5 2.5s1.12 2.5 2.5 2.5c.62 0 1.167-.23 1.583-.604a3.003 3.003 0 00.167-1.895v-1.698c.22.07.408.163.567.267C11.433 9.418 13 11.042 13 13.5c0 2.485-2.015 4.5-4.5 4.5S4 15.985 4 13.5c0-2.458 1.567-4.082 3-4.996.433-.28.955-.536 1.433-.786zM12 4a1 1 0 011 1v1a1 1 0 11-2 0V5a1 1 0 011-1z" /><path fill-rule="evenodd" d="M14 2a2 2 0 012 2v8a2 2 0 01-2 2h- телевизор-8a2 2 0 01-2-2V4a2 2 0 012-2h8zM8 4H6v1h2V4z" clip-rule="evenodd" /></svg>
                        </button>
                        <button wire:click="$dispatch('abrirModalHistorico', { contaId: {{ $conta->id }} })" class="text-gray-500 hover:text-gray-800 dark:hover:text-white" title="Ver Histórico de Pagamentos">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm0 10a2 2 0 00-2 2v.5a.5.5 0 00.5.5h15a.5.5 0 00.5-.5V16a2 2 0 00-2-2H4z" clip-rule="evenodd" /></svg>
                                </button>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="7" class="text-center py-6">Nenhuma conta a pagar encontrada.</td></tr>
        @endforelse
    </tbody>
</table>
                    </div>
                     <div class="mt-4">{{ $contas->links() }}</div>
                </div>
                @livewire('contas-a-pagar.pagar-modal')
                @livewire('contas-a-pagar.historico-pagamentos-modal')
            </div>