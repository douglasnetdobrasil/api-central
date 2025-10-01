<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Criar Nova Conta a Pagar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            {{-- Este é o comando que carrega o seu formulário Livewire --}}
            @livewire('contas-a-pagar.conta-form', ['conta' => $conta])
        </div>
    </div>
</x-app-layout>