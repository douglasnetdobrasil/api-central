<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-900">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Produto</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Qtd.</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vlr. Unit.</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ações</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($os->produtos as $item)
            <tr>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item->produto->nome }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ (float)$item->quantidade }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-semibold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                <td class="px-4 py-3 text-right">
                    {{-- O wire:click chama o método de remoção, passando o ID do item. O wire:confirm adiciona um pop-up de confirmação. --}}
                    <button type="button" wire:click="removePeca({{ $item->id }})" wire:confirm="Tem certeza que deseja remover esta peça?" class="text-red-500 hover:text-red-700" title="Remover Item">
                        Remover
                    </button>
                </td>
            </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma peça ou produto adicionado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>