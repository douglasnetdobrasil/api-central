<div class="container mx-auto p-4 sm:p-6 lg:p-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b">
            <h1 class="text-2xl font-bold text-gray-800">Fechamento de Caixa</h1>
            @if ($caixaSessao)
                <p class="text-sm text-gray-500">Operador: {{ Auth::user()->name }} | Abertura: {{ $caixaSessao->data_abertura->format('d/m/Y H:i') }}</p>
            @endif
        </div>

        @if ($caixaSessao)
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Resumo da Sessão</h2>
                        
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-bold text-green-600 mb-2">(+) ENTRADAS</h3>
                            <dl class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Fundo de Abertura (Troco):</dt>
                                    <dd class="font-mono">R$ {{ number_format($valorAbertura, 2, ',', '.') }}</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Suprimentos (Adições):</dt>
                                    <dd class="font-mono">R$ {{ number_format($totalSuprimentos, 2, ',', '.') }}</dd>
                                </div>
                                @foreach($vendasPorFormaPagamento as $nome => $total)
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Vendas ({{ $nome }}):</dt>
                                    <dd class="font-mono">R$ {{ number_format($total, 2, ',', '.') }}</dd>
                                </div>
                                @endforeach
                            </dl>
                            <hr class="my-2">
                            <div class="flex justify-between font-bold">
                                <dt>Total de Entradas:</dt>
                                <dd class="font-mono text-green-700">R$ {{ number_format($valorAbertura + $totalVendas + $totalSuprimentos, 2, ',', '.') }}</dd>
                            </div>
                        </div>

                        <div class="p-4 bg-gray-50 rounded-lg">
                            <h3 class="font-bold text-red-600 mb-2">(-) SAÍDAS</h3>
                            <dl class="space-y-1 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Sangrias (Retiradas):</dt>
                                    <dd class="font-mono">R$ {{ number_format($totalSangrias, 2, ',', '.') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <div class="space-y-6">
                         <h2 class="text-lg font-semibold text-gray-700 border-b pb-2">Conferência do Caixa</h2>
                        
                        <div class="p-4 bg-blue-50 border-l-4 border-blue-500 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700">Saldo Esperado em Dinheiro</label>
                            <p class="text-2xl font-mono font-bold text-blue-800">R$ {{ number_format($saldoEsperadoDinheiro, 2, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 mt-1">(Abertura + Vendas Dinheiro + Suprimentos - Sangrias)</p>
                        </div>

                        <div>
                            <label for="valor_contado" class="block text-sm font-bold text-gray-700 mb-1">Valor Contado em Dinheiro (Gaveta)</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">R$</span>
                                <input type="number" step="0.01" id="valor_contado" wire:model.live="valorContadoDinheiro" class="pl-10 text-xl font-mono w-full p-2 border border-gray-300 rounded-md" placeholder="0,00" autofocus>
                            </div>
                            @error('valorContadoDinheiro') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                        </div>

                        @if($valorContadoDinheiro !== '')
                        <div class="p-4 rounded-lg
                            @if($diferencaCaixa == 0) bg-gray-100 @endif
                            @if($diferencaCaixa > 0) bg-yellow-100 border-l-4 border-yellow-500 @endif
                            @if($diferencaCaixa < 0) bg-red-100 border-l-4 border-red-500 @endif">
                            
                            <label class="block text-sm font-medium text-gray-700">Diferença de Caixa</label>
                            <p class="text-2xl font-mono font-bold 
                                @if($diferencaCaixa == 0) text-gray-800 @endif
                                @if($diferencaCaixa > 0) text-yellow-800 @endif
                                @if($diferencaCaixa < 0) text-red-800 @endif">
                                R$ {{ number_format($diferencaCaixa, 2, ',', '.') }}
                            </p>
                            <p class="text-xs font-semibold
                                @if($diferencaCaixa == 0) text-gray-500 @endif
                                @if($diferencaCaixa > 0) text-yellow-600 @endif
                                @if($diferencaCaixa < 0) text-red-600 @endif">
                                @if($diferencaCaixa == 0) Caixa Correto @endif
                                @if($diferencaCaixa > 0) SOBRA @endif
                                @if($diferencaCaixa < 0) QUEBRA @endif
                            </p>
                        </div>
                        @endif
                        
                        <div class="pt-4">
                            <button wire:click="fecharCaixa" wire:loading.attr="disabled" class="w-full bg-red-600 text-white font-bold py-3 px-4 rounded-md hover:bg-red-700 transition disabled:bg-gray-400">
                                Confirmar Fechamento de Caixa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="p-10 text-center">
                <p class="text-gray-600 font-semibold">Nenhum caixa aberto para este operador.</p>
                <a href="{{ route('pdv.caixa') }}" class="mt-4 inline-block bg-blue-600 text-white font-bold py-2 px-4 rounded-md hover:bg-blue-700">Ir para o PDV</a>
            </div>
        @endif
    </div>
</div>