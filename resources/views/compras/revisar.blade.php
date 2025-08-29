<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Revisão da Nota de Compra #{{ $dadosNFe['numero_nota'] }}
        </h2>
    </x-slot>

    {{-- ===== INÍCIO DAS ADIÇÕES: Estilos para os cards com destaque ===== --}}
    <style>
        .item-card-novo { background-color: #fefce8; border-color: #facc15; } /* Amarelo */
        .dark .item-card-novo { background-color: rgba(234, 179, 8, 0.1); border-color: #ca8a04; }
        
        .item-card-vinculo-encontrado { background-color: #f0f9ff; border-color: #7dd3fc; } /* Azul Claro */
        .dark .item-card-vinculo-encontrado { background-color: rgba(14, 165, 233, 0.1); border-color: #0ea5e9; }

        .item-card-custo-alterado { background-color: #fee2e2; border-color: #fca5a5; } /* Vermelho Claro */
        .dark .item-card-custo-alterado { background-color: rgba(239, 68, 68, 0.1); border-color: #ef4444; }

        .item-card-alterado-manual { background-color: #f0fdf4; border-color: #86efac; } /* Verde */
        .dark .item-card-alterado-manual { background-color: rgba(34, 197, 94, 0.1); border-color: #22c55e; }
    </style>
    {{-- ===== FIM DAS ADIÇÕES ===== --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Erro!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                    Dados Gerais e Financeiros da Nota
                </h3>
                <div class="flex flex-wrap gap-x-12 gap-y-6">
                    <div class="flex-1 min-w-[250px]">
                        <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Fornecedor</span>
                        <p class="mt-1 text-lg text-gray-900 dark:text-gray-100">
                            {{ $dadosNFe['fornecedor']['razao_social'] }}
                            @if ($dadosNFe['fornecedor']['existente'])
                                <span class="text-xs text-green-500">(Já cadastrado)</span>
                            @else
                                <span class="text-xs text-orange-500">(Novo fornecedor)</span>
                            @endif
                        </p>
                    </div>
                    <div class="flex-1 min-w-[150px]"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nº da Nota</span><p class="mt-1 text-lg text-gray-900 dark:text-gray-100">{{ $dadosNFe['numero_nota'] }}</p></div>
                    <div class="flex-1 min-w-[150px]"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Valor Total</span><p class="mt-1 text-lg text-gray-900 dark:text-gray-100">R$ {{ number_format($dadosNFe['valor_total'], 2, ',', '.') }}</p></div>
                </div>
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700"><span class="block font-medium text-sm text-gray-700 dark:text-gray-300">Chave de Acesso</span><p class="mt-1 text-sm font-mono text-gray-800 dark:text-gray-200 tracking-tight">{{ $dadosNFe['chave_acesso'] }}</p></div>
            </div>

            <form action="{{ route('compras.salvarImportacao') }}" method="POST">
                @csrf
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                        Itens da Nota para Conferência e Vinculação
                    </h3>
                    <div class="space-y-6">
                        @foreach ($dadosNFe['itens'] as $index => $item)
                        @php
                            // ===== INÍCIO DAS ADIÇÕES: Lógica para definir a classe de cor =====
                            $cardClass = '';
                            if ($item['status'] === 'novo') $cardClass = 'item-card-novo';
                            if ($item['status'] === 'vinculo_encontrado') $cardClass = 'item-card-vinculo-encontrado';
                            if ($item['status'] === 'custo_alterado') $cardClass = 'item-card-custo-alterado';
                        @endphp
                        
                        {{-- O card agora recebe a classe de cor dinamicamente --}}
                        <div class="item-card transition-colors duration-300 ease-in-out border rounded-lg p-4 space-y-4 {{ $cardClass }}">

                            <div class="flex flex-wrap justify-between items-start gap-4">
                                <div class="flex-grow">
                                    <span class="block text-xs font-bold text-indigo-600 dark:text-indigo-400">ITEM #{{ $index + 1 }}</span>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $item['descricao_nota'] }}</p>
                                    
                                    {{-- ===== INÍCIO DAS ADIÇÕES: Exibe o status do item ===== --}}
                                    @if($item['status'] === 'novo')
                                        <span class="text-xs font-semibold text-yellow-800 dark:text-yellow-400">PRODUTO NOVO</span>
                                    @elseif($item['status'] === 'vinculo_encontrado')
                                        <span class="text-xs font-semibold text-sky-800 dark:text-sky-400">VÍNCULO ENCONTRADO</span>
                                    @elseif($item['status'] === 'custo_alterado')
                                        <span class="text-xs font-bold text-red-800 dark:text-red-400">ATENÇÃO: CUSTO ALTERADO (Cadastrado: R$ {{ number_format($item['vinculo_existente']['preco_custo'] ?? 0, 2, ',', '.') }})</span>
                                    @endif
                                    {{-- ===== FIM DAS ADIÇÕES ===== --}}
                                </div>
                                <div class="flex items-center space-x-8 md:space-x-12 text-right flex-shrink-0">
                                    <div><span class="block text-xs font-medium text-gray-500">PREÇO CUSTO</span><p class="text-md font-semibold">R$ {{ number_format($item['preco_custo'], 2, ',', '.') }}</p></div>
                                    <div><span class="block text-xs font-medium text-gray-500">QTD</span><p class="text-md font-semibold">{{ number_format($item['quantidade'], 0, ',', '.') }} {{ $item['unidade'] }}</p></div>
                                    <div><span class="block text-xs font-medium text-gray-500">SUBTOTAL</span><p class="text-md font-semibold">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</p></div>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div><strong>NCM:</strong> {{ $item['ncm'] ?? 'N/A' }}</div>
                                <div><strong>CFOP:</strong> {{ $item['cfop'] ?? 'N/A' }}</div>
                                <div><strong>EAN:</strong> {{ $item['ean'] ?? 'N/A' }}</div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="nome_{{ $index }}" value="Nome do Produto (no seu sistema)" />
                                        {{-- Adicionada a classe 'campo-alteravel' --}}
                                        <x-text-input type="text" name="itens[{{ $index }}][nome]" id="nome_{{ $index }}" class="campo-alteravel block mt-1 w-full"
                                                      value="{{ old('itens.'.$index.'.nome', $item['vinculo_existente']['nome'] ?? $item['descricao_nota']) }}" required />
                                    </div>
                                    <div>
                                        <x-input-label for="categoria_{{ $index }}" value="Categoria" />
                                        {{-- Adicionada a classe 'campo-alteravel' --}}
                                        <select name="itens[{{ $index }}][categoria_id]" id="categoria_{{ $index }}" class="campo-alteravel block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                            <option value="">Selecione...</option>
                                            @foreach ($categorias as $categoria)
                                                <option value="{{ $categoria->id }}" @selected(old('itens.'.$index.'.categoria_id', $item['vinculo_existente']['categoria_id'] ?? null) == $categoria->id)>
                                                    {{ $categoria->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label for="preco_venda_{{ $index }}" value="Preço de Venda Final" />
                                        {{-- Adicionada a classe 'campo-alteravel' --}}
                                        <x-text-input type="number" step="0.01" name="itens[{{ $index }}][preco_venda]" id="preco_venda_{{ $index }}" class="campo-alteravel block mt-1 w-full"
                                                      value="{{ old('itens.'.$index.'.preco_venda', $item['preco_venda_sugerido'] ?? '') }}" required />
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="produto_id_{{ $index }}" value="Vincular ao Produto do Sistema" />
                                        @if ($item['vinculo_existente'])
                                            <div class="mt-1 p-2 text-sm bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-md">
                                                <p class="font-semibold text-green-800 dark:text-green-300">Vínculo Automático Encontrado.</p>
                                            </div>
                                        @endif
                                        {{-- Adicionada a classe 'campo-alteravel' --}}
                                        <select name="itens[{{ $index }}][produto_id]" id="produto_id_{{ $index }}" class="campo-alteravel mt-2 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                            <option value="">-- Criar como um Novo Produto --</option>
                                            @foreach ($produtosDoSistema as $produto)
                                                <option value="{{ $produto->id }}" @selected(old('itens.'.$index.'.produto_id', $item['vinculo_existente']['id'] ?? null) == $produto->id)>
                                                    {{ $produto->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <div class="mt-6 flex flex-wrap items-center justify-end gap-4">
                        <a href="{{ route('compras.index') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">
                            Cancelar Importação
                        </a>
                        <x-primary-button>
                            Finalizar e Salvar Nota
                        </x-primary-button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ===== INÍCIO DAS ADIÇÕES: Script para a cor verde (alterado manualmente) ===== --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Seleciona todos os campos que, ao serem alterados, devem marcar o card como "editado"
            const camposAlteraveis = document.querySelectorAll('.campo-alteravel');

            const marcarCardComoAlterado = (event) => {
                const campo = event.target;
                // Encontra o 'card' pai mais próximo do campo que foi alterado
                const card = campo.closest('.item-card');
                if (card) {
                    // Remove as cores de status originais (amarelo, azul, vermelho)
                    card.classList.remove('item-card-novo', 'item-card-custo-alterado', 'item-card-vinculo-encontrado');
                    // Adiciona a cor verde para indicar que foi editado manualmente
                    card.classList.add('item-card-alterado-manual');
                }
            };

            // Adiciona o "ouvinte" de eventos para cada campo
            camposAlteraveis.forEach(campo => {
                campo.addEventListener('input', marcarCardComoAlterado);
            });
        });
    </script>
    {{-- ===== FIM DAS ADIÇÕES ===== --}}
</x-app-layout>