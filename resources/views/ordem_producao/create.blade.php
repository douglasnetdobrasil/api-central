<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nova Ordem de Produção
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if ($errors->any())
                        <div class="mb-4 text-red-500">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('ordem-producao.store') }}" method="POST">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label for="produto_acabado_id">Produto a ser Produzido</label>
                                <select name="produto_acabado_id" id="produto_acabado_id" class="mt-1 block w-full rounded-md shadow-sm" required>
                                    <option value="">Selecione um produto...</option>
                                    @foreach ($produtosParaProduzir as $produto)
                                        <option value="{{ $produto->id }}" {{ old('produto_acabado_id') == $produto->id ? 'selected' : '' }}>
                                            {{ $produto->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Apenas produtos com Ficha Técnica cadastrada são listados.</p>
                            </div>
                            <div>
                                <label for="quantidade_planejada">Quantidade a Produzir</label>
                                <input type="number" name="quantidade_planejada" id="quantidade_planejada" value="{{ old('quantidade_planejada') }}" class="mt-1 block w-full rounded-md shadow-sm" required>
                            </div>
                             <div>
                                <label for="data_inicio_prevista">Data de Início (Prevista)</label>
                                <input type="date" name="data_inicio_prevista" id="data_inicio_prevista" value="{{ old('data_inicio_prevista') }}" class="mt-1 block w-full rounded-md shadow-sm">
                            </div>
                             <div>
                                <label for="data_fim_prevista">Data de Fim (Prevista)</label>
                                <input type="date" name="data_fim_prevista" id="data_fim_prevista" value="{{ old('data_fim_prevista') }}" class="mt-1 block w-full rounded-md shadow-sm">
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('ordem-producao.index') }}"><x-secondary-button class="mr-3">Cancelar</x-secondary-button></a>
                            <x-primary-button type="submit">Criar Ordem de Produção</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>