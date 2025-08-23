<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Categoria: {{ $categoria->nome }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <form method="POST" action="{{ route('categorias.update', $categoria) }}">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input-label for="nome" value="Nome da Categoria" />
                            <x-text-input id="nome" class="block mt-1 w-full" type="text" name="nome" :value="old('nome', $categoria->nome)" required autofocus />
                            <x-input-error :messages="$errors->get('nome')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="margem_lucro" value="Margem de Lucro (%)" />
                            <x-text-input id="margem_lucro" class="block mt-1 w-full" type="number" step="0.01" name="margem_lucro" :value="old('margem_lucro', $categoria->margem_lucro)" />
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Deixe em branco para usar a margem de lucro padrão do sistema.</p>
                            <x-input-error :messages="$errors->get('margem_lucro')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4 mt-4">
                            <x-primary-button>Atualizar</x-primary-button>
                            <a href="{{ route('categorias.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Deletar Categoria</h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Após deletar, todos os dados serão permanentemente removidos. Tenha certeza antes de continuar.
                    </p>
                    <form method="POST" action="{{ route('categorias.destroy', $categoria) }}" class="mt-6">
                        @csrf
                        @method('DELETE')
                        <x-danger-button onclick="return confirm('Tem certeza que deseja excluir esta categoria?')">
                            Deletar Categoria
                        </x-danger-button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>