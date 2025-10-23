<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Serviço (Executado por)</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qtd/Horas</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vlr. Unit.</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($os->servicos as $item)
            <tr>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                    {{ $item->servico->nome }}
                    @if($item->tecnico)
                        <span class="block text-xs text-gray-500 dark:text-gray-400">Téc: {{ $item->tecnico->name }}</span>
                    @endif
                </td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ (float)$item->quantidade }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-semibold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-right">
                    <button type="button" wire:click="removeServico({{ $item->id }})" wire:confirm="Tem certeza que deseja remover este serviço?" class="text-red-500 hover:text-red-700" title="Remover Item">
                        Remover
                    </button>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum serviço adicionado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>