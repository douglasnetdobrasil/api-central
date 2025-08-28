<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Configurações da Minha Empresa
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('empresa.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PATCH')

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <x-input-label for="razao_social" value="Razão Social" />
                                    <x-text-input id="razao_social" name="razao_social" type="text" class="mt-1 block w-full" :value="old('razao_social', $empresa->razao_social)" required />
                                </div>
                                <div>
                                    <x-input-label for="nome_fantasia" value="Nome Fantasia" />
                                    <x-text-input id="nome_fantasia" name="nome_fantasia" type="text" class="mt-1 block w-full" :value="old('nome_fantasia', $empresa->nome_fantasia)" />
                                </div>
                                <div>
                                    <x-input-label for="cnpj" value="CNPJ" />
                                    <x-text-input id="cnpj" name="cnpj" type="text" class="mt-1 block w-full" :value="old('cnpj', $empresa->cnpj)" required />
                                </div>
                                <div>
                                    <x-input-label for="endereco" value="Endereço Completo" />
                                    <x-text-input id="endereco" name="endereco" type="text" class="mt-1 block w-full" :value="old('endereco', $empresa->endereco)" />
                                </div>
                            </div>

                            <div>
                                <x-input-label for="logo" value="Logo da Empresa" />
                                <div class="mt-2 flex items-center space-x-6">
                                    @if ($empresa->logo_path)
                                        <img src="{{ Storage::url($empresa->logo_path) }}" alt="Logo Atual" class="h-24 w-24 rounded-full object-cover">
                                    @else
                                        <div class="h-24 w-24 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center">
                                            <span class="text-sm text-gray-500">Sem Logo</span>
                                        </div>
                                    @endif
                                    <input id="logo" name="logo" type="file" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('logo')" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
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