<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Fichas Técnicas de Produção
            </h2>
            <a href="{{ route('ficha-tecnica.create') }}">
                <x-primary-button>
                    Nova Ficha Técnica
                </x-primary-button>
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Mensagens de sucesso ou erro --}}
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="space-y-6">
                        @forelse ($produtosComFicha as $produto)
                            <div class="p-4 border rounded-lg dark:border-gray-700 flex justify-between items-center">
                                <div>
                                    <h3 class="text-lg font-bold">{{ $produto->nome }}</h3>
                                    <p class="text-sm text-gray-500">
                                        {{ $produto->fichaTecnica->count() }} ingrediente(s) na receita.
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('ficha-tecnica.edit', $produto->id) }}">
                                        <x-primary-button>Gerenciar</x-primary-button>
                                    </a>
                                </div>
                            </div>
                        @empty
                            <p>Nenhuma ficha técnica encontrada. Comece criando uma nova.</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $produtosComFicha->links() }}</div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>