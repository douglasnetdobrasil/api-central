<div class="space-y-8">

    {{-- 1. DADOS CADASTRAIS --}}
    <div class="p-4 border rounded-lg dark:border-gray-700">
        <h3 class="font-semibold text-lg mb-4">1. Dados Cadastrais</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="razao_social" value="Razão Social *" />
                <x-text-input id="razao_social" name="razao_social" type="text" class="mt-1 block w-full" :value="old('razao_social', $empresa->razao_social)" required />
                <x-input-error class="mt-2" :messages="$errors->get('razao_social')" />
            </div>
            <div>
                <x-input-label for="nome_fantasia" value="Nome Fantasia" />
                <x-text-input id="nome_fantasia" name="nome_fantasia" type="text" class="mt-1 block w-full" :value="old('nome_fantasia', $empresa->nome_fantasia)" />
            </div>
            <div>
                <x-input-label for="cnpj" value="CNPJ *" />
                <div class="flex items-center space-x-2 mt-1">
                    <x-text-input id="cnpj" name="cnpj" type="text" class="block w-full" :value="old('cnpj', $empresa->cnpj)" required />
                    <x-secondary-button type="button" id="buscar-cnpj-btn">Buscar</x-secondary-button>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('cnpj')" />
            </div>
            <div>
                <x-input-label for="ie" value="Inscrição Estadual" />
                <x-text-input id="ie" name="ie" type="text" class="mt-1 block w-full" :value="old('ie', $empresa->ie)" />
            </div>
        </div>
    </div>

    {{-- 2. ENDEREÇO FISCAL --}}
    <div class="p-4 border rounded-lg dark:border-gray-700">
        <h3 class="font-semibold text-lg mb-4">2. Endereço Fiscal</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2">
                <x-input-label for="logradouro" value="Logradouro (Rua, Av.)" />
                <x-text-input id="logradouro" name="logradouro" type="text" class="mt-1 block w-full" :value="old('logradouro', $empresa->logradouro)" />
            </div>
            <div>
                <x-input-label for="numero" value="Número" />
                <x-text-input id="numero" name="numero" type="text" class="mt-1 block w-full" :value="old('numero', $empresa->numero)" />
            </div>
            <div>
                <x-input-label for="bairro" value="Bairro" />
                <x-text-input id="bairro" name="bairro" type="text" class="mt-1 block w-full" :value="old('bairro', $empresa->bairro)" />
            </div>
            <div>
                <x-input-label for="cep" value="CEP" />
                <x-text-input id="cep" name="cep" type="text" class="mt-1 block w-full" :value="old('cep', $empresa->cep)" />
            </div>
             <div>
                <x-input-label for="municipio" value="Município" />
                <x-text-input id="municipio" name="municipio" type="text" class="mt-1 block w-full" :value="old('municipio', $empresa->municipio)" />
            </div>
            <div>
                <x-input-label for="uf" value="UF" />
                <x-text-input id="uf" name="uf" type="text" class="mt-1 block w-full" :value="old('uf', $empresa->uf)" />
            </div>
            <div>
                <x-input-label for="codigo_municipio" value="Código IBGE do Município" />
                <x-text-input id="codigo_municipio" name="codigo_municipio" type="text" class="mt-1 block w-full" :value="old('codigo_municipio', $empresa->codigo_municipio)" />
            </div>
             <div>
                <x-input-label for="telefone" value="Telefone" />
                <x-text-input id="telefone" name="telefone" type="text" class="mt-1 block w-full" :value="old('telefone', $empresa->telefone)" />
            </div>
        </div>
    </div>

    {{-- 3. CONFIGURAÇÃO FISCAL --}}
    <div class="p-4 border rounded-lg dark:border-gray-700">
        <h3 class="font-semibold text-lg mb-4">3. Configuração Fiscal</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Linha 1 --}}
            <div>
                <x-input-label for="crt" value="Regime Tributário (CRT) *" />
                <select name="crt" id="crt" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                    <option value="">Selecione...</option>
                    <option value="1" @selected(old('crt', $empresa->crt) == 1)>1 - Simples Nacional</option>
                    <option value="3" @selected(old('crt', $empresa->crt) == 3)>3 - Regime Normal</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('crt')" />
            </div>
            <div class="md:col-span-2">
            <x-input-label for="nfe_proximo_numero" value="Próximo Número da NF-e (Série 1)" />
            <x-text-input id="nfe_proximo_numero" name="nfe_proximo_numero" type="number" class="mt-1 block w-full" :value="old('nfe_proximo_numero', $empresa->nfe_proximo_numero)" />
            <x-input-error class="mt-2" :messages="$errors->get('nfe_proximo_numero')" />
            <p class="text-xs text-gray-500 mt-1">
                Preencha este campo apenas para empresas que estão migrando de outro sistema. Informe o número da PRÓXIMA nota a ser emitida. Ex: se a última foi 99, informe 100.
            </p>
        </div>
            <div>
                <x-input-label for="ambiente_nfe" value="Ambiente de Emissão de NF-e *" />
                <select name="ambiente_nfe" id="ambiente_nfe" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                    <option value="2" @selected(old('ambiente_nfe', $empresa->ambiente_nfe) == 2)>2 - Homologação (Ambiente de Testes)</option>
                    <option value="1" @selected(old('ambiente_nfe', $empresa->ambiente_nfe) == 1)>1 - Produção (Valor Fiscal)</option>
                </select>
                <x-input-error class="mt-2" :messages="$errors->get('ambiente_nfe')" />
            </div>

            {{-- Linha 2 --}}
            <div>
                <x-input-label for="nicho_negocio" value="Nicho de Negócio *" />
                <select name="nicho_negocio" id="nicho_negocio" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
                    <option value="">Selecione um nicho</option>
                    <option value="mercado" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'mercado')>Mercado</option>
                    <option value="oficina" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'oficina')>Oficina</option>
                    <option value="restaurante" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'restaurante')>Restaurante</option>
                    <option value="loja_roupas" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'loja_roupas')>Loja de Roupas</option>
                </select>
            </div>
            <div>
                <x-input-label for="codigo_uf" value="Código IBGE da UF *" />
                <x-text-input id="codigo_uf" name="codigo_uf" type="text" class="mt-1 block w-full" :value="old('codigo_uf', $empresa->codigo_uf)" required />
                <x-input-error class="mt-2" :messages="$errors->get('codigo_uf')" />
                <p class="text-xs text-gray-500 mt-1">Ex: 35 para SP, 33 para RJ. Consulte a tabela IBGE.</p>
            </div>

            {{-- Divisor --}}
            <hr class="md:col-span-2 my-2 border-gray-200 dark:border-gray-700">

            {{-- Linha 3 --}}
            <div>
                <x-input-label for="csc_nfe" value="Token CSC (NF-e/NFC-e) *" />
                <x-text-input id="csc_nfe" name="csc_nfe" type="text" class="mt-1 block w-full" :value="old('csc_nfe', $empresa->csc_nfe)" required />
                <x-input-error class="mt-2" :messages="$errors->get('csc_nfe')" />
                <p class="text-xs text-gray-500 mt-1">Fornecido pela SEFAZ do seu estado.</p>
            </div>
            <div>
                <x-input-label for="csc_id_nfe" value="ID do Token CSC *" />
                <x-text-input id="csc_id_nfe" name="csc_id_nfe" type="text" class="mt-1 block w-full" :value="old('csc_id_nfe', $empresa->csc_id_nfe)" required />
                <x-input-error class="mt-2" :messages="$errors->get('csc_id_nfe')" />
                <p class="text-xs text-gray-500 mt-1">Geralmente '000001' ou '000002'.</p>
            </div>
        </div>
    </div>

    {{-- 4. CERTIFICADO DIGITAL --}}
    <div class="p-4 border rounded-lg dark:border-gray-700">
        <h3 class="font-semibold text-lg mb-4">4. Certificado Digital A1</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input-label for="certificado_a1_path" value="Arquivo do Certificado (.pfx)" />
                <input id="certificado_a1_path" name="certificado_a1_path" type="file" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"/>
                <x-input-error class="mt-2" :messages="$errors->get('certificado_a1_path')" />
                @if($empresa->certificado_a1_path)
                    <p class="text-xs text-green-600 mt-2">Um certificado já foi enviado. Envie um novo apenas para substituí-lo.</p>
                @endif
            </div>
             <div>
                <x-input-label for="certificado_a1_password" value="Senha do Certificado" />
                <x-text-input id="certificado_a1_password" name="certificado_a1_password" type="password" class="mt-1 block w-full" />
                 <p class="text-xs text-gray-500 mt-1">Deixe em branco se não quiser alterar a senha atual.</p>
            </div>
        </div>
    </div>
</div>

<div class="flex items-center justify-end mt-8">
    <a href="{{ route('empresa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md">
       Cancelar
   </a>
   <x-primary-button class="ml-4">
       Salvar
   </x-primary-button>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const buscarBtn = document.getElementById('buscar-cnpj-btn');
        if (!buscarBtn) return;

        buscarBtn.addEventListener('click', function () {
            const cnpjInput = document.getElementById('cnpj');
            const cnpj = cnpjInput.value.replace(/[^0-9]/g, '');

            if (cnpj.length !== 14) {
                alert('Por favor, digite um CNPJ válido com 14 dígitos.');
                return;
            }

            this.textContent = 'Buscando...';
            this.disabled = true;

            // Assumindo que você tem uma rota /consulta/cnpj/{cnpj} que busca os dados
            fetch(`/consulta/cnpj/${cnpj}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('CNPJ não encontrado ou inválido.');
                    }
                    return response.json();
                })
                .then(data => {
                    document.getElementById('razao_social').value = data.razao_social || '';
                    document.getElementById('nome_fantasia').value = data.nome_fantasia || '';
                    document.getElementById('logradouro').value = data.logradouro || '';
                    document.getElementById('numero').value = data.numero || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cep').value = data.cep || '';
                    document.getElementById('municipio').value = data.municipio || '';
                    document.getElementById('uf').value = data.uf || '';
                    document.getElementById('telefone').value = data.ddd_telefone_1 || '';
                    
                    // ===== A CORREÇÃO ESTÁ AQUI =====
                    document.getElementById('codigo_municipio').value = data.codigo_municipio || '';
                    // ===================================
                })
                .catch(error => {
                    alert('Erro ao buscar dados do CNPJ: ' + error.message);
                })
                .finally(() => {
                    this.textContent = 'Buscar';
                    this.disabled = false;
                });
        });
    });
</script>