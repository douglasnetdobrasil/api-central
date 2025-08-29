<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    {{-- Coluna 1: Dados Principais --}}
    <div class="space-y-4">
        <div>
            <x-input-label for="razao_social" value="Razão Social" />
            <x-text-input id="razao_social" name="razao_social" type="text" class="mt-1 block w-full" :value="old('razao_social', $transportadora->razao_social ?? '')" required />
        </div>
        <div>
            <x-input-label for="nome_fantasia" value="Nome Fantasia" />
            <x-text-input id="nome_fantasia" name="nome_fantasia" type="text" class="mt-1 block w-full" :value="old('nome_fantasia', $transportadora->nome_fantasia ?? '')" />
        </div>
        <div>
            <x-input-label for="cnpj" value="CNPJ / CPF" />
            <div class="flex items-center gap-2 mt-1">
                <x-text-input id="cnpj" name="cnpj" type="text" class="block w-full" :value="old('cnpj', $transportadora->cnpj ?? '')" required />
                <x-secondary-button type="button" id="buscar-cnpj-btn">Buscar</x-secondary-button>
            </div>
            <div id="cnpj-status" class="text-sm text-gray-500 mt-1"></div>
        </div>
         <div>
            <x-input-label for="inscricao_estadual" value="Inscrição Estadual" />
            <x-text-input id="inscricao_estadual" name="inscricao_estadual" type="text" class="mt-1 block w-full" :value="old('inscricao_estadual', $transportadora->inscricao_estadual ?? '')" />
        </div>
    </div>

    {{-- Coluna 2: Endereço --}}
    <div class="space-y-4">
        <div>
            <x-input-label for="cep" value="CEP" />
            <x-text-input id="cep" name="cep" type="text" class="mt-1 block w-full" :value="old('cep', $transportadora->cep ?? '')" />
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <x-input-label for="logradouro" value="Logradouro" />
                <x-text-input id="logradouro" name="logradouro" type="text" class="mt-1 block w-full" :value="old('logradouro', $transportadora->logradouro ?? '')" />
            </div>
            <div>
                <x-input-label for="numero" value="Número" />
                <x-text-input id="numero" name="numero" type="text" class="mt-1 block w-full" :value="old('numero', $transportadora->numero ?? '')" />
            </div>
        </div>
        <div>
            <x-input-label for="bairro" value="Bairro" />
            <x-text-input id="bairro" name="bairro" type="text" class="mt-1 block w-full" :value="old('bairro', $transportadora->bairro ?? '')" />
        </div>
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-2">
                <x-input-label for="cidade" value="Cidade" />
                <x-text-input id="cidade" name="cidade" type="text" class="mt-1 block w-full" :value="old('cidade', $transportadora->cidade ?? '')" />
            </div>
            <div>
                <x-input-label for="uf" value="UF" />
                <x-text-input id="uf" name="uf" type="text" class="mt-1 block w-full" :value="old('uf', $transportadora->uf ?? '')" />
            </div>
        </div>
    </div>
    
    {{-- Coluna 3: Contato e Outros --}}
    <div class="space-y-4">
        <div>
            <x-input-label for="telefone" value="Telefone" />
            <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $transportadora->telefone ?? '')" />
        </div>
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $transportadora->email ?? '')" />
        </div>
        <div>
            <x-input-label for="rntc" value="RNTC" />
            <x-text-input id="rntc" name="rntc" type="text" class="mt-1 block w-full" :value="old('rntc', $transportadora->rntc ?? '')" />
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-8">
    <a href="{{ route('transportadoras.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
       Cancelar
   </a>
   <x-primary-button>
       Salvar Transportadora
   </x-primary-button>
</div>