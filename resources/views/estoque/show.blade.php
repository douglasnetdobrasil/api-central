<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Extrato de Estoque
                </h2>
                <p class="text-sm text-gray-500">{{ $produto->nome }}</p>
            </div>
            <a href="{{ route('estoque.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                &larr; Voltar para a busca
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        @livewire('estoque.movimentacao-produto', ['produto' => $produto])
        </div>
    </div>
</x-app-layout>