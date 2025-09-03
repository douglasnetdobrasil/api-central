<x-app-layout>
    <x-slot name="header">
        {{-- CORREÇÃO LÓGICA: Usando $produto->exists para o título --}}
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ $produto->exists ? 'Editar Produto' : 'Cadastrar Novo Produto' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    @if ($errors->any())
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                            <strong class="font-bold">Ocorreu um Erro!</strong>
                            <span class="block sm:inline">{{ session('error') }}</span>
                        </div>
                    @endif

                    {{-- ===================================================================== --}}
                    {{-- |||||||||||||||||||||||||| ÁREA CORRIGIDA |||||||||||||||||||||||||| --}}
                    {{-- ===================================================================== --}}
                    @if ($produto->exists)
                        {{-- Formulário para EDITAR um produto existente --}}
                        <form action="{{ route('produtos.update', $produto) }}" method="POST">
                            @method('PUT')
                    @else
                        {{-- Formulário para CRIAR um novo produto --}}
                        <form action="{{ route('produtos.store') }}" method="POST">
                    @endif
                        @csrf
                        {{-- ===================================================================== --}}
                        {{-- |||||||||||||||||||||||| FIM DA ÁREA CORRIGIDA ||||||||||||||||||||| --}}
                        {{-- ===================================================================== --}}


                        {{-- DADOS PRINCIPAIS --}}
                        <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Dados Principais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                            <div>
                                <x-input-label for="nome" value="Nome do Produto" />
                                <x-text-input id="nome" name="nome" type="text" class="mt-1 block w-full" :value="old('nome', $produto->nome ?? '')" required />
                            </div>
                            <div>
                                <x-input-label for="categoria_id" value="Categoria" />
                                <select id="categoria_id" name="categoria_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                                    <option value="">Selecione...</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria->id }}" @selected(old('categoria_id', $produto->categoria_id ?? '') == $categoria->id)>
                                            {{ $categoria->nome }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- PREÇOS E ESTOQUE --}}
                        <h3 class="text-lg font-medium mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">Preços e Estoque</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-4">
                            <div>
                                <x-input-label for="preco_custo" value="Preço de Custo" />
                                <x-text-input id="preco_custo" name="preco_custo" type="number" step="0.01" class="mt-1 block w-full" :value="old('preco_custo', $produto->preco_custo ?? '0.00')" />
                            </div>
                            <div>
                                <x-input-label for="preco_venda" value="Preço de Venda" />
                                <x-text-input id="preco_venda" name="preco_venda" type="number" step="0.01" class="mt-1 block w-full" :value="old('preco_venda', $produto->preco_venda ?? '0.00')" required />
                            </div>
                            <div>
                                <x-input-label for="estoque_atual" value="Estoque Atual" />
                                <x-text-input id="estoque_atual" name="estoque_atual" type="number" step="0.001" class="mt-1 block w-full" :value="old('estoque_atual', $produto->estoque_atual ?? '0')" />
                            </div>
                        </div>

                        {{-- DADOS FISCAIS (O restante do arquivo continua igual) --}}
                        <h3 class="text-lg font-medium mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">Dados Fiscais</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mt-4">
                            <div>
                                <x-input-label for="codigo_barras" value="Código de Barras (GTIN/EAN)" />
                                <x-text-input id="codigo_barras" name="codigo_barras" type="text" class="mt-1 block w-full" :value="old('codigo_barras', $produto->codigo_barras ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="ncm" value="NCM" />
                                <x-text-input id="ncm" name="fiscal[ncm]" type="text" class="mt-1 block w-full" :value="old('fiscal.ncm', $produto->dadosFiscais->ncm ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="cest" value="CEST" />
                                <x-text-input id="cest" name="fiscal[cest]" type="text" class="mt-1 block w-full" :value="old('fiscal.cest', $produto->dadosFiscais->cest ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="cfop" value="CFOP (Padrão de Saída)" />
                                <x-text-input id="cfop" name="fiscal[cfop]" type="text" class="mt-1 block w-full" :value="old('fiscal.cfop', $produto->dadosFiscais->cfop ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="origem" value="Origem" />
                                <x-text-input id="origem" name="fiscal[origem]" type="text" class="mt-1 block w-full" :value="old('fiscal.origem', $produto->dadosFiscais->origem ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="icms_cst" value="ICMS CST" />
                                <x-text-input id="icms_cst" name="fiscal[icms_cst]" type="text" class="mt-1 block w-full" :value="old('fiscal.icms_cst', $produto->dadosFiscais->icms_cst ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="pis_cst" value="PIS CST" />
                                <x-text-input id="pis_cst" name="fiscal[pis_cst]" type="text" class="mt-1 block w-full" :value="old('fiscal.pis_cst', $produto->dadosFiscais->pis_cst ?? '')" />
                            </div>
                            <div>
                                <x-input-label for="cofins_cst" value="COFINS CST" />
                                <x-text-input id="cofins_cst" name="fiscal[cofins_cst]" type="text" class="mt-1 block w-full" :value="old('fiscal.cofins_cst', $produto->dadosFiscais->cofins_cst ?? '')" />
                            </div>
                             <div>
                                <x-input-label for="csosn" value="CSOSN" />
                                <x-text-input id="csosn" name="fiscal[csosn]" type="text" class="mt-1 block w-full" :value="old('fiscal.csosn', $produto->dadosFiscais->csosn ?? '')" />
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-end mt-8">
                            <a href="{{ route('produtos.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline mr-4">
                                Cancelar
                            </a>
                            <x-primary-button>
                                Salvar Produto
                            </x-primary-button>
                        </div>
                    </form> {{-- A tag de fechamento agora fica aqui no final --}}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>