<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 text-gray-900 dark:text-gray-100">
        <div class="mb-4">
            <input 
                type="text" 
                wire:model.live.debounce.300ms="search" 
                placeholder="Buscar por nome ou código do produto..." 
                class="block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y dark:divide-gray-700">
                <thead>
                    <tr>
                        <th class="px-4 py-2 text-left">Produto</th>
                        <th class="px-4 py-2 text-right">Estoque Atual</th>
                        <th class="px-4 py-2 text-center">Ação</th>
                    </tr>
                </thead>
                <tbody class="dark:bg-gray-800 divide-y dark:divide-gray-700">
                    @forelse($produtos as $produto)
                        <tr>
                            <td class="px-4 py-3 font-semibold">{{ $produto->nome }}</td>
                            <td class="px-4 py-3 text-right">{{ $produto->estoque_atual }} {{ $produto->unidade }}</td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('estoque.show', $produto) }}" class="text-indigo-600 hover:text-indigo-900">
                                    Ver Movimentações
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-center py-6">Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $produtos->links() }}</div>
    </div>
</div>