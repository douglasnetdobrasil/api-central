<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center no-print">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Relatório Financeiro
            </h2>
            <x-primary-button onclick="window.print()">
                Imprimir Relatório
            </x-primary-button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div id="filtros" class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6 no-print">
                <form method="GET" action="{{ route('relatorios.financeiro.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div>
                            <label for="data_inicio" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Início</label>
                            <input type="date" name="data_inicio" id="data_inicio" value="{{ $dataInicio }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data Fim</label>
                            <input type="date" name="data_fim" id="data_fim" value="{{ $dataFim }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                        <x-primary-button type="submit">Filtrar</x-primary-button>
                    </div>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="p-4 bg-green-100 dark:bg-green-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-green-700 dark:text-green-300">Total de Receitas</h3>
                    <p class="mt-1 text-3xl font-semibold text-green-900 dark:text-green-100">R$ {{ number_format($totalReceitas, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 bg-red-100 dark:bg-red-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-red-700 dark:text-red-300">Total de Despesas</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-900 dark:text-red-100">R$ {{ number_format($totalDespesas, 2, ',', '.') }}</p>
                </div>
                <div class="p-4 {{ $saldo >= 0 ? 'bg-blue-100 dark:bg-blue-900' : 'bg-orange-100 dark:bg-orange-900' }} shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium {{ $saldo >= 0 ? 'text-blue-700 dark:text-blue-300' : 'text-orange-700 dark:text-orange-300' }}">Saldo do Período</h3>
                    <p class="mt-1 text-3xl font-semibold {{ $saldo >= 0 ? 'text-blue-900 dark:text-blue-100' : 'text-orange-900 dark:text-orange-100' }}">R$ {{ number_format($saldo, 2, ',', '.') }}</p>
                </div>
            </div>
            
            <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg mb-6">
                <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Fluxo de Caixa (Entradas vs. Saídas)</h3>
                <canvas id="fluxoCaixaChart" height="100"></canvas>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Contas a Receber Pendentes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead><tr><th class="text-left">Vencimento</th><th class="text-left">Cliente</th><th class="text-right">Valor</th></tr></thead>
                            <tbody>
                                @forelse($contasAReceber as $conta)
                                <tr class="border-t {{ $conta->data_vencimento < $hoje ? 'text-red-500' : '' }}">
                                    <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                                    <td>{{ $conta->cliente_nome }}</td>
                                    <td class="text-right">R$ {{ number_format($conta->valor - $conta->valor_recebido, 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4">Nenhuma conta a receber pendente.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                     <h3 class="font-semibold mb-4 text-gray-800 dark:text-gray-200">Contas a Pagar Pendentes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead><tr><th class="text-left">Vencimento</th><th class="text-left">Fornecedor</th><th class="text-right">Valor</th></tr></thead>
                            <tbody>
                                @forelse($contasAPagar as $conta)
                                <tr class="border-t {{ $conta->data_vencimento < $hoje ? 'text-red-500' : '' }}">
                                    <td>{{ \Carbon\Carbon::parse($conta->data_vencimento)->format('d/m/Y') }}</td>
                                    <td>{{ $conta->fornecedor_nome ?? $conta->descricao }}</td>
                                    <td class="text-right">R$ {{ number_format($conta->valor_total - $conta->valor_pago, 2, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center py-4">Nenhuma conta a pagar pendente.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @include('relatorios.financeiro.charts-script')
</x-app-layout>