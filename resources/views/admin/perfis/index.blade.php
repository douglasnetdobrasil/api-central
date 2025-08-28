<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Gerenciamento de Perfis') }}
            </h2>
            <a href="{{ route('perfis.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                Novo Perfil
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-700">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome do Perfil</th>
                                    <th class="px-6 py-3"></th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200">
                                @forelse ($perfis as $perfil)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $perfil->id }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">{{ $perfil->name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('perfis.edit', $perfil) }}" class="text-indigo-600 hover:text-indigo-900">Editar Permissões</a>

                                            {{-- Formulário de Exclusão --}}
                                            <form action="{{ route('perfis.destroy', $perfil) }}" method="POST" class="inline ml-4" onsubmit="return confirm('Tem certeza que deseja excluir este perfil?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900">Excluir</button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-4 text-center">Nenhum perfil encontrado.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $perfis->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>