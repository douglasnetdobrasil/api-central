<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($fornecedor) ? 'Editar Fornecedor' : 'Cadastrar Novo Fornecedor' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ isset($fornecedor) ? route('fornecedores.update', $fornecedor->id) : route('fornecedores.store') }}" method="POST">
                        @csrf
                        @if (isset($fornecedor))
                            @method('PUT')
                        @endif

                        {{-- DADOS DO FORNECEDOR --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <x-input-label for="razao_social" value="Razão Social" />
                                <x-text-input id="razao_social" name="razao_social" type="text" class="mt-1 block w-full" :value="old('razao_social', $fornecedor->razao_social ?? '')" required />
                            </div>
                            <div>
                                <x-input-label for="nome_fantasia" value="Nome Fantasia" />
                                <x-text-input id="nome_fantasia" name="nome_fantasia" type="text" class="mt-1 block w-full" :value="old('nome_fantasia', $fornecedor->nome_fantasia ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="cpf_cnpj" value="CPF/CNPJ" />
                                <x-text-input id="cpf_cnpj" name="cpf_cnpj" type="text" class="mt-1 block w-full" :value="old('cpf_cnpj', $fornecedor->cpf_cnpj ?? '')" required />
                            </div>
                            <div>
                                <x-input-label for="telefone" value="Telefone" />
                                <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $fornecedor->telefone ?? '')" />
                            </div>
                             <div>
                                <x-input-label for="email" value="E-mail" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $fornecedor->email ?? '')" />
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-8">
                            <x-primary-button>
                                Salvar Fornecedor
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>