<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Análise da Cotação #{{ $cotacao->id }}
            </h2>
            <a href="{{ route('cotacoes.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                Voltar para a Lista
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-full mx-auto sm:px-6 lg:px-8">

            {{-- DADOS GERAIS --}}
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 md:p-8 border-b border-gray-200 dark:border-gray-700">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Detalhes da Cotação</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Descrição</span>
                            <p class="text-gray-800 dark:text-gray-200">{{ $cotacao->descricao ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Data</span>
                            <p class="text-gray-800 dark:text-gray-200">{{ $cotacao->data_cotacao?->format('d/m/Y') ?? 'Data não informada' }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Status</span>
                            <p class="text-gray-800 dark:text-gray-200">
                                @if($cotacao->status == 'aberta')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Aguardando Respostas</span>
                                @elseif($cotacao->status == 'finalizada')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Finalizada</span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Cancelada</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            {{-- TABELA COMPARATIVA --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Quadro Comparativo de Preços</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Produto</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Qtd. Solicitada</th>
                                @foreach($cotacao->fornecedores as $fornecedor)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">{{ $fornecedor->razao_social }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($cotacao->produtos as $produto)
                                <tr>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $produto->nome }}</td>
                                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400 text-center">{{ $produto->pivot->quantidade }}</td>
                                    
                                    @foreach ($cotacao->fornecedores as $fornecedor)
                                        <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                            @php
                                                // Usamos a matriz que criamos no controller para buscar a resposta
                                                $resposta = $respostasGrid[$produto->id][$fornecedor->id] ?? null;
                                            @endphp
                                            
                                            @if($resposta)
                                                <span class="font-semibold">R$ {{ number_format($resposta->preco_ofertado, 2, ',', '.') }}</span>
                                                <div class="text-xs text-gray-400">
                                                    Entrega: {{ $resposta->prazo_entrega_dias ?? 'N/I' }} dias
                                                </div>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ 2 + $cotacao->fornecedores->count() }}" class="px-6 py-4 text-center text-gray-500">
                                        Nenhum produto nesta cotação.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>