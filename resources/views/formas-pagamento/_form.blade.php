<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-4">
        <div>
            <x-input-label for="nome" value="Nome / Descrição" />
            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" 
                          :value="old('nome', $formaPagamento->nome ?? '')" required 
                          placeholder="Ex: Cartão 30/60 DDL" />
        </div>
        <div>
            <x-input-label for="codigo_sefaz" value="Código da SEFAZ (tPag)" />
            <x-text-input id="codigo_sefaz" name="codigo_sefaz" type="text" class="mt-1 block w-full" 
                          :value="old('codigo_sefaz', $formaPagamento->codigo_sefaz ?? '')" required 
                          placeholder="Ex: 01, 03, 17" />
            <p class="text-xs text-gray-500 mt-1">01=Dinheiro, 03=Cartão Crédito, 17=PIX, 99=Outros</p>
        </div>
        <div>
            <x-input-label for="tipo" value="Tipo" />
            <select name="tipo" id="tipo" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                <option value="a_vista" @selected(old('tipo', $formaPagamento->tipo ?? '') == 'a_vista')>À Vista</option>
                <option value="a_prazo" @selected(old('tipo', $formaPagamento->tipo ?? '') == 'a_prazo')>A Prazo</option>
            </select>
        </div>
    </div>
    <div class="space-y-4">
        <div>
            <x-input-label for="numero_parcelas" value="Nº de Parcelas" />
            <x-text-input id="numero_parcelas" name="numero_parcelas" type="number" class="mt-1 block w-full" :value="old('numero_parcelas', $formaPagamento->numero_parcelas ?? '1')" required />
        </div>
        <div>
            <x-input-label for="dias_intervalo" value="Intervalo entre Parcelas (dias)" />
            <x-text-input id="dias_intervalo" name="dias_intervalo" type="number" class="mt-1 block w-full" :value="old('dias_intervalo', $formaPagamento->dias_intervalo ?? '30')" required />
        </div>
    </div>
</div>

<div class="mt-6 border-t border-gray-200 dark:border-gray-700 pt-6">
    <label for="ativo" class="flex items-center">
        {{-- 1. Adiciona um campo oculto com valor "0". Ele só será enviado se o checkbox estiver desmarcado. --}}
        <input type="hidden" name="ativo" value="0">
        
        {{-- 2. Define o valor do checkbox como "1". Se estiver marcado, ele sobrescreve o campo oculto. --}}
        <x-checkbox id="ativo" name="ativo" value="1" :checked="old('ativo', $formaPagamento->ativo ?? true)" />

        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Manter Ativo</span>
    </label>
</div>
<div class="flex items-center justify-end mt-8">
    <a href="{{ route('formas-pagamento.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 mr-4">
       Cancelar
   </a>
   <x-primary-button>
       Salvar
   </x-primary-button>
</div>