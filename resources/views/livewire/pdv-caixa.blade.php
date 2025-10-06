<div class="grid grid-cols-12 gap-4 h-screen font-sans bg-gray-100">

    <div class="col-span-7 p-4 flex flex-col">
        <div class="mb-4">
            <h1 class="text-2xl font-bold text-gray-800">PDV - Caixa Aberto</h1>
            <p class="text-sm text-gray-500">Operador: {{ auth()->user()->name }}</p>
        </div>

        <div class="mb-4 bg-white p-4 rounded-lg shadow-md border">
            <label for="barcode-input" class="block text-sm font-medium text-gray-700 mb-1">Código do Produto (F2)</label>
            <div class="flex items-center gap-2">
                <input
                    id="barcode-input"
                    type="text"
                    wire:model.lazy="barcode"
                    wire:keydown.enter="addProduto"
                    placeholder="Leia o código de barras ou digite e pressione ENTER"
                    class="w-full text-lg p-3 border-2 border-gray-300 rounded-md focus:border-blue-500 transition"
                    autofocus
                >
                <input type="number" wire:model="quantidade" class="w-24 text-center p-3 border-2 border-gray-300 rounded-md">
                <button wire:click="addProduto" class="p-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Adicionar</button>
            </div>
            
            {{-- Mensagens de Erro --}}
            @if($mensagemErro)
                <div class="text-red-600 font-semibold text-sm mt-2">{{ $mensagemErro }}</div>
            @endif
            @error('finalizacao')
                <div class="text-red-600 font-semibold text-sm mt-2 break-words">{{ $message }}</div>
            @enderror
        </div>

        <div class="flex-grow overflow-y-auto bg-white rounded-lg shadow-inner border">
            <table class="min-w-full">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Item</th>
                        <th class="p-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">Preço Unit.</th>
                        <th class="p-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Qtd.</th>
                        <th class="p-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">Subtotal</th>
                        <th class="p-3 text-center text-xs font-semibold text-gray-600 uppercase tracking-wider">Ação</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($cart as $key => $item)
                        <tr wire:key="cart-item-{{ $key }}" class="hover:bg-gray-50">
                            <td class="p-3 whitespace-nowrap">{{ $item['nome'] }}</td>
                            <td class="p-3 whitespace-nowrap">R$ {{ number_format($item['preco'], 2, ',', '.') }}</td>
                            <td class="p-3 text-center">{{ $item['qtd'] }}</td>
                            <td class="p-3 text-right font-medium">R$ {{ number_format($item['preco'] * $item['qtd'], 2, ',', '.') }}</td>
                            <td class="p-3 text-center">
                                <button wire:click="removerItem({{ $key }})" class="text-red-500 hover:text-red-700 font-bold">X</button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-10 text-center text-gray-400">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H4a2 2 0 01-2-2z" /></svg>
                                <span class="mt-2 block text-sm font-medium">Caixa Livre</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="col-span-5 bg-blue-900 text-white p-6 flex flex-col justify-between">
        <div>
            <h2 class="text-2xl font-bold mb-4">Resumo da Venda</h2>
            @if ($vendaFinalizada)
                <div class="bg-green-500 p-4 rounded-lg text-center shadow-lg animate-pulse">
                    <p class="font-bold text-lg">NFC-e Emitida com Sucesso!</p>
                    <p class="text-xs break-all">Chave: {{ $dadosUltimaNfce['chave'] ?? '' }}</p>
                    <button wire:click="resetarPdv" class="mt-4 font-bold underline text-lg">Nova Venda (F1)</button>
                </div>
            @endif
        </div>
        
        <div class="text-right">
            <span class="text-3xl uppercase tracking-wider">Total</span>
            <h3 class="text-8xl font-mono font-bold transition-opacity duration-300" wire:dirty.class="opacity-50">
                R$ {{ number_format($total, 2, ',', '.') }}
            </h3>
        </div>

        <div class="space-y-3">
            <button
                wire:click="abrirModalPagamento"
                wire:loading.attr="disabled"
                wire:target="finalizarVenda"
                @if(empty($cart) || $vendaFinalizada) disabled @endif
                class="w-full bg-green-500 hover:bg-green-600 text-white font-bold text-2xl py-4 rounded-md uppercase transition disabled:bg-gray-500 disabled:cursor-not-allowed flex items-center justify-center"
            >
                <span wire:loading.remove wire:target="finalizarVenda">
                    Finalizar Venda (F8)
                </span>
                <span wire:loading wire:target="finalizarVenda">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Processando...
                </span>
            </button>
            <button 
                wire:click="resetarPdv"
                class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded-md uppercase transition"
            >
                Cancelar Venda (F9)
            </button>
        </div>
    </div>
    
    @if ($showPaymentModal)
    <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" 
         x-data @keydown.escape.window="$wire.fecharModalPagamento()">
        
        <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-2xl">
            <div class="p-4 border-b flex justify-between items-center">
                <h2 class="text-2xl font-bold">Formas de Pagamento</h2>
                <button wire:click="fecharModalPagamento" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
            </div>

            <div class="p-6 grid grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <label for="formaPagamento" class="block text-sm font-medium">Forma de Pagamento</label>
                        <select wire:model="formaPagamentoAtual" id="formaPagamento" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                            @foreach($formasPagamento as $forma)
                                <option value="{{ $forma->id }}">{{ $forma->nome }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="valorPagamento" class="block text-sm font-medium">Valor a Pagar</label>
                        <input type="number" step="0.01" wire:model="valorPagamentoAtual" wire:keydown.enter="addPagamento" id="valorPagamento" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">
                        @error('valorPagamentoAtual') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <button wire:click="addPagamento" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Adicionar Pagamento</button>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg space-y-3 border">
                    <div class="text-lg flex justify-between"><span>Total da Venda:</span> <span class="font-bold">R$ {{ number_format($total, 2, ',', '.') }}</span></div>
                    <div class="text-lg flex justify-between"><span>Valor Recebido:</span> <span class="font-bold text-blue-600">R$ {{ number_format($valorRecebido, 2, ',', '.') }}</span></div>
                    <div class="text-lg flex justify-between"><span>Falta Pagar:</span> <span class="font-bold text-red-600">R$ {{ number_format($faltaPagar, 2, ',', '.') }}</span></div>
                    <div class="text-lg flex justify-between"><span>Troco:</span> <span class="font-bold text-green-600">R$ {{ number_format($troco, 2, ',', '.') }}</span></div>
                    
                    <hr class="my-2">
                    
                    <h4 class="font-bold pt-2">Pagamentos Adicionados:</h4>
                    <ul class="text-sm space-y-1 h-24 overflow-y-auto">
                        @forelse($pagamentos as $index => $pag)
                            <li class="flex justify-between items-center bg-gray-100 p-1 rounded">
                                <span>{{ $pag['nome'] }} - R$ {{ number_format($pag['valor'], 2, ',', '.') }}</span>
                                <button wire:click="removerPagamento({{ $index }})" class="text-red-500 font-bold px-2">&times;</button>
                            </li>
                        @empty
                            <li class="text-gray-500 text-center pt-4">Nenhum pagamento.</li>
                        @endforelse {{-- <-- CORREÇÃO 1: @endoreach para @endforelse --}}
                    </ul>
                </div>
            </div>

            <div class="p-4 bg-gray-50 border-t">
                @error('finalizacao') <div class="text-red-600 mb-2 font-semibold">{{ $message }}</div> @enderror
                <button 
                    wire:click="finalizarVenda" 
                    wire:loading.attr="disabled"
                    @if($faltaPagar > 0) disabled @endif
                    class="w-full bg-green-600 text-white font-bold py-3 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed">
                    <span wire:loading.remove wire:target="finalizarVenda">Confirmar e Emitir NFC-e</span>
                    <span wire:loading wire:target="finalizarVenda">Emitindo...</span>
                </button>
            </div>
        </div>
    </div>
    @endif {{-- <-- CORREÇÃO 2: Adicionado o @endif que faltava --}}
    </div>

@script
<script>
    document.addEventListener('livewire:initialized', () => {
        const barcodeInput = document.getElementById('barcode-input');

        // Foco volta para o input sempre que um produto é adicionado
        Livewire.on('produto-adicionado', () => {
            barcodeInput.value = ''; // Garante que o campo está limpo
            barcodeInput.focus();
        });

        // Foco volta para o input quando o PDV é resetado
        Livewire.on('pdv-resetado', () => {
            barcodeInput.focus();
        });

        // Atalhos de teclado
        document.addEventListener('keydown', e => {
            // F1 para Nova Venda (quando já finalizou)
            if (e.key === 'F1') {
                e.preventDefault();
                @this.call('resetarPdv');
            }
            // F2 para focar no campo de código de barras
            if (e.key === 'F2') {
                e.preventDefault();
                barcodeInput.focus();
            }
            // F8 para Finalizar Venda
            if (e.key === 'F8') {
                e.preventDefault();
                // Chama a função para abrir o modal
                @this.call('abrirModalPagamento');
            }
            // F9 para Cancelar/Resetar
            if (e.key === 'F9') {
                e.preventDefault();
                @this.call('resetarPdv');
            }
        });

        // Garante que o foco inicial está correto
        barcodeInput.focus();
    });
</script>
@endscript