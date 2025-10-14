<x-app-layout>
    {{-- Adicionando o Alpine.js via CDN para simplicidade --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Contagem do Inventário #{{ $inventario->id }}
            </h2>
        </div>
    </x-slot>

    <div class="py-12" x-data="inventarioContagem()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            {{-- Card de Progresso e Ações --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Progresso da Contagem</h3>
                    <div class="mt-4">
                        <div class="flex justify-between mb-1">
                            <span class="text-base font-medium text-blue-700 dark:text-white">Itens Contados</span>
                            <span class="text-sm font-medium text-blue-700 dark:text-white">{{ $stats['items_contados'] }} de {{ $stats['total_items'] }}</span>
                        </div>
                        @php $progress = $stats['total_items'] > 0 ? ($stats['items_contados'] / $stats['total_items']) * 100 : 0; @endphp
                        <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                            <div class="bg-blue-600 h-2.5 rounded-full" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>
                    <div class="mt-6 text-right">
                        <form method="POST" action="{{ route('inventarios.marcarContado', $inventario) }}">
                            @csrf
                            <x-primary-button type="submit">
                                Contagem Concluída, Ir para Reconciliação
                            </x-primary-button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Tabela de Contagem --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- FORMULÁRIO DE BUSCA FUNCIONAL --}}
                    <div class="mb-4">
                        <form method="GET" action="{{ route('inventarios.contagem', $inventario) }}">
                            <div class="flex items-center space-x-2">
                                <input 
                                    type="text" 
                                    name="busca" 
                                    placeholder="Buscar por Nome, Código de Barras ou ID..." 
                                    class="w-full rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600"
                                    value="{{ request('busca') }}">
                                
                                <x-primary-button type="submit">
                                    Buscar
                                </x-primary-button>

                                @if(request('busca'))
                                    <a href="{{ route('inventarios.contagem', $inventario) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                                        Limpar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Produto</th>
                                    <th class="px-4 py-2 text-center">Estoque Esperado</th>
                                    <th class="px-4 py-2 text-center" style="width: 150px;">Qtd. Contada</th>
                                    <th class="px-4 py-2 text-center">Diferença</th>
                                    <th class="px-4 py-2 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($itens as $item)
                                    <tr id="item-row-{{ $item->id }}">
                                        {{-- COLUNA DE PRODUTO COM MAIS DETALHES --}}
                                        <td class="px-4 py-2 align-top">
                                            <div class="font-bold text-gray-800 dark:text-gray-200">{{ $item->produto->nome }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">ID: {{ $item->produto->id }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">Cód. Barras: {{ $item->produto->codigo_barras ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-2 text-center align-top">{{ $item->estoque_esperado }}</td>
                                        <td class="px-4 py-2 align-top">
                                            <input type="number" 
                                                   step="0.001"
                                                   class="w-full text-center rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-600"
                                                   value="{{ $item->quantidade_contada }}"
                                                   @input.debounce.750ms="salvarContagem({{ $item->id }}, $event)">
                                        </td>
                                        <td class="px-4 py-2 text-center font-bold align-top" id="diferenca-{{ $item->id }}">
                                            {{ $item->diferenca != 0 ? $item->diferenca : '' }}
                                        </td>
                                        <td class="px-4 py-2 text-center text-xs align-top" id="status-{{ $item->id }}">
                                            {{-- O status será atualizado via JS --}}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4">
                                            @if(request('busca'))
                                                Nenhum produto encontrado para a busca "{{ request('busca') }}".
                                            @else
                                                Nenhum item neste inventário.
                                            @endif
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $itens->links() }}
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Lógica JavaScript com Alpine.js (sem alterações) --}}
        <script>
            function inventarioContagem() {
                return {
                    salvarContagem(itemId, event) {
                        const input = event.target;
                        const quantidade = input.value;
                        const statusEl = document.getElementById(`status-${itemId}`);
                        const diferencaEl = document.getElementById(`diferenca-${itemId}`);
                        const rowEl = document.getElementById(`item-row-${itemId}`);

                        if (quantidade === '' || quantidade < 0) {
                            return;
                        }

                        statusEl.textContent = 'Salvando...';
                        statusEl.classList.remove('text-green-500', 'text-red-500');
                        
                        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                        fetch(`/inventarios/item/${itemId}`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({
                                quantidade_contada: quantidade
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if(data.success) {
                                statusEl.textContent = 'Salvo!';
                                statusEl.classList.add('text-green-500');
                                
                                diferencaEl.textContent = data.diferenca;
                                if(data.diferenca > 0) {
                                    diferencaEl.className = 'px-4 py-2 text-center font-bold align-top text-green-500';
                                } else if (data.diferenca < 0) {
                                    diferencaEl.className = 'px-4 py-2 text-center font-bold align-top text-red-500';
                                } else {
                                    diferencaEl.className = 'px-4 py-2 text-center font-bold align-top';
                                }

                                rowEl.classList.add('bg-green-50', 'dark:bg-gray-700');

                            } else {
                                statusEl.textContent = 'Erro!';
                                statusEl.classList.add('text-red-500');
                            }
                        })
                        .catch(() => {
                            statusEl.textContent = 'Erro de rede!';
                            statusEl.classList.add('text-red-500');
                        });
                    }
                }
            }
        </script>
        {{-- Lembre-se de ter esta meta tag no seu layout principal (ex: app.blade.php) dentro do <head> --}}
        {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    </div>
</x-app-layout>