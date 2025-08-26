<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $cliente->exists ? 'Editar Cliente' : 'Cadastrar Novo Cliente' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ $cliente->exists ? route('clientes.update', $cliente->id) : route('clientes.store') }}" method="POST">
                        @csrf
                        @if ($cliente->exists)
                            @method('PUT')
                        @endif

                        {{-- DADOS DO CLIENTE --}}
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dados Principais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <x-input-label for="nome" value="Nome / Razão Social" />
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $cliente->nome ?? '')" required />
                            </div>
                            <div>
                                <x-input-label for="cpf_cnpj" value="CPF/CNPJ" />
                                <div class="flex items-center gap-2 mt-1">
                                    <x-text-input id="cpf_cnpj" name="cpf_cnpj" type="text" class="block w-full" :value="old('cpf_cnpj', $cliente->cpf_cnpj ?? '')" />
                                    <x-secondary-button type="button" id="buscar-cnpj-btn">Buscar</x-secondary-button>
                                </div>
                            </div>
                            <div>
                                <x-input-label for="telefone" value="Telefone" />
                                <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $cliente->telefone ?? '')" />
                            </div>
                             <div>
                                <x-input-label for="email" value="E-mail" />
                                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $cliente->email ?? '')" />
                            </div>
                        </div>
                        
                        {{-- ENDEREÇO --}}
                        <h3 class="text-lg font-medium mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">Endereço</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
                            <div class="md:col-span-1">
                                <x-input-label for="cep" value="CEP" />
                                <x-text-input id="cep" name="cep" type="text" class="mt-1 block w-full" :value="old('cep', $cliente->cep ?? '')" />
                            </div>
                            <div class="md:col-span-3">
                                <x-input-label for="logradouro" value="Logradouro" />
                                <x-text-input id="logradouro" name="logradouro" type="text" class="mt-1 block w-full" :value="old('logradouro', $cliente->logradouro ?? '')" />
                            </div>
                             <div class="md:col-span-1">
                                <x-input-label for="numero" value="Número" />
                                <x-text-input id="numero" name="numero" type="text" class="mt-1 block w-full" :value="old('numero', $cliente->numero ?? '')" />
                            </div>
                            <div class="md:col-span-1">
                                <x-input-label for="complemento" value="Complemento" />
                                <x-text-input id="complemento" name="complemento" type="text" class="mt-1 block w-full" :value="old('complemento', $cliente->complemento ?? '')" />
                            </div>
                            <div class="md:col-span-2">
                                <x-input-label for="bairro" value="Bairro" />
                                <x-text-input id="bairro" name="bairro" type="text" class="mt-1 block w-full" :value="old('bairro', $cliente->bairro ?? '')" />
                            </div>
                             <div class="md:col-span-3">
                                <x-input-label for="cidade" value="Cidade" />
                                <x-text-input id="cidade" name="cidade" type="text" class="mt-1 block w-full" :value="old('cidade', $cliente->cidade ?? '')" />
                            </div>
                             <div class="md:col-span-1">
                                <x-input-label for="estado" value="Estado (UF)" />
                                <x-text-input id="estado" name="estado" type="text" class="mt-1 block w-full" :value="old('estado', $cliente->estado ?? '')" />
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-8">
                             <a href="{{ route('clientes.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Salvar Cliente
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const buscarBtn = document.getElementById('buscar-cnpj-btn');
            const cnpjInput = document.getElementById('cpf_cnpj');

            buscarBtn.addEventListener('click', async function () {
                const cnpj = cnpjInput.value.replace(/[^0-9]/g, '');
                if (cnpj.length !== 14) {
                    alert('Por favor, digite um CNPJ válido com 14 dígitos.');
                    return;
                }

                buscarBtn.textContent = 'Buscando...';
                buscarBtn.disabled = true;

                try {
                    const response = await fetch(`https://brasilapi.com.br/api/cnpj/v1/${cnpj}`);
                    if (!response.ok) throw new Error('CNPJ não encontrado ou inválido.');
                    
                    const data = await response.json();

                    // Preenche os campos do formulário com os dados da API
                    document.getElementById('nome').value = data.razao_social || '';
                    document.getElementById('telefone').value = data.ddd_telefone_1 || '';
                    
                    document.getElementById('cep').value = data.cep || '';
                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('numero').value = data.numero || '';
                    document.getElementById('complemento').value = data.complemento || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.municipio || '';
                    document.getElementById('estado').value = data.uf || '';

                    alert('Dados do CNPJ preenchidos com sucesso!');
                } catch (error) {
                    alert(`Erro ao buscar CNPJ: ${error.message}`);
                } finally {
                    buscarBtn.textContent = 'Buscar';
                    buscarBtn.disabled = false;
                }
            });
        });
    </script>
</x-app-layout>