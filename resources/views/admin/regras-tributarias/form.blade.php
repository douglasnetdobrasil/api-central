<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{-- A variável agora é $regra --}}
            {{ isset($regra) && $regra->exists ? 'Editar Regra Tributária' : 'Criar Nova Regra Tributária' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{-- As rotas de action agora apontam para o novo controller --}}
                    <form action="{{ isset($regra) && $regra->exists ? route('admin.regras-tributarias.update', $regra->id) : route('admin.regras-tributarias.store') }}" method="POST">
                        @csrf
                        @if(isset($regra) && $regra->exists)
                            @method('PUT')
                        @endif

                        {{-- SEÇÃO DE INFORMAÇÕES GERAIS --}}
                        <div class="p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium border-b dark:border-gray-700 pb-2 mb-4">Informações Gerais</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="md:col-span-2">
                                    <x-input-label for="descricao" value="Descrição da Regra (Ex: Venda para consumidor final dentro do estado)" />
                                    <x-text-input id="descricao" name="descricao" type="text" class="mt-1 block w-full" :value="old('descricao', $regra->descricao ?? '')" required />
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO DE GATILHOS (QUANDO A REGRA SE APLICA?) --}}
                        <div class="mt-6 p-4 border dark:border-gray-700 rounded-lg">
                            <h3 class="text-lg font-medium border-b dark:border-gray-700 pb-2 mb-4">Gatilhos da Operação</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <x-input-label for="cfop" value="CFOP" />
                                    <x-text-input id="cfop" name="cfop" type="text" class="mt-1 block w-full" :value="old('cfop', $regra->cfop ?? '')" required placeholder="5102" />
                                </div>
                                <div>
                                    <x-input-label for="crt_emitente" value="CRT do Emitente" />
                                    <select id="crt_emitente" name="crt_emitente" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                        <option value="">Todos os Regimes</option>
                                        <option value="1" @selected(old('crt_emitente', $regra->crt_emitente ?? '') == 1)>1 - Simples Nacional</option>
                                        <option value="3" @selected(old('crt_emitente', $regra->crt_emitente ?? '') == 3)>3 - Regime Normal</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="uf_origem" value="UF de Origem" />
                                    {{-- Idealmente, preencher com um array de UFs --}}
                                    <select id="uf_origem" name="uf_origem" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                        <option value="">Todas as UFs</option>
                                        {{-- Adicionar <option> para cada estado --}}
                                    </select>
                                </div>
                                 <div>
                                    <x-input-label for="uf_destino" value="UF de Destino" />
                                    <select id="uf_destino" name="uf_destino" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                         <option value="">Todas as UFs</option>
                                        {{-- Adicionar <option> para cada estado --}}
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- SEÇÃO DE RESULTADOS (IMPOSTOS) --}}
<div class="mt-6 p-4 border dark:border-gray-700 rounded-lg">
    <h3 class="text-lg font-medium border-b dark:border-gray-700 pb-2 mb-4">Impostos a Serem Aplicados</h3>
    
    {{-- ICMS --}}
    <div class="mt-4 p-3 border rounded dark:border-gray-600">
        <h4 class="font-semibold">ICMS</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mt-2">
            <div><x-input-label value="Origem Merc." /><x-text-input name="icms_origem" class="w-full" :value="old('icms_origem', $regra->icms_origem ?? '0')" /></div>
            <div><x-input-label value="CSOSN (Simples)" /><x-text-input name="csosn" class="w-full" :value="old('csosn', $regra->csosn ?? '')" /></div>
            <div><x-input-label value="CST (Normal)" /><x-text-input name="icms_cst" class="w-full" :value="old('icms_cst', $regra->icms_cst ?? '')" /></div>
            <div><x-input-label value="Modalidade BC" /><x-text-input name="icms_mod_bc" class="w-full" :value="old('icms_mod_bc', $regra->icms_mod_bc ?? '')" /></div>
            <div><x-input-label value="Redução BC %" /><x-text-input name="icms_reducao_bc" type="number" step="0.01" class="w-full" :value="old('icms_reducao_bc', $regra->icms_reducao_bc ?? '0.00')" /></div>
            <div><x-input-label value="Alíquota ICMS %" /><x-text-input name="icms_aliquota" type="number" step="0.01" class="w-full" :value="old('icms_aliquota', $regra->icms_aliquota ?? '0.00')" /></div>
        </div>
    </div>

    {{-- ICMS-ST --}}
    <div class="mt-4 p-3 border rounded dark:border-gray-600">
        <h4 class="font-semibold">ICMS-ST (Substituição Tributária)</h4>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-2">
            <div><x-input-label value="Modalidade BC ST" /><x-text-input name="icms_mod_bc_st" class="w-full" :value="old('icms_mod_bc_st', $regra->icms_mod_bc_st ?? '')" /></div>
            <div><x-input-label value="MVA/IVA %" /><x-text-input name="mva_st" type="number" step="0.01" class="w-full" :value="old('mva_st', $regra->mva_st ?? '0.00')" /></div>
            <div><x-input-label value="Alíquota ICMS ST %" /><x-text-input name="icms_aliquota_st" type="number" step="0.01" class="w-full" :value="old('icms_aliquota_st', $regra->icms_aliquota_st ?? '0.00')" /></div>
        </div>
    </div>

    {{-- IPI --}}
    <div class="mt-4 p-3 border rounded dark:border-gray-600">
        <h4 class="font-semibold">IPI (Imposto sobre Produtos Industrializados)</h4>
        <div class="grid grid-cols-2 md:grid-cols-2 gap-4 mt-2">
            <div><x-input-label value="CST IPI" /><x-text-input name="ipi_cst" class="w-full" :value="old('ipi_cst', $regra->ipi_cst ?? '')" /></div>
            <div><x-input-label value="Alíquota IPI %" /><x-text-input name="ipi_aliquota" type="number" step="0.01" class="w-full" :value="old('ipi_aliquota', $regra->ipi_aliquota ?? '0.00')" /></div>
        </div>
    </div>
    
    {{-- PIS/COFINS --}}
     <div class="mt-4 p-3 border rounded dark:border-gray-600">
        <h4 class="font-semibold">PIS / COFINS</h4>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-2">
            <div><x-input-label value="CST PIS" /><x-text-input name="pis_cst" class="w-full" :value="old('pis_cst', $regra->pis_cst ?? '')" /></div>
            <div><x-input-label value="Alíquota PIS %" /><x-text-input name="pis_aliquota" type="number" step="0.01" class="w-full" :value="old('pis_aliquota', $regra->pis_aliquota ?? '0.00')" /></div>
            <div><x-input-label value="CST COFINS" /><x-text-input name="cofins_cst" class="w-full" :value="old('cofins_cst', $regra->cofins_cst ?? '')" /></div>
            <div><x-input-label value="Alíquota COFINS %" /><x-text-input name="cofins_aliquota" type="number" step="0.01" class="w-full" :value="old('cofins_aliquota', $regra->cofins_aliquota ?? '0.00')" /></div>
        </div>
    </div>
</div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('admin.regras-tributarias.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                            <x-primary-button class="ml-4">
                                Salvar Regra
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>