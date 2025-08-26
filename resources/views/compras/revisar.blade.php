<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Revisão da Nota de Compra #{{ $dadosNFe['numero_nota'] }}
        </h2>
    </x-slot>

    {{-- INÍCIO: Adição do estilo para o card alterado --}}
    <style>
        .item-card-alterado {
            background-color: #f0fdf4; /* Equivalente a 'bg-green-50' do Tailwind */
            border-color: #bbf7d0; /* Equivalente a 'border-green-200' do Tailwind */
        }
        .dark .item-card-alterado {
            background-color: rgba(74, 222, 128, 0.07); /* Verde bem sutil para o modo escuro */
            border-color: #22c55e; /* Equivalente a 'border-green-500' do Tailwind */
        }
    </style>
    {{-- FIM: Adição do estilo --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Bloco para exibir erros, se houver --}}
            @if (session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <strong class="font-bold">Erro!</strong>
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            {{-- DADOS GERAIS DA NOTA --}}
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
                        {{-- ALTERAÇÃO: Adicionada a classe 'item-card' e classes de transição --}}
                        <div class="item-card transition-colors duration-300 ease-in-out border border-gray-200 dark:border-gray-700 rounded-lg p-4 space-y-4">

                            {{-- 1. NOME E VALORES DO ITEM (DA NOTA) --}}
                            <div class="flex justify-between items-start gap-4">
                                <div class="flex-grow">
                                    <span class="block text-xs font-bold text-indigo-600 dark:text-indigo-400">ITEM #{{ $index + 1 }}</span>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $item['descricao_nota'] }}</p>
                                </div>
                                <div class="flex items-center space-x-8 md:space-x-12 text-right flex-shrink-0">
                                    <div><span class="block text-xs font-medium text-gray-500">PREÇO CUSTO</span><p class="text-md font-semibold">R$ {{ number_format($item['preco_custo'], 2, ',', '.') }}</p></div>
                                    <div><span class="block text-xs font-medium text-gray-500">QTD</span><p class="text-md font-semibold">{{ number_format($item['quantidade'], 0, ',', '.') }} {{ $item['unidade'] }}</p></div>
                                    <div><span class="block text-xs font-medium text-gray-500">SUBTOTAL</span><p class="text-md font-semibold">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</p></div>
                                </div>
                            </div>

                            {{-- 2. DADOS FISCAIS (DA NOTA) --}}
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <div><strong>NCM:</strong> {{ $item['ncm'] ?? 'N/A' }}</div>
                                <div><strong>CFOP:</strong> {{ $item['cfop'] ?? 'N/A' }}</div>
                                <div><strong>EAN:</strong> {{ $item['ean'] ?? 'N/A' }}</div>
                            </div>

                            {{-- 3. LÓGICA DE VINCULAÇÃO E CADASTRO --}}
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                                {{-- COLUNA ESQUERDA --}}
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="nome_{{ $index }}" value="Nome do Produto (no seu sistema)" />
                                        {{-- ALTERAÇÃO: Adicionada a classe 'campo-alteravel' --}}
                                        <x-text-input type="text" name="itens[{{ $index }}][nome]" id="nome_{{ $index }}" class="campo-alteravel block mt-1 w-full"
                                                      value="{{ old('itens.'.$index.'.nome', $item['vinculo_existente']['nome'] ?? $item['descricao_nota']) }}" required />
                                    </div>
                                    <div>
                                        <x-input-label for="categoria_{{ $index }}" value="Categoria" />
                                        {{-- ALTERAÇÃO: Adicionada a classe 'campo-alteravel' --}}
                                        <select name="itens[{{ $index }}][categoria_id]" id="categoria_{{ $index }}" data-role="categoria-selector" data-index="{{ $index }}" class="campo-alteravel block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
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
                                        {{-- ALTERAÇÃO: Adicionada a classe 'campo-alteravel' --}}
                                        <x-text-input type="number" step="0.01" name="itens[{{ $index }}][preco_venda]" id="preco_venda_{{ $index }}" class="campo-alteravel block mt-1 w-full"
                                                      value="{{ old('itens.'.$index.'.preco_venda', $item['preco_venda_sugerido'] ?? '') }}" required />
                                        <input type="hidden" id="preco_custo_{{ $index }}" value="{{ $item['preco_custo'] }}">
                                    </div>
                                </div>

                                {{-- COLUNA DIREITA --}}
                                <div class="space-y-4">
                                    <div>
                                        <x-input-label for="produto_id_{{ $index }}" value="Vincular ao Produto do Sistema" />
                                        @if ($item['vinculo_existente'])
                                            <div class="mt-1 p-2 text-sm bg-green-50 dark:bg-green-900/50 border border-green-200 dark:border-green-800 rounded-md">
                                                <p class="font-semibold text-green-800 dark:text-green-300">Vínculo Automático Encontrado.</p>
                                            </div>
                                        @endif
                                        {{-- ALTERAÇÃO: Adicionada a classe 'campo-alteravel' --}}
                                        <select name="itens[{{ $index }}][produto_id]" id="produto_id_{{ $index }}" class="campo-alteravel mt-2 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                            <option value="">-- Criar como um Novo Produto --</option>
                                            @foreach ($produtosDoSistema as $produto)
                                                <option value="{{ $produto->id }}" @selected(old('itens.'.$index.'.produto_id', $item['vinculo_existente']['id'] ?? null) == $produto->id)>
                                                    {{ $produto->nome }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <x-input-label value="Unidade de Medida (Venda)" />
                                        <div class="flex items-center gap-2 mt-1">
                                           <div class="flex-1">
                                               <span class="text-sm text-gray-500">Unid. Compra: {{ $item['unidade'] }}</span>
                                               {{-- ALTERAÇÃO: Adicionada a classe 'campo-alteravel' --}}
                                               <select name="itens[{{ $index }}][unidade_venda_id]" class="campo-alteravel w-full mt-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                                   @foreach (\App\Models\UnidadeMedida::all() as $unidade)
                                                       <option value="{{ $unidade->id }}" @selected($item['vinculo_existente']['unidade_medida_venda_id'] ?? 1 == $unidade->id)>
                                                           {{ $unidade->sigla }}
                                                       </option>
                                                   @endforeach
                                               </select>
                                           </div>
                                           <div class="flex-1">
                                               <x-input-label for="fator_conversao_{{ $index }}" value="Fator Conversão" />
                                               {{-- ALTERAÇÃO: Adicionada a classe 'campo-alteravel' --}}
                                               <x-text-input type="number" step="0.01" id="fator_conversao_{{ $index }}" name="itens[{{ $index }}][fator_conversao]" class="campo-alteravel block mt-1 w-full" value="{{ $item['vinculo_existente']['fator_conversao'] ?? 1 }}" />
                                           </div>
                                        </div>
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

    <script>
        // Converte os dados do PHP para um objeto JavaScript para fácil acesso
        const categoriasComMargem = @json($categorias->mapWithKeys(fn($cat) => [$cat->id => $cat->margem_lucro]));
        const margemPadrao = {{ \App\Models\Configuracao::where('chave', 'margem_lucro_padrao')->value('valor') ?? 0 }};

        document.addEventListener('DOMContentLoaded', () => {
            // LÓGICA EXISTENTE: Cálculo automático de preço de venda
            document.querySelectorAll('[data-role="categoria-selector"]').forEach(select => {
                select.addEventListener('change', function() {
                    const index = this.dataset.index;
                    const categoriaId = this.value;
                    const precoCustoInput = document.getElementById(`preco_custo_${index}`);
                    const precoVendaInput = document.getElementById(`preco_venda_${index}`);

                    if (!precoCustoInput || !precoVendaInput) return;

                    const precoCusto = parseFloat(precoCustoInput.value);
                    let margem = margemPadrao;

                    if (categoriaId && categoriasComMargem[categoriaId] !== null) {
                        margem = parseFloat(categoriasComMargem[categoriaId]);
                    }

                    const novoPrecoVenda = precoCusto * (1 + (margem / 100));
                    precoVendaInput.value = novoPrecoVenda.toFixed(2);
                });
            });

            // --- INÍCIO: NOVA LÓGICA PARA DESTACAR CARDS ALTERADOS ---

            // 1. Seleciona todos os campos que devem ser monitorados
            const camposAlteraveis = document.querySelectorAll('.campo-alteravel');

            // 2. Define a função que será executada quando um campo mudar
            const marcarCardComoAlterado = (event) => {
                const campo = event.target;
                // Encontra o card pai mais próximo do campo que foi alterado
                const card = campo.closest('.item-card');
                if (card) {
                    // Adiciona a classe que muda a cor
                    card.classList.add('item-card-alterado');
                }
            };

            // 3. Adiciona um "ouvinte" para cada campo
            camposAlteraveis.forEach(campo => {
                campo.addEventListener('input', marcarCardComoAlterado); // Para digitação
                campo.addEventListener('change', marcarCardComoAlterado); // Para seleções e checkboxes
            });
            // --- FIM: NOVA LÓGICA ---
        });
    </script>
</x-app-layout>