<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Criar Nova Ficha Técnica
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <p class="mb-4">Selecione o produto para o qual você deseja criar uma nova receita. Apenas produtos que ainda não possuem uma Ficha Técnica são listados.</p>
                    
                    @if ($errors->any())
                        <div class="mb-4 text-red-500">{{ $errors->first() }}</div>
                    @endif

                    <form method="POST" action="{{ route('ficha-tecnica.store') }}">
                        @csrf
                        <div>
                            <label for="produto_acabado_id" class="block text-sm font-medium">Produto Acabado</label>
                            <select name="produto_acabado_id" id="produto_acabado_id" class="mt-1 block w-full rounded-md shadow-sm">
                                <option value="">Selecione...</option>
                                @foreach ($produtosAcabados as $produto)
                                    <option value="{{ $produto->id }}">{{ $produto->nome }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('ficha-tecnica.index') }}"><x-secondary-button class="mr-3">Cancelar</x-secondary-button></a>
                            <x-primary-button type="submit">Prosseguir</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>