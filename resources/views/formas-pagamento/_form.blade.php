<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="space-y-4">
        <div>
            <x-input-label for="nome" value="Nome / Descrição" />
            <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" 
                          :value="old('nome', $formaPagamento->nome ?? '')" required 
                          placeholder="Ex: Cartão 30/60 DDL" />
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
        <x-checkbox id="ativo" name="ativo" :checked="old('ativo', $formaPagamento->ativo ?? true)" />
        <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Manter Ativo</span>
    </label>
</div>

<div class="flex items-center justify-end mt-8">
    <a href="{{ route('formas-pagamento.index') }}" class="text-sm ... mr-4">
       Cancelar
   </a>
   <x-primary-button>
       Salvar
   </primary-button>
</div>