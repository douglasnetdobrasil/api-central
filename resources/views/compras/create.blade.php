<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Lançar Nova Nota de Compra') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('compras.store') }}" method="POST">
                        @csrf

                        <h3 class="text-lg font-semibold mb-4">Dados da Nota</h3>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <x-input-label for="fornecedor_id" :value="__('Fornecedor')" />
                                <select name="fornecedor_id" id="fornecedor_id" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Selecione um fornecedor</option>
                                    @foreach ($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id }}">{{ $fornecedor->razao_social }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="numero_nota" :value="__('Número da Nota')" />
                                <x-text-input type="text" name="numero_nota" id="numero_nota" class="block mt-1 w-full" required />
                            </div>
                            <div>
                                <x-input-label for="data_emissao" :value="__('Data de Emissão')" />
                                <x-text-input type="date" name="data_emissao" id="data_emissao" class="block mt-1 w-full" required />
                            </div>
                        </div>

                        <div class="mt-8">
                            <h3 class="text-lg font-semibold mb-4">Itens da Nota</h3>
                            <div class="p-4 border-dashed border-2 border-gray-300 rounded-lg">
                                <p class="text-center text-gray-500">A seção para adicionar os itens da nota será implementada aqui.</p>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end">
                            <x-primary-button>
                                {{ __('Salvar Rascunho da Nota') }}
                            </x-primary-button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>