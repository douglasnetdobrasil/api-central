<x-app-layout>
    {{-- INÍCIO: Importação do CSS para o TomSelect (adicionar no seu layout principal ou aqui) --}}
    <x-slot name="head">
        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.css" rel="stylesheet">
    </x-slot>
    {{-- FIM: Importação do CSS --}}

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Criar Nova Ordem de Serviço') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    
                    {{-- Formulário de Criação --}}
                    <form action="{{ route('ordens-servico.store') }}" method="POST">
                        @csrf  {{-- Token de segurança do Laravel --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- MELHORIA: Campo Cliente com busca --}}
                            <div>
                                <label for="cliente_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente *</label>
                                {{-- A classe 'select-search' será usada pelo nosso JavaScript para ativar a busca --}}
                                <select name="cliente_id" id="cliente_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Selecione ou digite para buscar um cliente...</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{-- MELHORIA: Exibir CPF/CNPJ para diferenciar clientes com nomes parecidos --}}
                                            {{ $cliente->nome }} {{ $cliente->cpf_cnpj ? '('.$cliente->cpf_cnpj.')' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- MELHORIA: Campo Técnico Responsável com busca --}}
                            <div>
                                <label for="tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Técnico Responsável</label>
                                <select name="tecnico_id" id="tecnico_id" class="select-search mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">(Opcional) Atribuir a um técnico...</option>
                                    @foreach ($tecnicos as $tecnico)
                                        <option value="{{ $tecnico->id }}" {{ old('tecnico_id') == $tecnico->id ? 'selected' : '' }}>
                                            {{ $tecnico->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('tecnico_id')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Campo Equipamento --}}
                            <div>
                                <label for="equipamento" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Equipamento *</label>
                                <input type="text" name="equipamento" id="equipamento" value="{{ old('equipamento') }}" placeholder="Ex: Notebook Dell Vostro, iPhone 13 Pro" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                @error('equipamento')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            {{-- NOVO CAMPO: Número de Série --}}
                            <div>
                                <label for="numero_serie" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Nº de Série / IMEI</label>
                                <input type="text" name="numero_serie" id="numero_serie" value="{{ old('numero_serie') }}" placeholder="(Opcional, mas recomendado)" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @error('numero_serie')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Campo Defeito Relatado (ocupando a linha inteira) --}}
                            <div class="md:col-span-2">
                                <label for="defeito_relatado" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Defeito Relatado / Observações Iniciais *</label>
                                <textarea name="defeito_relatado" id="defeito_relatado" rows="4" placeholder="Descreva aqui o problema informado pelo cliente..." class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('defeito_relatado') }}</textarea>
                                @error('defeito_relatado')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                             {{-- MELHORIA: Campo Status com opções mais claras --}}
                             <div>
                                <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status Inicial *</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="Em Aberto" @if(old('status') == 'Em Aberto') selected @endif>Em Aberto</option>
                                    <option value="Aguardando Orçamento" @if(old('status') == 'Aguardando Orçamento') selected @endif>Aguardando Orçamento</option>
                                    <option value="Aguardando Aprovação do Cliente" @if(old('status') == 'Aguardando Aprovação do Cliente') selected @endif>Aguardando Aprovação do Cliente</option>
                                    <option value="Aguardando Peças" @if(old('status') == 'Aguardando Peças') selected @endif>Aguardando Peças</option>
                                </select>
                                @error('status')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                             {{-- MELHORIA: Campo Previsão com data mínima --}}
                             <div>
                                <label for="data_previsao_conclusao" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Previsão de Conclusão</label>
                                <input type="date" name="data_previsao_conclusao" id="data_previsao_conclusao" min="{{ date('Y-m-d') }}" value="{{ old('data_previsao_conclusao') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @error('data_previsao_conclusao')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        {{-- Botões de Ação --}}
                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('ordens-servico.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-800 dark:text-gray-200 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150 mr-4">
                                Cancelar
                            </a>
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700 active:bg-indigo-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                                Criar Ordem de Serviço
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    {{-- MELHORIA: Inclusão de script para a busca nos selects. --}}
    @push('scripts')
        {{-- Importação do JS do TomSelect via CDN --}}
        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
        <script>
            // Inicializa o TomSelect em todos os elementos com a classe '.select-search'
            document.addEventListener('DOMContentLoaded', function() {
                var selects = document.querySelectorAll('.select-search');
                selects.forEach((select) => {
                    new TomSelect(select, {
                        create: false, // Impede que o usuário crie novas opções
                        sortField: {
                            field: "text",
                            direction: "asc"
                        },
                        // Adiciona classes do Tailwind para um visual consistente
                        // (pode requerer ajustes no seu tailwind.config.js)
                        // render: {
                        //     option: function(data, escape) {
                        //         return '<div class="p-2 dark:text-gray-300">' + escape(data.text) + '</div>';
                        //     },
                        //     item: function(data, escape) {
                        //         return '<div class="dark:text-gray-300">' + escape(data.text) + '</div>';
                        //     }
                        // }
                    });
                });
            });
        </script>
    @endpush

</x-app-layout>