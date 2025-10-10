<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Monitor de Contingência Fiscal') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Esta é a linha que "coloca a foto na moldura" --}}
            <livewire:fiscal.contingencia-monitor />
        </div>
    </div>
</x-app-layout>