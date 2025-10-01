<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ isset($conta) && $conta->id ? 'Editar Conta a Pagar' : 'Criar Nova Conta a Pagar' }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    
                    <form action="{{ isset($conta) && $conta->id ? route('contas_a_pagar.update', $conta->id) : route('contas_a_pagar.store') }}" method="POST">
                        @csrf
                        @if(isset($conta) && $conta->id)
                            @method('PUT')
                        @endif

                        {{-- Exibição de Erros de Validação --}}
                        @if ($errors->any())
                            <div class="mb-4 p-4 bg-red-100 text-red-700 border border-red-400 rounded">
                                <strong class="font-bold">Ops! Ocorreram alguns erros:</strong>
                                <ul class="list-disc list-inside mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="space-y-6">
                            {{-- Descrição --}}
                            <div>
                                <label for="descricao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descrição</label>
                                <input type="text" name="descricao" id="descricao" value="{{ old('descricao', $conta->descricao ?? '') }}" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            </div>

                            {{-- Fornecedor --}}
                            <div>
                                <label for="fornecedor_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Fornecedor (Opcional)</label>
                                <select name="fornecedor_id" id="fornecedor_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                    <option value="">Nenhum fornecedor</option>
                                    @foreach($fornecedores as $fornecedor)
                                        <option value="{{ $fornecedor->id }}" @selected(old('fornecedor_id', $conta->fornecedor_id ?? '') == $fornecedor->id)>
                                            {{ $fornecedor->razao_social }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                {{-- Número do Documento --}}
                                <div>
                                    <label for="numero_documento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nº do Documento</label>
                                    <input type="text" name="numero_documento" id="numero_documento" value="{{ old('numero_documento', $conta->numero_documento ?? '') }}" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                </div>
                                <div>
    <label for="categoria_conta_a_pagar_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoria (Opcional)</label>
    <select name="categoria_conta_a_pagar_id" id="categoria_conta_a_pagar_id" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
        <option value="">Nenhuma categoria</option>
        @foreach($categorias as $categoria)
            <option value="{{ $categoria->id }}" @selected(old('categoria_conta_a_pagar_id', $conta->categoria_conta_a_pagar_id ?? '') == $categoria->id)>
                {{-- Lógica para exibir hierarquia vai aqui --}}
                {{ $categoria->nome }}
            </option>
        @endforeach
    </select>
</div>

                                {{-- Data de Emissão --}}
                                <div>
                                    <label for="data_emissao" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Emissão</label>
                                    <input type="date" name="data_emissao" id="data_emissao" value="{{ old('data_emissao', \Carbon\Carbon::parse($conta->data_emissao ?? now())->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                </div>
                                {{-- Data de Vencimento --}}
                                <div>
                                    <label for="data_vencimento" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Data de Vencimento</label>
                                    <input type="date" name="data_vencimento" id="data_vencimento" value="{{ old('data_vencimento', \Carbon\Carbon::parse($conta->data_vencimento ?? now()->addDays(30))->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                                </div>
                            </div>
                            
                             {{-- Valor Total --}}
                            <div>
                                <label for="valor_total" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Valor Total (R$)</label>
                                <input type="number" step="0.01" name="valor_total" id="valor_total" value="{{ old('valor_total', $conta->valor_total ?? '') }}" required class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">
                            </div>
                            
                            {{-- Observações --}}
                            <div>
                                <label for="observacoes" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observações</glabel>
                                <textarea name="observacoes" id="observacoes" rows="3" class="mt-1 block w-full rounded-md dark:bg-gray-900 border-gray-300 dark:border-gray-600">{{ old('observacoes', $conta->observacoes ?? '') }}</textarea>
                            </div>
                        </div>

                        <div class="flex items-center justify-end mt-8 pt-6 border-t dark:border-gray-700">
                            <a href="{{ route('contas_a_pagar.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">Cancelar</a>
                            <button type="submit" class="ml-4 bg-indigo-600 text-white px-4 py-2 rounded-md">
                                Salvar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>