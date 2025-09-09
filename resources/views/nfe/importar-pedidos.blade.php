<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Importar Pedidos para Emissão de NF-e
        </h2>
    </x-slot>
    @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Sucesso</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <p class="font-bold">Erro</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <form action="{{ route('nfe.prepararAgrupada') }}" method="POST" id="agrupar-form">
                @csrf
            </form>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    <div class="flex justify-between items-center mb-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Selecione um ou mais pedidos do mesmo cliente para agrupar em uma única NF-e.
                        </p>
                        <button id="agrupar-btn" type="submit" form="agrupar-form" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150" disabled>
                            Agrupar e Prosseguir
                        </button>
                    </div>

                    <div class="overflow-x-auto border border-gray-200 dark:border-gray-700 rounded-lg">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="p-3 w-4"><input type="checkbox" id="selecionar-todos"></th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Pedido</th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                    <th class="p-3 text-left text-xs font-medium text-gray-500 uppercase">Valor</th>
                                    <th class="p-3 text-center text-xs font-medium text-gray-500 uppercase">Ação Individual</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse ($vendasParaEmitir as $venda)
                                    <tr>
                                        <td class="p-3">
                                            <input type="checkbox" class="pedido-checkbox" name="venda_ids[]" value="{{ $venda->id }}" data-cliente-id="{{ $venda->cliente->id ?? '' }}" form="agrupar-form">
                                        </td>
                                        <td class="p-3 whitespace-nowrap">#{{ $venda->id }}</td>
                                        <td class="p-3 whitespace-nowrap">{{ $venda->cliente->nome ?? 'N/A' }}</td>
                                        <td class="p-3 whitespace-nowrap">{{ $venda->created_at->format('d/m/Y') }}</td>
                                        <td class="p-3 whitespace-nowrap">R$ {{ number_format($venda->total, 2, ',', '.') }}</td>
                                        <td class="p-3 text-center whitespace-nowrap">
                                            <form action="{{ route('pedidos.emitirNFe', $venda) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja emitir a NF-e para este pedido?');">
                                                @csrf
                                                <button type="submit" class="font-medium text-indigo-600 hover:text-indigo-900 focus:outline-none">
                                                    Emitir só este
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="p-8 text-center text-gray-500">Nenhum pedido pendente de emissão encontrado.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-4">
                        {{ $vendasParaEmitir->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selecionarTodos = document.getElementById('selecionar-todos');
            const checkboxes = document.querySelectorAll('.pedido-checkbox');
            const agruparBtn = document.getElementById('agrupar-btn');

            function toggleAgruparBtn() {
                const selecionados = document.querySelectorAll('.pedido-checkbox:checked');

                // Desabilita se não houver NENHUM selecionado
                if (selecionados.length === 0) {
                    agruparBtn.disabled = true;
                    return;
                }
                
                // Habilita se tiver apenas UM selecionado
                if (selecionados.length === 1) {
                    agruparBtn.disabled = false;
                    return;
                }

                // Se tiver mais de um, verifica se são do mesmo cliente
                const primeiroClienteId = selecionados[0].getAttribute('data-cliente-id');
                let todosDoMesmoCliente = true;
                for (let i = 1; i < selecionados.length; i++) {
                    if (selecionados[i].getAttribute('data-cliente-id') !== primeiroClienteId) {
                        todosDoMesmoCliente = false;
                        break;
                    }
                }
                agruparBtn.disabled = !todosDoMesmoCliente;
            }

            selecionarTodos.addEventListener('change', function (e) {
                checkboxes.forEach(checkbox => {
                    checkbox.checked = e.target.checked;
                });
                toggleAgruparBtn();
            });

            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', toggleAgruparBtn);
            });
        });
    </script>
   
</x-app-layout>