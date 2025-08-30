<div class="space-y-6">
    <div>
        <x-input-label for="razao_social" value="Razão Social" />
        <x-text-input id="razao_social" name="razao_social" type="text" class="mt-1 block w-full" 
                      :value="old('razao_social', $empresa->razao_social)" required autofocus />
        <x-input-error class="mt-2" :messages="$errors->get('razao_social')" />
    </div>

    <div>
        <x-input-label for="cnpj" value="CNPJ" />
        <x-text-input id="cnpj" name="cnpj" type="text" class="mt-1 block w-full" 
                      :value="old('cnpj', $empresa->cnpj)" required />
        <x-input-error class="mt-2" :messages="$errors->get('cnpj')" />
    </div>

    <div>
        <x-input-label for="nicho_negocio" value="Nicho de Negócio" />
        <select name="nicho_negocio" id="nicho_negocio" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm" required>
            <option value="">Selecione um nicho</option>
            <option value="mercado" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'mercado')>Mercado</option>
            <option value="oficina" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'oficina')>Oficina</option>
            <option value="restaurante" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'restaurante')>Restaurante</option>
            <option value="loja_roupas" @selected(old('nicho_negocio', $empresa->nicho_negocio) == 'loja_roupas')>Loja de Roupas</option>
        </select>
        <x-input-error class="mt-2" :messages="$errors->get('nicho_negocio')" />
    </div>
</div>

<div class="flex items-center justify-end mt-8">
    <a href="{{ route('empresa.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
       Cancelar
   </a>
   <x-primary-button>
       Salvar
   </x-primary-button>
</div>