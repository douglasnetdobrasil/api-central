<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Editar Empresa: {{ $empresa->razao_social }}
        </h2>
    </x-slot>

    {{-- O bloco duplicado foi removido daqui --}}

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    {{-- A action aponta para a rota correta de update --}}
                    <form action="{{ route('empresa.update', $empresa) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH') {{-- ou 'PUT' --}}

                        {{-- Incluindo o formulário que já está correto --}}
                        @include('admin.empresa._form')
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>