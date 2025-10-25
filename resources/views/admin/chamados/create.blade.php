<x-app-layout> {{-- Usa o layout do Admin --}}
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Abrir Novo Chamado (Admin)
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 md:p-8 text-gray-900 dark:text-gray-100">
                    <form action="{{ route('admin.chamados.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="space-y-6">

                            {{-- SELEÇÃO DE CLIENTE --}}
                            <div>
                                <x-input-label for="cliente_id" value="Cliente *" />
                                <select name="cliente_id" id="cliente_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    <option value="">-- Selecione o Cliente --</option>
                                    @foreach($clientes as $cliente)
                                        <option value="{{ $cliente->id }}" {{ old('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                            {{ $cliente->nome }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                            </div>

                            {{-- Título --}}
                            <div>
                                <x-input-label for="titulo" value="Título *" />
                                <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo')" required />
                                <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                            </div>

                            {{-- Prioridade --}}
                            <div>
                                <x-input-label for="prioridade" value="Prioridade *" />
                                <select name="prioridade" id="prioridade" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                    @foreach($prioridades as $p)
                                        <option value="{{ $p }}" {{ old('prioridade', 'Média') == $p ? 'selected' : '' }}>{{ $p }}</option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('prioridade')" class="mt-2" />
                            </div>

                            {{-- Equipamento (Opcional - Carrega dinamicamente) --}}
                            <div>
                                <x-input-label for="cliente_equipamento_id" value="Equipamento (Opcional)" />
                                <select name="cliente_equipamento_id" id="cliente_equipamento_id" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" disabled>
                                    <option value="">-- Selecione o cliente primeiro --</option>
                                    {{-- Opções carregadas via JS --}}
                                </select>
                                {{-- Poderíamos adicionar o botão +Novo aqui também, se desejado --}}
                            </div>

                            {{-- Descrição do Problema --}}
                            <div>
                                <x-input-label for="descricao_problema" value="Descreva o problema *" />
                                <textarea id="descricao_problema" name="descricao_problema" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>{{ old('descricao_problema') }}</textarea>
                                <x-input-error :messages="$errors->get('descricao_problema')" class="mt-2" />
                            </div>

                            {{-- Anexos --}}
                            <div>
                                <x-input-label for="anexos" value="Anexar arquivos (Opcional)" />
                                <input type="file" name="anexos[]" id="anexos" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-gray-700 dark:file:text-gray-300"/>
                                <x-input-error :messages="$errors->get('anexos.*')" class="mt-2" />
                            </div>

                            {{-- Botões Salvar/Cancelar --}}
                            <div class="flex items-center justify-end gap-4 mt-4">
                                <a href="{{ route('admin.chamados.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:underline">
                                    Cancelar
                                </a>
                                <x-primary-button>
                                    Abrir Chamado
                                </x-primary-button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Script para carregar equipamentos do cliente selecionado --}}
    @push('scripts')
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        {{-- Se você usa TomSelect ou Select2, inclua o JS/CSS aqui --}}
        <script>
            $(document).ready(function() {
                const clienteSelect = $('#cliente_id');
                const equipamentoSelect = $('#cliente_equipamento_id');

                // Opcional: inicializar Select2/TomSelect no clienteSelect aqui
                // new TomSelect('#cliente_id',{ /* options */ });

                clienteSelect.on('change', function() {
                    const clienteId = $(this).val();
                    equipamentoSelect.prop('disabled', true).html('<option value="">Carregando...</option>');

                    if (!clienteId) {
                        equipamentoSelect.html('<option value="">-- Selecione o cliente primeiro --</option>');
                        return;
                    }

                    // Busca equipamentos via AJAX (mesma rota usada na OS)
                    fetch(`/os/clientes/${clienteId}/equipamentos`) // Ajuste a URL se necessário
                        .then(response => response.json())
                        .then(data => {
                            equipamentoSelect.prop('disabled', false).html('<option value="">-- Nenhum --</option>');
                            if(data && data.length > 0) {
                                data.forEach(equip => {
                                    equipamentoSelect.append(new Option(equip.texto, equip.id));
                                });
                            } else {
                                equipamentoSelect.append('<option value="" disabled>Nenhum equipamento cadastrado</option>');
                            }
                        })
                        .catch(error => {
                            console.error('Erro ao buscar equipamentos:', error);
                            equipamentoSelect.html('<option value="">Erro ao carregar</option>');
                        });
                });
            });
        </script>
    @endpush

</x-app-layout>