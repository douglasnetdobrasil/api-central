<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Contas a Pagar
            </h2>
            <a href="{{ route('contas_a_pagar.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md">
                Nova Conta
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- A mágica acontece aqui! Chamamos o componente Livewire --}}
            @livewire('contas-a-pagar.conta-index')
        </div>
    </div>
</x-app-layout>