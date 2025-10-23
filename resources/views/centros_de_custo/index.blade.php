<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Centros de Custo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Colocando o componente de volta --}}
            @livewire('centros-de-custo.centro-custo-index')
        </div>
    </div>
</x-app-layout>