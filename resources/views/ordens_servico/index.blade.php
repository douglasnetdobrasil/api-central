<x-app-layout>
    {{-- O x-data inicializa o Alpine.js neste bloco, criando uma variável 'showFilters' com o valor 'false' --}}
    <div x-data="{ showFilters: false }">
        <x-slot name="header">
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    {{ __('Ordens de Serviço') }}
                </h2>
                <div class="flex items-center space-x-4">
                    {{-- ESTE É O BOTÃO QUE ABRE O MODAL --}}
                    {{-- @click="showFilters = true" muda a variável para 'true', fazendo o modal aparecer --}}
                    <button @click="showFilters = true" title="Filtrar Ordens de Serviço" class="px-3 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 rounded-md font-semibold text-sm shadow-sm">
                        <i class="fas fa-filter"></i>
                    </button>
                    <a href="{{ route('ordens-servico.create') }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-sm shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Nova OS
                    </a>
                </div>
            </div>
        </x-slot>

        {{-- =============================================== --}}
        {{-- |||||||||||||||||| O MODAL DE FILTROS |||||||||||||||||| --}}
        {{-- =============================================== --}}
        {{-- x-show="showFilters" faz este bloco inteiro só ser visível quando 'showFilters' for 'true' --}}
        <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black bg-opacity-50 z-40" style="display: none;">
            
            {{-- Container do Modal --}}
            {{-- @click.away="showFilters = false" fecha o modal se clicar fora dele --}}
            <div @click.away="showFilters = false" class="relative bg-white dark:bg-gray-800 rounded-lg shadow-xl max-w-2xl mx-auto my-12 p-6">
                {{-- Cabeçalho do Modal --}}
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Filtrar Ordens de Serviço</h3>
                    {{-- Botão para fechar o modal --}}
                    <button @click="showFilters = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <span class="text-2xl">&times;</span>
                    </button>
                </div>

                {{-- O formulário de busca que já tínhamos, agora dentro do modal --}}
                <form action="{{ route('ordens-servico.index') }}" method="GET">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="search_os" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nº da OS</label>
                            <input type="number" name="search_os" id="search_os" value="{{ request('search_os') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                        <div>
                            <label for="search_cliente" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nome do Cliente</label>
                            <input type="text" name="search_cliente" id="search_cliente" value="{{ request('search_cliente') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                        <div class="sm:col-span-2">
    <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Status</label>
    <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
        <option value="Todos">Todos</option>
        <option value="Em Aberto" {{ request('status') == 'Em Aberto' ? 'selected' : '' }}>Em Aberto</option>
        <option value="Aguardando Orçamento" {{ request('status') == 'Aguardando Orçamento' ? 'selected' : '' }}>Aguardando Orçamento</option>
        <option value="Aprovada" {{ request('status') == 'Aprovada' ? 'selected' : '' }}>Aprovada</option>
        <option value="Em Execução" {{ request('status') == 'Em Execução' ? 'selected' : '' }}>Em Execução</option>
        <option value="Concluída" {{ request('status') == 'Concluída' ? 'selected' : '' }}>Concluída</option>
        <option value="Cancelada" {{ request('status') == 'Cancelada' ? 'selected' : '' }}>Cancelada</option>
    </select>
</div>
                        <div>
                            <label for="data_fim" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Até</label>
                            <input type="date" name="data_fim" id="data_fim" value="{{ request('data_fim') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        </div>
                    </div>
                    <div class="mt-6 flex items-center justify-end">
                        <a href="{{ route('ordens-servico.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">Limpar Filtros</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-sm shadow-sm">
                            <i class="fas fa-search mr-1"></i> Aplicar Filtros
                        </button>
                    </div>
                </form>
            </div>
        </div>
        {{-- =============================================== --}}
        {{-- |||||||||||||||||| FIM DO MODAL |||||||||||||||||| --}}
        {{-- =============================================== --}}

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                {{-- ... Mensagem de sucesso e a sua TABELA continuam aqui, sem alterações ... --}}
                 @if (session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Sucesso!</p>
                        <p>{{ session('success') }}</p>
                    </div>
                @endif

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        {{-- O código da sua tabela de OS vai aqui, exatamente como era antes --}}
                        @include('ordens_servico.partials.tabela-os') {{-- Sugestão: Mover a tabela para um partial --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>