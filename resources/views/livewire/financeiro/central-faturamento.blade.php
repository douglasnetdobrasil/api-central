<div>
    {{-- 
      Isto agora é injetado no <x-slot name="header"> do seu 'layouts.app'
      graças ao atributo #[Layout] que colocamos no arquivo .php 
    --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Central de Faturamento (Dinheiro na Mesa)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Sessão de Alertas (Corrigindo o 'class' que estava com '...') --}}
            @if (session()->has('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Sucesso!</p>
                    <p>{{ session('success') }}</p>
                </div>
            @endif
            @if (session()->has('error'))
                 <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p class="font-bold">Erro!</p>
                    <p>{{ session('error') }}</p>
                </div>
            @endif

            {{-- 1. KPIs - O "DINHEIRO NA MESA" --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                
                {{-- PRONTO PARA FATURAR --}}
                <div class="bg-green-50 dark:bg-green-900/50 p-6 rounded-lg shadow-sm border border-green-300 dark:border-green-700">
                    <p class="text-sm font-medium text-green-700 dark:text-green-300">Pronto para Faturar</p>
                    <p class="text-3xl font-bold text-green-900 dark:text-green-100 mt-1">
                        R$ {{ number_format($kpiProntoParaFaturar, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-green-600 dark:text-green-400">OS e Pedidos concluídos aguardando faturamento.</p>
                </div>

                {{-- EM ANDAMENTO --}}
                <div class="bg-yellow-50 dark:bg-yellow-900/50 p-6 rounded-lg shadow-sm border border-yellow-300 dark:border-yellow-700">
                    <p class="text-sm font-medium text-yellow-700 dark:text-yellow-300">Em Andamento (Com Valor)</p>
                    <p class="text-3xl font-bold text-yellow-900 dark:text-yellow-100 mt-1">
                        R$ {{ number_format($kpiEmAndamento, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400">OS abertas que precisam ser finalizadas.</p>
                </div>
                
                {{-- PROPOSTAS --}}
                <div class="bg-blue-50 dark:bg-blue-900/50 p-6 rounded-lg shadow-sm border border-blue-300 dark:border-blue-700">
                    <p class="text-sm font-medium text-blue-700 dark:text-blue-300">Em Proposta</p>
                    <p class="text-3xl font-bold text-blue-900 dark:text-blue-100 mt-1">
                        R$ {{ number_format($kpiPropostas, 2, ',', '.') }}
                    </p>
                    <p class="text-xs text-blue-600 dark:text-blue-400">Orçamentos pendentes de aprovação.</p>
                </div>
            </div>

            {{-- 2. ABAS DE TRABALHO --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                {{-- Botões das Abas (CORRIGIDO para usar ->total() de paginadores) --}}
                <div class="flex border-b border-gray-200 dark:border-gray-700">
                    <button wire:click="$set('abaAtual', 'pronto')" 
                            class="py-4 px-6 font-medium text-sm @if($abaAtual == 'pronto') border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 @else text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 @endif">
                        Pronto para Faturar ({{ $osProntasParaFaturar->total() + $pedidosProntosParaFaturar->total() }})
                    </button>
                    <button wire:click="$set('abaAtual', 'andamento')" 
                            class="py-4 px-6 font-medium text-sm @if($abaAtual == 'andamento') border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 @else text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 @endif">
                        OS em Andamento ({{ $osEmAndamento->total() }})
                    </button>
                    <button wire:click="$set('abaAtual', 'propostas')" 
                            class="py-4 px-6 font-medium text-sm @if($abaAtual == 'propostas') border-b-2 border-indigo-500 text-indigo-600 dark:text-indigo-400 @else text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 @endif">
                        Propostas Pendentes ({{ $orcamentosPendentes->total() }})
                    </button>
                </div>

                <div class="p-6">
                    {{-- ABA 1: PRONTO PARA FATURAR --}}
                    <div wire:loading.remove wire:target="abaAtual" x-show="abaAtual === 'pronto'" x-cloak>
                        
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Ordens de Serviço Prontas</h3>
                        {{-- 
                          REUTILIZAÇÃO PROFISSIONAL:
                          Passamos a variável paginada para o include.
                          O 'wire:click="faturarRapido"' será capturado por este componente.
                        --}}

                        {{-- ABA 1: PRONTO PARA FATURAR --}}
                    <div wire:loading.remove wire:target="abaAtual" x-show="abaAtual === 'pronto'" x-cloak>
                        
                        <div class="flex justify-between items-center mb-4"> {{-- Container para Título e Botão --}}
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Ordens de Serviço Prontas</h3>
                            {{-- BOTÃO FATURAR TODAS --}}
                            @if($osProntasParaFaturar->isNotEmpty()) {{-- Só mostra se houver OS para faturar --}}
                            <button 
                                wire:click="faturarTodasOS" 
                                wire:confirm="Tem certeza que deseja faturar TODAS as {{ $osProntasParaFaturar->total() }} Ordens de Serviço pendentes (modo rápido: à vista, hoje)?"
                                wire:loading.attr="disabled" {{-- Desabilita enquanto fatura --}}
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest disabled:opacity-50">
                                <span wire:loading wire:target="faturarTodasOS">Faturando...</span> {{-- Mostra texto "Faturando..." --}}
                                <span wire:loading.remove wire:target="faturarTodasOS">Faturar Todas as OS</span> {{-- Mostra texto normal --}}
                            </button>
                            @endif
                        </div>

                        @include('ordens_servico.partials.tabela-os-index', ['ordensServico' => $osProntasParaFaturar])

                        
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 my-6">Pedidos Prontos</h3>
                        {{-- Tabela para Pedidos --}}
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Pedido #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($pedidosProntosParaFaturar as $pedido)
                                        <tr>
                                            <td class="px-6 py-4">{{ $pedido->id }}</td>
                                            <td class="px-6 py-4">{{ $pedido->cliente->nome ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 text-right">R$ {{ number_format($pedido->valor_total, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <button wire:click="faturarPedido({{ $pedido->id }})" wire:confirm="Confirma o faturamento deste pedido?" class="text-green-600 hover:text-green-800 dark:hover:text-green-400 font-medium">
                                                    Faturar Pedido
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">Nenhum pedido pronto para faturar.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $pedidosProntosParaFaturar->links() }}
                        </div>
                    </div>

                    {{-- ABA 2: OS EM ANDAMENTO --}}
                    <div wire:loading.remove wire:target="abaAtual" x-show="abaAtual === 'andamento'" x-cloak>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Ordens de Serviço que já possuem valor de peças ou serviços, mas que ainda não foram marcadas como "Concluída".
                        </p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">OS #</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Cliente</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Técnico</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Valor Atual</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($osEmAndamento as $os)
                                        <tr>
                                            <td class="px-6 py-4">{{ $os->id }}</td>
                                            <td class="px-6 py-4">{{ $os->cliente->nome ?? 'N/A' }}</td>
                                            <td class="px-6 py-4">{{ $os->tecnico->name ?? 'N/A' }}</td>
                                            <td class="px-6 py-4">{{ $os->status }}</td>
                                            <td class="px-6 py-4 text-right font-semibold">R$ {{ number_format($os->valor_total, 2, ',', '.') }}</td>
                                            <td class="px-6 py-4 text-right">
                                                <a href="{{ route('ordens-servico.edit', $os) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                    Ver OS
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-4 text-gray-500 dark:text-gray-400">Nenhuma OS em andamento com valor.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">
                            {{ $osEmAndamento->links() }}
                        </div>
                    </div>

                    {{-- ABA 3: PROPOSTAS PENDENTES --}}
                    <div wire:loading.remove wire:target="abaAtual" x-show="abaAtual === 'propostas'" x-cloak>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                            Orçamentos enviados que aguardam aprovação do cliente.
                        </p>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                     <tr>
                                        <th class="px-6 py-3 text-left ...">Orçamento #</th>
                                        <th class="px-6 py-3 text-left ...">Cliente</th>
                                        <th class="px-6 py-3 text-right ...">Valor</th>
                                        <th class="px-6 py-3 text-right ...">Ações</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($orcamentosPendentes as $orcamento)
                                    <tr>
                                        <td class="px-6 py-4">{{ $orcamento->id }}</td>
                                        <td class="px-6 py-4">{{ $orcamento->cliente->nome ?? 'N/A' }}</td>
                                        <td class="px-6 py-4 text-right">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                        <td class="px-6 py-4 text-right">
                                            <a href="#" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                                Ver Orçamento
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr><td colspan="4" class="text-center py-4 text-gray-500 dark:text-gray-400">Nenhuma proposta pendente.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                         <div class="mt-4">
                            {{ $orcamentosPendentes->links() }}
                        </div>
                    </div>
                    
                    {{-- Indicador de Carregamento --}}
                    <div wire:loading.flex wire:target="abaAtual" class="w-full items-center justify-center p-6">
                        <svg class="animate-spin h-8 w-8 text-indigo-600 dark:text-indigo-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="ml-3 text-gray-700 dark:text-gray-300">Carregando dados da aba...</span>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>