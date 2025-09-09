<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $perfil->exists ? 'Editar Perfil Fiscal' : 'Criar Novo Perfil Fiscal' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{ $perfil->exists ? route('admin.perfis-fiscais.update', $perfil->id) : route('admin.perfis-fiscais.store') }}" method="POST">
                        @csrf
                        @if($perfil->exists)
                            @method('PUT')
                        @endif

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <x-input-label for="nome_perfil" value="Nome do Perfil *" />
                                <x-text-input id="nome_perfil" name="nome_perfil" type="text" class="mt-1 block w-full" :value="old('nome_perfil', $perfil->nome_perfil)" required />
                            </div>

                            <hr class="md:col-span-2 my-2 border-gray-200 dark:border-gray-700">

                            <div>
                                <x-input-label for="cfop_padrao" value="CFOP Padrão (Venda Estadual)" />
                                <x-text-input id="cfop_padrao" name="cfop_padrao" type="text" class="mt-1 block w-full" :value="old('cfop_padrao', $perfil->cfop_padrao)" placeholder="Ex: 5102" />
                            </div>
                            <div>
                                <x-input-label for="ncm_padrao" value="NCM Padrão" />
                                <x-text-input id="ncm_padrao" name="ncm_padrao" type="text" class="mt-1 block w-full" :value="old('ncm_padrao', $perfil->ncm_padrao)" placeholder="Apenas se XML não tiver" />
                            </div>
                             <div>
                                <x-input-label for="csosn_padrao" value="CSOSN Padrão (Simples Nacional)" />
                                <x-text-input id="csosn_padrao" name="csosn_padrao" type="text" class="mt-1 block w-full" :value="old('csosn_padrao', $perfil->csosn_padrao)" placeholder="Ex: 102" />
                            </div>
                            <div>
                                <x-input-label for="icms_cst_padrao" value="CST ICMS Padrão (Regime Normal)" />
                                <x-text-input id="icms_cst_padrao" name="icms_cst_padrao" type="text" class="mt-1 block w-full" :value="old('icms_cst_padrao', $perfil->icms_cst_padrao)" placeholder="Ex: 00" />
                            </div>
                            <div>
                                <x-input-label for="pis_cst_padrao" value="CST PIS Padrão" />
                                <x-text-input id="pis_cst_padrao" name="pis_cst_padrao" type="text" class="mt-1 block w-full" :value="old('pis_cst_padrao', $perfil->pis_cst_padrao)" placeholder="Ex: 07" />
                            </div>
                             <div>
                                <x-input-label for="cofins_cst_padrao" value="CST COFINS Padrão" />
                                <x-text-input id="cofins_cst_padrao" name="cofins_cst_padrao" type="text" class="mt-1 block w-full" :value="old('cofins_cst_padrao', $perfil->cofins_cst_padrao)" placeholder="Ex: 07" />
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('admin.perfis-fiscais.index') }}" class="text-sm text-gray-600 hover:underline">Cancelar</a>
                            <x-primary-button class="ml-4">
                                Salvar Perfil
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>