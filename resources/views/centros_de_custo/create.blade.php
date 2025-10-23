<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Novo Centro de Custo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Esta é a linha mais importante. Ela carrega o formulário correto. --}}
            @livewire('centros-de-custo.centro-custo-form')
        </div>
    </div>
</x-app-layout>