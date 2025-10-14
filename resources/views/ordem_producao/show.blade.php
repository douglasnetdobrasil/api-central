<x-app-layout>
<x-slot name="header">
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Detalhes da OP #{{ $ordemProducao->id }}
        </h2>
        <div class="flex space-x-2">

            @if ($ordemProducao->status == 'Planejada')
                <form action="{{ route('ordem-producao.iniciar', $ordemProducao) }}" method="POST" onsubmit="return confirm('Tem certeza que deseja iniciar esta produção? Esta ação dará baixa no estoque das matérias-primas.');">
                    @csrf
                    <x-primary-button type="submit">
                        Iniciar Produção
                    </x-primary-button>
                </form>

            @elseif ($ordemProducao->status == 'Em Produção')
                {{-- ======================================================= --}}
                {{-- |||||||||||||||||||| BOTÃO ATIVADO |||||||||||||||||||||| --}}
                {{-- ======================================================= --}}
                {{-- Alpine.js para controlar a visibilidade do modal/formulário --}}
                <div x-data="{ open: false }">
                    <x-primary-button @click="open = true" class="bg-green-600 hover:bg-green-700">
                        Finalizar Produção
                    </x-primary-button>

                    <div x-show="open" @click.away="open = false" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-xl w-full max-w-md">
                            <h3 class="text-lg font-bold mb-4">Finalizar Ordem de Produção</h3>
                            <form action="{{ route('ordem-producao.finalizar', $ordemProducao) }}" method="POST">
                                @csrf
                                <div>
                                    <label for="quantidade_produzida" class="block text-sm font-medium">Informe a Quantidade Realmente Produzida</label>
                                    <input type="number" step="1" name="quantidade_produzida" id="quantidade_produzida"
                                           value="{{ $ordemProducao->quantidade_planejada }}"
                                           class="mt-1 block w-full rounded-md shadow-sm" required>
                                    <p class="text-xs text-gray-500 mt-1">Este valor será adicionado ao estoque do produto acabado.</p>
                                </div>
                                <div class="mt-6 flex justify-end space-x-4">
                                    <x-secondary-button @click="open = false" type="button">Cancelar</x-secondary-button>
                                    <x-primary-button type="submit" class="bg-green-600 hover:bg-green-700">Confirmar Finalização</x-primary-button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</x-slot>

    {{-- ======================================================= --}}
    {{-- |||||||||||||| ÁREA CORRIGIDA COMEÇA AQUI |||||||||||||| --}}
    {{-- ======================================================= --}}

    {{-- Todo o conteúdo principal da página deve ficar após o <x-slot> --}}
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            {{-- As mensagens de sessão agora estão dentro da estrutura principal --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Produto</h4>
                        <p class="text-lg font-semibold">{{ $ordemProducao->produtoAcabado->nome }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Qtd. Planejada</h4>
                        <p class="text-lg font-semibold">{{ number_format($ordemProducao->quantidade_planejada, 0, ',', '.') }} {{ $ordemProducao->produtoAcabado->unidade }}</p>
                    </div>
                    <div>
                        <h4 class="text-sm font-medium text-gray-500">Status</h4>
                        <p class="text-lg font-semibold">{{ $ordemProducao->status }}</p>
                    </div>
                     <div>
                        <h4 class="text-sm font-medium text-gray-500">Responsável</h4>
                        <p class="text-lg font-semibold">{{ $ordemProducao->responsavel->name ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Matérias-Primas Necessárias</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="px-4 py-2 text-left">Ingrediente</th>
                                    <th class="px-4 py-2 text-center">Qtd. Necessária</th>
                                    <th class="px-4 py-2 text-center">Estoque Atual</th>
                                    <th class="px-4 py-2 text-center">Disponibilidade</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($ordemProducao->itens as $item)
                                    <tr class="border-t">
                                        <td class="px-4 py-2">{{ $item->materiaPrima->nome }}</td>
                                        <td class="px-4 py-2 text-center">{{ $item->quantidade_necessaria }} {{ $item->materiaPrima->unidade }}</td>
                                        <td class="px-4 py-2 text-center">{{ $item->materiaPrima->estoque_atual }}</td>
                                        <td class="px-4 py-2 text-center">
                                            @if ($item->materiaPrima->estoque_atual >= $item->quantidade_necessaria)
                                                <span class="text-green-500 font-bold">Disponível</span>
                                            @else
                                                <span class="text-red-500 font-bold">Insuficiente</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
             <div class="mt-6 text-right">
                <a href="{{ route('ordem-producao.index') }}"><x-secondary-button>Voltar para a Lista</x-secondary-button></a>
            </div>
        </div>
    </div>
</x-app-layout>