<h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
    Serviços Prestados
</h3>

<form action="{{ route('os.servicos.store', $ordemServico->id) }}" method="POST" class="mb-6 p-4 border border-gray-200 dark:border-gray-700 rounded-md">
    @csrf
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
        <div class="md:col-span-2">
            <label for="servico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Serviço *</label>
            <select name="servico_id" id="servico_id" class="select-search mt-1 block w-full" required>
                <option value="">Buscar serviço...</option>
                @foreach ($servicos as $servico)
                    <option value="{{ $servico->id }}">{{ $servico->nome }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="tecnico_id_servico" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Executado por</label>
            <select name="tecnico_id" id="tecnico_id_servico" class="select-search mt-1 block w-full">
                <option value="">(Não especificado)</option>
                @foreach ($tecnicos as $tecnico)
                    <option value="{{ $tecnico->id }}">{{ $tecnico->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="quantidade_servico" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Qtd/Horas *</label>
            <input type="number" name="quantidade" id="quantidade_servico" value="1" min="0.01" step="0.01" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
        </div>
        <div>
            <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest">
                <i class="fas fa-plus mr-2"></i> Adicionar Serviço
            </button>
        </div>
    </div>
</form>

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
            @forelse ($ordemServico->servicos as $item)
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
                <form action="{{ route('os.servicos.destroy', $item->id) }}" method="POST">
    @csrf
    @method('DELETE')
    <button type="submit" class="text-red-500 hover:text-red-700" title="Remover Item" onclick="return confirm('Tem certeza que deseja remover este serviço?')">
        <i class="fas fa-trash-alt"></i>
    </button>
</form>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Nenhum serviço adicionado.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>