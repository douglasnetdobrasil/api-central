<x-app-layout>
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
                        @csrf  {{-- Token de segurança do Laravel, essencial para forms --}}

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            
                            {{-- Campo Cliente --}}
                            <div>
                                <label for="cliente_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Cliente *</label>
                                <select name="cliente_id" id="cliente_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="">Selecione um cliente</option>
                                    @foreach ($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cliente_id')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                            {{-- Campo Técnico Responsável --}}
                            <div>
                                <label for="tecnico_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Técnico Responsável</label>
                                <select name="tecnico_id" id="tecnico_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                    <option value="">(Opcional) Selecione um técnico</option>
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
                            <div class="md:col-span-2">
                                <label for="equipamento" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Equipamento *</label>
                                <input type="text" name="equipamento" id="equipamento" value="{{ old('equipamento') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                @error('equipamento')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>
                            
                            {{-- Campo Defeito Relatado --}}
                            <div class="md:col-span-2">
                                <label for="defeito_relatado" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Defeito Relatado *</label>
                                <textarea name="defeito_relatado" id="defeito_relatado" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>{{ old('defeito_relatado') }}</textarea>
                                @error('defeito_relatado')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                             {{-- Campo Status --}}
                             <div>
                                <label for="status" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Status Inicial *</label>
                                <select name="status" id="status" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                                    <option value="Aberta" selected>Aberta</option>
                                    <option value="Aguardando Aprovação">Aguardando Aprovação</option>
                                    <option value="Aguardando Peças">Aguardando Peças</option>
                                </select>
                                @error('status')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                             {{-- Campo Previsão de Conclusão --}}
                             <div>
                                <label for="data_previsao_conclusao" class="block font-medium text-sm text-gray-700 dark:text-gray-300">Previsão de Conclusão</label>
                                <input type="date" name="data_previsao_conclusao" id="data_previsao_conclusao" value="{{ old('data_previsao_conclusao') }}" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                @error('data_previsao_conclusao')
                                    <span class="text-red-500 text-sm mt-1">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>

                        {{-- Botões de Ação --}}
                        <div class="mt-6 flex justify-end">
                            <a href="{{ route('ordens-servico.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest mr-4">
                                Cancelar
                            </a>
                            <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-md font-semibold text-xs uppercase tracking-widest">
                                Salvar
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>