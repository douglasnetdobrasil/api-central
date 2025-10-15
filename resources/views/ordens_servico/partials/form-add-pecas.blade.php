<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
    Peças e Produtos Utilizados
</h3>

<form action="{{ route('os.produtos.store', $ordemServico->id) }}" method="POST" class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div class="md:col-span-2">
            <label for="produto_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Produto/Peça *</label>
            <select id="produto_id" name="produto_id" class="select-search mt-1 block w-full" required>
                <option value="">Buscar peça ou produto...</option>
                @foreach ($pecas as $peca)
                    <option value="{{ $peca->id }}" data-preco="{{ $peca->preco_venda }}">
                        {{ $peca->nome }} (Estoque: {{ (float)$peca->estoque_atual }})
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="quantidade" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Quantidade *</label>
            <input type="number" id="quantidade" name="quantidade" value="1" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
        </div>
        <div>
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest">
                <i class="fas fa-plus mr-2"></i> Adicionar Peça
            </button>
        </div>
    </div>
</form>

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
            @forelse ($ordemServico->produtos as $item)
            <tr>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $item->produto->nome }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">{{ (float)$item->quantidade }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500 dark:text-gray-300">R$ {{ number_format($item->preco_unitario, 2, ',', '.') }}</td>
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 font-semibold">R$ {{ number_format($item->subtotal, 2, ',', '.') }}</td>
                <form action="{{ route('os.produtos.destroy', $item->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-red-500 hover:text-red-700" title="Remover Item" onclick="return confirm('Tem certeza que deseja remover esta peça?')">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>
            </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhuma peça ou produto adicionado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>