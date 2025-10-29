<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Central de Cobranças (Contas a Receber)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- KPIs --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-blue-50 dark:bg-blue-900/50 p-6 rounded-lg shadow-sm border border-blue-300 dark:border-blue-700">
                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Total a Receber</p>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">R$ {{ number_format($totalAReceber, 2, ',', '.') }}</p>
                </div>
                 <div class="bg-red-50 dark:bg-red-900/50 p-6 rounded-lg shadow-sm border border-red-300 dark:border-red-700">
                    <p class="text-sm font-medium text-red-700 dark:text-red-300">Total Vencido</p>
                    <p class="text-3xl font-bold text-red-900 dark:text-red-100 mt-1">R$ {{ number_format($totalVencido, 2, ',', '.') }}</p>
                </div>
                 <div class="bg-green-50 dark:bg-green-900/50 p-6 rounded-lg shadow-sm border border-green-300 dark:border-green-700">
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">Recebido (Período)</p>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100 mt-1">R$ {{ number_format($totalRecebidoPeriodo, 2, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filtros --}}
            <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                 <form method="GET" action="{{ route('financeiro.cobrancas.index') }}">
                    {{-- Usando flex para melhor alinhamento e espaçamento --}}
                    <div class="flex flex-wrap items-end gap-4">
                        {{-- Cliente --}}
                        <div class="flex-1 min-w-[200px]"> {{-- Permite que o campo cresça, com largura mínima --}}
                            <x-input-label for="cliente_id" value="Cliente" />
                            <select name="cliente_id" id="cliente_id" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Todos</option>
                                @foreach($clientes as $id => $nome)
                                    <option value="{{ $id }}" @selected($clienteId == $id)>{{ $nome }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Status --}}
                         <div class="flex-1 min-w-[150px]">
                            <x-input-label for="status" value="Status" />
                            <select name="status" id="status" class="mt-1 block w-full rounded-md shadow-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                <option value="">Todos</option>
                                @foreach($statusOptions as $s)
                                    <option value="{{ $s }}" @selected($status == $s)>{{ $s }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Data Vencimento Início --}}
                        <div class="min-w-[150px]">
                            <x-input-label for="venc_inicio" value="Venc. Início" />
                            <x-text-input type="date" name="venc_inicio" value="{{ $dataVencInicio }}" class="mt-1 block w-full text-sm" />
                        </div>
                        {{-- Data Vencimento Fim --}}
                        <div class="min-w-[150px]">
                            <x-input-label for="venc_fim" value="Venc. Fim" />
                            <x-text-input type="date" name="venc_fim" value="{{ $dataVencFim }}" class="mt-1 block w-full text-sm" />
                        </div>
                        {{-- Botão Aplicar --}}
                        <div class="ml-auto"> {{-- Empurra o botão para a direita --}}
                           <x-primary-button type="submit" class="h-10"> {{-- Altura fixa para alinhar --}}
                                Filtrar
                            </x-primary-button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Tabela de Contas a Receber --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="overflow-x-auto"> {{-- Movido para fora do p-6 para melhor rolagem --}}
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                {{-- Ajustando padding e texto --}}
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">#</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Descrição</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Vencimento</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Total</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Recebido</th>
                                <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse ($contasAReceber as $conta)
                                <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 @if($conta->data_vencimento < \Carbon\Carbon::today() && !in_array($conta->status, ['Recebido', 'Cancelado'])) bg-red-50 dark:bg-red-900/30 @endif">
                                    {{-- Ajustando padding e tamanho do texto --}}
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $conta->id }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300">{{ $conta->cliente->nome ?? 'N/A' }}</td>
                                    <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-300">{{ $conta->descricao }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-center text-gray-600 dark:text-gray-300">{{ $conta->data_vencimento->format('d/m/Y') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-900 dark:text-gray-100 font-medium">R$ {{ number_format($conta->valor, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-gray-600 dark:text-gray-300">R$ {{ number_format($conta->valor_recebido, 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 whitespace-nowrap text-center text-sm">
                                         <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full
                                            @if($conta->status == 'Recebido') bg-green-100 text-green-800 dark:bg-green-700 dark:text-green-100
                                            @elseif($conta->status == 'Recebido Parcialmente') bg-yellow-100 text-yellow-800 dark:bg-yellow-700 dark:text-yellow-100
                                            @elseif($conta->status == 'Cancelado') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-100
                                            @else bg-blue-100 text-blue-800 dark:bg-blue-700 dark:text-blue-100 @endif
                                            @if($conta->data_vencimento < \Carbon\Carbon::today() && !in_array($conta->status, ['Recebido', 'Cancelado'])) !bg-red-100 !text-red-800 dark:!bg-red-700 dark:!text-red-100 @endif">
                                            {{ $conta->status == 'A Receber' && $conta->data_vencimento < \Carbon\Carbon::today() ? 'Vencido' : $conta->status }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                                        {{-- Dropdown de Ações (A lógica interna permanece a mesma) --}}
                                        <div x-data="{ open: false }" @click.away="open = false" class="relative inline-block text-left">
                                            <button @click="open = !open" type="button" class="inline-flex justify-center w-full rounded-md border border-gray-300 dark:border-gray-700 shadow-sm px-3 py-1.5 bg-white dark:bg-gray-700 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                                Ações
                                                <svg class="-mr-1 ml-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div x-show="open" x-transition ... class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white dark:bg-gray-800 ring-1 ring-black ring-opacity-5 focus:outline-none z-10" style="display: none;">
                                                <div class="py-1" role="none">
                                                    @if(!in_array($conta->status, ['Recebido', 'Cancelado']))
                                                    <button @click="$dispatch('open-modal', 'registrar-pagamento-{{ $conta->id }}')" class="text-green-600 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <i class="fas fa-dollar-sign mr-2"></i> Registrar Pagamento
                                                    </button>
                                                    @endif
                                                    <a href="{{ route('financeiro.cobrancas.pdf', $conta) }}" target="_blank" class="text-blue-600 block px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <i class="fas fa-file-pdf mr-2"></i> Gerar PDF
                                                    </a>
                                                    <form action="{{ route('financeiro.cobrancas.enviar-email', $conta) }}" method="POST" onsubmit="return confirm('Enviar email de cobrança para {{ $conta->cliente->email ?? 'cliente' }}?')">
                                                        @csrf
                                                        <button type="submit" class="text-purple-600 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                                            <i class="fas fa-envelope mr-2"></i> Enviar por Email
                                                        </button>
                                                    </form>
                                                    {{-- Botão Gerar Boleto (Placeholder) --}}
                                                    <button onclick="alert('Funcionalidade Gerar Boleto ainda não implementada.')" class="text-gray-600 block w-full text-left px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-700">
                                                        <i class="fas fa-barcode mr-2"></i> Gerar Boleto
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Modal para Registrar Pagamento (A lógica interna permanece a mesma) --}}
                                        <x-modal name="registrar-pagamento-{{ $conta->id }}" focusable>
                                            <form method="post" action="{{ route('financeiro.cobrancas.registrar-pagamento', $conta) }}" class="p-6">
                                                @csrf
                                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                    Registrar Pagamento para Conta #{{ $conta->id }}
                                                </h2>
                                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                                   Valor Pendente: R$ {{ number_format($conta->valor - $conta->valor_recebido, 2, ',', '.') }}
                                                </p>
                                                <div class="mt-6">
                                                    <x-input-label for="valor_pago_{{ $conta->id }}" value="Valor Pago *" class="sr-only" />
                                                    <x-text-input type="number" step="0.01" name="valor_pago" id="valor_pago_{{ $conta->id }}" placeholder="Valor Pago *" class="mt-1 block w-full" required />
                                                     @error('valor_pago') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                                </div>
                                                 <div class="mt-4">
                                                    <x-input-label for="data_pagamento_{{ $conta->id }}" value="Data do Pagamento *" />
                                                    <x-text-input type="date" name="data_pagamento" id="data_pagamento_{{ $conta->id }}" value="{{ date('Y-m-d') }}" class="mt-1 block w-full" required />
                                                     @error('data_pagamento') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                                </div>
                                                 <div class="mt-4">
                                                    <x-input-label for="forma_pagamento_id_{{ $conta->id }}" value="Forma de Pagamento *" />
                                                    <select name="forma_pagamento_id" id="forma_pagamento_id_{{ $conta->id }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-sm" required>
                                                        <option value="">Selecione...</option>
                                                        @php $formasPagamento = \App\Models\FormaPagamento::where('ativo', 1)->orderBy('nome')->get(); @endphp
                                                        @foreach($formasPagamento as $fp)
                                                        <option value="{{ $fp->id }}">{{ $fp->nome }}</option>
                                                        @endforeach
                                                    </select>
                                                     @error('forma_pagamento_id') <span class="text-red-500 text-sm mt-1">{{ $message }}</span> @enderror
                                                </div>

                                                <div class="mt-6 flex justify-end">
                                                    <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                                                    <x-primary-button class="ml-3">Registrar</x-primary-button>
                                                </div>
                                            </form>
                                        </x-modal>

                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center py-8 text-gray-500 dark:text-gray-400">Nenhuma conta a receber encontrada com os filtros atuais.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginação --}}
                <div class="px-6 py-4 border-t dark:border-gray-700"> {{-- Adicionado padding para separar da tabela --}}
                    {{ $contasAReceber->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>