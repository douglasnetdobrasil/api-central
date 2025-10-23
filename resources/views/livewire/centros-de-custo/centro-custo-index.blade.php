<div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
    <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
        <div class="flex justify-between items-center mb-6">
            {{-- Campo de Busca --}}
            <div class="w-1/3">
                <input 
                    type="text" 
                    wire:model.live.debounce.300ms="search" 
                    placeholder="Buscar por nome ou código..."
                    class="w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600 focus:ring-indigo-500 focus:border-indigo-500"
                >
            </div>
            <a href="{{ route('centros-custo.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">
                Novo Centro de Custo
            </a>
        </div>
        
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-400 rounded">
                {{ session('success') }}
            </div>
        @endif
        
        {{-- Cabeçalho da Tabela --}}
        <div class="flex bg-gray-50 dark:bg-gray-700 p-2 rounded-t-lg font-bold text-sm">
            <div class="w-2/5">NOME</div>
            <div class="w-1/5">CÓDIGO</div>
            <div class="w-1/5">TIPO</div>
            <div class="w-1/5 text-center">STATUS</div>
            <div class="w-1/5 text-right">AÇÕES</div>
        </div>

        {{-- Corpo da Tabela (Árvore) --}}
        <div class="tree-container border-l border-r border-b dark:border-gray-700 rounded-b-lg">
            @forelse ($centrosCusto as $centro)
                {{-- Usamos o seu blade partial, mas agora passamos o nível de profundidade --}}
                @include('centros_custo._item_tree', ['item' => $centro, 'level' => 0])
            @empty
                <div class="p-4 text-center text-gray-500">
                    Nenhum centro de custo encontrado.
                </div>
            @endforelse
        </div>
    </div>
</div>