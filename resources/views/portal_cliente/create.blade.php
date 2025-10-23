<x-portal-layout> {{-- Usa o layout do portal --}}

    {{-- 1. ADICIONADO: Inicia Alpine.js para controlar o modal --}}
    <div x-data="{ openModal: false }">

        <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mb-6">
            Abrir Novo Chamado
        </h2>

        {{-- Formulário Principal --}}
        {{-- 2. ADICIONADO: @close-modal.window para fechar o modal via evento JS --}}
        <form action="{{ route('portal.chamados.store') }}" method="POST" enctype="multipart/form-data" @close-modal.window="openModal = false">
            @csrf
            <div class="space-y-6">
                {{-- Título --}}
                <div>
                    <x-input-label for="titulo" value="Título *" />
                    <x-text-input id="titulo" name="titulo" type="text" class="mt-1 block w-full" :value="old('titulo')" required />
                    <x-input-error :messages="$errors->get('titulo')" class="mt-2" />
                </div>
                <div>
                <x-input-label for="prioridade" value="Prioridade *" />
                <select name="prioridade" id="prioridade" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <option value="Baixa" {{ old('prioridade') == 'Baixa' ? 'selected' : '' }}>Baixa</option>
                    <option value="Média" {{ old('prioridade', 'Média') == 'Média' ? 'selected' : '' }}>Média (Padrão)</option> {{-- Padrão selecionado --}}
                    <option value="Alta" {{ old('prioridade') == 'Alta' ? 'selected' : '' }}>Alta</option>
                    <option value="Urgente" {{ old('prioridade') == 'Urgente' ? 'selected' : '' }}>Urgente</option>
                </select>
                <x-input-error :messages="$errors->get('prioridade')" class="mt-2" />
            </div>

                {{-- Equipamento (com botão Novo) --}}
                <div>
                    <x-input-label for="cliente_equipamento_id" value="Equipamento (Opcional)" />
                    <div class="flex items-center gap-2 mt-1">
                        <select name="cliente_equipamento_id" id="cliente_equipamento_id" class="block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="">-- Nenhum --</option>
                            {{-- Equipamentos existentes (carregados pelo Controller) --}}
                            @foreach($equipamentos as $equip)
                                <option value="{{ $equip->id }}" {{ old('cliente_equipamento_id') == $equip->id ? 'selected' : '' }}>
                                    {{ $equip->descricao }} {{ $equip->numero_serie ? '(SN: '.$equip->numero_serie.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                        {{-- Botão que abre o modal --}}
                        {{-- 3. CORRIGIDO: @click para funcionar com Alpine --}}
                        <button type="button" 
        @click.prevent="openModal = true; alert('Alpine click funcionou!');" {{-- <-- ADICIONE O ALERT AQUI --}}
        class="flex-shrink-0 px-4 py-2 bg-green-600 text-white rounded-md shadow-sm text-sm font-medium hover:bg-green-700">
    + Novo
</button>
                    </div>
                </div>

                {{-- Descrição do Problema --}}
                <div>
                    <x-input-label for="descricao_problema" value="Descreva o problema *" />
                    <textarea id="descricao_problema" name="descricao_problema" rows="8" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>{{ old('descricao_problema') }}</textarea>
                    <x-input-error :messages="$errors->get('descricao_problema')" class="mt-2" />
                </div>

                {{-- Anexos --}}
                <div>
                    <x-input-label for="anexos" value="Anexar arquivos (Opcional)" />
                    <input type="file" name="anexos[]" id="anexos" multiple class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 dark:file:bg-gray-700 dark:file:text-gray-300"/>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Você pode enviar vários prints ou fotos (JPG, PNG, PDF).</p>
                    <x-input-error :messages="$errors->get('anexos.*')" class="mt-2" />
                </div>

                {{-- Botão Salvar --}}
                <div class="flex justify-end">
                    <x-primary-button>
                        Abrir Chamado
                    </x-primary-button>
                </div>
            </div>
        </form>


        {{-- ====================================================== --}}
        {{-- ||||||||||||||| MODAL NOVO EQUIPAMENTO (HTML) ||||||||||||||| --}}
        {{-- ====================================================== --}}
        {{-- 4. CORRIGIDO: Atributos Alpine x-show e @keydown --}}
        <div x-show="openModal" @keydown.escape.window="openModal = false" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">

                {{-- Background Overlay (Alpine) --}}
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 transition-opacity" @click="openModal = false">
                    <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
                </div>

                {{-- Conteúdo do Modal (Alpine) --}}
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen"></span>&#8203;
                <div x-show="openModal" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white dark:bg-gray-800 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">

                    <div id="form_novo_equipamento"> {{-- ID usado pelo JS --}}
                        <div class="bg-white dark:bg-gray-800 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100">
                                Cadastrar Novo Equipamento
                            </h3>
                            <div class="mt-4 space-y-4">
                                <div id="modal_error_message" class="hidden p-3 bg-red-100 text-red-700 border border-red-300 rounded-md"></div>

                                <div>
                                    <x-input-label for="modal_descricao" value="Descrição (Ex: Notebook Dell Vostro) *" />
                                    <x-text-input type="text" id="modal_descricao" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <x-input-label for="modal_numero_serie" value="Nº de Série / IMEI" />
                                    <x-text-input type="text" id="modal_numero_serie" class="mt-1 block w-full" />
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <x-input-label for="modal_marca" value="Marca" />
                                        <x-text-input type="text" id="modal_marca" class="mt-1 block w-full" />
                                    </div>
                                    <div>
                                        <x-input-label for="modal_modelo" value="Modelo" />
                                        <x-text-input type="text" id="modal_modelo" class="mt-1 block w-full" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-gray-700 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            {{-- 5. CORRIGIDO: onclick chama a função JS correta --}}
                            <button type="button" id="btn_salvar_modal" onclick="salvarNovoEquipamentoPortal()"
                                    class="w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Salvar
                            </button>
                            {{-- 6. CORRIGIDO: @click para fechar via Alpine --}}
                            <button type="button" @click="openModal = false"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border shadow-sm px-4 py-2 bg-white dark:bg-gray-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Cancelar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div> {{-- Fim do x-data --}}


    {{-- ====================================================== --}}
    {{-- ||||||||||||||| SCRIPT DO MODAL (ADAPTADO DA OS) ||||||||||||||| --}}
    {{-- ====================================================== --}}
    {{-- 7. ADICIONADO: @push('scripts') para garantir que rode após o jQuery --}}
    @push('scripts')
        {{-- 8. ADICIONADO: Inclusão do jQuery (necessário para $.ajax) --}}
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

        <script>
            /**
             * Função para salvar o novo equipamento (via modal)
             * Adaptada do create.blade.php da OS
             * Usa jQuery/AJAX
             */
            function salvarNovoEquipamentoPortal() { // Nome da função alterado para evitar conflito
                var btnSalvar = $('#btn_salvar_modal'); // ID do botão no modal
                var errorDiv = $('#modal_error_message'); // ID da div de erro no modal
                btnSalvar.prop('disabled', true).text('Salvando...');
                errorDiv.hide().empty();

                $.ajax({
                    url: '{{ route("portal.equipamentos.storeModal") }}', // Rota CORRETA do Portal
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        // IDs dos campos DENTRO do modal
                        descricao: $('#modal_descricao').val(),
                        numero_serie: $('#modal_numero_serie').val(),
                        marca: $('#modal_marca').val(),
                        modelo: $('#modal_modelo').val()
                    },
                    success: function(equipamento) {
                        // 'equipamento' retorna { id: 123, texto: "Descricao (SN: 123)" }

                        const selectEquipamento = document.getElementById('cliente_equipamento_id'); // ID do select PRINCIPAL

                        // Adiciona a nova opção no select e já a seleciona (JavaScript Puro)
                        const newOption = document.createElement('option');
                        newOption.value = equipamento.id;
                        newOption.text = equipamento.texto;
                        selectEquipamento.appendChild(newOption);
                        selectEquipamento.value = equipamento.id; // Seleciona o novo

                        // Dispara evento para fechar o modal via Alpine.js
                        window.dispatchEvent(new Event('close-modal'));

                        // Limpa os campos do modal (jQuery)
                        $('#modal_descricao, #modal_numero_serie, #modal_marca, #modal_modelo').val('');
                        btnSalvar.prop('disabled', false).text('Salvar');
                    },
                    error: function(xhr) {
                        // Exibe os erros de validação (jQuery)
                        var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        if(errors){
                            var errorMsg = "Ocorreram erros:<ul>";
                            $.each(errors, function(key, value) {
                                errorMsg += "<li>" + value[0] + "</li>";
                            });
                            errorMsg += "</ul>";
                            errorDiv.html(errorMsg).show();
                        } else {
                            errorDiv.html('Erro ao salvar. Tente novamente.').show();
                        }
                        btnSalvar.prop('disabled', false).text('Salvar');
                    }
                });
            }
        </script>
    @endpush
</x-portal-layout>