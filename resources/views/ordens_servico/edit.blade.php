<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        TESTE - Editar Ordem de Serviço #{{ $ordemServico->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- Formulário de Dados Principais (NÃO-LIVEWIRE) --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-8">
                <form action="{{ route('ordens-servico.update', $ordemServico->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        {{-- Este include está correto, pois este formulário não foi movido para o Livewire --}}
                        @include('ordens_servico.partials.form-dados-principais')
                    </div>
                    
                    <div class="flex items-center justify-end mt-6">
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-sm shadow-sm">
                            Salvar Dados Principais
                        </button>
                    </div>
                </form>
            </div>

            {{-- COMPONENTE LIVEWIRE PARA ITENS DINÂMICOS --}}
            {{-- Esta linha carrega o arquivo do Passo 1, que por sua vez carrega todos os partials '-livewire' --}}
            @livewire('ordem-servico.ordem-servico-edit-form', ['ordemServico' => $ordemServico])

        </div>
    </div>
</x-app-layout>