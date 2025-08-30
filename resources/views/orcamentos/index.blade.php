<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap justify-between items-center gap-4">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Orçamentos de Venda
            </h2>
            <a href="{{ route('orcamentos.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                Novo Orçamento
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            {{-- Formulário de Busca --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <form action="{{ route('orcamentos.index') }}" method="GET">
                    <div class="flex items-center gap-4">
                        <div class="flex-grow">
                            <x-input-label for="search" value="Buscar Orçamento" />
                            <x-text-input id="search" name="search" type="text" class="mt-1 block w-full" 
                                          :value="request('search')" 
                                          placeholder="Buscar por cliente ou número..."/>
                        </div>
                        <x-primary-button class="mt-6">Buscar</x-primary-button>
                        <a href="{{ route('orcamentos.index') }}" class="mt-6 inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700">Limpar</a>
                    </div>
                </form>
            </div>

            {{-- Tabela de Dados --}}
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Número</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Valor Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="relative px-6 py-3"><span class="sr-only">Ações</span></th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                            @forelse ($orcamentos as $orcamento)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">#{{ $orcamento->id }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $orcamento->cliente->nome ?? 'Cliente não encontrado' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($orcamento->data_emissao)->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">R$ {{ number_format($orcamento->valor_total, 2, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        {{-- Lógica de cor para o status --}}
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @switch($orcamento->status)
                                                @case('Pendente') bg-yellow-100 text-yellow-800 @break
                                                @case('Aprovado') bg-green-100 text-green-800 @break
                                                @case('Recusado') bg-red-100 text-red-800 @break
                                                @case('Cancelado') bg-gray-100 text-gray-800 @break
                                            @endswitch">
                                            {{ $orcamento->status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-4">
                                        <a href="{{ route('orcamentos.show', $orcamento) }}" class="text-gray-500 hover:text-gray-900">Ver</a>
                                        <a href="{{ route('orcamentos.edit', $orcamento) }}" class="text-indigo-600 hover:text-indigo-900">Editar</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">Nenhum orçamento encontrado.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $orcamentos->withQueryString()->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>