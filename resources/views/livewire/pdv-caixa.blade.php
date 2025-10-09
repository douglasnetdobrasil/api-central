<div>
    {{-- Se houver uma sessão de caixa ABERTA, mostra a interface principal do PDV --}}
    @if ($caixaSessao)

        <div class="flex flex-col h-screen font-sans bg-gray-100">

            <div class="flex-grow p-4 flex flex-col overflow-y-auto">
                <div class="mb-4">
                    <h1 class="text-2xl font-bold text-gray-800">PDV - Caixa Aberto</h1>
                    <p class="text-sm text-gray-500">Operador: {{ auth()->user()->name }}</p>
                </div>

                <div class="mb-4 bg-white p-4 rounded-lg shadow-md border">
                    <label for="barcode-input" class="block text-sm font-medium text-gray-700 mb-1">Código do Produto (F2)</label>
                    <div class="flex items-center gap-2">
                        <input id="barcode-input" type="text" wire:model="barcode" wire:keydown.enter="addProduto" placeholder="Leia o código de barras ou digite e pressione ENTER" class="w-full text-lg p-3 border-2 border-gray-300 rounded-md focus:border-blue-500 transition" autofocus>
                        <input type="number" wire:model="quantidade" value="1" class="w-24 text-center p-3 border-2 border-gray-300 rounded-md">
                        <button wire:click="addProduto" class="p-3 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition">Adicionar</button>
                    </div>
                    
                    @if($mensagemErro)
                        <div class="text-red-600 font-semibold text-sm mt-2">{{ $mensagemErro }}</div>
                    @endif
                    @error('finalizacao')
                        <div class="text-red-600 font-semibold text-sm mt-2 break-words">{{ $message }}</div>
                    @enderror
                </div>

                <div class="flex-grow bg-white rounded-lg shadow-inner border overflow-auto">
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
                                    <td class="p-3 whitespace-nowrap">
                                        <div class="font-medium text-gray-900">{{ $item['nome'] }}</div>
                                    </td>
                                    <td class="p-3 whitespace-nowrap">R$ {{ number_format($item['preco'], 2, ',', '.') }}</td>
                                    <td class="p-3 text-center">{{ $item['qtd'] }}</td>
                                    <td class="p-3 text-right font-medium">R$ {{ number_format($item['preco'] * $item['qtd'], 2, ',', '.') }}</td>
                                    <td class="p-3 text-center">
                                        <button wire:click="removerItem({{ $key }})" class="text-red-500 hover:text-red-700 font-bold text-lg">×</button>
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

            <div class="w-full bg-gray-800 text-white p-4 shadow-t-lg flex items-center justify-between">
                <div class="flex items-center gap-3 w-1/4">
                    <button wire:click="abrirMenuOpcoes" class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-3 px-6 rounded-md uppercase transition text-sm">
                        Opções (F10)
                    </button>
                    @if ($clienteNome)
                        <div class="p-2 bg-gray-700 text-xs rounded-md">
                            <p class="font-semibold">Consumidor: {{ $clienteNome }}</p>
                        </div>
                    @endif
                </div>
                <div class="text-center">
                    <span class="text-xl uppercase tracking-wider text-gray-400">Total</span>
                    <h3 class="text-5xl font-mono font-bold text-white transition-opacity duration-300" wire:dirty.class="opacity-50">
                        R$ {{ number_format($total, 2, ',', '.') }}
                    </h3>
                </div>
                <div class="w-1/4">
                    <button wire:click="abrirModalPagamento" wire:loading.attr="disabled" @if(empty($cart) || $vendaFinalizada) disabled @endif class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-xl py-4 rounded-md uppercase transition disabled:bg-gray-500 disabled:cursor-not-allowed flex items-center justify-center shadow-lg">
                        Finalizar (F8)
                    </button>
                </div>
            </div>
            
            @if ($showPaymentModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" x-data @keydown.escape.window="$wire.fecharModalPagamento()">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-2xl">
                    <div class="p-4 border-b flex justify-between items-center"><h2 class="text-2xl font-bold">Formas de Pagamento</h2><button wire:click="fecharModalPagamento" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button></div>
                    <div class="p-6 grid grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div><label for="formaPagamento" class="block text-sm font-medium">Forma de Pagamento</label><select wire:model="formaPagamentoAtual" id="formaPagamento" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">@foreach($formasPagamento as $forma)<option value="{{ $forma->id }}">{{ $forma->nome }}</option>@endforeach</select></div>
                            <div><label for="valorPagamento" class="block text-sm font-medium">Valor a Pagar</label><input type="number" step="0.01" wire:model="valorPagamentoAtual" wire:keydown.enter="addPagamento" id="valorPagamento" class="mt-1 block w-full p-2 border border-gray-300 rounded-md">@error('valorPagamentoAtual') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror</div>
                            <button wire:click="addPagamento" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700">Adicionar Pagamento</button>
                        </div>
                        <div class="bg-gray-50 p-4 rounded-lg space-y-3 border">
                            <div class="text-lg flex justify-between"><span>Total da Venda:</span> <span class="font-bold">R$ {{ number_format($total, 2, ',', '.') }}</span></div>
                            <div class="text-lg flex justify-between"><span>Valor Recebido:</span> <span class="font-bold text-blue-600">R$ {{ number_format($valorRecebido, 2, ',', '.') }}</span></div>
                            <div class="text-lg flex justify-between"><span>Falta Pagar:</span> <span class="font-bold text-red-600">R$ {{ number_format($faltaPagar, 2, ',', '.') }}</span></div>
                            <div class="text-lg flex justify-between"><span>Troco:</span> <span class="font-bold text-green-600">R$ {{ number_format($troco, 2, ',', '.') }}</span></div>
                            <hr class="my-2"><h4 class="font-bold pt-2">Pagamentos Adicionados:</h4>
                            <ul class="text-sm space-y-1 h-24 overflow-y-auto">
                                @forelse($pagamentos as $index => $pag)<li class="flex justify-between items-center bg-gray-100 p-1 rounded"><span>{{ $pag['nome'] }} - R$ {{ number_format($pag['valor'], 2, ',', '.') }}</span><button wire:click="removerPagamento({{ $index }})" class="text-red-500 font-bold px-2">&times;</button></li>
                                @empty<li class="text-gray-500 text-center pt-4">Nenhum pagamento.</li>@endforelse
                            </ul>
                        </div>
                    </div>
                    <div class="p-4 bg-gray-50 border-t">
                        @error('finalizacao') <div class="text-red-600 mb-2 font-semibold">{{ $message }}</div> @enderror
                        <button wire:click="finalizarVenda" wire:loading.attr="disabled" @if($faltaPagar > 0) disabled @endif class="w-full bg-green-600 text-white font-bold py-3 rounded-md hover:bg-green-700 disabled:bg-gray-400 disabled:cursor-not-allowed"><span wire:loading.remove wire:target="finalizarVenda">Confirmar e Emitir NFC-e</span><span wire:loading wire:target="finalizarVenda">Emitindo...</span></button>
                    </div>
                </div>
            </div>
            @endif

            @if ($showOptionsMenu)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" x-data @keydown.escape.window="$wire.fecharMenuOpcoes()">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-lg">
                    <div class="p-4 border-b flex justify-between items-center"><h2 class="text-2xl font-bold">Menu de Opções</h2><button wire:click="fecharMenuOpcoes" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button></div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 gap-4">
                            <button wire:click="$set('showIdentificarModal', true)" class="p-4 bg-gray-200 hover:bg-gray-300 rounded-md text-left font-semibold">Identificar Consumidor</button>
                            <button wire:click="solicitarAutorizacao('abrirModalDesconto')" class="p-4 bg-gray-200 hover:bg-gray-300 rounded-md text-left font-semibold">Aplicar Desconto</button>
                            <button wire:click="solicitarAutorizacao('abrirModalSangria')" class="p-4 bg-yellow-100 hover:bg-yellow-200 rounded-md text-left font-semibold text-yellow-800">Sangria / Retirada</button>
                            <button wire:click="solicitarAutorizacao('abrirModalSuprimento')" class="p-4 bg-green-100 hover:bg-green-200 rounded-md text-left font-semibold text-green-800">Suprimento / Adição</button>
                            <button wire:click="trocarOperador" ...>Trocar de Operador</button>

    <a href="{{ route('pdv.fechamento') }}" class="p-4 bg-red-500 hover:bg-red-600 text-white rounded-md text-left font-semibold">
        Fechar Caixa
    </a>

                            <button wire:click="resetarPdv" class="p-4 bg-red-100 hover:bg-red-200 rounded-md text-left font-semibold text-red-800">Cancelar Venda (F9)</button>
                            <button wire:click="solicitarAutorizacao('abrirModalCancelarNfce')" class="p-4 bg-orange-100 hover:bg-orange-200 rounded-md text-left font-semibold text-orange-800 col-span-2">Cancelar Última NFC-e</button>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($showPinModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @keydown.escape.window="$wire.fecharModalPin()">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-sm">
                    <div class="p-4 border-b"><h2 class="text-xl font-bold">Autorização Necessária</h2></div>
                    <div class="p-6 space-y-4">
                        <label for="pin-input" class="block text-sm font-medium text-gray-700">Digite o PIN do Supervisor</label>
                        <input id="pin-input" type="password" wire:model="supervisorPin" wire:keydown.enter="verificarPin" class="w-full p-2 border border-gray-300 rounded-md text-center text-lg" autofocus>
                        @if($pinIncorreto) <span class="text-red-500 text-sm">PIN inválido ou usuário não é um Supervisor.</span> @endif
                        @error('pin') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="p-3 bg-gray-50 flex justify-end gap-2">
                        <button wire:click="fecharModalPin" class="px-4 py-2 bg-gray-200 rounded-md">Cancelar</button>
                        <button wire:click="verificarPin" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">Autorizar</button>
                    </div>
                </div>
            </div>
            @endif

            @if ($showCancelNfceModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @keydown.escape.window="$wire.fecharModalCancelarNfce()">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-lg">
                    <div class="p-4 border-b flex justify-between items-center"><h2 class="text-xl font-bold">Cancelar Última NFC-e</h2><button wire:click="fecharModalCancelarNfce" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button></div>
                    <div class="p-6 space-y-4">
                        @if($ultimaNfeAutorizada)
                            <div class="text-sm"><p><strong>Chave:</strong> {{ $ultimaNfeAutorizada->chave_acesso }}</p><p><strong>Data:</strong> {{ $ultimaNfeAutorizada->created_at->format('d/m/Y H:i') }}</p></div>
                            <div><label for="justificativa" class="block text-sm font-medium">Justificativa (mínimo 15 caracteres)</label><textarea id="justificativa" wire:model="justificativaCancelamento" rows="4" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="Ex: Erro de digitação no valor."></textarea>@error('justificativaCancelamento') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror @error('cancelamento_nfce') <span class="text-red-500 text-sm mt-2 block">{{ $message }}</span> @enderror</div>
                        @else
                            <p class="text-center text-gray-600">Nenhuma nota fiscal para cancelar.</p>
                        @endif
                    </div>
                    <div class="p-3 bg-gray-50 flex justify-end">
                        <button wire:click="fecharModalCancelarNfce" class="px-4 py-2 bg-gray-200 rounded-md mr-2">Fechar</button>
                        @if($ultimaNfeAutorizada)<button wire:click="cancelarUltimaNfce" wire:loading.attr="disabled" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"><span wire:loading.remove wire:target="cancelarUltimaNfce">Confirmar</span><span wire:loading wire:target="cancelarUltimaNfce">Cancelando...</span></button>@endif
                    </div>
                </div>
            </div>
            @endif

            @if ($showIdentificarModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @keydown.escape.window="$set('showIdentificarModal', false)">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-md">
                    <div class="p-4 border-b"><h2 class="text-xl font-bold">Identificar Consumidor</h2></div>
                    <div class="p-6 space-y-4">
                        <input type="text" wire:model="documentoCliente" wire:keydown.enter="identificarCliente" placeholder="Digite o CPF/CNPJ e pressione ENTER" class="w-full p-2 border border-gray-300 rounded-md" autofocus>
                        @error('finalizacao') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <div class="p-3 bg-gray-50 text-right"><button wire:click="$set('showIdentificarModal', false)" class="px-4 py-2 bg-gray-200 rounded-md">Fechar</button></div>
                </div>
            </div>
            @endif

            @if ($showSangriaModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @keydown.escape.window="$wire.fecharModalSangria()">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-md">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h2 class="text-xl font-bold">Realizar Sangria de Caixa</h2>
                        <button wire:click="fecharModalSangria" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="valorSangria" class="block text-sm font-medium text-gray-700">Valor da Retirada</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">R$</span>
                                <input type="number" step="0.01" id="valorSangria" wire:model="valorSangria" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md text-lg" placeholder="0,00" autofocus>
                            </div>
                            @error('valorSangria') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="observacaoSangria" class="block text-sm font-medium text-gray-700">Observação (Opcional)</label>
                            <textarea id="observacaoSangria" wire:model="observacaoSangria" rows="3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="Ex: Retirada para o malote."></textarea>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 flex justify-end">
                        <button wire:click="fecharModalSangria" class="px-4 py-2 bg-gray-200 rounded-md mr-2">Cancelar</button>
                        <button wire:click="executarSangria" wire:loading.attr="disabled" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Confirmar Retirada
                        </button>
                    </div>
                </div>
            </div>
            @endif

            @if ($showSuprimentoModal)
            <div class="fixed inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center z-50" @keydown.escape.window="$wire.fecharModalSuprimento()">
                <div class="bg-white rounded-lg shadow-xl text-gray-800 w-full max-w-md">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h2 class="text-xl font-bold">Realizar Suprimento de Caixa</h2>
                        <button wire:click="fecharModalSuprimento" class="text-gray-500 hover:text-gray-800 text-3xl">&times;</button>
                    </div>
                    <div class="p-6 space-y-4">
                        <div>
                            <label for="valorSuprimento" class="block text-sm font-medium text-gray-700">Valor da Adição</label>
                            <div class="relative mt-1">
                                <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">R$</span>
                                <input type="number" step="0.01" id="valorSuprimento" wire:model="valorSuprimento" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md text-lg" placeholder="0,00" autofocus>
                            </div>
                            @error('valorSuprimento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                        </div>
                        <div>
                            <label for="observacaoSuprimento" class="block text-sm font-medium text-gray-700">Observação (Opcional)</label>
                            <textarea id="observacaoSuprimento" wire:model="observacaoSuprimento" rows="3" class="mt-1 block w-full p-2 border border-gray-300 rounded-md" placeholder="Ex: Adição de troco."></textarea>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 flex justify-end">
                        <button wire:click="fecharModalSuprimento" class="px-4 py-2 bg-gray-200 rounded-md mr-2">Cancelar</button>
                        <button wire:click="executarSuprimento" wire:loading.attr="disabled" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Confirmar Adição
                        </button>
                    </div>
                </div>
            </div>
            @endif
        </div>

    {{-- Se o caixa estiver FECHADO, mostra a tela para abrir --}}
    @else
        <div class="flex items-center justify-center h-screen bg-gray-200">
            <div class="bg-white p-8 rounded-lg shadow-lg text-center w-full max-w-md">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Caixa Fechado</h1>
                <p class="text-gray-600 mb-6">Você precisa abrir o caixa para iniciar as vendas.</p>
                <div class="w-full max-w-xs mx-auto">
                    <label for="valorAbertura" class="block text-sm font-medium text-gray-700 mb-1">Valor Inicial (Troco)</label>
                    <div class="relative"><span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">R$</span><input type="number" step="0.01" id="valorAbertura" wire:model="valorAbertura" wire:keydown.enter="abrirCaixa" class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-md text-center text-lg" placeholder="0,00" autofocus></div>
                    @error('valorAbertura') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                </div>
                <button wire:click="abrirCaixa" wire:loading.attr="disabled" class="mt-6 w-full max-w-xs mx-auto bg-green-600 text-white font-bold py-3 rounded-md hover:bg-green-700 transition"><span wire:loading.remove wire:target="abrirCaixa">Abrir Caixa</span><span wire:loading wire:target="abrirCaixa">Abrindo...</span></button>
            </div>
        </div>
    @endif

    @script
    <script>
        document.addEventListener('livewire:initialized', () => {
            const barcodeInput = document.getElementById('barcode-input');
            const pinInput = document.getElementById('pin-input');

            // Foco automático no input de código de barras
            const focusBarcode = () => {
                if(barcodeInput) {
                    barcodeInput.value = '';
                    barcodeInput.focus();
                }
            };

            Livewire.on('produto-adicionado', focusBarcode);
            Livewire.on('pdv-resetado', focusBarcode);

            // Foco automático no input de PIN quando o modal abre
            Livewire.on('show-pin-modal', () => {
                setTimeout(() => {
                    document.getElementById('pin-input')?.focus();
                }, 100);
            });

            // Gerenciador de atalhos do teclado
            document.addEventListener('keydown', e => {
                if (e.key === 'F2') { e.preventDefault(); barcodeInput?.focus(); }
                if (e.key === 'F8') { e.preventDefault(); @this.call('abrirModalPagamento'); }
                if (e.key === 'F9') { e.preventDefault(); @this.call('resetarPdv'); }
                if (e.key === 'F10') { e.preventDefault(); @this.call('abrirMenuOpcoes'); }
            });

            if(barcodeInput) { barcodeInput.focus(); }
        });
    </script>
    @endscript
</div>