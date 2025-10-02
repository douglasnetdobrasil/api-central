<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">

        <div class="flex items-end space-x-4 mb-4">
            <div>
                <label for="data_inicio">Data Início</label>
                <input type="date" wire:model.live="data_inicio" class="block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
            </div>
            <div>
                <label for="data_fim">Data Fim</label>
                <input type="date" wire:model.live="data_fim" class="block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Data/Hora</th>
                        <th class="px-4 py-2 text-left">Tipo</th>
                        <th class="px-4 py-2 text-left">Origem</th>
                        <th class="px-4 py-2 text-right">Entrada</th>
                        <th class="px-4 py-2 text-right">Saída</th>
                        <th class="px-4 py-2 text-right">Saldo Final</th>
                        <th class="px-4 py-2 text-left">Usuário</th>
                    </tr>
                </thead>
                <tbody class="dark:bg-gray-800 divide-y dark:divide-gray-700 text-sm">
                    @forelse($movimentos as $movimento)
                        @php
                            $isEntrada = $movimento->saldo_novo > $movimento->saldo_anterior;
                        @endphp
                        <tr>
                            <td class="px-4 py-3">{{ \Carbon\Carbon::parse($movimento->created_at)->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">{{ str_replace('_', ' ', $movimento->tipo_movimento) }}</td>
                            <td class="px-4 py-3">
                                {{-- << INÍCIO DA CORREÇÃO >> --}}
                                @if($movimento->origem_type == 'App\Models\Venda' && $movimento->origem)
                                    <a href="#" class="text-indigo-600 hover:underline">Venda #{{ $movimento->origem_id }}</a>
                                @elseif($movimento->origem_type == 'App\Models\Compra' && $movimento->origem)
                                    <a href="#" class="text-indigo-600 hover:underline">Compra #{{ $movimento->origem_id }}</a>
                                {{-- Adiciona a condição para a origem ser o próprio Produto --}}
                                @elseif($movimento->origem_type == 'App\Models\Produto' && $movimento->origem)
                                    <span class="text-gray-500">Ajuste/Entrada Manual</span>
                                @else
                                    -
                                @endif
                                {{-- << FIM DA CORREÇÃO >> --}}
                            </td>
                            <td class="px-4 py-3 text-right text-green-600">
                                {{ $isEntrada ? number_format($movimento->quantidade, 2, ',', '.') : '' }}
                            </td>
                            <td class="px-4 py-3 text-right text-red-600">
                                {{ !$isEntrada ? number_format($movimento->quantidade, 2, ',', '.') : '' }}
                            </td>
                            <td class="px-4 py-3 text-right font-bold">{{ number_format($movimento->saldo_novo, 2, ',', '.') }}</td>
                            <td class="px-4 py-3">{{ $movimento->user->name ?? 'Sistema' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-6">Nenhuma movimentação encontrada para este período.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $movimentos->links() }}</div>
    </div>
</div>