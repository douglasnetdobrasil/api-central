<x-app-layout>
    <x-slot name="head">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
        {{-- Adiciona FontAwesome para ícones, se ainda não tiver --}}
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    </x-slot>

    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Editando Ordem de Serviço #{{ $ordemServico->id }}
            </h2>
            <a href="{{ route('ordens-servico.show', $ordemServico->id) }}" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md font-semibold text-sm shadow-sm">
                <i class="fas fa-eye mr-1"></i> Ver Detalhes
            </a>
            <a href="{{ route('ordens-servico.imprimir', $ordemServico->id) }}" target="_blank" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md font-semibold text-sm shadow-sm">
               <i class="fas fa-print mr-1"></i> Imprimir
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Card 0: Resumo Financeiro --}}
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Resumo Financeiro
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Valor em Peças</span>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($ordemServico->valor_produtos, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Valor em Serviços</span>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($ordemServico->valor_servicos, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Descontos</span>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">R$ {{ number_format($ordemServico->valor_desconto, 2, ',', '.') }}</p>
                    </div>
                    <div>
                        <span class="text-sm font-medium text-white bg-indigo-600 px-3 py-1 rounded">Valor Total</span>
                        <p class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">R$ {{ number_format($ordemServico->valor_total, 2, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Card 1: Informações Principais da OS --}}
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Dados Principais
                </h3>
                <form action="{{ route('ordens-servico.update', ['ordemServico' => $ordemServico->id]) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @include('ordens_servico.partials.form-dados-principais')
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                            Salvar Alterações Principais
                        </button>
                    </div>
                </form>
            </div>

            {{-- Card 2: Peças e Produtos Utilizados --}}
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                @include('ordens_servico.partials.form-add-pecas')
            </div>

            {{-- Card 3: Serviços Prestados --}}
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                @include('ordens_servico.partials.form-add-servicos')
            </div>

            {{-- Card 4: Histórico de Alterações --}}
            <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                    Histórico de Alterações
                </h3>
                <div class="space-y-4">
                    @forelse ($ordemServico->historico as $evento)
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <span class="inline-flex items-center justify-center h-8 w-8 rounded-full bg-gray-200 dark:bg-gray-700">
                                    <i class="fas fa-history text-gray-500 dark:text-gray-400"></i>
                                </span>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $evento->descricao }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Por: {{ $evento->usuario->name }} em {{ $evento->created_at->format('d/m/Y H:i') }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum evento de histórico registrado.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('.select-search').forEach((select) => {
                    new TomSelect(select, { create: false, sortField: { field: "text", direction: "asc" } });
                });
            });
        </script>
    @endpush
</x-app-layout>