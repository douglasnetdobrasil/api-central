<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Perfil: {{ $perfi->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- Formulário de Edição --}}
                    <form action="{{ route('perfis.update', $perfi) }}" method="POST">
                        @csrf
                        @method('PUT')

                        {{-- Campo para editar o Nome do Perfil --}}
                        <div>
                            <x-input-label for="name" value="Nome do Perfil" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $perfi->name)" required autofocus />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>

                        {{-- Seção de Permissões --}}
                        <div class="mt-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                Permissões Associadas
                            </h3>
                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                Marque as permissões que este perfil poderá acessar.
                            </p>

                            {{-- Grid com todas as permissões disponíveis --}}
                            <div class="mt-4 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                                @forelse ($permissions as $permission)
                                    <label class="flex items-center space-x-2">
                                        <input type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                               {{-- Lógica para marcar as permissões que o perfil já possui --}}
                                               @if( $perfi->hasPermissionTo($permission->name) ) checked @endif
                                        >
                                        <span>{{ $permission->name }}</span>
                                    </label>
                                @empty
                                    <p class="text-gray-500">Nenhuma permissão encontrada. Crie as permissões primeiro.</p>
                                @endforelse
                            </div>
                        </div>

                        {{-- Botões de Ação --}}
                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('perfis.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                               Cancelar
                           </a>
                           <x-primary-button>
                               Salvar Alterações
                           </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>