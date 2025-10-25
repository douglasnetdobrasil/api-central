<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Editar Ordem de Serviço #{{ $ordemServico->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">

            {{-- ====================================================== --}}
            {{-- |||||||||||||| BLOCO DE INFORMAÇÃO DO CHAMADO |||||||||||||| --}}
            {{-- ====================================================== --}}
            @if ($ordemServico->suporte_chamado_id)
                <div class="p-4 bg-purple-100 dark:bg-purple-900/50 border-l-4 border-purple-500 dark:border-purple-600 rounded-lg text-purple-700 dark:text-purple-200 shadow-sm">
                    <p class="font-medium">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" /></svg>
                        Esta Ordem de Serviço foi **convertida** do Chamado de Suporte: 
                        <a href="{{ route('admin.chamados.show', $ordemServico->suporte_chamado_id) }}" target="_blank" class="font-bold underline hover:text-purple-800 dark:hover:text-purple-100 transition duration-150">
                            #{{ $ordemServico->chamadoDeOrigem->protocolo ?? $ordemServico->suporte_chamado_id }}
                        </a>
                        ({{ $ordemServico->chamadoDeOrigem->titulo ?? 'Chamado Original' }}).
                    </p>
                    <p class="text-sm mt-1 ml-7">
                        Acesse o link para ver o histórico de conversas e soluções aplicadas antes da abertura da OS.
                    </p>
                </div>
            @endif
            {{-- ====================================================== --}}
            
            
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