<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4 border-b pb-2 dark:border-gray-700">
        Faturamento da Ordem
    </h3>

    {{-- BLOCO DE AVISO: OS JÁ FATURADA --}}
    @if ($vendaFaturada)
        <div class="p-4 mb-4 text-sm text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/50 rounded-lg" role="alert">
            <span class="font-medium">OS Faturada!</span> Esta Ordem de Serviço foi convertida na 
            <a href="{{ route('vendas.show', $vendaFaturada->id) }}" class="underline hover:text-green-900 dark:hover:text-green-100 font-bold">Venda #{{ $vendaFaturada->id }}</a>.
        </div>
        
        {{-- Opções de Ações Fiscais/Financeiras (Simples, para manter a funcionalidade de base) --}}
        <div class="space-y-2">
            <h4 class="text-sm font-semibold text-gray-600 dark:text-gray-400">Ações Pós-Faturamento:</h4>
            <a href="{{ route('ordens-servico.imprimir', $os->id) }}" target="_blank" class="inline-flex justify-center w-full px-3 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-md font-semibold text-sm shadow-sm">
                Imprimir Documento
            </a>
            <button class="inline-flex justify-center w-full px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-sm shadow-sm">
                Emitir Nota Fiscal
            </button>
        </div>
        
    {{-- BLOCO DE FATURAMENTO (Formulário) --}}
    @else
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
            Total a Faturar: <span class="font-bold text-lg">R$ {{ number_format($os->valor_total, 2, ',', '.') }}</span>
        </p>

        <form wire:submit.prevent="faturar">
            <div class="space-y-4">
                {{-- Forma de Pagamento --}}
                <div>
                    <label for="forma_pagamento_id" class="block text-sm font-medium">Forma de Pagamento</label>
                    <select wire:model="forma_pagamento_id" id="forma_pagamento_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                        @forelse($formasPagamento as $forma)
                            <option value="{{ $forma->id }}" 
                                    title="{{ $forma->tipo === 'a_prazo' ? $forma->numero_parcelas . 'x de ' . $forma->dias_intervalo . ' em ' . $forma->dias_intervalo . ' dias' : 'À vista' }}">
                                {{ $forma->nome }} 
                                ({{ $forma->numero_parcelas }}x)
                            </option>
                        @empty
                             <option value="">Nenhuma forma de pagamento cadastrada</option>
                        @endforelse
                    </select>
                    @error('forma_pagamento_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                {{-- Data Vencimento (Primeira Parcela) --}}
                <div>
                    <label for="data_vencimento" class="block text-sm font-medium">Venc. da 1ª Parcela</label>
                    <input type="date" wire:model="data_vencimento" id="data_vencimento" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                    @error('data_vencimento') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>

            <div class="flex justify-end mt-6 pt-4 border-t dark:border-gray-700">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50" 
                        {{ $os->status !== 'Concluida' ? 'disabled' : '' }}>
                    Faturar OS (Gerar Venda)
                </button>
            </div>
             @if ($os->status !== 'Concluida')
                 <p class="text-xs text-red-500 mt-2 text-right">Mude o status da OS para "Concluída" para habilitar o faturamento.</p>
             @endif
        </form>
    @endif
</div>