<x-app-layout>
    <div x-data="{ showFilters: false }">
        <x-slot name="header">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Ordens de Serviço') }}
                </h2>
                <div class="flex items-center space-x-4">
                    <button @click="showFilters = true" title="Filtrar Ordens de Serviço" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md font-semibold text-sm shadow-sm">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('ordens-servico.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-sm shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Nova OS
                    </a>
                </div>
            </div>
        </x-slot>

        {{-- O seu Modal de Filtros continua aqui, sem alterações --}}
        <div x-show="showFilters" x-transition class="fixed inset-0 bg-black bg-opacity-50 z-40" style="display: none;">
            <div @click.away="showFilters = false" class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl mx-auto my-12 p-6">
                 {{-- ... todo o conteúdo do seu modal ... --}}
            </div>
        </div>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Sucesso!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        {{-- =============================================== --}}
                        {{-- |||||||||||||||||| A CORREÇÃO ESTÁ AQUI |||||||||||||||||| --}}
                        {{-- =============================================== --}}
                        {{-- Estamos chamando o novo arquivo que você criou no Passo 1 --}}
                        @include('ordens_servico.partials.tabela-os-index') 
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>