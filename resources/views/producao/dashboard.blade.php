<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Painel de Produção
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="p-4 bg-yellow-100 dark:bg-yellow-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-yellow-800 dark:text-yellow-200">OPs Planejadas</h3>
                    <p class="mt-1 text-3xl font-semibold text-yellow-900 dark:text-yellow-100">{{ $stats['ops_planejadas'] }}</p>
                </div>
                <div class="p-4 bg-blue-100 dark:bg-blue-900 shadow-sm sm:rounded-lg text-center">
                    <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200">OPs em Produção</h3>
                    <p class="mt-1 text-3xl font-semibold text-blue-900 dark:text-blue-100">{{ $stats['ops_em_producao'] }}</p>
                </div>
                <a href="{{ route('ficha-tecnica.create') }}" class="p-4 bg-red-100 dark:bg-red-900 shadow-sm sm:rounded-lg text-center hover:opacity-80">
                    <h3 class="text-lg font-medium text-red-800 dark:text-red-200">Produtos Sem Ficha Técnica</h3>
                    <p class="mt-1 text-3xl font-semibold text-red-900 dark:text-red-100">{{ $stats['produtos_sem_ficha'] }}</p>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Passo 1: Fichas Técnicas</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Gerencie as "receitas" dos seus produtos. Adicione ou remova ingredientes e defina as quantidades necessárias para a produção.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('ficha-tecnica.index') }}">
                                <x-primary-button>Gerenciar Fichas Técnicas</x-primary-button>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-gray-100">Passo 2: Ordens de Produção</h3>
                        <p class="mt-2 text-gray-600 dark:text-gray-400">
                            Crie, inicie e finalize as ordens para fabricar seus produtos com base nas Fichas Técnicas já cadastradas.
                        </p>
                        <div class="mt-4">
                            <a href="{{ route('ordem-producao.index') }}">
                                <x-secondary-button>Ver Ordens de Produção</x-secondary-button>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Ordens de Produção Recentes</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <tbody>
                                @forelse ($ordensRecentes as $op)
                                    <tr class="border-t">
                                        <td class="px-4 py-3 font-bold">OP #{{ $op->id }}</td>
                                        <td class="px-4 py-3">{{ $op->produtoAcabado->nome }}</td>
                                        <td class="px-4 py-3 text-center">{{ number_format($op->quantidade_planejada, 0, ',', '.') }} un.</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($op->status == 'Planejada') bg-yellow-100 text-yellow-800 @endif
                                                @if($op->status == 'Em Produção') bg-blue-100 text-blue-800 @endif
                                                @if($op->status == 'Concluída') bg-green-100 text-green-800 @endif
                                            ">
                                                {{ $op->status }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('ordem-producao.show', $op) }}" class="text-indigo-600 hover:text-indigo-900">Detalhes</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td class="py-4 text-center">Nenhuma atividade recente.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>