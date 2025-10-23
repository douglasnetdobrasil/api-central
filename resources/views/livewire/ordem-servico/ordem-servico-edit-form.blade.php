<div>
    {{-- Notificação de sucesso do Livewire --}}
    @if (session('success'))
        <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md" role="alert">
            {{ session('success') }}
        </div>
    @endif

    {{-- Seção de Peças e Produtos --}}
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-8">
        {{-- AQUI! Garanta que o nome do arquivo inclui '-livewire' --}}
        @include('ordens_servico.partials.form-add-pecas-livewire')
        
        <div class="mt-6">
            {{-- E AQUI TAMBÉM! --}}
            @include('ordens_servico.partials.tabela-pecas-livewire')
        </div>
    </div>

    {{-- Seção de Serviços --}}
    <div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-8">
        {{-- AQUI! --}}
        @include('ordens_servico.partials.form-add-servicos-livewire')
        
        <div class="mt-6">
            {{-- E FINALMENTE AQUI! --}}
            @include('ordens_servico.partials.tabela-servicos-livewire')
        </div>
    </div>
    {{-- NOVO BLOCO DE TOTAIS --}}
<div class="mt-8 bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 md:p-8">
    
    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
        Resumo Financeiro
    </h3>

    <div class="space-y-3">
        {{-- Total de Peças --}}
        <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
            <span class="text-md">Total de Peças e Produtos:</span>
            <span class="text-md font-semibold">
                {{-- A variável $os vem do seu componente Livewire --}}
                R$ {{ number_format($os->valor_produtos, 2, ',', '.') }}
            </span>
        </div>

        {{-- Total de Serviços --}}
        <div class="flex justify-between items-center text-gray-700 dark:text-gray-300">
            <span class="text-md">Total de Serviços:</span>
            <span class="text-md font-semibold">
                R$ {{ number_format($os->valor_servicos, 2, ',', '.') }}
            </span>
        </div>

        {{-- Desconto (AINDA NÃO IMPLEMENTADO, MAS JÁ PODEMOS DEIXAR O CAMPO) --}}
        <div class="flex justify-between items-center text-gray-700 dark:text-gray-300 border-t dark:border-gray-700 pt-3">
            <span class="text-md">Descontos:</span>
            <span class="text-md font-semibold text-red-500">
                - R$ {{ number_format($os->valor_desconto, 2, ',', '.') }}
            </span>
            {{-- Futuramente, você pode adicionar um input aqui com wire:model="os.valor_desconto" --}}
        </div>

        {{-- Total Geral --}}
        <div class="flex justify-between items-center text-gray-900 dark:text-gray-100 border-t dark:border-gray-700 pt-3">
            <span class="text-xl font-bold">TOTAL GERAL:</span>
            <span class="text-xl font-bold">
                R$ {{ number_format($os->valor_total, 2, ',', '.') }}
            </span>
        </div>
    </div>
</div>
</div>