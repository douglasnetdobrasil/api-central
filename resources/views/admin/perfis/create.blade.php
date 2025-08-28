<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Criar Novo Perfil
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('perfis.store') }}" method="POST">
                        @csrf

                        <div>
                            <x-input-label for="name" value="Nome do Perfil" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name')" required autofocus />
                        </div>

                        <div class="flex items-center justify-end mt-4">
                             <a href="{{ route('perfis.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Salvar Perfil
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>