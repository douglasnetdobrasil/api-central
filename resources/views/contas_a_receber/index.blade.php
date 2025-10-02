<x-app-layout>
<x-slot name="header">
        {{-- << ADICIONE ESTE DIV ENVOLVENDO O TÍTULO E O BOTÃO >> --}}
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Contas a Receber
            </h2>
            <a href="{{ route('contas_a_receber.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm">
                Nova Conta
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Carrega o nosso componente Livewire da listagem --}}
            @livewire('contas-a-receber.conta-receber-index')
        </div>
    </div>
</x-app-layout>